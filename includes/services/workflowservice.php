<?php

class WorkflowService
{
    private $db;
    private $conditionEvaluator;
    private $scheduler;
    private $contentService;

    public function __construct(
        $db,
        ConditionEvaluator $conditionEvaluator,
        ContentService $contentService,
        SchedulerInterface $scheduler = null
    ) {
        $this->db = $db;
        $this->conditionEvaluator = $conditionEvaluator;
        $this->contentService = $contentService;
        $this->scheduler = $scheduler;
    }

    public function evaluateTriggers(string $triggerType, array $context = [])
    {
        $triggers = $this->db->query(
            "SELECT * FROM workflow_triggers WHERE trigger_type = ?",
            [$triggerType]
        )->fetchAll();

        $matchedWorkflows = [];
        foreach ($triggers as $trigger) {
            $config = json_decode($trigger['trigger_config'], true);
            if ($this->conditionEvaluator->evaluate($config['conditions'], $context)) {
                if (!$this->hasConflicts($trigger['workflow_id'], $context)) {
                    $matchedWorkflows[] = $trigger['workflow_id'];
                }
            }
        }

        return array_unique($matchedWorkflows);
    }

    private function hasConflicts(int $workflowId, array $context): bool
    {
        // Check for concurrent executions
        $running = $this->db->query(
            "SELECT COUNT(*) FROM workflow_executions
             WHERE workflow_id = ? AND status = 'running'",
            [$workflowId]
        )->fetchColumn();

        if ($running > 0) {
            return true;
        }

        // Check for resource conflicts
        if (isset($context['resource_id'])) {
            $locked = $this->db->query(
                "SELECT COUNT(*) FROM resource_locks
                 WHERE resource_id = ? AND expires_at > NOW()",
                [$context['resource_id']]
            )->fetchColumn();

            if ($locked > 0) {
                return true;
            }
        }

        // Check scheduling conflicts if scheduler is available
        if ($this->scheduler && isset($context['scheduled_time'])) {
            return $this->scheduler->hasConflict(
                $workflowId,
                new DateTime($context['scheduled_time'])
            );
        }

        return false;
    }

    public function executeWorkflow(int $workflowId, array $context = [])
    {
        // Start workflow execution tracking
        $executionId = $this->startExecution($workflowId, $context);

        try {
            $actions = $this->db->query(
                "SELECT * FROM workflow_actions
                 WHERE workflow_id = ?
                 ORDER BY execution_order ASC",
                [$workflowId]
            )->fetchAll();

            $results = [];
            foreach ($actions as $action) {
                try {
                    $this->acquireResources($action, $context);
                    $results[] = $this->executeAction(
                        $action['action_type'],
                        json_decode($action['action_config'], true),
                        $context
                    );
                } catch (Exception $e) {
                    $this->handleFailure($executionId, $workflowId, $action['id'], $e);
                    throw $e;
                }
            }

            $this->completeExecution($executionId);
            return $results;
        } catch (Exception $e) {
            $this->failExecution($executionId, $e);
            throw $e;
        }
    }

    private function startExecution(int $workflowId, array $context): int
    {
        $this->db->query(
            "INSERT INTO workflow_executions
             (workflow_id, status, context, started_at)
             VALUES (?, 'running', ?, NOW())",
            [$workflowId, json_encode($context)]
        );
        return $this->db->lastInsertId();
    }

    private function completeExecution(int $executionId)
    {
        $this->db->query(
            "UPDATE workflow_executions
             SET status = 'completed', completed_at = NOW()
             WHERE id = ?",
            [$executionId]
        );
    }

    private function failExecution(int $executionId, Exception $e)
    {
        $this->db->query(
            "UPDATE workflow_executions
             SET status = 'failed', error_message = ?, completed_at = NOW()
             WHERE id = ?",
            [$e->getMessage(), $executionId]
        );
    }

    private function acquireResources(array $action, array $context)
    {
        if (isset($action['resource_keys'])) {
            foreach ($action['resource_keys'] as $key) {
                if (isset($context[$key])) {
                    $this->db->query(
                        "INSERT INTO resource_locks
                         (resource_id, workflow_id, action_id, expires_at)
                         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))",
                        [$context[$key], $action['workflow_id'], $action['id']]
                    );
                }
            }
        }
    }

    private function handleFailure(int $executionId, int $workflowId, int $actionId, Exception $e)
    {
        $this->logError($workflowId, $actionId, $e);
        $this->releaseResources($workflowId, $actionId);
    }

    private function releaseResources(int $workflowId, int $actionId)
    {
        $this->db->query(
            "DELETE FROM resource_locks
             WHERE workflow_id = ? AND action_id = ?",
            [$workflowId, $actionId]
        );
    }

    private function executeAction(string $type, array $config, array $context)
    {
        switch ($type) {
            case 'content_publish':
                return $this->executeContentPublish($config, $context);
            case 'n8n_webhook':
                return $this->executeN8nWebhook($config, $context);
            default:
                throw new InvalidArgumentException("Unknown action type: $type");
        }
    }

    private function executeN8nWebhook(array $config, array $context)
    {
        $url = $config['webhook_url'];
        $payload = $this->interpolatePayload($config['payload'], $context);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("n8n webhook failed with status $status");
        }

        return json_decode($response, true) ?? [];
    }

    private function executeContentPublish($contentIdOrConfig, array $context = []): bool
    {
        error_log("Executing content publish with: " . print_r($contentIdOrConfig, true));
        error_log("Context: " . print_r($context, true));
        $contentId = is_array($contentIdOrConfig)
            ? ($contentIdOrConfig['content_id'] ?? null)
            : $contentIdOrConfig;

        if (empty($contentId)) {
            throw new InvalidArgumentException("Missing content_id");
        }

        $this->db->beginTransaction();
        try {
            $result = $this->contentService->publishContent($contentId);
            if (!$result) {
                throw new RuntimeException("Content publish failed");
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Workflow publish failed for content {$contentId}: " . $e->getMessage());
            throw $e;
        }
    }

    private function interpolatePayload(array $payload, array $context): array
    {
        array_walk_recursive($payload, function (&$value) use ($context) {
            if (is_string($value)) {
                $value = str_replace(
                    array_map(fn($k) => "{{$k}}", array_keys($context)),
                    array_values($context),
                    $value
                );
            }
        });
        return $payload;
    }

    private function logError(int $workflowId, int $actionId, Exception $e)
    {
        $this->db->query(
            "INSERT INTO workflow_errors
             (workflow_id, action_id, error_message, created_at)
             VALUES (?, ?, ?, NOW())",
            [$workflowId, $actionId, $e->getMessage()]
        );
    }

    public function resolveConflict(int $workflowId, string $strategy, array $context = [])
    {
        switch ($strategy) {
            case 'queue':
                return $this->queueWorkflow($workflowId, $context);
            case 'priority':
                return $this->prioritizeWorkflow($workflowId, $context);
            case 'manual':
                return $this->requestManualResolution($workflowId, $context);
            default:
                throw new InvalidArgumentException("Unknown conflict resolution strategy: $strategy");
        }
    }

    private function queueWorkflow(int $workflowId, array $context): bool
    {
        $this->db->query(
            "INSERT INTO workflow_queue
             (workflow_id, context, created_at)
             VALUES (?, ?, NOW())",
            [$workflowId, json_encode($context)]
        );
        return true;
    }

    private function prioritizeWorkflow(int $workflowId, array $context): bool
    {
        $canPreempt = $this->db->query(
            "SELECT COUNT(*) FROM workflow_executions
             WHERE workflow_id IN (
                 SELECT id FROM workflows
                 WHERE priority < (
                     SELECT priority FROM workflows WHERE id = ?
                 )
             ) AND status = 'running'",
            [$workflowId]
        )->fetchColumn();

        if ($canPreempt > 0) {
            $this->db->query(
                "UPDATE workflow_executions
                 SET status = 'preempted'
                 WHERE workflow_id IN (
                     SELECT id FROM workflows
                     WHERE priority < (
                         SELECT priority FROM workflows WHERE id = ?
                     )
                 ) AND status = 'running'",
                [$workflowId]
            );
            return true;
        }

        return false;
    }

    private function requestManualResolution(int $workflowId, array $context): bool
    {
        $this->db->query(
            "INSERT INTO workflow_conflicts
             (workflow_id, context, status, created_at)
             VALUES (?, ?, 'pending', NOW())",
            [$workflowId, json_encode($context)]
        );
        return false;
    }
}
