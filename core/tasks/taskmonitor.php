<?php
/**
 * Task Monitor - Tracks task status and health
 */
class TaskMonitor {
    const FAILURE_THRESHOLD = 3;
    const INACTIVE_THRESHOLD = 86400; // 24 hours

    /**
     * Get overall system status
     * @return array Status summary
     */
    public static function getSystemStatus(): array {
        $status = [
            'total_tasks' => 0,
            'active_tasks' => 0,
            'inactive_tasks' => 0,
            'failing_tasks' => 0,
            'last_execution' => null
        ];

        $tasks = TaskScheduler::getAllTasks();
        $status['total_tasks'] = count($tasks);

        foreach ($tasks as $task) {
            if ($task['failures'] >= self::FAILURE_THRESHOLD) {
                $status['failing_tasks']++;
            }

            if ($task['last_run'] && (time() - $task['last_run']) > self::INACTIVE_THRESHOLD) {
                $status['inactive_tasks']++;
            } else {
                $status['active_tasks']++;
            }

            if (!$status['last_execution'] || $task['last_run'] > $status['last_execution']) {
                $status['last_execution'] = $task['last_run'];
            }
        }

        return $status;
    }

    /**
     * Check for failing tasks
     * @return array List of failing tasks
     */
    public static function getFailingTasks(): array {
        $failing = [];
        $tasks = TaskScheduler::getAllTasks();

        foreach ($tasks as $name => $task) {
            if ($task['failures'] >= self::FAILURE_THRESHOLD) {
                $failing[$name] = [
                    'failures' => $task['failures'],
                    'last_run' => $task['last_run'],
                    'next_run' => $task['next_run']
                ];
            }
        }

        return $failing;
    }

    /**
     * Check for inactive tasks
     * @return array List of inactive tasks
     */
    public static function getInactiveTasks(): array {
        $inactive = [];
        $tasks = TaskScheduler::getAllTasks();

        foreach ($tasks as $name => $task) {
            if (!$task['last_run'] || (time() - $task['last_run']) > self::INACTIVE_THRESHOLD) {
                $inactive[$name] = [
                    'last_run' => $task['last_run'],
                    'next_run' => $task['next_run']
                ];
            }
        }

        return $inactive;
    }
}
