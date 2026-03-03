<?php
/**
 * Jessie CMS — CLI Cron Runner
 * 
 * Add to crontab:
 *   * * * * * php /var/www/cms/cron.php >> /var/log/jessie-cron.log 2>&1
 * 
 * Or run manually:
 *   php cron.php                 # Run all due tasks
 *   php cron.php --task=backup   # Run specific task
 *   php cron.php --list          # List scheduled jobs
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('CLI only');
}

define('CMS_ROOT', __DIR__);
define('CMS_APP', CMS_ROOT . '/app');
define('CMS_CONFIG', CMS_ROOT . '/config');

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';

$pdo = \core\Database::connection();

// Parse CLI arguments
$args = getopt('', ['task:', 'list', 'help', 'verbose']);
$verbose = isset($args['verbose']);

function cron_log(string $msg, bool $verbose = false): void {
    $ts = date('Y-m-d H:i:s');
    if ($verbose || php_sapi_name() === 'cli') {
        echo "[{$ts}] {$msg}\n";
    }
    error_log("[cron] {$msg}");
}

// --help
if (isset($args['help'])) {
    echo "Jessie CMS Cron Runner\n";
    echo "Usage: php cron.php [options]\n";
    echo "  --list       List all scheduled jobs\n";
    echo "  --task=NAME  Run a specific task (backup, cleanup, email-queue, cache, log-rotate, session-clean, temp-clean)\n";
    echo "  --verbose    Show detailed output\n";
    echo "  --help       This message\n";
    exit(0);
}

// --list: show scheduled jobs from DB
if (isset($args['list'])) {
    try {
        $jobs = $pdo->query("SELECT * FROM scheduler_jobs ORDER BY next_run ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo "Scheduled Jobs (" . count($jobs) . "):\n";
        echo str_repeat('-', 80) . "\n";
        printf("%-30s %-10s %-20s %-20s\n", 'Name', 'Enabled', 'Last Run', 'Next Run');
        echo str_repeat('-', 80) . "\n";
        foreach ($jobs as $j) {
            printf("%-30s %-10s %-20s %-20s\n",
                $j['name'] ?? $j['task_name'] ?? '?',
                ($j['enabled'] ?? $j['is_active'] ?? 0) ? 'Yes' : 'No',
                $j['last_run'] ?? $j['last_run_at'] ?? 'Never',
                $j['next_run'] ?? $j['next_run_at'] ?? '?'
            );
        }
    } catch (\Throwable $e) {
        echo "No scheduler_jobs table or error: " . $e->getMessage() . "\n";
    }
    exit(0);
}

// ─── Task Registry ───
$tasks = [
    'backup' => 'core/tasks/backuptask.php',
    'cleanup' => 'core/tasks/cleanuptask.php',
    'email-queue' => 'core/tasks/emailqueuetask.php',
    'cache' => 'core/tasks/cacherefreshertask.php',
    'log-rotate' => 'core/tasks/logrotationtask.php',
    'session-clean' => 'core/tasks/sessioncleanertask.php',
    'temp-clean' => 'core/tasks/tempcleanertask.php',
];

// --task=NAME: run specific task
if (!empty($args['task'])) {
    $taskName = $args['task'];
    if (!isset($tasks[$taskName])) {
        cron_log("Unknown task: {$taskName}. Available: " . implode(', ', array_keys($tasks)));
        exit(1);
    }
    $taskFile = CMS_ROOT . '/' . $tasks[$taskName];
    if (!file_exists($taskFile)) {
        cron_log("Task file not found: {$taskFile}");
        exit(1);
    }
    cron_log("Running task: {$taskName}", $verbose);
    try {
        require_once $taskFile;
        cron_log("Task {$taskName} completed", $verbose);
    } catch (\Throwable $e) {
        cron_log("Task {$taskName} FAILED: " . $e->getMessage());
        exit(1);
    }
    exit(0);
}

// ─── Default: Run all due scheduler jobs from DB ───
cron_log("Cron started", $verbose);
$ran = 0;

try {
    // Check if scheduler_jobs table exists
    $pdo->query("SELECT 1 FROM scheduler_jobs LIMIT 1");
    
    $stmt = $pdo->prepare("SELECT * FROM scheduler_jobs WHERE (enabled = 1 OR is_active = 1) AND (next_run IS NULL OR next_run <= NOW())");
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($jobs as $job) {
        $jobName = $job['name'] ?? $job['task_name'] ?? 'unknown';
        $taskClass = $job['task_class'] ?? $job['handler'] ?? '';
        
        cron_log("Running job: {$jobName}", $verbose);

        try {
            // If task_class maps to a registered task file
            $taskKey = strtolower(str_replace(['Task', '.php'], '', basename($taskClass)));
            $taskKey = preg_replace('/[^a-z0-9]/', '-', $taskKey);
            
            if (isset($tasks[$taskKey])) {
                require_once CMS_ROOT . '/' . $tasks[$taskKey];
            } elseif (file_exists(CMS_ROOT . '/' . $taskClass)) {
                require_once CMS_ROOT . '/' . $taskClass;
            } elseif (file_exists(CMS_ROOT . '/core/tasks/' . $taskClass)) {
                require_once CMS_ROOT . '/core/tasks/' . $taskClass;
            } else {
                cron_log("  Task handler not found: {$taskClass}");
                continue;
            }

            // Update last_run and calculate next_run
            $interval = $job['interval_minutes'] ?? $job['frequency'] ?? 60;
            $pdo->prepare("UPDATE scheduler_jobs SET last_run = NOW(), next_run = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?")
                ->execute([(int)$interval, $job['id']]);
            
            $ran++;
            cron_log("  Completed: {$jobName}", $verbose);
        } catch (\Throwable $e) {
            cron_log("  FAILED {$jobName}: " . $e->getMessage());
            // Update last_run even on failure, push next_run
            $pdo->prepare("UPDATE scheduler_jobs SET last_run = NOW(), last_error = ?, next_run = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?")
                ->execute([$e->getMessage(), $job['id']]);
        }
    }
} catch (\Throwable $e) {
    // scheduler_jobs table might not exist
    cron_log("Scheduler table not available, running built-in tasks", $verbose);
    
    // Run essential tasks directly
    foreach (['email-queue', 'session-clean', 'temp-clean'] as $essential) {
        $file = CMS_ROOT . '/' . $tasks[$essential];
        if (file_exists($file)) {
            try {
                require_once $file;
                $ran++;
                cron_log("  Ran built-in: {$essential}", $verbose);
            } catch (\Throwable $e) {
                cron_log("  Failed built-in {$essential}: " . $e->getMessage());
            }
        }
    }
}

cron_log("Cron finished. Ran {$ran} tasks.", $verbose);
