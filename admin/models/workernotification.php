<?php
/**
 * Worker Notification Model
 */

require_once __DIR__ . '/../../core/database.php';
class WorkerNotification {
    const TABLE = 'worker_notifications';
    const TYPES = ['info', 'warning', 'critical', 'success'];

    /**
     * Create a new worker notification
     */
    public static function create(array $data): int {
        $db = \core\Database::connection();
        $stmt = $db->prepare("
            INSERT INTO " . self::TABLE . " 
            (worker_id, title, message, type, is_scheduled, batch_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['worker_id'],
            $data['title'],
            $data['message'],
            $data['type'] ?? 'info',
            $data['is_scheduled'] ?? 0,
            $data['batch_id'] ?? null
        ]);
        return $db->lastInsertId();
    }

    /**
     * Get notifications for worker
     */
    public static function getForWorker(string $workerId, bool $unreadOnly = false, ?string $type = null): array {
        $db = \core\Database::connection();
        $sql = "SELECT * FROM " . self::TABLE . " WHERE worker_id = ?";
        $params = [$workerId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY created_at DESC";
        return $db->query($sql, $params)->fetchAll();
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $notificationId): bool {
        $db = \core\Database::connection();
        return $db->update(
            self::TABLE,
            ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')],
            ['notification_id' => $notificationId]
        ) > 0;
    }

    /**
     * Get notification types
     */
    public static function getTypes(): array {
        return self::TYPES;
    }

    /**
     * Count unread notifications for worker
     */
    public static function countUnread(string $workerId): int {
        $db = \core\Database::connection();
        return $db->query(
            "SELECT COUNT(*) FROM " . self::TABLE . " 
             WHERE worker_id = ? AND is_read = 0",
            [$workerId]
        )->fetchColumn();
    }
}
