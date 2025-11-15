<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../includes/permission/permissionmanager.php';
require_once __DIR__ . '/../../includes/securelogger.php';
require_once __DIR__ . '/../models/notificationmodel.php';
require_once __DIR__ . '/../../includes/security/ratelimiter.php';
require_once __DIR__ . '/../../core/csrf.php';

class NotificationController {
    private PermissionManager $permissionManager;
    private NotificationModel $notificationModel;
    private RateLimiter $rateLimiter;

    public function __construct() {
        $this->permissionManager = new PermissionManager();
        $this->notificationModel = new NotificationModel();
        $this->rateLimiter = new RateLimiter('notification_ops', 10, 60); // 10 requests per minute
    }

    /**
     * List all notifications for current user
     * @return void
     */
    public function listAll(): void {
        header('Content-Type: application/json');
        
        try {
            if (!$this->permissionManager->hasPermission('notifications_view')) {
                throw new Exception('Permission denied');
            }

            $userId = $_SESSION['user_id'] ?? 0;
            $notifications = $this->notificationModel->getByUserId((int)$userId);
            
            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Notification list failed');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark notification as read
     * @return void
     */
    public function markAsRead(): void {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            csrf_validate_or_403();

            if (!$this->permissionManager->hasPermission('notifications_edit')) {
                throw new Exception('Permission denied');
            }

            if (!$this->rateLimiter->checkLimit()) {
                throw new Exception('Too many requests');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['notification_id'])) {
                throw new Exception('Invalid request data');
            }

            if (!is_numeric($input['notification_id']) || $input['notification_id'] <= 0) {
                throw new Exception('Invalid notification ID');
            }

            $success = $this->notificationModel->markAsRead((int)$input['notification_id']);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification marked as read' : 'Operation failed'
            ]);
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Mark notification as read failed');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark notification as unread
     * @return void
     */
    public function markAsUnread(): void {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            csrf_validate_or_403();

            if (!$this->permissionManager->hasPermission('notifications_edit')) {
                throw new Exception('Permission denied');
            }

            if (!$this->rateLimiter->checkLimit()) {
                throw new Exception('Too many requests');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['notification_id'])) {
                throw new Exception('Invalid request data');
            }

            if (!is_numeric($input['notification_id']) || $input['notification_id'] <= 0) {
                throw new Exception('Invalid notification ID');
            }

            $success = $this->notificationModel->markAsUnread((int)$input['notification_id']);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification marked as unread' : 'Operation failed'
            ]);
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Mark notification as unread failed');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark all notifications as read for current user
     * @return void
     */
    public function markAllAsRead(): void {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            csrf_validate_or_403();

            if (!$this->permissionManager->hasPermission('notifications_edit')) {
                throw new Exception('Permission denied');
            }

            if (!$this->rateLimiter->checkLimit()) {
                throw new Exception('Too many requests');
            }

            $userId = $_SESSION['user_id'] ?? 0;
            $success = $this->notificationModel->markAllAsRead((int)$userId);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'All notifications marked as read' : 'Operation failed'
            ]);
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Mark all notifications as read failed');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create a new notification
     * @return void
     */
    public function createNotification(): void {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            csrf_validate_or_403();

            if (!$this->permissionManager->hasPermission('notifications_create')) {
                throw new Exception('Permission denied');
            }

            if (!$this->rateLimiter->checkLimit()) {
                throw new Exception('Too many requests');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['user_id'], $input['type'], $input['message'])) {
                throw new Exception('Invalid request data');
            }

            $notificationId = $this->notificationModel->create(
                (int)$input['user_id'],
                $input['type'],
                $input['message']
            );
            
            echo json_encode([
                'success' => $notificationId !== false,
                'notification_id' => $notificationId,
                'message' => $notificationId ? 'Notification created' : 'Failed to create notification'
            ]);
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Create notification failed');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a notification
     * @return void
     */
    public function deleteNotification(): void {
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            csrf_validate_or_403();

            if (!$this->permissionManager->hasPermission('notifications_delete')) {
                throw new Exception('Permission denied');
            }

            if (!$this->rateLimiter->checkLimit()) {
                throw new Exception('Too many requests');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['notification_id'])) {
                throw new Exception('Invalid request data');
            }

            $success = $this->notificationModel->delete((int)$input['notification_id']);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Notification deleted' : 'Failed to delete notification'
            ]);
        } catch (Exception $e) {
            SecureLogger::logError($e, 'Delete notification failed');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
