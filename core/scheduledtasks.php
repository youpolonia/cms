<?php
declare(strict_types=1);

class ScheduledTasks {
    private static array $tasks = [
        'daily_report' => [
            'last_run' => null,
            'interval' => 86400 // 24 hours in seconds
        ],
        'scheduled_content_publish' => [
            'last_run' => null,
            'interval' => 300 // 5 minutes in seconds
        ]
    ];

    public static function runPendingTasks(): void {
        foreach (self::$tasks as $name => &$task) {
            if ($task['last_run'] === null || 
                time() - $task['last_run'] >= $task['interval']) {
                
                if (self::executeTask($name)) {
                    $task['last_run'] = time();
                }
            }
        }
    }

    private static function executeTask(string $name): bool {
        switch ($name) {
            case 'daily_report':
                $tenants = DB::select("SELECT DISTINCT tenant_id FROM tenant_analytics");
                foreach ($tenants as $tenant) {
                    ReportGenerator::generateDailyReport($tenant['tenant_id']);
                }
                return true;
            case 'scheduled_content_publish':
                require_once __DIR__ . '/../includes/system/ScheduledContentExecutor.php';
                $executor = new ScheduledContentExecutor();
                $executor->execute();
                return true;
            default:
                return false;
        }
    }
}
