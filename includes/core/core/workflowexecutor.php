<?php
class WorkflowExecutor {
    private $stateManager;
    
    public function __construct(StateManager $stateManager) {
        $this->stateManager = $stateManager;
    }

    public function execute(string $workflowId, array $payload): array {
        try {
            $state = $this->stateManager::loadState($workflowId);
            
            // Merge payload with current state
            $newState = array_merge($state, $payload, [
                'last_executed' => date('c'),
                'execution_count' => ($state['execution_count'] ?? 0) + 1
            ]);

            $this->stateManager::saveState($workflowId, $newState);
            
            return $newState;
        } catch (StateManagerException $e) {
            error_log("Workflow execution failed: " . $e->getMessage());
            throw new WorkflowExecutionException("Workflow processing error", 0, $e);
        }
    }
}

class WorkflowExecutionException extends RuntimeException {}
