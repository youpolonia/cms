<?php
class WorkflowEngine {
    private static $instance;
    private $pdo;
    private $workflows = [];
    private $activeTasks = [];

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function getInstance(\PDO $pdo): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function registerWorkflow(string $name, callable $handler): void {
        $this->workflows[$name] = $handler;
    }

    public function executeWorkflow(string $name, array $params = []) {
        if (!isset($this->workflows[$name])) {
            throw new \InvalidArgumentException("Workflow $name not registered");
        }
        return $this->workflows[$name]($params);
    }

    public function trackTask(string $taskId, array $metadata = []): void {
        $this->activeTasks[$taskId] = [
            'start_time' => microtime(true),
            'metadata' => $metadata
        ];
    }

    public function completeTask(string $taskId): array {
        if (!isset($this->activeTasks[$taskId])) {
            throw new \InvalidArgumentException("Task $taskId not found");
        }
        
        $task = $this->activeTasks[$taskId];
        $task['end_time'] = microtime(true);
        $task['duration'] = $task['end_time'] - $task['start_time'];
        
        unset($this->activeTasks[$taskId]);
        return $task;
    }
}
