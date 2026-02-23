<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Email Settings Controller
 * Manages SMTP configuration via the `settings` table (key/value).
 *
 * Keys: smtp_host, smtp_port, smtp_user, smtp_pass,
 *       smtp_encryption, smtp_from_email, smtp_from_name
 */
class EmailSettingsController
{
    /** Setting keys managed on this page */
    private const KEYS = [
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_pass',
        'smtp_encryption',
        'smtp_from_email',
        'smtp_from_name',
    ];

    /* ─── helpers ─── */

    private function loadSettings(): array
    {
        $pdo = db();
        $defaults = [
            'smtp_host'       => '',
            'smtp_port'       => '587',
            'smtp_user'       => '',
            'smtp_pass'       => '',
            'smtp_encryption' => 'tls',
            'smtp_from_email' => '',
            'smtp_from_name'  => '',
        ];

        $placeholders = implode(',', array_fill(0, count(self::KEYS), '?'));
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ($placeholders)");
        $stmt->execute(self::KEYS);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (array_key_exists($row['key'], $defaults)) {
                $defaults[$row['key']] = $row['value'] ?? '';
            }
        }

        return $defaults;
    }

    private function saveSetting(string $key, string $value): void
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "INSERT INTO settings (`key`, `value`, `group_name`, `updated_at`)
             VALUES (?, ?, 'email', NOW())
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()"
        );
        $stmt->execute([$key, $value]);
    }

    /* ─── GET /admin/email-settings ─── */

    public function index(Request $request): void
    {
        $settings = $this->loadSettings();

        render('admin/email-settings/index', [
            'settings' => $settings,
            'success'  => Session::getFlash('success'),
            'error'    => Session::getFlash('error'),
        ]);
    }

    /* ─── POST /admin/email-settings ─── */

    public function update(Request $request): void
    {
        $validEncryptions = ['tls', 'ssl', 'none'];

        $input = [
            'smtp_host'       => trim($request->post('smtp_host', '')),
            'smtp_port'       => trim($request->post('smtp_port', '587')),
            'smtp_user'       => trim($request->post('smtp_user', '')),
            'smtp_pass'       => $request->post('smtp_pass', ''),
            'smtp_encryption' => trim($request->post('smtp_encryption', 'tls')),
            'smtp_from_email' => trim($request->post('smtp_from_email', '')),
            'smtp_from_name'  => trim($request->post('smtp_from_name', '')),
        ];

        // Validate encryption value
        if (!in_array($input['smtp_encryption'], $validEncryptions, true)) {
            $input['smtp_encryption'] = 'tls';
        }

        // Validate port
        $port = (int)$input['smtp_port'];
        if ($port < 1 || $port > 65535) {
            $input['smtp_port'] = '587';
        }

        // Validate from_email if provided
        if ($input['smtp_from_email'] !== '' && !filter_var($input['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid From Email address.');
            Response::redirect('/admin/email-settings');
        }

        // If password field is blank and we already have one saved, keep the old value
        if ($input['smtp_pass'] === '') {
            $existing = $this->loadSettings();
            $input['smtp_pass'] = $existing['smtp_pass'];
        }

        foreach ($input as $key => $value) {
            $this->saveSetting($key, $value);
        }

        Session::flash('success', 'SMTP settings saved successfully.');
        Response::redirect('/admin/email-settings');
    }

    /* ─── POST /admin/email-settings/test ─── */

    public function testEmail(Request $request): void
    {
        $testTo = trim($request->post('test_email', ''));

        if ($testTo === '' || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address for the test.');
            Response::redirect('/admin/email-settings');
        }

        // Load the mailer
        require_once CMS_ROOT . '/core/mailer.php';

        $subject = 'Test Email from Jessie CMS';
        $body    = "This is a test email sent from your Jessie CMS.\n\n"
                 . "If you received this, your email configuration is working.\n\n"
                 . "Timestamp: " . date('Y-m-d H:i:s') . "\n";

        $ok = cms_send_email($testTo, $subject, $body);

        if ($ok) {
            Session::flash('success', 'Test email sent successfully to ' . htmlspecialchars($testTo));
        } else {
            Session::flash('error', 'Failed to send test email. Check the error log for details.');
        }

        Response::redirect('/admin/email-settings');
    }
}
