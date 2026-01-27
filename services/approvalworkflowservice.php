<?php

require_once __DIR__.'/WorkflowService.php';

class ApprovalWorkflowService extends WorkflowService {
    public function startApprovalWorkflow(string $workflowId, string $contentId, string $initiator): string {
        $instanceId = parent::startWorkflow($workflowId, $contentId, $initiator);
        
        // Set up initial approval level
        $this->setupFirstApprovalLevel($instanceId, $workflowId);
        
        return $instanceId;
    }

    private function setupFirstApprovalLevel(string $instanceId, string $workflowId): void {
        $stmt = $this->pdo->prepare("
            SELECT id FROM approval_levels 
            WHERE workflow_id = ? AND level_number = 1
        ");
        $stmt->execute([$workflowId]);
        $level = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($level) {
            $this->updateCurrentLevel($instanceId, $level['id']);
            $this->setDeadline($instanceId, $level['id']);
        }
    }

    public function processApproval(string $instanceId, string $action, string $approver): bool {
        if (!parent::processTransition($instanceId, $action, $approver)) {
            return false;
        }

        if ($action === 'approve') {
            return $this->advanceToNextLevel($instanceId);
        }

        return true;
    }

    private function advanceToNextLevel(string $instanceId): bool {
        $currentLevel = $this->getCurrentLevel($instanceId);
        $nextLevel = $this->getNextLevel($currentLevel['workflow_id'], $currentLevel['level_number']);

        if ($nextLevel) {
            $this->updateCurrentLevel($instanceId, $nextLevel['id']);
            $this->setDeadline($instanceId, $nextLevel['id']);
            return true;
        }

        return false;
    }

    private function getCurrentLevel(string $instanceId): array {
        $stmt = $this->pdo->prepare("
            SELECT ai.workflow_id, al.level_number 
            FROM approval_instances ai
            JOIN approval_levels al ON ai.current_level_id = al.id
            WHERE ai.id = ?
        ");
        $stmt->execute([$instanceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getNextLevel(string $workflowId, int $currentLevel): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id FROM approval_levels 
            WHERE workflow_id = ? AND level_number = ?
        ");
        $stmt->execute([$workflowId, $currentLevel + 1]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function updateCurrentLevel(string $instanceId, string $levelId): void {
        $stmt = $this->pdo->prepare("
            UPDATE approval_instances 
            SET current_level_id = ? 
            WHERE id = ?
        ");
        $stmt->execute([$levelId, $instanceId]);
    }

    private function setDeadline(string $instanceId, string $levelId): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO approval_deadlines 
            (id, instance_id, level_id, due_at, status) 
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? HOUR), 'pending')
        ");
        $stmt->execute([
            uniqid('dl_', true),
            $instanceId,
            $levelId,
            $this->getLevelDeadlineHours($levelId)
        ]);
    }

    private function getLevelDeadlineHours(string $levelId): int {
        $stmt = $this->pdo->prepare("
            SELECT deadline_hours FROM approval_levels WHERE id = ?
        ");
        $stmt->execute([$levelId]);
        return (int)$stmt->fetchColumn();
    }
}
