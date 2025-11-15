<?php
/**
 * Workflow Execution Engine
 * Processes 50-phase workflows with dependency resolution
 */

class WorkflowExecutor {
    private $workflowFile;
    private $logFile;
    private $stateFile;
    private $workflowData;
    private $completedTasks = [];
    private $pendingTasks = [];
    
    public function __construct(string $workflowFile, string $logFile) {
        $this->workflowFile = $workflowFile;
        $this->logFile = $logFile;
        $this->stateFile = dirname($workflowFile) . '/execution_state.json';
        
        // Load workflow definition
        $this->loadWorkflow();
        
        // Load execution state if exists
        $this->loadState();
    }
    
    private function loadWorkflow(): void {
        if (!file_exists($this->workflowFile)) {
            throw new RuntimeException("Workflow file not found: {$this->workflowFile}");
        }
        
        $json = file_get_contents($this->workflowFile);
        $this->workflowData = json_decode($json, true);
        if ($this->workflowData === null) {
            throw new RuntimeException("Failed to parse workflow JSON");
        }
        
        $this->log("Workflow loaded successfully with " . count($this->workflowData['phases']) . " phases");
    }
    
    private function loadState(): void {
        if (file_exists($this->stateFile)) {
            $state = json_decode(file_get_contents($this->stateFile), true);
            $this->completedTasks = $state['completed'] ?? [];
            $this->pendingTasks = $state['pending'] ?? [];
            $this->log("Loaded execution state with " . count($this->completedTasks) . " completed tasks");
        } else {
            $this->initializeTasks();
        }
    }
    
    private function initializeTasks(): void {
        foreach ($this->workflowData['phases'] as $phase) {
            foreach ($phase['tasks'] as $task) {
                $this->pendingTasks[$task['task']] = $task;
            }
        }
        $this->log("Initialized workflow with " . count($this->pendingTasks) . " total tasks");
    }
    
    private function saveState(): void {
        $state = [
            'completed' => $this->completedTasks,
            'pending' => $this->pendingTasks
        ];
        file_put_contents($this->stateFile, json_encode($state));
    }
    
    public function execute(): void {
        $this->log("Starting workflow execution");
        
        while (!empty($this->pendingTasks)) {
            $executed = false;
            
            foreach ($this->pendingTasks as $taskId => $task) {
                if ($this->canExecute($task)) {
                    $this->executeTask($task);
                    $executed = true;
                }
            }
            
            if (!$executed) {
                $this->log("No tasks ready for execution - possible circular dependency");
                break;
            }
        }
        
        $this->log("Workflow execution completed");
    }
    
    private function canExecute(array $task): bool {
        foreach ($task['dependencies'] as $dep) {
            if (!isset($this->completedTasks[$dep])) {
                return false;
            }
        }
        return true;
    }
    
    private function executeTask(array $task): void {
        $taskId = $task['task'];
        $this->log("Executing task: {$taskId} - {$task['name']}");
        
        try {
            // Simulate task execution
            $this->simulateTaskWork($task);
            
            // Mark as completed
            $this->completedTasks[$taskId] = $task;
            unset($this->pendingTasks[$taskId]);
            $this->saveState();
            
            $this->log("Task completed: {$taskId}");
        } catch (Exception $e) {
            $this->log("Task failed: {$taskId} - " . $e->getMessage());
            // Continue with next task despite failure
        }
    }
    
    private function simulateTaskWork(array $task): void {
        // In a real implementation, this would perform the actual task work
        $duration = $this->parseDuration($task['estimated_duration']);
        sleep(min($duration, 1)); // Simulate work with max 1 second delay
        
        // Randomly fail 5% of tasks to test error handling
        if (rand(1, 100) <= 5) {
            throw new RuntimeException("Simulated task failure");
        }
    }
    
    private function parseDuration(string $duration): int {
        // Convert "2h" or "30m" to seconds
        if (str_ends_with($duration, 'h')) {
            return (int)substr($duration, 0, -1) * 3600;
        }
        if (str_ends_with($duration, 'm')) {
            return (int)substr($duration, 0, -1) * 60;
        }
        return 60; // Default 1 minute
    }
    
    private function log(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}

// Main execution
try {
    $executor = new WorkflowExecutor(
        __DIR__ . '/50phase_workflow.json',
        __DIR__ . '/execution.log'
    );
    $executor->execute();
} catch (Exception $e) {
    file_put_contents(
        __DIR__ . '/execution.log',
        "[ERROR] " . date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n",
        FILE_APPEND
    );
    exit(1);
}
