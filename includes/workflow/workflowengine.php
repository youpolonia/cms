<?php
/**
 * Workflow Engine for content state management
 */
class WorkflowEngine {
    private $db;
    private $validTransitions = [
        'draft' => ['review'],
        'review' => ['published', 'draft'],
        'published' => ['archived'],
        'archived' => ['published']
    ];

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function transition(int $content_id, string $target_state): bool {
        $current_state = $this->getCurrentState($content_id);
        
        if (!$this->isValidTransition($current_state, $target_state)) {
            throw new Exception("Invalid transition from $current_state to $target_state");
        }

        return $this->updateState($content_id, $target_state);
    }

    private function getCurrentState(int $content_id): string {
        $stmt = $this->db->prepare("SELECT state FROM content_workflow WHERE content_id = ?");
        $stmt->execute([$content_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['state'] : 'draft';
    }

    private function isValidTransition(string $current, string $target): bool {
        return in_array($target, $this->validTransitions[$current] ?? []);
    }

    private function updateState(int $content_id, string $state): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO content_workflow (content_id, state, updated_at) 
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE state = ?, updated_at = NOW()"
        );
        
        return $stmt->execute([$content_id, $state, $state]);
    }
}
