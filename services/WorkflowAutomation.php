<?php

class WorkflowAutomation {
    private $workflowService;
    private $notificationHandler;
    private $aiService;

    public function __construct(
        NotificationHandler $notificationHandler,
        ?AIService $aiService = null,
        ?WorkflowService $workflowService = null
    ) {
        $this->notificationHandler = $notificationHandler;
        $this->aiService = $aiService;
        $this->workflowService = $workflowService;
    }

    private function validatePayload(array $payload): bool {
        $required = ['id', 'title', 'slug', 'userId', 'timestamp'];
        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                return false;
            }
        }
        return true;
    }

    private function logAction(string $eventType, array $context, string $status): void {
        if ($this->workflowService) {
            $this->workflowService->getAuditService()->logAction($eventType, $context, $status);
        } else {
            // Fallback to direct file logging if WorkflowService not available
            $logDir = __DIR__ . '/../../logs/workflows/';
            $logData = [
                'timestamp' => date('c'),
                'eventType' => $eventType,
                'status' => $status,
                'context' => $context,
                'triggerSource' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
                'userId' => $context['userId'] ?? null
            ];
            file_put_contents($logDir . date('Y-m-d') . '.log', json_encode($logData) . PHP_EOL, FILE_APPEND);
        }
    }

    public function addTrigger(string $type, callable $condition, array $actions, ?string $id = null): void {
        if (str_starts_with($type, 'ai_') && !$this->aiService) {
            throw new RuntimeException('AIService required for AI triggers');
        }
        
        if ($this->workflowService) {
            $this->workflowService->addTrigger($type, $condition, $actions, $id);
        } else {
            // Fallback to local trigger storage if WorkflowService not available
            $trigger = ['type' => $type, 'condition' => $condition, 'actions' => $actions];
            if ($type === 'manual' && $id) {
                $trigger['id'] = $id;
            }
            $this->triggers[] = $trigger;
        }
    }

    public function checkDatabaseTriggers(array $changes): void {
        if ($this->workflowService) {
            $this->workflowService->checkDatabaseTriggers($changes);
        } else {
            // Fallback to local trigger checking if WorkflowService not available
            foreach ($this->triggers as $trigger) {
                if ($trigger['type'] === 'database') {
                    if (isset($changes['old']['slug']) && isset($changes['new']['slug']) &&
                        $changes['old']['slug'] !== $changes['new']['slug']) {
                        $this->logAction('slug_change', $changes, 'detected');
                    }

                    if ($trigger['condition']($changes)) {
                        $this->executeActions($trigger['actions'], $changes);
                    }
                }
            }
        }
    }

    public function checkTimeBasedTriggers(): void {
        if ($this->workflowService) {
            $this->workflowService->checkTimeBasedTriggers();
        } else {
            // Fallback to local trigger checking if WorkflowService not available
            foreach ($this->triggers as $trigger) {
                if ($trigger['type'] === 'time' && $trigger['condition']()) {
                    $this->executeActions($trigger['actions']);
                }
            }
        }
    }

    public function executeManualTrigger(string $triggerId, array $params = []): void {
        if ($this->workflowService) {
            $this->workflowService->executeManualTrigger($triggerId, $params);
        } else {
            // Fallback to local trigger execution if WorkflowService not available
            foreach ($this->triggers as $trigger) {
                if ($trigger['type'] === 'manual' && $trigger['id'] === $triggerId) {
                    $this->executeActions($trigger['actions'], $params);
                }
            }
        }
    }

    private function executeActions(array $actions, array $context = []): void {
        try {
            foreach ($actions as $action) {
                if (is_callable($action)) {
                    $action($context);
                } elseif (is_string($action) && method_exists($this, $action)) {
                    $this->$action($context);
                }
            }
            $this->logAction('action_executed', $context, 'success');
        } catch (Exception $e) {
            $this->logAction('action_executed', $context, 'failed');
            throw $e;
        }
    }

    public function registerWebhook(string $url, string $eventType): void {
        $this->notificationHandler->registerWebhook($url, $eventType);
    }

    public function handleWebhook(string $eventType, array $payload): void {
        if (!$this->validatePayload($payload)) {
            $this->logAction($eventType, $payload, 'invalid_payload');
            throw new InvalidArgumentException('Invalid payload: missing required fields');
        }

        $this->logAction($eventType, $payload, 'received');
        $this->executeActions($this->getActionsForEvent($eventType), $payload);
    }

    private function getActionsForEvent(string $eventType): array {
        return array_filter($this->actions, fn($action) => $action['eventType'] === $eventType);
    }
}
