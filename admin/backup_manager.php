<?php
require_once __DIR__ . '/../config.php';
if (!defined('BACKUP_MANAGER_INCLUDED')) { define('BACKUP_MANAGER_INCLUDED', true); }
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/backupmanager.php';

function backup_lock_path(): string {
    return CMS_ROOT . '/backups/backup.lock';
}

function create_backup_lock(): bool {
    $lockPath = backup_lock_path();
    $dir = dirname($lockPath);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $tmp = $lockPath . '.tmp.' . uniqid('', true);
    $data = json_encode(['ts' => time()], JSON_UNESCAPED_SLASHES);
    if (@file_put_contents($tmp, $data, LOCK_EX) === false) { return false; }
    return @rename($tmp, $lockPath);
}

function remove_backup_lock(): void {
    $lockPath = backup_lock_path();
    if (is_file($lockPath)) { @unlink($lockPath); }
}

function log_backup_event(array $data): void {
    $logFile = CMS_ROOT . '/logs/backup_manager.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $line = json_encode($data, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function run_backup_internal(): array {
    $okLock = create_backup_lock();
    if (!$okLock) {
        $err = ['ok' => false, 'error' => 'lock_failed'];
        log_backup_event(['ts' => gmdate('c'), 'action' => 'run', 'status' => 'lock_failed']);
        return $err;
    }
    try {
        $bm = new \core\BackupManager();
        $file = $bm->generateTimestampedBackup();
        $ok = $file !== false;
        $base = $ok ? basename((string)$file) : '';
        log_backup_event(['ts' => gmdate('c'), 'action' => 'run', 'status' => $ok ? 'ok' : 'fail', 'file' => $base]);
        return ['ok' => $ok, 'file' => $base];
    } finally {
        remove_backup_lock();
    }
}

function handle_backup_action(array $request) {
    $action = isset($request['action']) ? (string)$request['action'] : 'status';
    if ($action === 'run') {
        return json_encode(run_backup_internal(), JSON_UNESCAPED_SLASHES);
    }
    if ($action === 'logs') {
        $path = CMS_ROOT . '/logs/backup_manager.log';
        if (!is_file($path)) { return ""; }
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) { return ""; }
        $tail = array_slice($lines, -100);
        return implode("\n", $tail) . "\n";
    }
    if ($action === 'list') {
        $dir = CMS_ROOT . '/backups';
        if (!is_dir($dir)) { return ""; }
        $items = array_values(array_filter(scandir($dir) ?: [], function($f) use ($dir) {
            return $f !== '.' && $f !== '..' && is_file($dir . '/' . $f);
        }));
        sort($items);
        return implode("\n", $items) . (count($items) ? "\n" : "");
    }
    return "Backup Manager OK\n";
}

function display_backup_ui(): string {
    csrf_boot();
    ob_start();
    csrf_field();
    $token = ob_get_clean();
    return "
    <pre>Backup Manager</pre>" . $token;
}
