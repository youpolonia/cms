<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Worker Notifications Controller
 */
class WorkerNotificationsController {
    /**
     * List all notifications for a worker
     */
    public function index(string $workerId): array {
        $unreadOnly = $_GET['unread'] ?? false;
        $typeFilter = $_GET['type'] ?? null;
        
        return WorkerNotification::getForWorker(
            $workerId,
            (bool)$unreadOnly,
            $typeFilter
        );
    }

    /**
     * Create a new notification
     */
    public function create(): array {
        $data = [
            'worker_id' => $_POST['worker_id'],
            'title' => $_POST['title'],
            'message' => $_POST['message'],
            'type' => $_POST['type'] ?? 'info',
            'is_scheduled' => $_POST['is_scheduled'] ?? 0,
            'batch_id' => $_POST['batch_id'] ?? null
        ];

        $notificationId = WorkerNotification::create($data);
        return ['success' => true, 'notification_id' => $notificationId];
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): array {
        return [
            'success' => WorkerNotification::markAsRead($notificationId)
        ];
    }

    /**
     * Get notification statistics
     */
    public function stats(string $workerId): array {
        return [
            'total' => count(WorkerNotification::getForWorker($workerId)),
            'unread' => WorkerNotification::countUnread($workerId),
            'types' => WorkerNotification::getTypes()
        ];
    }
}
