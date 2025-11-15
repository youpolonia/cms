<?php
/**
 * Notification Service - Handles notification operations
 * 
 * Features:
 * - Tenant isolation
 * - Delivery logging
 * - Timestamp management
 */

require_once __DIR__ . '/../../admin/db/migrations/phase3_worker_notifications_index/NotificationSchema.php';

class NotificationService {
    private static $instance = null;
    private $tenantId;
    private $dbConnection;

    private function __construct($tenantId) {
        $this->tenantId = $tenantId;
        $this->dbConnection = $this->getDbConnection();
    }

    public static function getInstance($tenantId) {
        if (self::$instance === null || self::$instance->tenantId !== $tenantId) {
            self::$instance = new self($tenantId);
        }
        return self::$instance;
    }

    /**
     * Send a notification
     * @param string $recipient
     * @param string $subject
     * @param string $message
     * @param string $type
     * @return array [success: bool, notificationId: int|null, error: string|null]
     */
    public function sendNotification($recipient, $subject, $message, $type = 'system') {
        $timestamp = date('Y-m-d H:i:s');
        $notificationId = $this->storeNotification([
            'tenant_id' => $this->tenantId,
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);

        if (!$notificationId) {
            return ['success' => false, 'notificationId' => null, 'error' => 'Failed to store notification'];
        }

        $deliveryResult = $this->deliverNotification($notificationId, $recipient, $subject, $message);
        
        $this->logDelivery($notificationId, $deliveryResult['success'], $deliveryResult['error'] ?? null);

        return [
            'success' => $deliveryResult['success'],
            'notificationId' => $notificationId,
            'error' => $deliveryResult['error'] ?? null
        ];
    }

    /**
     * Store notification in database
     * @param array $notificationData
     * @return int|null Notification ID or null on failure
     */
    private function storeNotification($notificationData) {
        // Placeholder - db-support should implement actual DB operations
        return $this->dbConnection->insertNotification($notificationData);
    }

    /**
     * Deliver notification to recipient
     * @param int $notificationId
     * @param string $recipient
     * @param string $subject
     * @param string $message
     * @return array [success: bool, error: string|null]
     */
    private function deliverNotification($notificationId, $recipient, $subject, $message) {
        // Placeholder - implement actual delivery method (email, SMS, etc.)
        return ['success' => true];
    }

    /**
     * Log delivery attempt
     * @param int $notificationId
     * @param bool $success
     * @param string|null $error
     */
    private function logDelivery($notificationId, $success, $error = null) {
        $logData = [
            'notification_id' => $notificationId,
            'attempted_at' => date('Y-m-d H:i:s'),
            'success' => $success ? 1 : 0,
            'error_message' => $error
        ];
        $this->dbConnection->insertDeliveryLog($logData);
    }

    /**
     * Get notifications for user
     * @param string $recipient
     * @param int $limit
     * @param string $status
     * @return array
     */
    public function getNotifications($recipient, $limit = 10, $status = null) {
        $conditions = [
            'tenant_id' => $this->tenantId,
            'recipient' => $recipient
        ];
        
        if ($status) {
            $conditions['status'] = $status;
        }

        return $this->dbConnection->getNotifications($conditions, $limit);
    }

    /**
     * Get single notification by ID
     * @param int $notificationId
     * @return array|null
     */
    public function getNotification($notificationId) {
        return $this->dbConnection->getNotification($notificationId, $this->tenantId);
    }

    /**
     * Mark notification as read
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead($notificationId) {
        return $this->dbConnection->updateNotification(
            $notificationId,
            ['status' => 'read', 'updated_at' => date('Y-m-d H:i:s')],
            $this->tenantId
        );
    }

    /**
     * Get DB connection
     * @return object Database connection with required methods
     */
    private function getDbConnection() {
        // Placeholder - db-support should implement actual DB connection
        return new class {
            public function insertNotification($data) { return rand(1, 1000); }
            public function insertDeliveryLog($data) { return true; }
            public function getNotifications($conditions, $limit) { return []; }
            public function getNotification($id, $tenantId) { return null; }
            public function updateNotification($id, $data, $tenantId) { return true; }
        };
    }
}
