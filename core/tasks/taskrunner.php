<?php
/**
 * Task Runner - Executes scheduled tasks
 */
class TaskRunner {
    const MAX_EXECUTION_TIME = 300; // 5 minutes
    const MEMORY_LIMIT = '256M';

    /**
     * Execute a specific task by name
     * @param string $taskName Name of registered task
     * @return array Execution result
     */
    public static function execute(string $taskName): array {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', self::MEMORY_LIMIT);

        $task = TaskScheduler::getStatus($taskName);
        if (!$task) {
            NotificationManager::queueNotification(
                'error',
                'Scheduled task not found: ' . $taskName,
                ['task' => $taskName, 'timestamp' => date('c')]
            );
            return ['success' => false, 'error' => 'Task not found'];
        }

        $startTime = microtime(true);
        $memoryBefore = memory_get_usage();

        try {
            $result = call_user_func($task['callback']);
            $executionTime = round(microtime(true) - $startTime, 3);
            $memoryUsed = round((memory_get_usage() - $memoryBefore) / 1024 / 1024, 2);

            return [
                'success' => true,
                'result' => $result,
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed
            ];
        } catch (Throwable $e) {
            NotificationManager::queueNotification(
                'error',
                'Scheduled task failed: ' . $taskName,
                [
                    'task' => $taskName,
                    'timestamp' => date('c'),
                    'error' => $e->getMessage()
                ]
            );
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Execute all due tasks
     * @return array Summary of executions
     */
    public static function runAllDue(): array {
        $results = [];
        $tasks = TaskScheduler::getAllTasks();
        
        foreach ($tasks as $name => $task) {
            if ($task['next_run'] <= time()) {
                $result = self::execute($name);
                $results[$name] = $result;
                
                if ($result['success'] && in_array($name, ['backup', 'ai_cleanup'])) {
                    NotificationManager::queueNotification(
                        'info',
                        'Scheduled task completed: ' . $name,
                        [
                            'task' => $name,
                            'execution_time' => $result['execution_time'] ?? null,
                            'memory_used' => $result['memory_used'] ?? null
                        ]
                    );
                }
            }
        }

        return $results;
    }
}
