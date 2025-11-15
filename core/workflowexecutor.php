<?php
require_once __DIR__ . '/workflowstepprocessor.php';
require_once __DIR__ . '/workflowlogger.php';

class WorkflowExecutor {
    private $workflowId;
    private $workflowData;
    private $executionId;
    private $context = [];
    private $logger;

    public function __construct($workflowId, $inputVars = []) {
        $this->workflowId = $workflowId;
        $this->context = $inputVars;
        $this->executionId = uniqid('wf_', true);
        $this->logger = new WorkflowLogger();

        $filePath = "storage/workflows/{$this->workflowId}.json";
        if (!file_exists($filePath)) {
            throw new Exception("Workflow not found");
        }
        
        $this->workflowData = json_decode(file_get_contents($filePath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid workflow schema");
        }
    }

    public function execute() {
        $this->logger->startLog($this->executionId, $this->workflowId);

        try {
            foreach ($this->workflowData['steps'] as $stepId => $step) {
                $processor = new WorkflowStepProcessor($step, $this->context);
                $result = $processor->execute();

                $this->context = array_merge($this->context, $result['output_vars'] ?? []);
                $this->logger->logStep($this->executionId, $stepId, $result);

                if ($result['status'] === 'failed') {
                    throw new Exception("Step {$stepId} failed: " . ($result['error'] ?? 'Unknown error'));
                }
            }

            $this->logger->finalizeLog($this->executionId, 'completed');
            return [
                'status' => 'success',
                'execution_id' => $this->executionId,
                'output_vars' => $this->context
            ];
        } catch (Exception $e) {
            $this->logger->finalizeLog($this->executionId, 'failed', $e->getMessage());
            throw $e;
        }
    }
}
