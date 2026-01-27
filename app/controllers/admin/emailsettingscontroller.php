<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Email Settings Controller
 * Manages email configuration via config/email_settings.json
 */
class EmailSettingsController
{
    /**
     * Display email settings form
     */
    public function index(Request $request): void
    {
        // Load email settings helper
        require_once CMS_ROOT . '/core/settings_email.php';

        $settings = email_settings_get();

        // Check SMTP config status from config.php
        $smtpConfigured = defined('SMTP_HOST') && SMTP_HOST !== '';

        // Get email queue stats if table exists
        $queueStats = $this->getQueueStats();

        render('admin/email-settings/index', [
            'settings' => $settings,
            'smtpConfigured' => $smtpConfigured,
            'smtpHost' => defined('SMTP_HOST') ? SMTP_HOST : null,
            'smtpPort' => defined('SMTP_PORT') ? SMTP_PORT : null,
            'smtpUser' => defined('SMTP_USER') ? SMTP_USER : null,
            'smtpEncryption' => defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls',
            'queueStats' => $queueStats,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Update email settings
     */
    public function update(Request $request): void
    {
        require_once CMS_ROOT . '/core/settings_email.php';

        $updated = [
            'from_name'      => trim($request->post('from_name', '')),
            'from_email'     => trim($request->post('from_email', '')),
            'reply_to_email' => trim($request->post('reply_to_email', '')),
            'send_mode'      => trim($request->post('send_mode', 'smtp')),
        ];

        // Validate email addresses
        if (!empty($updated['from_email']) && !filter_var($updated['from_email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid From Email address.');
            Response::redirect('/admin/email-settings');
        }

        if (!empty($updated['reply_to_email']) && !filter_var($updated['reply_to_email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid Reply-To Email address.');
            Response::redirect('/admin/email-settings');
        }

        // Normalize send_mode
        if (!in_array($updated['send_mode'], ['smtp', 'phpmail'], true)) {
            $updated['send_mode'] = 'smtp';
        }

        email_settings_update($updated);

        Session::flash('success', 'Email settings saved successfully.');
        Response::redirect('/admin/email-settings');
    }

    /**
     * Send a test email
     */
    public function testEmail(Request $request): void
    {
        require_once CMS_ROOT . '/core/settings_email.php';

        $testTo = trim($request->post('test_email', ''));

        if (empty($testTo) || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address for the test.');
            Response::redirect('/admin/email-settings');
        }

        $settings = email_settings_get();

        // Try to send test email
        $result = $this->sendTestEmail($testTo, $settings);

        if ($result['success']) {
            Session::flash('success', 'Test email sent successfully to ' . htmlspecialchars($testTo));
        } else {
            Session::flash('error', 'Failed to send test email: ' . htmlspecialchars($result['error']));
        }

        Response::redirect('/admin/email-settings');
    }

    /**
     * Get email queue statistics
     */
    private function getQueueStats(): array
    {
        $stats = [
            'pending' => 0,
            'sending' => 0,
            'sent' => 0,
            'failed' => 0,
            'total' => 0
        ];

        try {
            $pdo = db();

            // Check if email_queue table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'email_queue'");
            if ($stmt->rowCount() === 0) {
                return $stats;
            }

            $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM email_queue GROUP BY status");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $stats[$row['status']] = (int)$row['cnt'];
            }
            $stats['total'] = array_sum($stats);
        } catch (\Exception $e) {
            // Table might not exist, return empty stats
        }

        return $stats;
    }

    /**
     * Send a test email using configured settings
     */
    private function sendTestEmail(string $to, array $settings): array
    {
        $fromName = $settings['from_name'] ?: 'CMS System';
        $fromEmail = $settings['from_email'] ?: 'noreply@localhost';
        $subject = 'Test Email from CMS';
        $body = "This is a test email sent from your CMS.\n\nConfiguration:\n";
        $body .= "- Send Mode: " . $settings['send_mode'] . "\n";
        $body .= "- From Name: " . $fromName . "\n";
        $body .= "- From Email: " . $fromEmail . "\n";
        $body .= "- Reply-To: " . ($settings['reply_to_email'] ?: 'Not set') . "\n";
        $body .= "\nTimestamp: " . date('Y-m-d H:i:s') . "\n";

        if ($settings['send_mode'] === 'phpmail') {
            // Use PHP mail() function
            $headers = "From: {$fromName} <{$fromEmail}>\r\n";
            if (!empty($settings['reply_to_email'])) {
                $headers .= "Reply-To: {$settings['reply_to_email']}\r\n";
            }
            $headers .= "X-Mailer: PHP/" . phpversion();

            $result = @mail($to, $subject, $body, $headers);

            if ($result) {
                return ['success' => true];
            } else {
                $error = error_get_last();
                return ['success' => false, 'error' => $error['message'] ?? 'Unknown mail() error'];
            }
        } else {
            // SMTP mode - queue the email or use SMTP directly
            if (!defined('SMTP_HOST') || SMTP_HOST === '') {
                return ['success' => false, 'error' => 'SMTP is not configured in config.php'];
            }

            // Try to queue the email for sending
            try {
                $pdo = db();

                // Check if email_queue table exists
                $stmt = $pdo->query("SHOW TABLES LIKE 'email_queue'");
                if ($stmt->rowCount() === 0) {
                    return ['success' => false, 'error' => 'Email queue table does not exist'];
                }

                $stmt = $pdo->prepare("
                    INSERT INTO email_queue
                    (to_email, to_name, subject, body_text, body_html, priority, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 1, 'pending', NOW())
                ");
                $stmt->execute([
                    $to,
                    null,
                    $subject,
                    $body,
                    nl2br(htmlspecialchars($body))
                ]);

                return ['success' => true];
            } catch (\Exception $e) {
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
    }
}
