<?php
class TaskScheduler {
    private static $instance;
    private $pdo;
    private $taskQueue = [];
    private $running = false;

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function getInstance(\PDO $pdo): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function scheduleTask(
        string $taskName,
        callable $task,
        int $priority = 0,
        ?DateTime $runAt = null
    ): string {
        $taskId = uniqid('task_');
        $this->taskQueue[$taskId] = [
            'task' => $task,
            'priority' => $priority,
            'run_at' => $runAt ?? new DateTime(),
            'status' => 'pending'
        ];
        return $taskId;
    }

    public function runNextTask(): void {
        if ($this->running || empty($this->taskQueue)) {
            return;
        }

        $this->running = true;
        usort($this->taskQueue, function($a, $b) {
            return $b['priority'] <=> $a['priority'] 
                ?: $a['run_at'] <=> $b['run_at'];
        });

        $nextTask = array_shift($this->taskQueue);
        try {
            $nextTask['status'] = 'running';
            call_user_func($nextTask['task']);
            $nextTask['status'] = 'completed';
        } catch (\Exception $e) {
            $nextTask['status'] = 'failed';
            error_log("Task failed: " . $e->getMessage());
        } finally {
            $this->running = false;
        }
    }

    public function getQueueSize(): int {
        return count($this->taskQueue);
    }
}
