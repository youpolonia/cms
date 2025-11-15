<?php
require_once __DIR__ . '/../../core/database.php';

declare(strict_types=1);

class WorkflowApprovalSystem {
    private static string $approvalTable = 'workflow_approvals';
    
    public static function requestApproval(
        int $contentId,
        int $requesterId,
        array $approvers,
        string $message = ''
    ): bool {
        $pdo = \core\Database::connection();
        
        try {
            $pdo->beginTransaction();
            
            // Create approval request
            $stmt = $pdo->prepare(
                "INSERT INTO " . self::$approvalTable . " 
                (content_id, requester_id, message, created_at) 
                VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$contentId, $requesterId, $message]);
            $approvalId = $pdo->lastInsertId();
            
            // Add approvers
            foreach ($approvers as $approverId) {
                $stmt = $pdo->prepare(
                    "INSERT INTO workflow_approval_users 
                    (approval_id, user_id, status) 
                    VALUES (?, ?, 'pending')"
                );
                $stmt->execute([$approvalId, $approverId]);
            }
            
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Approval request failed: " . $e->getMessage());
            return false;
        }
    }

    public static function processApproval(
        int $approvalId,
        int $approverId,
        bool $approved,
        string $comment = ''
    ): bool {
        $pdo = \core\Database::connection();
        
        try {
            $pdo->beginTransaction();
            
            // Update approver status
            $stmt = $pdo->prepare(
                "UPDATE workflow_approval_users 
                SET status = ?, comment = ?, updated_at = NOW() 
                WHERE approval_id = ? AND user_id = ?"
            );
            $stmt->execute([
                $approved ? 'approved' : 'rejected',
                $comment,
                $approvalId,
                $approverId
            ]);
            
            // Check if all approvals are complete
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) as pending 
                FROM workflow_approval_users 
                WHERE approval_id = ? AND status = 'pending'"
            );
            $stmt->execute([$approvalId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['pending'] === 0) {
                self::completeApprovalProcess($approvalId);
            }
            
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Approval processing failed: " . $e->getMessage());
            return false;
        }
    }

    private static function completeApprovalProcess(int $approvalId): void {
        // Implementation would trigger next workflow steps
        // based on approval results
    }
}
