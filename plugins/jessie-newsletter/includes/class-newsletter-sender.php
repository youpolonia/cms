<?php
declare(strict_types=1);

class NewsletterSender
{
    private const BATCH_SIZE = 50;
    private const BATCH_DELAY_MS = 500;

    /**
     * Send a campaign to all subscribers on its list.
     */
    public static function send(int $campaignId): array
    {
        $campaign = \NewsletterCampaign::get($campaignId);
        if (!$campaign) return ['ok' => false, 'error' => 'Campaign not found'];
        if (!$campaign['list_id']) return ['ok' => false, 'error' => 'No list assigned'];
        if (!$campaign['subject'] || !$campaign['content_html']) return ['ok' => false, 'error' => 'Subject and content required'];

        $pdo = db();
        $pdo->prepare("UPDATE newsletter_campaigns SET status = 'sending', started_at = NOW() WHERE id = ?")->execute([$campaignId]);

        $totalSent = 0; $totalFailed = 0;
        $offset = 0;

        while (true) {
            $subscribers = \NewsletterSubscriber::getForCampaign($campaign['list_id'], self::BATCH_SIZE, $offset);
            if (empty($subscribers)) break;

            foreach ($subscribers as $sub) {
                $html = \NewsletterCampaign::personalize($campaign['content_html'], $sub, $campaign);
                // Add tracking pixel
                $html .= '<img src="/api/newsletter/track/open?cid=' . $campaignId . '&sid=' . $sub['id'] . '" width="1" height="1" style="display:none" alt="">';

                $success = self::sendEmail(
                    $sub['email'],
                    $campaign['subject'],
                    $html,
                    $campaign['from_name'],
                    $campaign['from_email'],
                    $campaign['reply_to']
                );

                if ($success) {
                    $totalSent++;
                    self::logEvent($campaignId, $sub['id'], 'sent');
                } else {
                    $totalFailed++;
                }
            }

            $offset += self::BATCH_SIZE;
            if (count($subscribers) < self::BATCH_SIZE) break;
            usleep(self::BATCH_DELAY_MS * 1000);
        }

        $pdo->prepare("UPDATE newsletter_campaigns SET status = 'sent', completed_at = NOW(), stats_sent = ? WHERE id = ?")
            ->execute([$totalSent, $campaignId]);

        if (function_exists('cms_event')) {
            cms_event('newsletter.campaign.sent', ['campaign_id' => $campaignId, 'sent' => $totalSent, 'failed' => $totalFailed]);
        }

        return ['ok' => true, 'sent' => $totalSent, 'failed' => $totalFailed];
    }

    /**
     * Send a test email.
     */
    public static function sendTest(int $campaignId, string $testEmail): array
    {
        $campaign = \NewsletterCampaign::get($campaignId);
        if (!$campaign) return ['ok' => false, 'error' => 'Campaign not found'];

        $testSub = ['id' => 0, 'email' => $testEmail, 'name' => 'Test Subscriber'];
        $html = \NewsletterCampaign::personalize($campaign['content_html'], $testSub, $campaign);

        $success = self::sendEmail($testEmail, '[TEST] ' . $campaign['subject'], $html, $campaign['from_name'], $campaign['from_email'], $campaign['reply_to']);
        return ['ok' => $success, 'error' => $success ? null : 'Failed to send test email'];
    }

    /**
     * Process scheduled campaigns (called by cron).
     */
    public static function processScheduled(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT id FROM newsletter_campaigns WHERE status = 'scheduled' AND scheduled_at <= NOW()");
        $campaigns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $results = [];
        foreach ($campaigns as $cid) {
            $results[$cid] = self::send((int)$cid);
        }
        return ['ok' => true, 'processed' => count($campaigns), 'results' => $results];
    }

    /**
     * Track email open.
     */
    public static function trackOpen(int $campaignId, int $subscriberId): void
    {
        $pdo = db();
        // Deduplicate: only count first open
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_events WHERE campaign_id = ? AND subscriber_id = ? AND event_type = 'opened'");
        $stmt->execute([$campaignId, $subscriberId]);
        if ((int)$stmt->fetchColumn() === 0) {
            self::logEvent($campaignId, $subscriberId, 'opened');
            $pdo->prepare("UPDATE newsletter_campaigns SET stats_opened = stats_opened + 1 WHERE id = ?")->execute([$campaignId]);
        }
    }

    /**
     * Track link click.
     */
    public static function trackClick(int $campaignId, int $subscriberId, string $url): void
    {
        $pdo = db();
        self::logEvent($campaignId, $subscriberId, 'clicked', ['url' => $url]);
        // Deduplicate clicks per subscriber
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_events WHERE campaign_id = ? AND subscriber_id = ? AND event_type = 'clicked'");
        $stmt->execute([$campaignId, $subscriberId]);
        if ((int)$stmt->fetchColumn() <= 1) { // first click
            $pdo->prepare("UPDATE newsletter_campaigns SET stats_clicked = stats_clicked + 1 WHERE id = ?")->execute([$campaignId]);
        }
    }

    /**
     * Send email via PHP mail() or SMTP (extensible).
     */
    private static function sendEmail(string $to, string $subject, string $htmlBody, string $fromName = '', string $fromEmail = '', string $replyTo = ''): bool
    {
        $fromEmail = $fromEmail ?: 'newsletter@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $fromName = $fromName ?: 'Newsletter';

        $boundary = md5(uniqid());
        $headers = [
            "From: {$fromName} <{$fromEmail}>",
            "Reply-To: " . ($replyTo ?: $fromEmail),
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
            "X-Mailer: Jessie-Newsletter/1.0",
        ];

        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));
        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . "--{$boundary}--";

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private static function logEvent(int $campaignId, int $subscriberId, string $type, array $metadata = []): void
    {
        $pdo = db();
        $pdo->prepare("INSERT INTO newsletter_events (campaign_id, subscriber_id, event_type, metadata) VALUES (?, ?, ?, ?)")
            ->execute([$campaignId, $subscriberId, $type, $metadata ? json_encode($metadata) : null]);
    }
}
