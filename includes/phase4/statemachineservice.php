<?php

declare(strict_types=1);

namespace Includes\Phase4;

use PDO;
use Includes\Core\DB; // Assuming DB class for PDO connection
use Includes\Core\ActivityTracker; // Assuming for logging

require_once __DIR__ . '/../../core/database.php';

/**
 * StateMachineService
 *
 * Manages state transitions for various entities within the CMS.
 * Adheres to pure PHP, static methods, and shared hosting constraints.
 */
final class StateMachineService
{
    private static ?PDO $pdo = null;

    private static function getPDO(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = \core\Database::connection();
        }
        return self::$pdo;
    }

    /**
     * Get available transitions for a given entity, its ID, and current state.
     *
     * @param string $entityType The type of entity (e.g., 'content', 'user_report').
     * @param int $entityId The ID of the entity.
     * @param string $currentState The current state of the entity.
     * @return array Array of available next states.
     */
    public static function getAvailableTransitions(string $entityType, int $entityId, string $currentState): array
    {
        // This is a simplified example. A real implementation would likely involve
        // a 'workflow_definitions' or 'state_transitions' table.
        $transitions = [];
        if ($entityType === 'content_approval') {
            switch ($currentState) {
                case 'draft':
                    $transitions = ['pending_review', 'archived'];
                    break;
                case 'pending_review':
                    $transitions = ['approved', 'rejected', 'draft'];
                    break;
                case 'approved':
                    $transitions = ['published', 'archived', 'needs_revision'];
                    break;
                case 'rejected':
                    $transitions = ['draft', 'archived'];
                    break;
                case 'published':
                    $transitions = ['archived', 'needs_revision'];
                    break;
                case 'needs_revision':
                    $transitions = ['draft', 'pending_review'];
                    break;
                case 'archived':
                    // No transitions from archived in this simple model
                    break;
            }
        }
        // Add more entity types and their state logic here
        return $transitions;
    }

    /**
     * Transition an entity to a new state.
     *
     * @param string $entityType The type of entity.
     * @param int $entityId The ID of the entity.
     * @param string $newState The target state.
     * @param int $userId The ID of the user performing the transition.
     * @param string|null $currentKnownState Optional: The expected current state for optimistic locking.
     * @param string|null $notes Optional notes for the transition.
     * @return bool True on success, false on failure.
     */
    public static function transitionToState(
        string $entityType,
        int $entityId,
        string $newState,
        int $userId,
        ?string $currentKnownState = null,
        ?string $notes = null
    ): bool {
        $pdo = self::getPDO();
        $pdo->beginTransaction();

        try {
            // 1. Get current state from the appropriate table based on entityType
            // This is highly dependent on your database schema.
            // Example for a generic 'entities_with_state' table:
            // $stmt = $pdo->prepare("SELECT current_state FROM entities_with_state WHERE id = :entity_id AND type = :entity_type FOR UPDATE");
            // $stmt->execute(['entity_id' => $entityId, 'entity_type' => $entityType]);
            // $currentActualState = $stmt->fetchColumn();

            // For this example, let's assume a 'content_approvals' table for 'content_approval' entityType
            $currentActualState = null;
            if ($entityType === 'content_approval') {
                 $stmt = $pdo->prepare("SELECT status FROM content_approvals WHERE id = :entity_id FOR UPDATE");
                 $stmt->execute(['entity_id' => $entityId]);
                 $currentActualState = $stmt->fetchColumn();
            } else {
                ActivityTracker::logError('StateMachineService: Unknown entityType for state transition.', ['entityType' => $entityType, 'entityId' => $entityId]);
                $pdo->rollBack();
                return false;
            }

            if ($currentActualState === false) { // Entity not found
                ActivityTracker::logError('StateMachineService: Entity not found for state transition.', ['entityType' => $entityType, 'entityId' => $entityId]);
                $pdo->rollBack();
                return false;
            }

            // 2. Optimistic locking check
            if ($currentKnownState !== null && $currentKnownState !== $currentActualState) {
                ActivityTracker::logWarning('StateMachineService: State transition conflict (optimistic lock).', [
                    'entityType' => $entityType, 'entityId' => $entityId,
                    'expectedState' => $currentKnownState, 'actualState' => $currentActualState
                ]);
                $pdo->rollBack();
                return false; // State has changed since last read
            }

            // 3. Validate if the transition is allowed
            $availableTransitions = self::getAvailableTransitions($entityType, $entityId, $currentActualState);
            if (!in_array($newState, $availableTransitions)) {
                ActivityTracker::logWarning('StateMachineService: Invalid state transition attempted.', [
                    'entityType' => $entityType, 'entityId' => $entityId,
                    'fromState' => $currentActualState, 'toState' => $newState
                ]);
                $pdo->rollBack();
                return false;
            }

            // 4. Update the entity's state
            // Again, this depends on your schema.
            if ($entityType === 'content_approval') {
                $updateStmt = $pdo->prepare("UPDATE content_approvals SET status = :new_state, updated_at = CURRENT_TIMESTAMP WHERE id = :entity_id");
                $updateStmt->execute(['new_state' => $newState, 'entity_id' => $entityId]);
            }
            // Add other entity types here

            // 5. Log the transition (e.g., in a 'workflow_audit_log' or similar)
            // This table was one of the "unplanned" ones, so its existence is not guaranteed.
            // If it exists and is desired:
            /*
            $logStmt = $pdo->prepare(
                "INSERT INTO workflow_audit_log (entity_type, entity_id, previous_state, new_state, user_id, notes, created_at)
                 VALUES (:entity_type, :entity_id, :previous_state, :new_state, :user_id, :notes, CURRENT_TIMESTAMP)"
            );
            $logStmt->execute([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'previous_state' => $currentActualState,
                'new_state' => $newState,
                'user_id' => $userId,
                'notes' => $notes
            ]);
            */
            // For now, using ActivityTracker as a generic log
            ActivityTracker::logInfo('StateMachineService: State transitioned.', [
                'entityType' => $entityType, 'entityId' => $entityId,
                'fromState' => $currentActualState, 'toState' => $newState, 'userId' => $userId, 'notes' => $notes
            ]);


            $pdo->commit();
            return true;
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ActivityTracker::logException('StateMachineService: PDOException during state transition.', $e, [
                'entityType' => $entityType, 'entityId' => $entityId, 'newState' => $newState
            ]);
            return false;
        } catch (\Throwable $t) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ActivityTracker::logException('StateMachineService: Throwable during state transition.', $t, [
                'entityType' => $entityType, 'entityId' => $entityId, 'newState' => $newState
            ]);
            return false;
        }
    }
}
