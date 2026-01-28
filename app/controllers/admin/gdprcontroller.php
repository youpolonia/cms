<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class GdprController
{
    public function index(Request $request): void
    {
        // Get recent GDPR actions from logs
        $recentActions = $this->getRecentActions(20);

        render('admin/gdpr/index', [
            'recentActions' => $recentActions,
            'stats' => $this->getStats(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function export(Request $request): void
    {
        $email = trim($request->post('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid email address.');
            Response::redirect('/admin/gdpr');
            return;
        }

        $pdo = db();

        // Find user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'No user found with this email address.');
            Response::redirect('/admin/gdpr');
            return;
        }

        // Collect all user data
        $userData = [
            'user' => $user,
            'exported_at' => date('Y-m-d H:i:s'),
            'exported_by' => Session::getAdminUsername()
        ];

        // Get related data from other tables if they exist
        $tables = ['user_meta', 'user_sessions', 'activity_logs', 'comments', 'orders'];
        foreach ($tables as $table) {
            try {
                $checkTable = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetch();
                if ($checkTable) {
                    $field = $table === 'activity_logs' ? 'user_id' : 'user_id';
                    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$field} = ?");
                    $stmt->execute([$user['id']]);
                    $userData[$table] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            } catch (\Exception $e) {
                // Table doesn't exist or has different structure
            }
        }

        // Log GDPR action
        $this->logGdprAction('export', $user['id'], "Data exported for {$email}");

        // Store export data in session for download
        $_SESSION['gdpr_export'] = $userData;
        $_SESSION['gdpr_export_email'] = $email;

        Session::flash('success', "User data exported successfully. Click Download to save the file.");
        Response::redirect('/admin/gdpr');
    }

    public function download(Request $request): void
    {
        if (empty($_SESSION['gdpr_export'])) {
            Session::flash('error', 'No export data available. Please run an export first.');
            Response::redirect('/admin/gdpr');
            return;
        }

        $data = $_SESSION['gdpr_export'];
        $email = $_SESSION['gdpr_export_email'] ?? 'user';

        // Clear session data
        unset($_SESSION['gdpr_export'], $_SESSION['gdpr_export_email']);

        // Send as JSON file
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="gdpr_export_' . preg_replace('/[^a-z0-9]/i', '_', $email) . '_' . date('Y-m-d') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public function anonymize(Request $request): void
    {
        $email = trim($request->post('email', ''));
        $confirm = $request->post('confirm', '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid email address.');
            Response::redirect('/admin/gdpr');
            return;
        }

        if ($confirm !== 'ANONYMIZE') {
            Session::flash('error', 'Please type ANONYMIZE to confirm this action.');
            Response::redirect('/admin/gdpr');
            return;
        }

        $pdo = db();

        // Find user
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'No user found with this email address.');
            Response::redirect('/admin/gdpr');
            return;
        }

        // Generate anonymous identifier
        $anonId = 'anon_' . substr(hash('sha256', $user['id'] . time()), 0, 12);

        // Anonymize user record
        $stmt = $pdo->prepare("UPDATE users SET
            username = ?,
            email = ?,
            first_name = 'Anonymized',
            last_name = 'User',
            phone = NULL,
            address = NULL,
            updated_at = NOW()
            WHERE id = ?");
        $stmt->execute([$anonId, $anonId . '@anonymized.local', $user['id']]);

        // Log GDPR action
        $this->logGdprAction('anonymize', $user['id'], "User {$email} anonymized to {$anonId}");

        Session::flash('success', "User {$email} has been anonymized successfully.");
        Response::redirect('/admin/gdpr');
    }

    public function deleteData(Request $request): void
    {
        $email = trim($request->post('email', ''));
        $confirm = $request->post('confirm', '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid email address.');
            Response::redirect('/admin/gdpr');
            return;
        }

        if ($confirm !== 'DELETE') {
            Session::flash('error', 'Please type DELETE to confirm this action.');
            Response::redirect('/admin/gdpr');
            return;
        }

        $pdo = db();

        // Find user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'No user found with this email address.');
            Response::redirect('/admin/gdpr');
            return;
        }

        // Log before deletion
        $this->logGdprAction('delete', $user['id'], "User {$email} data deleted (Right to be Forgotten)");

        // Delete related data
        $tables = ['user_meta', 'user_sessions', 'comments'];
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->prepare("DELETE FROM {$table} WHERE user_id = ?");
                $stmt->execute([$user['id']]);
            } catch (\Exception $e) {
                // Table might not exist
            }
        }

        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);

        Session::flash('success', "All data for {$email} has been permanently deleted.");
        Response::redirect('/admin/gdpr');
    }

    private function getRecentActions(int $limit = 20): array
    {
        $logDir = \CMS_ROOT . '/storage/logs/gdpr/';
        if (!is_dir($logDir)) {
            return [];
        }

        $actions = [];
        $files = glob($logDir . 'gdpr_*.log');
        rsort($files); // Newest first

        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach (array_reverse($lines) as $line) {
                $entry = json_decode($line, true);
                if ($entry) {
                    $actions[] = $entry;
                    if (count($actions) >= $limit) break 2;
                }
            }
        }

        return $actions;
    }

    private function getStats(): array
    {
        $logDir = \CMS_ROOT . '/storage/logs/gdpr/';
        $stats = ['exports' => 0, 'anonymizations' => 0, 'deletions' => 0, 'total' => 0];

        if (!is_dir($logDir)) return $stats;

        $files = glob($logDir . 'gdpr_*.log');
        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if ($entry) {
                    $stats['total']++;
                    if (str_contains($entry['action'] ?? '', 'export')) $stats['exports']++;
                    if (str_contains($entry['action'] ?? '', 'anonymize')) $stats['anonymizations']++;
                    if (str_contains($entry['action'] ?? '', 'delete')) $stats['deletions']++;
                }
            }
        }

        return $stats;
    }

    private function logGdprAction(string $action, $userId, string $description): void
    {
        $logDir = \CMS_ROOT . '/storage/logs/gdpr/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = [
            'timestamp' => date('c'),
            'action' => $action,
            'user_id' => $userId,
            'description' => $description,
            'admin_id' => Session::getAdminId(),
            'admin_username' => Session::getAdminUsername(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $logFile = $logDir . 'gdpr_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
    }
}
