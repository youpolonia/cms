<?php
require_once __DIR__ . '/../../core/database.php';

declare(strict_types=1);

class WorkflowNotificationSystem {
    private static string $notificationTable = 'workflow_notifications';

    public static function sendTransitionNotification(
        int $contentId,
        string $fromState,
        string $toState,
        int $initiatorId,
        array $context = []
    ): bool {
        $recipients = self::getNotificationRecipients($contentId, $toState);
        
        foreach ($recipients as $userId) {
            self::createNotification(
                $userId,
                'workflow_transition',
                [
                    'content_id' => $contentId,
                    'from_state' => $fromState,
                    'to_state' => $toState,
                    'initiator_id' => $initiatorId
                ] + $context
            );
        }

        return true;
    }

    public static function sendApprovalRequestNotification(
        int $contentId,
        int $requesterId,
        array $approvers
    ): bool {
        foreach ($approvers as $approverId) {
            self::createNotification(
                $approverId,
                'approval_request',
                [
                    'content_id' => $contentId,
                    'requester_id' => $requesterId
                ]
            );
        }

        return true;
    }

    private static function getNotificationRecipients(
        int $contentId,
        string $newState
    ): array {
        // Implementation would query database for users who should
        // receive notifications for this state transition
        return [];
    }

    private static function createNotification(
        int $userId,
        string $type,
        array $data
    ): bool {
        $pdo = \core\Database::connection();
        
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO " . self::$notificationTable . " 
                (user_id, type, data, created_at, is_read) 
                VALUES (?, ?, ?, NOW(), 0)"
            );
            $stmt->execute([
                $userId,
                $type,
                json_encode($data)
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Notification creation failed: " . $e->getMessage());
            return false;
        }
    }
}
