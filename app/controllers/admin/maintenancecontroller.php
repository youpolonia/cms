<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class MaintenanceController
{
    public function index(Request $request): void
    {
        $settings = $this->getSettings();

        // Parse allowed IPs
        $allowedIps = array_filter(array_map('trim', explode("\n", $settings['allowed_ips'] ?? '')));

        render('admin/maintenance/index', [
            'settings' => $settings,
            'allowedIps' => $allowedIps,
            'currentIp' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function toggle(Request $request): void
    {
        $pdo = db();
        $settings = $this->getSettings();

        $newStatus = $settings['is_enabled'] ? 0 : 1;

        if ($newStatus) {
            // Enabling maintenance mode
            $stmt = $pdo->prepare("UPDATE maintenance_settings SET is_enabled = 1, enabled_at = NOW(), enabled_by = ? WHERE id = 1");
            $stmt->execute([Session::getAdminId()]);

            // Create flag file for faster checking
            $this->createFlagFile();

            Session::flash('success', 'Maintenance mode ENABLED. Site is now offline for visitors.');
        } else {
            // Disabling maintenance mode
            $stmt = $pdo->prepare("UPDATE maintenance_settings SET is_enabled = 0, enabled_at = NULL, enabled_by = NULL WHERE id = 1");
            $stmt->execute();

            // Remove flag file
            $this->removeFlagFile();

            Session::flash('success', 'Maintenance mode DISABLED. Site is now online.');
        }

        Response::redirect('/admin/maintenance');
    }

    public function update(Request $request): void
    {
        $message = trim($request->post('message', ''));
        $allowedIps = trim($request->post('allowed_ips', ''));
        $retryAfter = max(60, (int)$request->post('retry_after', 3600));

        if (empty($message)) {
            Session::flash('error', 'Maintenance message is required.');
            Response::redirect('/admin/maintenance');
            return;
        }

        // Validate IPs
        $ips = array_filter(array_map('trim', explode("\n", $allowedIps)));
        $validIps = [];
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) || $ip === '127.0.0.1' || $ip === '::1') {
                $validIps[] = $ip;
            }
        }

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE maintenance_settings SET message = ?, allowed_ips = ?, retry_after = ? WHERE id = 1");
        $stmt->execute([$message, implode("\n", $validIps), $retryAfter]);

        // Update flag file if maintenance is active
        $settings = $this->getSettings();
        if ($settings['is_enabled']) {
            $this->createFlagFile();
        }

        Session::flash('success', 'Maintenance settings updated.');
        Response::redirect('/admin/maintenance');
    }

    public function addIp(Request $request): void
    {
        $ip = trim($request->post('ip', ''));

        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP) && $ip !== '127.0.0.1' && $ip !== '::1') {
            Session::flash('error', 'Invalid IP address.');
            Response::redirect('/admin/maintenance');
            return;
        }

        $pdo = db();
        $settings = $this->getSettings();

        $currentIps = array_filter(array_map('trim', explode("\n", $settings['allowed_ips'] ?? '')));

        if (!in_array($ip, $currentIps)) {
            $currentIps[] = $ip;
            $stmt = $pdo->prepare("UPDATE maintenance_settings SET allowed_ips = ? WHERE id = 1");
            $stmt->execute([implode("\n", $currentIps)]);

            Session::flash('success', "IP {$ip} added to allowed list.");
        } else {
            Session::flash('error', 'IP already in allowed list.');
        }

        Response::redirect('/admin/maintenance');
    }

    public function removeIp(Request $request): void
    {
        $ip = trim($request->post('ip', ''));

        $pdo = db();
        $settings = $this->getSettings();

        $currentIps = array_filter(array_map('trim', explode("\n", $settings['allowed_ips'] ?? '')));
        $currentIps = array_diff($currentIps, [$ip]);

        $stmt = $pdo->prepare("UPDATE maintenance_settings SET allowed_ips = ? WHERE id = 1");
        $stmt->execute([implode("\n", array_values($currentIps))]);

        Session::flash('success', "IP {$ip} removed from allowed list.");
        Response::redirect('/admin/maintenance');
    }

    private function getSettings(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM maintenance_settings WHERE id = 1");
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$settings) {
            $pdo->exec("INSERT INTO maintenance_settings (id, is_enabled, message, allowed_ips, retry_after) VALUES (1, 0, 'We are currently performing scheduled maintenance.', '', 3600)");
            return $this->getSettings();
        }

        return $settings;
    }

    private function createFlagFile(): void
    {
        $flagPath = \CMS_ROOT . '/storage/maintenance.flag';
        $settings = $this->getSettings();

        $data = [
            'enabled' => true,
            'message' => $settings['message'],
            'allowed_ips' => array_filter(array_map('trim', explode("\n", $settings['allowed_ips'] ?? ''))),
            'retry_after' => $settings['retry_after'],
            'enabled_at' => $settings['enabled_at']
        ];

        // Ensure storage directory exists
        $storageDir = \CMS_ROOT . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        file_put_contents($flagPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function removeFlagFile(): void
    {
        $flagPath = \CMS_ROOT . '/storage/maintenance.flag';
        if (file_exists($flagPath)) {
            unlink($flagPath);
        }
    }
}
