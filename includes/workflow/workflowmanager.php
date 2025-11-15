<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/approvalengine.php';
require_once __DIR__ . '/../services/notificationservice.php';
require_once __DIR__.'/../services/auditlogger.php';

class WorkflowManager {
    public static function triggerWorkflow(
        string $triggerType,
        array $payload,
        string $initiatorId
    ): bool {
        // Validate trigger type
        if (!in_array($triggerType, ['content_update', 'user_action', 'scheduled'])) {
            return false;
        }

        // Process workflow
        $result = self::processTrigger($triggerType, $payload);

        // Log and notify
        if ($result) {
            AuditLogger::log(
                $payload['workflow_id'] ?? 0,
                'trigger_'.$triggerType,
                $initiatorId,
                json_encode($payload)
            );
        }

        return $result;
    }

    private static function processTrigger(string $triggerType, array $payload): bool {
        $workflowId = $payload['workflow_id'] ?? 0;
        
        try {
            // Get active triggers for this workflow
            $triggers = self::getActiveTriggers($workflowId, $triggerType);
            
            foreach ($triggers as $trigger) {
                // Validate trigger conditions
                if (!self::validateTriggerConditions($trigger, $payload)) {
                    continue;
                }
                
                // Execute associated actions
                self::executeTriggerActions($trigger['id'], $payload);
                
                // Log successful trigger
                AuditLogger::log(
                    $workflowId,
                    'trigger_executed',
                    $payload['initiator_id'] ?? 'system',
                    json_encode(['trigger_id' => $trigger['id']])
                );
            }
            
            return true;
        } catch (Exception $e) {
            AuditLogger::log(
                $workflowId,
                'trigger_error',
                'system',
                $e->getMessage()
            );
            return false;
        }
    }
    
    private static function getActiveTriggers(int $workflowId, string $triggerType): array {
        $db = \core\Database::connection();
        $query = "SELECT * FROM workflow_triggers
                 WHERE workflow_id = ? AND trigger_type = ? AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$workflowId, $triggerType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private static function validateTriggerConditions(array $trigger, array $payload): bool {
        if (empty($trigger['conditions'])) {
            return true;
        }
        
        $conditions = json_decode($trigger['conditions'], true);
        foreach ($conditions as $condition) {
            if (!self::evaluateCondition($condition, $payload)) {
                return false;
            }
        }
        return true;
    }
    
    private static function evaluateCondition(array $condition, array $payload): bool {
        $fieldValue = $payload[$condition['field']] ?? null;
        
        switch ($condition['operator']) {
            case 'equals':
                return $fieldValue == $condition['value'];
            case 'contains':
                return str_contains((string)$fieldValue, (string)$condition['value']);
            case 'greater_than':
                return $fieldValue > $condition['value'];
            default:
                return false;
        }
    }
    
    public static function processContentUpdateTrigger(array $contentData, string $initiatorId): bool {
        $payload = [
            'workflow_id' => $contentData['workflow_id'] ?? 0,
            'content_id' => $contentData['id'],
            'content_type' => $contentData['type'],
            'status' => $contentData['status'],
            'initiator_id' => $initiatorId
        ];
        
        return self::triggerWorkflow('content_update', $payload, $initiatorId);
    }
    
    public static function processUserActionTrigger(array $userData, string $actionType, string $initiatorId): bool {
        $payload = [
            'workflow_id' => $userData['workflow_id'] ?? 0,
            'user_id' => $userData['id'],
            'action_type' => $actionType,
            'initiator_id' => $initiatorId
        ];
        
        return self::triggerWorkflow('user_action', $payload, $initiatorId);
    }
    
    public static function processScheduledTrigger(int $workflowId, string $scheduleId): bool {
        $payload = [
            'workflow_id' => $workflowId,
            'schedule_id' => $scheduleId,
            'initiator_id' => 'system'
        ];
        
        return self::triggerWorkflow('scheduled', $payload, 'system');
    }
    
    private static function executeTriggerActions(int $triggerId, array $payload): void {
        $db = \core\Database::connection();
        $query = "SELECT * FROM workflow_actions WHERE trigger_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$triggerId]);
        $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($actions as $action) {
            try {
                switch ($action['action_type']) {
                    case 'notification':
                        NotificationService::send(
                            $action['config']['recipient'],
                            $action['config']['message'],
                            $payload
                        );
                        break;
                    case 'webhook':
                        WebhookDispatcher::send(
                            $action['config']['url'],
                            $payload
                        );
                        break;
                }
            } catch (Exception $e) {
                AuditLogger::log(
                    $payload['workflow_id'] ?? 0,
                    'action_error',
                    'system',
                    $e->getMessage()
                );
            }
        }
    }
}
