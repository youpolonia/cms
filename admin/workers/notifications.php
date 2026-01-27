<?php
/**
 * Worker Notification System
 *
 * @package CMS
 * @subpackage Admin\Workers
 */

declare(strict_types=1);

require_once __DIR__ . '/../../core/csrf.php';

csrf_boot('admin');

// Check authentication
if (!isset($auth) || !$auth->isLoggedIn()) {
    header('Location: /auth/worker/login');
    exit;
}

// Load dependencies
require_once __DIR__ . '/bootstrap.php';

class WorkerNotificationSystem
{
    protected \PDO $db;

    public function __construct(\PDO $connection)
    {
        $this->db = $connection;
    }

    /**
     * Create a new notification
     */
    public function create(array $data): bool
    {
        $this->validateNotificationData($data);

        $sql = "INSERT INTO worker_notifications (
            worker_id, 
            title, 
            message, 
            type
        ) VALUES (?, ?, ?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['worker_id'],
                $data['title'],
                $data['message'],
                $data['type'] ?? 'info'
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to create notification: " . $e->getMessage());
        }
    }

    /**
     * Get notifications for a worker
     */
    public function getForWorker(string $workerId, bool $unreadOnly = false): array
    {
        $sql = "SELECT * FROM worker_notifications WHERE worker_id = ?";
        $params = [$workerId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to get notifications: " . $e->getMessage());
        }
    }

    /**
     * Get notifications with admin view status
     */
    public function getForAdmin(bool $unviewedOnly = false): array
    {
        $sql = "SELECT * FROM worker_notifications";
        $params = [];

        if ($unviewedOnly) {
            $sql .= " WHERE viewed_by_admin = 0";
        }

        $sql .= " ORDER BY created_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to get admin notifications: " . $e->getMessage());
        }
    }

    /**
     * Mark notification as viewed by admin
     */
    public function markAsViewedByAdmin(int $notificationId): bool
    {
        $sql = "UPDATE worker_notifications SET
            viewed_by_admin = 1,
            viewed_at = CURRENT_TIMESTAMP
        WHERE notification_id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to mark notification as viewed by admin: " . $e->getMessage());
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $sql = "UPDATE worker_notifications SET 
            is_read = 1,
            read_at = CURRENT_TIMESTAMP
        WHERE notification_id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to mark notification as read: " . $e->getMessage());
        }
    }

    /**
     * Get unread notification count for worker
     */
    public function getUnreadCount(string $workerId): int
    {
        $sql = "SELECT COUNT(*) FROM worker_notifications
                WHERE worker_id = ? AND is_read = 0";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$workerId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to get unread count: " . $e->getMessage());
        }
    }

    /**
     * Get count of unviewed notifications by admin
     */
    public function getUnviewedByAdminCount(): int
    {
        $sql = "SELECT COUNT(*) FROM worker_notifications
                WHERE viewed_by_admin = 0";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to get unviewed by admin count: " . $e->getMessage());
        }
    }

    /**
     * Validate notification data
     */
    protected function validateNotificationData(array $data): void
    {
        if (empty($data['worker_id'])) {
            throw new \InvalidArgumentException("Worker ID is required");
        }

        if (empty($data['title'])) {
            throw new \InvalidArgumentException("Title is required");
        }

        if (empty($data['message'])) {
            throw new \InvalidArgumentException("Message is required");
        }

        if (isset($data['type']) && !in_array($data['type'], ['info', 'warning', 'critical', 'success'])) {
            throw new \InvalidArgumentException("Invalid notification type");
        }
    }
}

// API endpoint for AJAX polling
if (isset($_GET['api']) && $_GET['api'] === 'poll') {
    header('Content-Type: application/json');
    
    try {
        $db = new \CMS\Includes\Database\Connection();
        $notificationSystem = new WorkerNotificationSystem($db);
        
        $workerId = $_GET['worker_id'] ?? null;
        if (!$workerId) {
            throw new \InvalidArgumentException("Worker ID required");
        }

        $notifications = $notificationSystem->getForWorker($workerId, true);
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => count($notifications)
        ]);
    } catch (\Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Main view logic would go here if needed
