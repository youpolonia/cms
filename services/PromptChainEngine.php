<?php
class PromptChainEngine {
    private $cloudService;
    private $storage;
    private $logger;
    private $memoryBank;

    public function __construct(
        CloudAIService $cloudService, 
        WorkflowStorage $storage, 
        AuditLogger $logger,
        MemoryBankInterface $memoryBank = null
    ) {
        $this->cloudService = $cloudService;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->memoryBank = $memoryBank ?? new NullMemoryBank();
    }

    public function executeWorkflow(array $workflowDef): array {
        $this->validateWorkflow($workflowDef);
        $context = $workflowDef['variables'] ?? [];
        $results = [];

        foreach ($workflowDef['steps'] as $step) {
            if (isset($step['depends_on'])) {
                if (!$this->checkDependencies($step['depends_on'], $results)) {
                    $this->memoryBank->log('step_skipped', [
                        'step' => $step['name'],
                        'reason' => 'Dependencies not met'
                    ]);
                    continue;
                }
            }

            try {
                $result = $this->executeStep($step, $context);
                $results[$step['name']] = $result;
                
                if (isset($step['output_var'])) {
                    $context[$step['output_var']] = $result;
                }
            } catch (Exception $e) {
                $this->handleStepError($step, $e, $context);
            }
        }

        return $results;
    }

    private function executeStep(array $step, array $context): string {
        $this->logger->logStepStart($step['name']);
        $this->memoryBank->log('step_start', [
            'step' => $step['name'],
            'context' => $context
        ]);
        
        $prompt = $this->interpolateVariables($step['prompt'], $context);
        $result = $this->cloudService->callService(
            $step['service'],
            $prompt,
            $step['timeout'] ?? 30
        );

        $this->logger->logStepComplete($step['name']);
        $this->memoryBank->log('step_complete', [
            'step' => $step['name'],
            'result' => $result
        ]);
        return $result;
    }

    private function handleStepError(array $step, Exception $e, array &$context) {
        $errorHandling = $step['error_handling'] ?? [];
        
        // Retry logic
        if ($errorHandling['retry'] ?? false) {
            for ($i = 0; $i < $errorHandling['retry']; $i++) {
                try {
                    return $this->executeStep($step, $context);
                } catch (Exception $retryEx) {
                    continue;
                }
            }
        }

        // Fallback logic
        if (isset($errorHandling['fallback'])) {
            switch ($errorHandling['fallback']['action']) {
                case 'use_template':
                    $context[$errorHandling['fallback']['var']] =
                        $this->getTemplateContent($errorHandling['fallback']['template_id']);
                    break;
                case 'use_default':
                    $context[$errorHandling['fallback']['var']] =
                        $errorHandling['fallback']['value'];
                    break;
            }
        }
        
        // Logging
        $this->logger->logStepError($step['name'], $e->getMessage());
        $this->memoryBank->append('prompt_chain_errors', [
            'step' => $step['name'],
            'error' => $e->getMessage(),
            'context' => $context,
            'timestamp' => time()
        ]);
        $this->memoryBank->log('step_error', [
            'step' => $step['name'],
            'error' => $e->getMessage(),
            'context' => $context
        ]);
    }

    private function interpolateVariables(string $template, array $context): string {
        return preg_replace_callback('/\{(\w+)\}/', function($matches) use ($context) {
            $value = $context[$matches[1]] ?? null;
            
            if (is_array($value)) {
                return json_encode($value);
            }
            
            return $value ?? $matches[0];
        }, $template);
    }

    private function validateWorkflow(array $workflowDef): void {
        if (empty($workflowDef['name'])) {
            throw new InvalidArgumentException('Workflow must have a name');
        }

        if (empty($workflowDef['steps']) || !is_array($workflowDef['steps'])) {
            throw new InvalidArgumentException('Workflow must have at least one step');
        }

        foreach ($workflowDef['steps'] as $step) {
            if (empty($step['name'])) {
                throw new InvalidArgumentException('Each step must have a name');
            }
            if (empty($step['service'])) {
                throw new InvalidArgumentException('Each step must specify a service');
            }
            if (empty($step['prompt'])) {
                throw new InvalidArgumentException('Each step must have a prompt');
            }
        }
    }

    private function checkDependencies(array $dependencies, array $results): bool {
        foreach ($dependencies as $dep) {
            if (!isset($results[$dep['step']]) || 
                ($dep['required_value'] && $results[$dep['step']] !== $dep['required_value'])) {
                return false;
            }
        }
        return true;
    }

    private function getTemplateContent(string $templateId): string {
        return $this->storage->getTemplate($templateId);
    }
}
