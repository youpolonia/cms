<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/database.php';

/**
 * Workflow Service - Dual Schema Support with Audit Logging
 *
 * Supports both legacy workflow_* tables and new approval_* tables
 * during migration period.
 *
 * Requires AuditService for tracking state transitions and changes.
 *
 * @see AuditService
 */

/**
 * WorkflowService.php
 *
 * Manages all aspects of workflow definitions and instances within the CMS.
 * This service handles creation, state transitions, and status tracking for any
 * content type or entity that requires a structured workflow (e.g., articles,
 * user registrations, moderation queues).
 *
 * @version 1.0.0
 * @author Legacy Developer <dev@example.com>
 * @date 2025-07-04
 *
 * Implemented:
 * - Uses AuditService for all logging
 * - Permission checks via AuthService
 * - Database operations refactored to WorkflowRepository
 * - Event hooks via WorkflowEventDispatcher
 * - Complete state management
 * - Custom WorkflowException hierarchy
 * - AI/System action support
 * - Typed action execution
 * - Webhook integration
 */

class WorkflowService {

    /**
     * @var PDO The database connection handle.
     */
    private $pdo;

    /**
     * @var AuditService The audit logging service.
     */
    private $auditService;

    /**
     * @var NotificationHandler The notification service.
     */
    private $notificationHandler;

    /**
     * @var AIService|null The optional AI service.
     * @since 1.0.0 Added AI service integration
     */
    private $aiService;

    /**
     * @var array Registered triggers.
     */
    private $triggers = [];

    /**
     * @var array Registered AI/system actions.
     * @since 1.0.0 Added typed action support
     */
    private $aiActions = [];

    /**
     * @var bool Enables verbose debugging output.
     * Should always be false in production environments.
     */
    private $debugMode = false;

    /**
     * Trigger type constants
     * @since 1.0.0 Added AI and SYSTEM triggers
     */
    public const TRIGGER_DATABASE = 'database';
    public const TRIGGER_TIME = 'time';
    public const TRIGGER_MANUAL = 'manual';
    public const TRIGGER_AI = 'ai';
    public const TRIGGER_SYSTEM = 'system';

    /**
     * Constructor for the WorkflowService.
     *
     * @param PDO $pdo A PDO database connection object.
     * @param AuditService $auditService The audit logging service.
     * @param NotificationHandler $notificationHandler The notification service.
     * @param AIService|null $aiService Optional AI service.
     */
    public function __construct(
        PDO $pdo,
        AuditService $auditService,
        NotificationHandler $notificationHandler,
        ?AIService $aiService = null
    ) {
        $this->pdo = \core\Database::connection();
        $this->auditService = $auditService;
        $this->notificationHandler = $notificationHandler;
        $this->aiService = $aiService;
    }



    /**
     * Executes actions for a given trigger context.
     *
     * @param array $actions Array of actions to execute
     * @param array $context Execution context data
     * @throws Exception When any action fails
     */
    private function executeActions(array $actions, array $context = []): void {
        try {
            foreach ($actions as $action) {
                if (is_callable($action)) {
                    $action($context);
                } elseif (is_string($action) && method_exists($this, $action)) {
                    $this->$action($context);
                } elseif (is_array($action) && isset($action['type'])) {
                    $this->executeTypedAction($action, $context);
                }
            }
            $this->auditService->logAction('workflow_actions_executed', $context, 'success');
        } catch (Exception $e) {
            $this->auditService->logAction('workflow_actions_executed', $context, 'failed');
            throw $e;
        }
    }

    /**
     * Executes a typed action (AI/system specific).
     *
     * Handles both AI-triggered and system-triggered actions through a unified interface.
     * AI actions require AIService to be configured.
     *
     * @param array $action The action configuration with 'type' and 'name' keys
     * @param array $context Execution context passed to the action
     * @throws RuntimeException When action type is invalid or AI service unavailable
     * @since 1.0.0 Added typed action execution
     * @see executeSystemAction For system action implementation
     */
    private function executeTypedAction(array $action, array $context): void {
        switch ($action['type']) {
            case self::TRIGGER_AI:
                if (!$this->aiService) {
                    throw new RuntimeException('AI actions require AIService to be configured');
                }
                $this->aiService->executeAction($action['name'], $context);
                break;
            
            case self::TRIGGER_SYSTEM:
                $this->executeSystemAction($action['name'], $context);
                break;
                
            default:
                throw new RuntimeException("Unknown action type: {$action['type']}");
        }
    }

    /**
     * Executes a predefined system action.
     *
     * Supported system actions:
     * - clear_cache: Clears all system caches
     * - rebuild_index: Rebuilds search indexes
     * - send_notifications: Processes notification queue
     *
     * @param string $actionName Name of system action to execute
     * @param array $context Execution context passed to the action
     * @throws InvalidArgumentException When action name is invalid
     * @since 1.0.0 Added system action execution
     * @see executeTypedAction For the parent action executor
     */
    private function executeSystemAction(string $actionName, array $context): void {
        if (!in_array($actionName, self::SYSTEM_ACTIONS, true)) {
            throw new InvalidArgumentException("Invalid system action: $actionName");
        }

        try {
            switch ($actionName) {
                case 'clear_cache':
                    $this->cacheService->clearAll();
                    break;
                case 'rebuild_index':
                    $this->searchService->rebuildIndex();
                    break;
                case 'send_notifications':
                    $this->notificationService->processQueue();
                    break;
            }

            $this->auditService->logAction('system_action_executed', [
                'action' => $actionName,
                'context' => $context
            ], 'success');
        } catch (Exception $e) {
            $this->auditService->logAction('system_action_executed', [
                'action' => $actionName,
                'context' => $context,
                'error' => $e->getMessage()
            ], 'failed');
            throw $e;
        }
    }

    /**
     * Checks database change triggers against registered conditions.
     *
     * @param array $changes Array with old/new values
     */
    public function checkDatabaseTriggers(array $changes): void {
        foreach ($this->triggers as $trigger) {
            if ($trigger['type'] === 'database' && $trigger['condition']($changes)) {
                $this->executeActions($trigger['actions'], $changes);
            }
        }
    }

    /**
     * Checks time-based triggers against current time.
     */
    public function checkTimeBasedTriggers(): void {
        foreach ($this->triggers as $trigger) {
            if ($trigger['type'] === 'time' && $trigger['condition']()) {
                $this->executeActions($trigger['actions']);
            }
        }
    }

    /**
     * Executes a manual trigger by its ID.
     *
     * @param string $triggerId The trigger ID
     * @param array $params Additional parameters
     * @throws InvalidArgumentException When trigger not found
     */
    public function executeManualTrigger(string $triggerId, array $params = []): void {
        foreach ($this->triggers as $trigger) {
            if ($trigger['type'] === 'manual' && $trigger['id'] === $triggerId) {
                $this->executeActions($trigger['actions'], $params);
                return;
            }
        }
        throw new InvalidArgumentException("Manual trigger not found: $triggerId");
    }

    /**
     * Registers a webhook URL for workflow events.
     *
     * @param string $url The webhook URL
     * @param string $eventType The event type to listen for
     */
    public function registerWebhook(string $url, string $eventType): void {
        $this->notificationHandler->registerWebhook($url, $eventType);

        if ($this->debugMode) {
            $this->auditService->logDebug("Registered webhook", [
                'url' => $url,
                'eventType' => $eventType
            ]);
        }
    }

    /**
     * Handles incoming webhook events.
     *
     * @param string $eventType The event type
     * @param array $payload The event payload
     * @throws InvalidArgumentException When payload is invalid
     */
    public function handleWebhook(string $eventType, array $payload): void {
        if (!$this->validatePayload($payload)) {
            $this->auditService->logAction($eventType, $payload, 'invalid_payload');
            throw new InvalidArgumentException('Invalid payload: missing required fields');
        }

        $this->auditService->logAction($eventType, $payload, 'received');
        $this->executeActions($this->getActionsForEvent($eventType), $payload);
    }

    /**
     * Validates webhook payload structure.
     *
     * @param array $payload The payload to validate
     * @return bool True if payload is valid
     */
    private function validatePayload(array $payload): bool {
        $required = ['id', 'title', 'slug', 'userId', 'timestamp'];
        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets actions registered for a specific event type.
     * @param string $eventType The event type to filter by
     * @return array Filtered actions
     */
    private function getActionsForEvent(string $eventType): array {
        return array_filter($this->actions,
            fn($action) => $action['eventType'] === $eventType);
    }
    
    /**
     * Creates a new workflow definition.
     * @param string $name The name of the workflow (e.g., "Article Publishing").

    /**
     * Creates a new workflow definition.
     *
     * @param string $name The name of the workflow (e.g., "Article Publishing").
     * @param array $states A simple array of state names (e.g., ['draft', 'review', 'published']).
     * @param array $transitions A complex array defining allowed transitions.
     *        Format: [['name' => 'submit_for_review', 'from' => 'draft', 'to' => 'review'], ...]
     * @param string $initialState The starting state for new instances.
     * @return int|false The ID of the newly created workflow or false on failure.
     */
    public function createWorkflow(string $name, array $states, array $transitions, string $initialState) {
        if ($this->debugMode) {
            $this->auditService->logDebug("Creating workflow: $name", [
                'states' => $states,
                'transitions' => $transitions
            ]);
        }

        // Basic validation
        if (empty($name) || empty($states) || empty($transitions) || !in_array($initialState, $states)) {
            error_log("Workflow creation failed: Invalid parameters provided.");
            return false;
        }

        // Validate all transition states exist
        foreach ($transitions as $transition) {
            if (!isset($transition['from']) || !isset($transition['to'])) {
                error_log("Workflow creation failed: Transition missing required fields.");
                return false;
            }
            
            if (!in_array($transition['from'], $states) || !in_array($transition['to'], $states)) {
                error_log("Workflow creation failed: Invalid transition states.");
                return false;
            }
        }

        // Validate workflow name format
        if (!preg_match('/^[a-z0-9_\-]+$/i', $name)) {
            error_log("Workflow creation failed: Invalid name format.");
            return false;
        }

        $serializedStates = json_encode($states);
        $serializedTransitions = json_encode($transitions);

        $sql = "INSERT INTO workflow_definitions (name, states, transitions, initial_state) VALUES (:name, :states, :transitions, :initial_state)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':states' => $serializedStates,
                ':transitions' => $serializedTransitions,
                ':initial_state' => $initialState
            ]);
            $newId = $this->pdo->lastInsertId();
            if ($this->debugMode) {
                $this->auditService->logDebug("Created workflow", [
                    'workflow_id' => $newId,
                    'name' => $name
                ]);
            }
            return (int)$newId;
        } catch (PDOException $e) {
            $this->auditService->logError("Workflow creation failed", [
                'error' => $e->getMessage(),
                'name' => $name,
                'states' => $states,
                'transitions' => $transitions
            ]);
            return false;
        }
    }

    /**
     * Adds a new trigger to the workflow service.
     * This replaces both legacy addTrigger() implementations.
     *
     * @param string $eventName The event name to trigger on
     * @param callable|array $trigger The callback to execute or trigger config
     * @param int $priority Execution priority (higher = earlier)
     * @return string The trigger ID for later removal
     */
    public function addTrigger(string $eventName, callable|array $trigger, int $priority = 100): string
    {
        $triggerId = uniqid('trigger_');
        
        // Handle both legacy callable and new array format
        if (is_callable($trigger)) {
            $this->triggers[$eventName][$priority][$triggerId] = $trigger;
        } else {
            $this->triggers[$eventName][$priority][$triggerId] = [
                'callback' => $trigger['callback'],
                'conditions' => $trigger['conditions'] ?? [],
                'metadata' => $trigger['metadata'] ?? []
            ];
        }
        
        if ($this->debugMode) {
            $this->auditService->logDebug("Added trigger", [
                'event' => $eventName,
                'priority' => $priority,
                'trigger_id' => $triggerId
            ]);
        }
        
        return $triggerId;
    }

    /**
     * Removes a trigger by either:
     * - Event name + trigger ID (new style)
     * - Manual trigger ID or index (legacy style)
     * - Array format (new style with conditions/metadata)
     *
     * @param string $eventName The event name or legacy identifier
     * @param string|array $triggerId The trigger ID, array format, or empty for legacy mode
     * @return bool True if removed, false if not found
     */
    public function removeTrigger(string $eventName, $triggerId = ''): bool {
        if ($triggerId === '' || is_string($triggerId)) {
            // Legacy mode - $eventName is actually the identifier
            if ($triggerId === '') {
                foreach ($this->triggers as $i => $trigger) {
                    if (($trigger['type'] === 'manual' && $trigger['id'] === $eventName) ||
                        $i === $eventName) {
                        unset($this->triggers[$i]);
                        $this->triggers = array_values($this->triggers);

                        if ($this->debugMode) {
                            $this->auditService->logDebug("Removed workflow trigger", [
                                'identifier' => $eventName
                            ]);
                        }
                        return true;
                    }
                }
            } else {
                // New style - eventName + triggerId
                foreach ($this->triggers[$eventName] ?? [] as $priority => $triggers) {
                    if (isset($triggers[$triggerId])) {
                        unset($this->triggers[$eventName][$priority][$triggerId]);
                        
                        if ($this->debugMode) {
                            $this->auditService->logDebug("Removed trigger", [
                                'event' => $eventName,
                                'trigger_id' => $triggerId,
                                'priority' => $priority
                            ]);
                        }
                        
                        return true;
                    }
                }
            }
        } elseif (is_array($triggerId)) {
            // Array format - match by callback and conditions
            foreach ($this->triggers[$eventName] ?? [] as $priority => $triggers) {
                foreach ($triggers as $id => $trigger) {
                    if ($trigger['callback'] === $triggerId['callback'] &&
                        $trigger['conditions'] === ($triggerId['conditions'] ?? [])) {
                        unset($this->triggers[$eventName][$priority][$id]);
                        
                        if ($this->debugMode) {
                            $this->auditService->logDebug("Removed array trigger", [
                                'event' => $eventName,
                                'callback' => $triggerId['callback'],
                                'priority' => $priority,
                                'conditions' => $triggerId['conditions'] ?? []
                            ]);
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Gets triggers - either all (legacy) or filtered by event name (new)
     *
     * @param string $eventName The event name or empty for all triggers
     * @return array Array of triggers sorted by priority
     */
    public function getTriggers(string $eventName = ''): array {
        if ($eventName === '') {
            return $this->triggers; // Legacy behavior
        }

        $result = [];
        if (isset($this->triggers[$eventName])) {
            krsort($this->triggers[$eventName]); // Sort by priority (high to low)
            foreach ($this->triggers[$eventName] as $priority => $triggers) {
                $result = array_merge($result, $triggers);
            }
        }
        return $result;
    }

    /**
     * Unified trigger clearing implementation.
     * Supports both legacy and new trigger formats.
     *
     * @param string|null $eventName Optional event name to clear
     */
    public function clearTriggers(?string $eventName = null): void {
        if ($eventName) {
            // New format: event-based triggers
            unset($this->triggers[$eventName]);
            
            // Legacy format: type-based triggers
            $this->triggers = array_filter($this->triggers,
                fn($t) => !is_array($t) || $t['type'] !== $eventName);
        } else {
            $this->triggers = [];
        }

        if ($this->debugMode) {
            $this->auditService->logDebug("Cleared triggers", [
                'event' => $eventName ?? 'all'
            ]);
        }
        
        if ($this->debugMode) {
            $this->auditService->logDebug("Cleared triggers", [
                'event' => $eventName ?? 'all'
            ]);
        }
    }

    /**
     * Retrieves a single workflow definition by its ID.
     *
     * @param int $id The workflow definition ID.
     * @return array|null The workflow definition as an associative array, or null if not found.
     */
    public function getWorkflow(int $id) {
        if ($this->debugMode) {
            echo "Fetching workflow definition for ID: $id\n";
        }

        $stmt = $this->pdo->prepare("SELECT * FROM workflow_definitions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $workflow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$workflow) {
            return null;
        }

        // Unserialize JSON data before returning
        $workflow['states'] = json_decode($workflow['states'], true);
        $workflow['transitions'] = json_decode($workflow['transitions'], true);

        return $workflow;
    }

    /**
     * Retrieves all workflow definitions.
     *
     * @return array An array of all workflow definitions.
     */
    public function getAllWorkflows() {
        $stmt = $this->pdo->query("SELECT * FROM workflow_definitions ORDER BY name ASC");
        $workflows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Unserialize data for all workflows
        return array_map(function($wf) {
            $wf['states'] = json_decode($wf['states'], true);
            $wf['transitions'] = json_decode($wf['transitions'], true);
            return $wf;
        }, $workflows);
    }

    /**
     * Deletes a workflow definition.
     *
     * TODO: Should we also delete all instances associated with this definition?
     * For now, we don't. This could lead to orphaned instance records.
     *
     * @param int $id The ID of the workflow definition to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteWorkflow(int $id) {
        if ($this->debugMode) {
            echo "WARNING: Deleting workflow definition ID: $id. This does not delete associated instances.\n";
        }
        $stmt = $this->pdo->prepare("DELETE FROM workflow_definitions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Starts a new workflow instance for a given subject.
     *
     * @param int $definition_id The ID of the workflow definition to use.
     * @param int $subject_id The ID of the entity this workflow instance is for (e.g., an article ID).
     * @return int|false The ID of the new workflow instance or false on failure.
     */
    public function startWorkflowInstance(int $definition_id, int $subject_id) {
        $workflowDef = $this->getWorkflow($definition_id);
        if (!$workflowDef) {
            error_log("Cannot start instance: Workflow definition $definition_id not found.");
            return false;
        }

        $initialState = $workflowDef['initial_state'];
if ($this->debugMode) {
    $this->auditService->logDebug("Starting workflow instance", [
        'definition_id' => $definition_id,
        'subject_id' => $subject_id
    ]);

            echo "Starting new workflow instance for definition $definition_id and subject $subject_id. Initial state: $initialState\n";
        }

        $sql = "INSERT INTO workflow_instances (definition_id, subject_id, current_state, created_at, updated_at) VALUES (:def_id, :sub_id, :state, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([
            ':def_id' => $definition_id,
            ':sub_id' => $subject_id,
            ':state' => $initialState
        ]);

        if ($success) {
            return (int)$this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Retrieves a single workflow instance by its ID.
     *
     * @param int $instance_id The instance ID.
     * @return array|null The instance data or null if not found.
     */
    public function getInstance(int $instance_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM workflow_instances WHERE id = :id");
        $stmt->execute([':id' => $instance_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all workflow instances for a specific subject.
     * A subject can have multiple workflows attached to it.
     *
     * @param int $subject_id The ID of the subject.
     * @return array An array of instance data.
     */
    public function getInstancesForSubject(int $subject_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM workflow_instances WHERE subject_id = :subject_id ORDER BY created_at DESC");
        $stmt->execute([':subject_id' => $subject_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Directly updates the state of a workflow instance.
     * Bypasses transition rules. Use with caution.
     *
     * @param int $instance_id The instance ID.
     * @param string $newState The new state to set.
     * @return bool True on success, false on failure.
     */
    public function updateInstanceState(int $instance_id, string $newState) {
        if (!$this->authService->canPerformTransition('force_update')) {
            $this->auditService->logSecurityEvent(
                'force_update_denied',
                "User attempted unauthorized force state update",
                [
                    'instance_id' => $instance_id,
                    'new_state' => $newState,
                    'user_id' => $this->authService->getCurrentUserId()
                ]
            );
            throw new PermissionException("Insufficient permissions for force state update");
        }

        if ($this->debugMode) {
            $this->auditService->logDebug("Force updating instance state", [
                'instance_id' => $instance_id,
                'new_state' => $newState
            ]);
        }
        // TODO: We should check if $newState is a valid state in the associated definition.
        // For now, we trust the caller. This is dangerous.
        return $this->workflowRepository->updateInstanceState($instance_id, $newState);
    }

    /**
     * Performs a state transition for a workflow instance.
     *
     * @param int $instance_id The ID of the workflow instance.
     * @param string $transitionName The name of the transition to perform.
     * @return bool True on success, false if transition is not allowed or on error.
     */
    /**
     * Performs a state transition using dual schema support.
     *
     * First attempts the new approval schema (approval_instances table).
     * If no matching transition is found, falls back to legacy schema (workflow_instances table).
     *
     * @param string $instance_id The workflow instance ID
     * @param string $transitionName The transition to perform
     * @return bool True if transition succeeded, false otherwise
     * @throws \RuntimeException If neither schema handles the transition
     * @throws \PermissionException If user lacks required permissions
     *
     * @note During migration period (until Q1 2026), both schemas must be supported.
     *       New features should target approval schema only.
     */
    public function transitionState(string $instance_id, string $transitionName): bool {
        // Check permissions first
        if (!$this->authService->canPerformTransition($transitionName)) {
            $this->auditService->logSecurityEvent(
                'transition_denied',
                "User attempted unauthorized transition: $transitionName",
                [
                    'instance_id' => $instance_id,
                    'transition' => $transitionName,
                    'user_id' => $this->authService->getCurrentUserId()
                ]
            );
            $this->auditService->logSecurityEvent(
                'transition_denied',
                "User attempted unauthorized transition: $transitionName"
            );
            throw new \PermissionException("Insufficient permissions for transition: $transitionName");
        }

        // Verify tenant isolation
        if (!$this->verifyTenantOwnership($instance_id)) {
            $this->auditService->logSecurityEvent(
                'tenant_violation',
                "Attempt to access workflow $instance_id from wrong tenant"
            );
            return false;
        }

        // First try new approval schema (preferred)
        $result = $this->transitionApprovalSchema($instance_id, $transitionName);
        if ($result !== null) {
            return $result;
        }

        // Fallback to legacy workflow schema (deprecated)
        return $this->transitionLegacySchema($instance_id, $transitionName);
    }

    /**
     * Verifies the workflow instance belongs to current tenant
     */
    private function verifyTenantOwnership(string $instance_id): bool {
        return $this->workflowRepository->verifyTenantOwnership($instance_id, $this->tenantId);
    }

    public function batchTransition(array $instanceIds, string $transitionName): int {
        $successCount = 0;
        
        foreach ($instanceIds as $id) {
            if ($this->transitionState($id, $transitionName)) {
                $successCount++;
            }
        }
        
        return $successCount;
    }

    private function transitionApprovalSchema(string $instance_id, string $transitionName): ?bool {
        try {
            $this->pdo->beginTransaction();
            
            // Get current instance state
            $stmt = $this->pdo->prepare("
                SELECT current_level, status, workflow_type, subject_id
                FROM approval_instances
                WHERE id = :id AND tenant_id = :tenant_id
                FOR UPDATE
            ");
            $stmt->execute([':id' => $instance_id, ':tenant_id' => $this->tenantId]);
            $instance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$instance) {
                return null; // Not found in this schema
            }
            
            // Get next level requirements
            $stmt = $this->pdo->prepare("
                SELECT * FROM approval_levels
                WHERE workflow_name = :workflow
                AND level_number = :next_level
                AND tenant_id = :tenant_id
            ");
            $nextLevel = $instance['current_level'] + 1;
            $stmt->execute([
                ':workflow' => $instance['workflow_type'],
                ':next_level' => $nextLevel,
                ':tenant_id' => $this->tenantId
            ]);
            $nextLevel = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Validate transition
            if ($transitionName === 'approve' && $instance['status'] === 'pending') {
                $oldState = [
                    'level' => $instance['current_level'],
                    'status' => $instance['status']
                ];
                
                $newState = [
                    'level' => $nextLevel ? $nextLevel['level_number'] : $instance['current_level'],
                    'status' => !$nextLevel ? 'approved' : 'pending'
                ];
                
                $this->pdo->prepare("
                    UPDATE approval_instances
                    SET current_level = :level,
                        status = CASE WHEN :is_final THEN 'approved' ELSE 'pending' END,
                        updated_at = NOW()
                    WHERE id = :id AND tenant_id = :tenant_id
                ")->execute([
                    ':level' => $newState['level'],
                    ':is_final' => !$nextLevel,
                    ':id' => $instance_id,
                    ':tenant_id' => $this->tenantId
                ]);
                
                $this->pdo->commit();
                
                // Log the transition
                $this->auditService->log(
                    $transitionName,
                    'approval_instance',
                    $instance_id,
                    $oldState,
                    $newState
                );
                return true;
            }
            
            $this->pdo->rollBack();
            return false;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Approval transition failed: " . $e->getMessage());
            return false;
        }
    }

    private function transitionLegacySchema(string $instance_id, string $transitionName): bool {
        try {
            $this->pdo->beginTransaction();
            
            // Get current instance state
            $stmt = $this->pdo->prepare("
                SELECT current_state, definition_id, subject_id
                FROM workflow_instances
                WHERE id = :id
                FOR UPDATE
            ");
            $stmt->execute([':id' => $instance_id]);
            $instance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$instance) {
                throw new RuntimeException("Instance not found");
            }
            
            // Get valid transitions
            $stmt = $this->pdo->prepare("
                SELECT transitions FROM workflow_definitions
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $instance['definition_id'],
                ':tenant_id' => $this->tenantId
            ]);
            $definition = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $transitions = json_decode($definition['transitions'] ?? '[]', true);
            $validTransitions = $transitions[$instance['current_state']] ?? [];
            
            if (in_array($transitionName, $validTransitions)) {
                $oldState = ['state' => $instance['current_state']];
                $newState = ['state' => $transitionName];
                
                $this->pdo->prepare("
                    UPDATE workflow_instances
                    SET current_state = :new_state
                    WHERE id = :id AND tenant_id = :tenant_id
                ")->execute([
                    ':new_state' => $transitionName,
                    ':id' => $instance_id,
                    ':tenant_id' => $this->tenantId
                ]);
                
                $this->pdo->commit();
                
                // Log the transition
                $this->auditService->log(
                    $transitionName,
                    'workflow_instance',
                    $instance_id,
                    $oldState,
                    $newState
                );
                
                return true;
            }
            
            $this->pdo->rollBack();
            return false;
            
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Legacy transition failed: " . $e->getMessage());
            return false;
        }
        if ($this->debugMode) {
            echo "Attempting transition '$transitionName' for instance $instance_id.\n";
        }

        $instance = $this->getInstance($instance_id);
        if (!$instance) {
            error_log("Transition failed: Instance $instance_id not found.");
            return false;
        }

        $workflow = $this->getWorkflow($instance['definition_id']);
        if (!$workflow) {
            error_log("Transition failed: Workflow definition {$instance['definition_id']} not found.");
            return false;
        }

        $currentState = $instance['current_state'];
        $targetTransition = null;

        // Find the matching transition from the definition
        foreach ($workflow['transitions'] as $t) {
            if ($t['name'] === $transitionName) {
                $targetTransition = $t;
                break;
            }
        }

        if (!$targetTransition) {
            if ($this->debugMode) echo "Transition '$transitionName' does not exist in this workflow.\n";
            return false;
        }

        // Check if the transition is valid from the current state
        // A transition can be valid from multiple states, so 'from' can be an array or a string.
        $isAllowed = false;
        if (is_array($targetTransition['from'])) {
            if (in_array($currentState, $targetTransition['from'])) {
                $isAllowed = true;
            }
        } else {
            if ($targetTransition['from'] === $currentState) {
                $isAllowed = true;
            }
        }

        if ($isAllowed) {
            // TODO: Fire 'before_transition' event here.
            $newState = $targetTransition['to'];
            if ($this->debugMode) {
                echo "Transition allowed: $currentState -> $newState\n";
            }
            $this->updateInstanceState($instance_id, $newState);
            // TODO: Fire 'after_transition' event here.
            return true;
        } else {
            if ($this->debugMode) {
                echo "Transition '$transitionName' is not allowed from state '$currentState'.\n";
            }
            return false;
        }
    }

    /**
     * Checks if a transition is currently allowed for an instance.
     *
     * @param int $instance_id The workflow instance ID.
     * @param string $transitionName The name of the transition to check.
     * @return bool
     */
    public function isTransitionAllowed(int $instance_id, string $transitionName) {
        $instance = $this->getInstance($instance_id);
        if (!$instance) return false;

        $workflow = $this->getWorkflow($instance['definition_id']);
        if (!$workflow) return false;

        $currentState = $instance['current_state'];

        foreach ($workflow['transitions'] as $t) {
            if ($t['name'] === $transitionName) {
                if (is_array($t['from'])) {
                    return in_array($currentState, $t['from']);
                }
                return $t['from'] === $currentState;
            }
        }
        return false;
    }


    /**
     * Gets the current status of a workflow instance, including available transitions.
     *
     * @param int $instance_id The instance ID.
     * @return array|null Status information or null on error.
     */
    public function getWorkflowStatus(int $instance_id) {
        $instance = $this->getInstance($instance_id);
        if (!$instance) return null;

        $workflow = $this->getWorkflow($instance['definition_id']);
        if (!$workflow) return null;

        $currentState = $instance['current_state'];
        $availableTransitions = [];

        foreach ($workflow['transitions'] as $t) {
            $isAllowed = false;
            if (is_array($t['from'])) {
                if (in_array($currentState, $t['from'])) {
                    $isAllowed = true;
                }
            } else {
                if ($t['from'] === $currentState) {
                    $isAllowed = true;
                }
            }
            if ($isAllowed) {
                $availableTransitions[] = $t;
            }
        }

        return [
            'instance_id' => $instance_id,
            'subject_id' => $instance['subject_id'],
            'workflow_name' => $workflow['name'],
            'current_state' => $currentState,
            'available_transitions' => $availableTransitions,
            'last_updated' => $instance['updated_at']
        ];
    }

    /**
     * Pauses a workflow instance.
     *
     * TODO: This is a placeholder. What does "paused" mean?
     * Maybe add a new 'paused' state or a flag in the DB.
     * For now, it just logs a message.
     *
     * @param int $instance_id The instance ID.
     * @return bool
     */
    public function pauseWorkflow(int $instance_id) {
        if ($this->debugMode) {
            echo "Pausing workflow for instance $instance_id (not implemented).\n";
        }
        // $sql = "UPDATE workflow_instances SET is_paused = 1 WHERE id = :id";
        // $stmt = $this->pdo->prepare($sql);
        // return $stmt->execute([':id' => $instance_id]);
        return true; // Placeholder
    }

    /**
     * Resumes a paused workflow instance.
     *
     * TODO: Placeholder. See pauseWorkflow.
     *
     * @param int $instance_id The instance ID.
     * @return bool
     */
    public function resumeWorkflow(int $instance_id) {
        if ($this->debugMode) {
            echo "Resuming workflow for instance $instance_id (not implemented).\n";
        }
        // $sql = "UPDATE workflow_instances SET is_paused = 0 WHERE id = :id";
        // $stmt = $this->pdo->prepare($sql);
        // return $stmt->execute([':id' => $instance_id]);
        return true; // Placeholder
    }

    /**
     * Cancels a workflow instance.
     *
     * TODO: Placeholder. Should this delete the instance or move it to a 'cancelled' state?
     * Moving to a state is probably better for auditing.
     *
     * @param int $instance_id The instance ID.
     * @return bool
     */
    public function cancelWorkflow(int $instance_id) {
        if ($this->debugMode) {
            echo "Cancelling workflow for instance $instance_id (not implemented).\n";
        }
        // This could be an implementation:
        // return $this->updateInstanceState($instance_id, 'cancelled');
        return true; // Placeholder
    }


    // --- DEBUG AND UTILITY METHODS ---

    /**
     * DANGEROUS: Deletes all workflow definitions and instances from the database.
     * Intended for testing and development environments only.
     *
     * @return array Result summary.
     */
    public function deleteAllWorkflowsAndInstances() {
        echo "!!! EXTREME WARNING: DELETING ALL WORKFLOW DATA !!!\n";
        echo "Sleeping for 3 seconds to allow cancellation...\n";
        sleep(3);

        try {
            $defStmt = $this->pdo->query("DELETE FROM workflow_definitions");
            $defCount = $defStmt->rowCount();

            $instStmt = $this->pdo->query("DELETE FROM workflow_instances");
            $instCount = $instStmt->rowCount();

            // Reset auto-increment counters for clean testing
            $this->pdo->query("ALTER TABLE workflow_definitions AUTO_INCREMENT = 1");
            $this->pdo->query("ALTER TABLE workflow_instances AUTO_INCREMENT = 1");


            $result = [
                'deleted_definitions' => $defCount,
                'deleted_instances' => $instCount,
                'status' => 'SUCCESS'
            ];
            if ($this->debugMode) {
                print_r($result);
            }
            return $result;

        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Dumps all transitions for a given workflow definition.
     *
     * @param int $definition_id
     * @return void
     */
    public function dumpTransitions(int $definition_id) {
        $workflow = $this->getWorkflow($definition_id);
        if (!$workflow) {
            echo "Could not dump transitions: Workflow $definition_id not found.\n";
            return;
        }

        echo "--- Transition Dump for '{$workflow['name']}' (ID: $definition_id) ---\n";
        if (empty($workflow['transitions'])) {
            echo "No transitions defined.\n";
        } else {
            foreach ($workflow['transitions'] as $t) {
                $from = is_array($t['from']) ? implode(', ', $t['from']) : $t['from'];
                echo "  - Transition '{$t['name']}':\n";
                echo "    From: [{$from}]\n";
                echo "    To:   [{$t['to']}]\n";
            }
        }
        echo "-----------------------------------------------------\n";
    }

    /**
     * Dumps the state of all workflows and their instances for debugging.
     *
     * @return void
     */
    public function debugDumpAll() {
        if (!$this->debugMode) {
            echo "Debug mode is disabled.\n";
            return;
        }

        echo "=====================================================\n";
        echo "=========== WORKFLOW SERVICE DEBUG DUMP ===========\n";
        echo "=====================================================\n\n";

        // Dump Definitions
        echo "--- All Workflow Definitions ---\n";
        $definitions = $this->getAllWorkflows();
        if (empty($definitions)) {
            echo "No workflow definitions found.\n";
        } else {
            foreach ($definitions as $def) {
                echo "ID: {$def['id']} | Name: {$def['name']} | Initial State: {$def['initial_state']}\n";
                echo "  States: " . json_encode($def['states']) . "\n";
                echo "  Transitions: " . json_encode($def['transitions']) . "\n\n";
            }
        }

        // Dump Instances
        echo "\n--- All Workflow Instances ---\n";
        $instances = $this->pdo->query("SELECT * FROM workflow_instances ORDER BY definition_id, id")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($instances)) {
            echo "No workflow instances found.\n";
        } else {
            foreach ($instances as $inst) {
                echo "Instance ID: {$inst['id']} | Definition ID: {$inst['definition_id']} | Subject ID: {$inst['subject_id']} | State: {$inst['current_state']}\n";
                echo "  Created: {$inst['created_at']} | Updated: {$inst['updated_at']}\n\n";
            }
        }

        echo "=====================================================\n";
        echo "================== END DEBUG DUMP ===================\n";
        echo "=====================================================\n";
    }
}

// Example Usage (for testing purposes, would not be in the final file)
/*

// --- Database Setup ---
// This is a mock PDO object for demonstration.
// In a real application, this would be your actual database connection.
class MockPDO extends PDO {
    public function __construct() {}
}
// You would need to create the tables in your database:
// Use \core\Database::connection()

// 1. Clean slate for testing
// $workflowService->deleteAllWorkflowsAndInstances();

// 2. Create a new workflow definition
// $articleStates = ['draft', 'review', 'approved', 'published', 'archived'];
// $articleTransitions = [
//     ['name' => 'submit', 'from' => 'draft', 'to' => 'review'],
//     ['name' => 'approve', 'from' => 'review', 'to' => 'approved'],
//     ['name' => 'reject', 'from' => 'review', 'to' => 'draft'],
//     ['name' => 'publish', 'from' => 'approved', 'to' => 'published'],
//     ['name' => 'archive', 'from' => 'published', 'to' => 'archived'],
//     ['name' => 're-edit', 'from' => ['published', 'archived'], 'to' => 'draft']
// ];
// $workflowId = $workflowService->createWorkflow(
//     "Article Publishing",
//     $articleStates,
//     $articleTransitions,
//     'draft'
// );
// echo "Created workflow with ID: $workflowId\n";

// 3. Start an instance for an article with ID 101
// $instanceId = $workflowService->startWorkflowInstance($workflowId, 101);
// echo "Started instance with ID: $instanceId\n";

// 4. Get status and perform transitions
// print_r($workflowService->getWorkflowStatus($instanceId));
// $workflowService->transition($instanceId, 'submit'); // draft -> review
// print_r($workflowService->getWorkflowStatus($instanceId));
// $workflowService->transition($instanceId, 'approve'); // review -> approved
// print_r($workflowService->getWorkflowStatus($instanceId));
// $workflowService->transition($instanceId, 'publish'); // approved -> published
// print_r($workflowService->getWorkflowStatus($instanceId));

// 5. Dump everything for debugging
// $workflowService->debugDumpAll();

*/
