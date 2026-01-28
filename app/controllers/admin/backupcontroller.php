<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class BackupController
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = \CMS_ROOT . '/storage/backups';
    }

    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM backups ORDER BY created_at DESC LIMIT 50");
        $backups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Check which files still exist
        foreach ($backups as &$backup) {
            $backup['exists'] = file_exists($this->backupDir . '/' . $backup['filename']);
        }

        // Get disk usage
        $totalSize = 0;
        $fileCount = 0;
        if (is_dir($this->backupDir)) {
            $files = scandir($this->backupDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $totalSize += filesize($this->backupDir . '/' . $file);
                    $fileCount++;
                }
            }
        }

        // Calculate stats
        $dbBackups = 0;
        $lastBackup = null;
        foreach ($backups as $b) {
            if ($b['type'] === 'database') {
                $dbBackups++;
            }
            if (!$lastBackup) {
                $lastBackup = $b['created_at'];
            }
        }

        render('admin/backup/index', [
            'backups' => $backups,
            'totalSize' => $totalSize,
            'fileCount' => $fileCount,
            'stats' => [
                'total' => count($backups),
                'totalSize' => $totalSize,
                'dbBackups' => $dbBackups,
                'lastBackup' => $lastBackup
            ],
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        $type = in_array($request->post('type'), ['database', 'files', 'full']) ? $request->post('type') : 'database';
        $notes = trim($request->post('notes', ''));

        try {
            if ($type === 'database' || $type === 'full') {
                $result = $this->createDatabaseBackup($notes);
                Session::flash('success', "Database backup created: {$result['filename']} ({$this->formatBytes($result['size'])})");
            } else {
                Session::flash('error', 'File backup requires CLI access. Use database backup instead.');
            }
        } catch (\Exception $e) {
            Session::flash('error', 'Backup failed: ' . $e->getMessage());
        }

        Response::redirect('/admin/backup');
    }

    public function download(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM backups WHERE id = ?");
        $stmt->execute([$id]);
        $backup = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$backup) {
            Session::flash('error', 'Backup not found.');
            Response::redirect('/admin/backup');
            return;
        }

        $filepath = $this->backupDir . '/' . $backup['filename'];

        if (!file_exists($filepath)) {
            Session::flash('error', 'Backup file not found on disk.');
            Response::redirect('/admin/backup');
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM backups WHERE id = ?");
        $stmt->execute([$id]);
        $backup = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($backup) {
            $filepath = $this->backupDir . '/' . $backup['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $stmt = $pdo->prepare("DELETE FROM backups WHERE id = ?");
            $stmt->execute([$id]);
        }

        Session::flash('success', 'Backup deleted.');
        Response::redirect('/admin/backup');
    }

    public function cleanup(Request $request): void
    {
        $days = (int)$request->post('days', 30);

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM backups WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        $oldBackups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $deleted = 0;
        foreach ($oldBackups as $backup) {
            $filepath = $this->backupDir . '/' . $backup['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $deleted++;
        }

        $stmt = $pdo->prepare("DELETE FROM backups WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);

        Session::flash('success', "Deleted {$deleted} old backups.");
        Response::redirect('/admin/backup');
    }

    private function createDatabaseBackup(string $notes = ''): array
    {
        $pdo = db();
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "db_backup_{$timestamp}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        // Get all tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        $sql = "-- CMS Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Tables: " . count($tables) . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Get create statement
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $row['Create Table'] . ";\n\n";

            // Get data
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function($val) use ($pdo) {
                        if ($val === null) return 'NULL';
                        return $pdo->quote((string)$val);
                    }, array_values($row));
                    $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Write file
        file_put_contents($filepath, $sql);
        $size = filesize($filepath);

        // Record in database
        $stmt = $pdo->prepare("INSERT INTO backups (filename, type, size_bytes, tables_count, notes, created_by, created_at) VALUES (?, 'database', ?, ?, ?, ?, NOW())");
        $stmt->execute([$filename, $size, count($tables), $notes, Session::getAdminId()]);

        return [
            'filename' => $filename,
            'size' => $size,
            'tables' => count($tables)
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
