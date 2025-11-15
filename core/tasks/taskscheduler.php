<?php
/**
 * Task Scheduler - Core task management system
 */
class TaskScheduler {
    private static $tasks = [];
    private static $running = false;

    /**
     * Register a new scheduled task
     * @param string $name Unique task identifier
     * @param callable $callback Task execution callback
     * @param array $schedule {
     *     @type string $frequency daily|hourly|weekly|custom
     *     @type string $time For daily/weekly - execution time (HH:MM)
     *     @type int $interval For custom - minutes between runs
     * }
     * @return bool True if registered successfully
     */
    public static function register(string $name, callable $callback, array $schedule): bool {
        if (isset(self::$tasks[$name])) {
            return false;
        }

        self::$tasks[$name] = [
            'callback' => $callback,
            'schedule' => $schedule,
            'last_run' => null,
            'next_run' => self::calculateNextRun($schedule),
            'failures' => 0
        ];
        return true;
    }

    /**
     * Calculate next run time based on schedule
     */
    private static function calculateNextRun(array $schedule): ?int {
        $now = time();
        
        switch ($schedule['frequency']) {
            case 'hourly':
                return strtotime('+1 hour', $now - ($now % 3600));
            case 'daily':
                return strtotime($schedule['time'] ?: '00:00');
            case 'weekly':
                return strtotime('next '.($schedule['day'] ?? 'monday').' '.$schedule['time']);
            case 'custom':
                return $now + ($schedule['interval'] * 60);
            default:
                return null;
        }
    }

    /**
     * Run all due tasks
     */
    public static function runDueTasks(): void {
        if (self::$running) return;
        self::$running = true;

        $now = time();
        foreach (self::$tasks as $name => &$task) {
            if ($task['next_run'] <= $now) {
                try {
                    call_user_func($task['callback']);
                    $task['last_run'] = $now;
                    $task['failures'] = 0;
                } catch (Exception $e) {
                    $task['failures']++;
                    // TODO: Log failure
                }
                $task['next_run'] = self::calculateNextRun($task['schedule']);
            }
        }

        self::$running = false;
    }

    /**
     * Get task status
     */
    public static function getStatus(string $name): ?array {
        return self::$tasks[$name] ?? null;
    }
}
