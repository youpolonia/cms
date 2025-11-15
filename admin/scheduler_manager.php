<?php
require_once __DIR__ . '/../config.php';
if (!defined('SCHEDULER_MANAGER_INCLUDED')) { define('SCHEDULER_MANAGER_INCLUDED', true); }
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/scheduler.php';

function handle_scheduler_action(array $request) {
    $action = isset($request['action']) ? (string)$request['action'] : 'status';
    switch ($action) {
        case 'run_due':
            $stats = \core\Scheduler::runDue(5);
            log_scheduler_event([
                'ts' => gmdate('c'),
                'action' => 'run_due',
                'stats' => $stats
            ]);
            return json_encode(['ok' => true, 'ran' => (int)($stats['ran'] ?? 0)], JSON_UNESCAPED_SLASHES);
        case 'logs':
            $path = CMS_ROOT . '/logs/scheduler_manager.log';
            if (!is_file($path)) {
                return "";
            }
            $data = @file($path, FILE_IGNORE_NEW_LINES);
            if ($data === false) {
                return "";
            }
            $tail = array_slice($data, -100);
            return implode("\n", $tail) . "\n";
        case 'status':
        default:
            return "Scheduler Manager OK\n";
    }
}

function display_scheduler_ui(): string {
    csrf_boot();
    ob_start();
    csrf_field();
    $token = ob_get_clean();
    return "
<pre>Scheduler Manager</pre>" .
 $token;
}

function log_scheduler_event(array $data): void {
    $logFile = CMS_ROOT . '/logs/scheduler_manager.log';
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = json_encode($data, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}
