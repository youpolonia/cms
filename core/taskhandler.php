<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/Logger/loggerfactory.php';

class TaskHandler {
    private $db;
    private $logger;
    
    public function __construct() {
        $this->db = \core\Database::connection();
        $this->logger = LoggerFactory::getInstance();
    }
    
    public function processDueTasks(bool $manualTrigger = false): array {
        $tasks = $this->getDueTasks($manualTrigger);
        $results = [];
        
        foreach ($tasks as $task) {
            try {
                $startTime = microtime(true);
                $output = $this->executeTask($task);
                $duration = microtime(true) - $startTime;
                
                $this->updateTaskStatus($task['id'], 'completed', $output);
                
                $results[] = [
                    'task_id' => $task['id'],
                    'status' => 'completed',
                    'duration' => round($duration, 3),
                    'output' => $output
                ];
                
                try {
                    $this->logger->info("Task completed", [
                        'task_id' => $task['id'],
                        'duration' => $duration,
                        'output' => $output
                    ]);
                } catch (Exception $e) {
                    error_log("Failed to log task completion: " . $e->getMessage());
                }
                
            } catch (Exception $e) {
                $this->updateTaskStatus($task['id'], 'failed', $e->getMessage());
                try {
                    $this->logger->error("Task failed", [
                        'task_id' => $task['id'],
                        'error' => $e->getMessage(),
                        'exception' => $e
                    ]);
                } catch (Exception $e) {
                    error_log("Failed to log task failure: " . $e->getMessage());
                }
                
                $results[] = [
                    'task_id' => $task['id'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    private function getDueTasks(bool $includeAll = false): array {
        $query = "SELECT * FROM scheduled_tasks 
                 WHERE status = 'pending' 
                 AND (run_at <= NOW() OR :includeAll)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':includeAll', $includeAll, PDO::PARAM_BOOL);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function executeTask(array $task): string {
        // Implement task execution logic based on task type
        switch ($task['type']) {
            case 'command':
                return $this->executeCommandTask($task);
            case 'http':
                return $this->executeHttpTask($task);
            case 'script':
                return $this->executeScriptTask($task);
            default:
                throw new RuntimeException("Unknown task type: {$task['type']}");
        }
    }
    
    private function updateTaskStatus(int $taskId, string $status, string $output): void {
        $query = "UPDATE scheduled_tasks 
                 SET status = :status, 
                     last_run = NOW(),
                     output = :output
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':output', $output);
        $stmt->bindValue(':id', $taskId);
        $stmt->execute();
    }
    
    // Additional task type handlers would be implemented here
    private function executeCommandTask(array $task): string {
        // Implementation for command tasks
        return 'Command executed successfully';
    }
    
    private function executeHttpTask(array $task): string {
        // Implementation for HTTP tasks
        return 'HTTP request completed';
    }
    
    private function executeScriptTask(array $task): string {
        // Implementation for script tasks
        return 'Script executed successfully';
    }
}
