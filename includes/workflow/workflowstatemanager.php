<?php
require_once __DIR__ . '/../../core/database.php';

declare(strict_types=1);

class WorkflowStateManager {
    private static string $stateTable = 'content_workflow_states';
    private static string $transitionTable = 'content_workflow_transitions';

    public static function getCurrentState(int $contentId): string {
        // Get current state from database
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT state FROM " . self::$stateTable . " WHERE content_id = ?");
        $stmt->execute([$contentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['state'] : 'draft';
    }

    public static function setState(int $contentId, string $state, int $userId): bool {
        $pdo = \core\Database::connection();
        
        try {
            $pdo->beginTransaction();
            
            // Update current state
            $stmt = $pdo->prepare(
                "INSERT INTO " . self::$stateTable . " 
                (content_id, state, user_id, changed_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                state = VALUES(state), 
                user_id = VALUES(user_id), 
                changed_at = VALUES(changed_at)"
            );
            $stmt->execute([$contentId, $state, $userId]);
            
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Workflow state change failed: " . $e->getMessage());
            return false;
        }
    }

    public static function logTransition(int $contentId, string $fromState, string $toState, int $userId): void {
        $pdo = \core\Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO " . self::$transitionTable . " 
            (content_id, from_state, to_state, user_id, transition_time) 
            VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$contentId, $fromState, $toState, $userId]);
    }
}
