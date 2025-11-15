<?php
class TaskScheduler {
    private static $instance;
    private $pdo;
    private $logger;

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->logger = LoggerFactory::create('task_scheduler');
    }

    public static function getInstance(\PDO $pdo): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function scheduleRecurringTask(
        string $taskName,
        string $cronExpression,
        string $handlerClass,
        array $parameters = [],
        int $priority = 0
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO scheduled_tasks 
            (task_name, cron_expression, handler_class, parameters, 
             priority, next_run_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 
                    NEXT_RUN_DATE(?), NOW(), NOW())
        ");
        
        $nextRun = $this->calculateNextRun($cronExpression);
        $stmt->execute([
            $taskName,
            $cronExpression,
            $handlerClass,
            json_encode($parameters),
            $priority,
            $nextRun
        ]);
        
        $taskId = $this->pdo->lastInsertId();
        $this->logger->info("Scheduled recurring task", [
            'task_name' => $taskName,
            'task_id' => $taskId
        ]);
        return $taskId;
    }

    public function scheduleOneTimeTask(
        string $taskName,
        DateTime $runAt,
        string $handlerClass,
        array $parameters = [],
        int $priority = 0
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO scheduled_tasks 
            (task_name, run_at, handler_class, parameters, 
             priority, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $taskName,
            $runAt->format('Y-m-d H:i:s'),
            $handlerClass,
            json_encode($parameters),
            $priority
        ]);
        
        $taskId = $this->pdo->lastInsertId();
        $this->logger->info("Scheduled one-time task", [
            'task_name' => $taskName,
            'task_id' => $taskId
        ]);
        return $taskId;
    }

    public function getDueTasks(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM scheduled_tasks
            WHERE (run_at <= NOW() OR next_run_at <= NOW())
            AND status = 'pending'
            ORDER BY priority DESC, run_at ASC
            LIMIT ?
            FOR UPDATE SKIP LOCKED
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function markTaskStarted(int $taskId): bool {
        $stmt = $this->pdo->prepare("
            UPDATE scheduled_tasks
            SET status = 'running',
                started_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
            AND status = 'pending'
        ");
        return $stmt->execute([$taskId]);
    }

    public function markTaskCompleted(int $taskId, bool $reschedule = true): bool {
        $task = $this->getTask($taskId);
        
        if ($reschedule && $task['cron_expression']) {
            $nextRun = $this->calculateNextRun($task['cron_expression']);
            $stmt = $this->pdo->prepare("
                UPDATE scheduled_tasks
                SET status = 'pending',
                    completed_at = NOW(),
                    next_run_at = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$nextRun, $taskId]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE scheduled_tasks
                SET status = 'completed',
                    completed_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$taskId]);
        }
        
        return true;
    }

    private function getTask(int $taskId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM scheduled_tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function calculateNextRun(string $cronExpression): string {
        // Implement cron expression parsing to calculate next run time
        return date('Y-m-d H:i:s', strtotime('+1 hour'));
    }
}
