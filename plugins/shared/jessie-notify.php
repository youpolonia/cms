<?php
/**
 * Jessie CMS — Notification Helper
 * Centralized email/event notifications for all plugins
 * 
 * Usage:
 *   JessieNotify::email($to, $subject, $body);
 *   JessieNotify::emailHtml($to, $subject, $htmlBody);
 *   JessieNotify::event('booking.created', ['id' => 123]);
 */
class JessieNotify
{
    /**
     * Send plain-text email
     */
    public static function email(string $to, string $subject, string $body, array $opts = []): bool
    {
        $fromName = $opts['from_name'] ?? self::setting('site_name', 'Jessie CMS');
        $fromEmail = $opts['from_email'] ?? self::setting('notification_email', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $replyTo = $opts['reply_to'] ?? $fromEmail;

        $headers = [
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $replyTo,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: JessieCMS',
        ];

        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        
        // Log
        self::log($sent ? 'sent' : 'failed', $to, $subject);
        
        // Fire CMS event if available
        if (function_exists('cms_event')) {
            cms_event($sent ? 'email.sent' : 'email.failed', [
                'to' => $to, 'subject' => $subject, 'type' => 'plain'
            ]);
        }

        return $sent;
    }

    /**
     * Send HTML email with inline styles (dark theme)
     */
    public static function emailHtml(string $to, string $subject, string $htmlContent, array $opts = []): bool
    {
        $fromName = $opts['from_name'] ?? self::setting('site_name', 'Jessie CMS');
        $fromEmail = $opts['from_email'] ?? self::setting('notification_email', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $siteName = self::setting('site_name', 'Jessie CMS');
        $siteUrl = self::setting('site_url', 'http://localhost');

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>'
            . '<body style="margin:0;padding:0;background:#0f172a;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif">'
            . '<div style="max-width:600px;margin:0 auto;padding:32px 20px">'
            . '<div style="background:#1e293b;border:1px solid #334155;border-radius:16px;overflow:hidden">'
            . '<div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:24px 32px;text-align:center">'
            . '<h1 style="color:#fff;margin:0;font-size:1.4rem">' . htmlspecialchars($siteName) . '</h1></div>'
            . '<div style="padding:32px;color:#e2e8f0;font-size:.95rem;line-height:1.6">' . $htmlContent . '</div>'
            . '<div style="padding:16px 32px;border-top:1px solid #334155;text-align:center;font-size:.75rem;color:#64748b">'
            . '<a href="' . htmlspecialchars($siteUrl) . '" style="color:#8b5cf6;text-decoration:none">' . htmlspecialchars($siteName) . '</a></div>'
            . '</div></div></body></html>';

        $headers = [
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . ($opts['reply_to'] ?? $fromEmail),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: JessieCMS',
        ];

        $sent = @mail($to, $subject, $html, implode("\r\n", $headers));
        self::log($sent ? 'sent' : 'failed', $to, $subject);
        
        if (function_exists('cms_event')) {
            cms_event($sent ? 'email.sent' : 'email.failed', [
                'to' => $to, 'subject' => $subject, 'type' => 'html'
            ]);
        }

        return $sent;
    }

    /**
     * Fire CMS event (wrapper)
     */
    public static function event(string $key, array $payload = []): void
    {
        if (function_exists('cms_event')) {
            cms_event($key, $payload);
        }
    }

    /**
     * Send booking confirmation email
     */
    public static function bookingConfirmation(array $appointment, array $service): bool
    {
        $email = $appointment['customer_email'] ?? '';
        if (!$email) return false;

        $html = '<h2 style="color:#22c55e;margin:0 0 16px">✅ Booking Confirmed!</h2>'
            . '<p>Hi <strong>' . htmlspecialchars($appointment['customer_name'] ?? '') . '</strong>,</p>'
            . '<p>Your appointment has been confirmed:</p>'
            . '<div style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;margin:16px 0">'
            . '<p style="margin:4px 0"><strong>Service:</strong> ' . htmlspecialchars($service['name'] ?? '') . '</p>'
            . '<p style="margin:4px 0"><strong>Date:</strong> ' . date('l, F j, Y', strtotime($appointment['date'] ?? '')) . '</p>'
            . '<p style="margin:4px 0"><strong>Time:</strong> ' . date('g:i A', strtotime($appointment['start_time'] ?? '')) . '</p>'
            . '<p style="margin:4px 0"><strong>Booking #:</strong> ' . ($appointment['id'] ?? '') . '</p>'
            . '</div>'
            . '<p style="color:#94a3b8;font-size:.85rem">Need to reschedule? Reply to this email.</p>';

        self::event('booking.confirmed', ['appointment_id' => $appointment['id'] ?? 0]);
        return self::emailHtml($email, 'Booking Confirmed — ' . ($service['name'] ?? ''), $html);
    }

    /**
     * Send event ticket confirmation
     */
    public static function eventTicketConfirmation(array $order, array $event, array $ticket): bool
    {
        $email = $order['buyer_email'] ?? '';
        if (!$email) return false;

        $html = '<h2 style="color:#22c55e;margin:0 0 16px">🎫 Ticket Confirmed!</h2>'
            . '<p>Hi <strong>' . htmlspecialchars($order['buyer_name'] ?? '') . '</strong>,</p>'
            . '<p>Your ticket purchase has been confirmed:</p>'
            . '<div style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;margin:16px 0">'
            . '<p style="margin:4px 0"><strong>Event:</strong> ' . htmlspecialchars($event['title'] ?? '') . '</p>'
            . '<p style="margin:4px 0"><strong>Date:</strong> ' . date('l, F j, Y', strtotime($event['start_date'] ?? '')) . '</p>'
            . '<p style="margin:4px 0"><strong>Ticket:</strong> ' . htmlspecialchars($ticket['name'] ?? '') . ' × ' . ($order['quantity'] ?? 1) . '</p>'
            . '<p style="margin:4px 0"><strong>Order #:</strong> ' . htmlspecialchars($order['order_number'] ?? '') . '</p>'
            . '<p style="margin:4px 0;font-size:1.1rem"><strong>Check-in Code:</strong> <code style="background:#6366f1;color:#fff;padding:4px 10px;border-radius:4px">' . htmlspecialchars($order['qr_code'] ?? '') . '</code></p>'
            . '</div>'
            . '<p style="color:#94a3b8;font-size:.85rem">Show the check-in code at the event entrance.</p>';

        self::event('event.ticket.purchased', ['order_id' => $order['id'] ?? 0, 'event_id' => $event['id'] ?? 0]);
        return self::emailHtml($email, 'Ticket Confirmed — ' . ($event['title'] ?? ''), $html);
    }

    /**
     * Send membership welcome email
     */
    public static function membershipWelcome(array $member, array $plan): bool
    {
        $email = $member['email'] ?? '';
        if (!$email) return false;

        $html = '<h2 style="color:#22c55e;margin:0 0 16px">🎉 Welcome to Membership!</h2>'
            . '<p>Hi <strong>' . htmlspecialchars($member['name'] ?? 'there') . '</strong>,</p>'
            . '<p>Your membership is now active:</p>'
            . '<div style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;margin:16px 0">'
            . '<p style="margin:4px 0"><strong>Plan:</strong> ' . htmlspecialchars($plan['name'] ?? '') . '</p>'
            . '<p style="margin:4px 0"><strong>Valid until:</strong> ' . date('M j, Y', strtotime($member['expires_at'] ?? '+1 month')) . '</p>'
            . '</div>';

        self::event('membership.activated', ['member_id' => $member['id'] ?? 0, 'plan_id' => $plan['id'] ?? 0]);
        return self::emailHtml($email, 'Welcome — Membership Activated', $html);
    }

    private static function setting(string $key, string $default = ''): string
    {
        static $cache = [];
        if (isset($cache[$key])) return $cache[$key];
        try { $cache[$key] = db()->prepare("SELECT value FROM settings WHERE `key` = ?")->execute([$key]) ? db()->prepare("SELECT value FROM settings WHERE `key` = ?")->execute([$key]) : $default; } catch (\Throwable $e) {}
        // Simpler approach
        try {
            $stmt = db()->prepare("SELECT `value` FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $cache[$key] = $stmt->fetchColumn() ?: $default;
        } catch (\Throwable $e) {
            $cache[$key] = $default;
        }
        return $cache[$key];
    }

    private static function log(string $status, string $to, string $subject): void
    {
        $logDir = (defined('CMS_ROOT') ? CMS_ROOT : '/var/www/cms') . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        @file_put_contents($logDir . '/notifications.log',
            date('Y-m-d H:i:s') . " [{$status}] to={$to} subject={$subject}\n",
            FILE_APPEND | LOCK_EX
        );
    }
}
