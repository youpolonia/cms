<?php
require_once __DIR__ . '/../core/database.php';

/**
 * Workflow Engine for Phase 14 Implementation
 * Manages state transitions and business process flows
 */
class WorkflowEngine {
    private static $instance;
    private $states = [];
    private $transitions = [];
    private $currentState = [];
    private $dbTable = 'workflow_states';

    private function __construct() {
        // Initialize with default states
        $this->states = [
            'draft' => ['name' => 'Draft', 'final' => false],
            'pending' => ['name' => 'Pending Approval', 'final' => false],
            'approved' => ['name' => 'Approved', 'final' => true],
            'rejected' => ['name' => 'Rejected', 'final' => true]
        ];

        // Define allowed transitions
        $this->transitions = [
            'submit' => ['from' => ['draft'], 'to' => 'pending'],
            'approve' => ['from' => ['pending'], 'to' => 'approved'],
            'reject' => ['from' => ['pending'], 'to' => 'rejected']
        ];
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCurrentState($entityId) {
        if (isset($this->currentState[$entityId])) {
            return $this->currentState[$entityId];
        }

        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT state FROM {$this->dbTable} WHERE entity_id = ?");
        $stmt->execute([$entityId]);
        $state = $stmt->fetchColumn();

        if ($state) {
            $this->currentState[$entityId] = $state;
            return $state;
        }

        return 'draft';
    }

    public function canTransition($entityId, $transition) {
        $current = $this->getCurrentState($entityId);
        return in_array($current, $this->transitions[$transition]['from'] ?? []);
    }

    public function applyTransition($entityId, $transition, $userId, $comment = '') {
        if (!$this->canTransition($entityId, $transition)) {
            throw new Exception("Invalid transition");
        }

        $newState = $this->transitions[$transition]['to'];
        $this->currentState[$entityId] = $newState;
        
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("REPLACE INTO {$this->dbTable} (entity_id, state, updated_at) VALUES (?, ?, ?)");
        $stmt->execute([$entityId, $newState, date('Y-m-d H:i:s')]);

        // Log the transition
        AuditLogger::getInstance()->logEvent(
            'state_change',
            $entityId,
            [
                'from' => $this->getCurrentState($entityId),
                'to' => $newState,
                'transition' => $transition,
                'comment' => $comment
            ]
        );

        // Notify about state change
        NotificationService::getInstance()->sendNotification(
            $newState,
            ['id' => $entityId, 'user' => $userId]
        );

        return true;
    }

    public function getAvailableTransitions($entityId) {
        $current = $this->getCurrentState($entityId);
        $available = [];

        foreach ($this->transitions as $name => $transition) {
            if (in_array($current, $transition['from'])) {
                $available[] = $name;
            }
        }

        return $available;
    }
}
