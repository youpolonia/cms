<?php

declare(strict_types=1);

namespace Includes\Content;

use PDO;
use PDOException;
use RuntimeException;
use Core\AuditLogger;

/**
 * Manages content workflow states and transitions.
 */
class WorkflowManager
{
    private PDO $pdo;

    // To store allowed transitions, e.g., ['draft' => ['review', 'published'], 'review' => ['draft', 'published']]
    // This can be loaded from a config file or database in a more advanced setup.
    private array $allowedTransitions = []; 

    /**
     * Constructor.
     *
     * @param PDO $pdo The database connection.
     * @param array $allowedTransitions Optional pre-defined transition rules.
     */
    public function __construct(PDO $pdo, array $allowedTransitions = [])
    {
        $this->pdo = $pdo;
        $this->allowedTransitions = $allowedTransitions; 
        // In a real app, you might load these from a config file or a dedicated table.
        // For now, keeping it simple. Example:
        // $this->allowedTransitions = [
        //     'draft' => ['review', 'published', 'archived'],
        //     'review' => ['draft', 'published', 'archived'],
        //     'published' => ['draft', 'archived'],
        // ];
    }

    /**
     * Sets the allowed transitions.
     * Example: $workflowManager->setAllowedTransitions([
     *     'draft' => ['review', 'published'], // From draft, can go to review or published
     *     'review' => ['published', 'draft'],   // From review, can go to published or back to draft
     *     'published' => ['archived']          // From published, can go to archived
     * ]);
     * @param array $transitions
     */
    public function setAllowedTransitions(array $transitions): void
    {
        $this->allowedTransitions = $transitions;
    }

    /**
     * Creates a new workflow state.
     *
     * @param string $name Machine-readable name (e.g., "draft").
     * @param string $label Human-readable label (e.g., "Draft").
     * @param string|null $description Optional description.
     * @param bool $isInitial Is this a default starting state?
     * @param bool $isTerminal Is this a final state?
     * @return int|false The ID of the new state, or false on failure.
     */
    public function createState(string $name, string $label, ?string $description = null, bool $isInitial = false, bool $isTerminal = false): int|false
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO workflow_states (name, label, description, is_initial, is_terminal, created_at, updated_at)
                 VALUES (:name, :label, :description, :is_initial, :is_terminal, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );
            $success = $stmt->execute([
                ':name' => $name,
                ':label' => $label,
                ':description' => $description,
                ':is_initial' => (int)$isInitial, // Ensure boolean is cast to int for DB
                ':is_terminal' => (int)$isTerminal,
            ]);
            return $success ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            // Log error: $e->getMessage()
            return false;
        }
    }

    /**
     * Retrieves a workflow state by its ID.
     * @param int $stateId
     * @return array|null
     */
    public function getStateById(int $stateId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM workflow_states WHERE id = :id");
            $stmt->execute([':id' => $stateId]);
            $state = $stmt->fetch(PDO::FETCH_ASSOC);
            return $state ?: null;
        } catch (PDOException $e) {
            // Log error
            return null;
        }
    }
    
    /**
     * Retrieves a workflow state by its name.
     * @param string $name
     * @return array|null
     */
    public function getStateByName(string $name): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM workflow_states WHERE name = :name");
            $stmt->execute([':name' => $name]);
            $state = $stmt->fetch(PDO::FETCH_ASSOC);
            return $state ?: null;
        } catch (PDOException $e) {
            // Log error
            return null;
        }
    }

    /**
     * Retrieves all defined workflow states.
     * @return array
     */
    public function getAllStates(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM workflow_states ORDER BY label ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error
            return [];
        }
    }

    /**
     * Gets the initial workflow state.
     * @return array|null The initial state or null if none is defined.
     */
    public function getInitialState(): ?array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM workflow_states WHERE is_initial = 1 LIMIT 1");
            $state = $stmt->fetch(PDO::FETCH_ASSOC);
            return $state ?: null;
        } catch (PDOException $e) {
            // Log error
            return null;
        }
    }


    /**
     * Transitions a content item to a new workflow state.
     *
     * @param int $contentId The ID of the content item.
     * @param int $toStateId The ID of the target workflow state.
     * @param int|null $userId The ID of the user performing the transition.
     * @param string|null $notes Optional notes for the transition.
     * @param int|null $assignedToUserId Optional user ID to assign the content to in the new state.
     * @return bool True on success, false on failure.
     * @throws RuntimeException If transition is not allowed or states are invalid.
     */
    public function transitionContent(int $contentId, int $toStateId, ?int $userId = null, ?string $notes = null, ?int $assignedToUserId = null): bool
    {
        $this->pdo->beginTransaction();

        try {
            $currentStateEntry = $this->getCurrentWorkflowEntry($contentId);
            $fromStateId = $currentStateEntry ? (int)$currentStateEntry['workflow_state_id'] : null;
            
            $toState = $this->getStateById($toStateId);
            if (!$toState) {
                throw new RuntimeException("Target workflow state ID {$toStateId} not found.");
            }

            if ($fromStateId !== null) {
                $fromState = $this->getStateById($fromStateId);
                if (!$fromState) {
                    // This should ideally not happen if data integrity is maintained
                    throw new RuntimeException("Current workflow state ID {$fromStateId} for content {$contentId} is invalid.");
                }
                // Check allowed transitions if rules are defined
                if (!empty($this->allowedTransitions)) {
                    if (!isset($this->allowedTransitions[$fromState['name']]) || 
                        !in_array($toState['name'], $this->allowedTransitions[$fromState['name']], true)) {
                        throw new RuntimeException("Transition from state '{$fromState['name']}' to '{$toState['name']}' is not allowed.");
                    }
                }
            } else {
                // If no current state, this is likely an initial assignment.
                // Check if the target state is an initial state if strict rules apply.
                // For now, we allow setting initial state directly.
            }


            // Log the transition to history
            $historyStmt = $this->pdo->prepare(
                "INSERT INTO content_workflow_history (content_id, from_workflow_state_id, to_workflow_state_id, user_id, notes, transitioned_at)
                 VALUES (:content_id, :from_state_id, :to_state_id, :user_id, :notes, CURRENT_TIMESTAMP)"
            );
            $historyStmt->execute([
                ':content_id' => $contentId,
                ':from_state_id' => $fromStateId,
                ':to_state_id' => $toStateId,
                ':user_id' => $userId,
                ':notes' => $notes,
            ]);

            // Update or insert the current state in content_workflow table
            if ($currentStateEntry) {
                $updateStmt = $this->pdo->prepare(
                    "UPDATE content_workflow 
                     SET workflow_state_id = :workflow_state_id, user_id = :user_id, assigned_to_user_id = :assigned_to_user_id, notes = :notes, updated_at = CURRENT_TIMESTAMP
                     WHERE content_id = :content_id"
                );
                $success = $updateStmt->execute([
                    ':workflow_state_id' => $toStateId,
                    ':user_id' => $userId,
                    ':assigned_to_user_id' => $assignedToUserId,
                    ':notes' => $notes, // Notes for the current state, could be different from transition notes
                    ':content_id' => $contentId,
                ]);
            } else {
                $insertStmt = $this->pdo->prepare(
                    "INSERT INTO content_workflow (content_id, workflow_state_id, user_id, assigned_to_user_id, notes, created_at, updated_at)
                     VALUES (:content_id, :workflow_state_id, :user_id, :assigned_to_user_id, :notes, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
                );
                $success = $insertStmt->execute([
                    ':content_id' => $contentId,
                    ':workflow_state_id' => $toStateId,
                    ':user_id' => $userId,
                    ':assigned_to_user_id' => $assignedToUserId,
                    ':notes' => $notes,
                ]);
            }

            if (!$success) {
                throw new PDOException("Failed to update/insert current workflow state for content ID {$contentId}.");
            }

            $this->pdo->commit();
            
            // Log successful transition
            $fromStateName = $fromState['name'] ?? 'none';
            $toStateName = $toState['name'];
            AuditLogger::log(
                $userId,
                'workflow_transition',
                'content',
                $contentId,
                "Transitioned from {$fromStateName} to {$toStateName}" . ($notes ? ": {$notes}" : "")
            );
            
            // Special case logging for common workflow actions
            if ($toStateName === 'review') {
                AuditLogger::log(
                    $userId,
                    'submit_for_review',
                    'content',
                    $contentId,
                    $notes
                );
            } elseif ($toStateName === 'published') {
                AuditLogger::log(
                    $userId,
                    'approve',
                    'content',
                    $contentId,
                    $notes
                );
            } elseif ($toStateName === 'rejected') {
                AuditLogger::log(
                    $userId,
                    'reject',
                    'content',
                    $contentId,
                    $notes
                );
            }
            
            return true;
        } catch (PDOException | RuntimeException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Log error: $e->getMessage()
            // Re-throw runtime exceptions to signal disallowed transitions or bad state.
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Gets the current workflow state entry for a content item.
     *
     * @param int $contentId
     * @return array|null Associative array of the content_workflow row, or null if not found.
     */
    public function getCurrentWorkflowEntry(int $contentId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT cw.*, ws.name as state_name, ws.label as state_label 
                                      FROM content_workflow cw
                                      JOIN workflow_states ws ON cw.workflow_state_id = ws.id
                                      WHERE cw.content_id = :content_id");
            $stmt->execute([':content_id' => $contentId]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            return $entry ?: null;
        } catch (PDOException $e) {
            // Log error
            return null;
        }
    }

    /**
     * Gets the history of workflow transitions for a content item.
     *
     * @param int $contentId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getWorkflowHistory(int $contentId, int $limit = 50, int $offset = 0): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    cwh.*, 
                    ufs.name as from_state_name, ufs.label as from_state_label,
                    uts.name as to_state_name, uts.label as to_state_label
                    -- Optionally join with users table to get user names/emails
                 FROM content_workflow_history cwh
                 LEFT JOIN workflow_states ufs ON cwh.from_workflow_state_id = ufs.id
                 LEFT JOIN workflow_states uts ON cwh.to_workflow_state_id = uts.id
                 WHERE cwh.content_id = :content_id
                 ORDER BY cwh.transitioned_at DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error
            return [];
        }
    }

    /**
     * Sets the initial workflow state for a new content item.
     *
     * @param int $contentId
     * @param int|null $userId
     * @param string|null $notes
     * @param int|null $assignedToUserId
     * @return bool
     * @throws RuntimeException If no initial state is defined or transition fails.
     */
    public function setInitialStateForContent(int $contentId, ?int $userId = null, ?string $notes = null, ?int $assignedToUserId = null): bool
    {
        $initialState = $this->getInitialState();
        if (!$initialState) {
            throw new RuntimeException("No initial workflow state is defined in the system.");
        }
        return $this->transitionContent($contentId, (int)$initialState['id'], $userId, $notes, $assignedToUserId);
    }
}
