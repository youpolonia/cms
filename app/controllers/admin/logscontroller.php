<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class LogsController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $action = $request->get('action', '');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];

        if ($action) {
            $where = 'WHERE action = ?';
            $params[] = $action;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) FROM activity_logs {$where}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Get logs
        $sql = "SELECT * FROM activity_logs {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get unique actions for filter
        $actions = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(\PDO::FETCH_COLUMN);

        render('admin/logs/index', [
            'logs' => $logs,
            'actions' => $actions,
            'currentAction' => $action,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => (int)ceil($total / $perPage),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function clear(Request $request): void
    {
        $days = (int)$request->post('days', 30);

        if ($days < 1) {
            Session::flash('error', 'Invalid number of days.');
            Response::redirect('/admin/logs');
        }

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        $deleted = $stmt->rowCount();

        Session::flash('success', "Deleted {$deleted} log entries older than {$days} days.");
        Response::redirect('/admin/logs');
    }

    public function files(Request $request): void
    {
        $logFiles = [];
        $logDir = CMS_ROOT . '/storage/logs';

        if (is_dir($logDir)) {
            $files = scandir($logDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    $path = $logDir . '/' . $file;
                    $logFiles[] = [
                        'name' => $file,
                        'size' => filesize($path),
                        'modified' => filemtime($path)
                    ];
                }
            }
            usort($logFiles, fn($a, $b) => $b['modified'] <=> $a['modified']);
        }

        render('admin/logs/files', [
            'logFiles' => $logFiles,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function viewFile(Request $request): void
    {
        $filename = basename($request->get('file', ''));
        $lines = (int)$request->get('lines', 100);

        if (empty($filename) || !preg_match('/^[\w\-\.]+\.log$/', $filename)) {
            Session::flash('error', 'Invalid file name.');
            Response::redirect('/admin/logs/files');
        }

        $path = CMS_ROOT . '/storage/logs/' . $filename;

        if (!file_exists($path)) {
            Session::flash('error', 'Log file not found.');
            Response::redirect('/admin/logs/files');
        }

        // Read last N lines
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);

        $logLines = [];
        while (!$file->eof()) {
            $line = $file->fgets();
            if (trim($line) !== '') {
                $logLines[] = $line;
            }
        }

        render('admin/logs/view', [
            'filename' => $filename,
            'logLines' => $logLines,
            'totalLines' => $totalLines,
            'showingLines' => $lines
        ]);
    }

    /**
     * Static method to log an activity
     */
    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, username, action, entity_type, entity_id, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                Session::getAdminId(),
                Session::getAdminUsername(),
                $action,
                $entityType,
                $entityId,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
            ]);
        } catch (\Exception $e) {
            // Silently fail - logging should not break the app
        }
    }
}
