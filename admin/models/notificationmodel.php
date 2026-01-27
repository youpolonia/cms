<?php
/**
 * Notification Model
 * 
 * Provides static methods for CRUD operations on notifications
 * 
 * @package Admin\Models
 */

class NotificationModel {
    /**
     * Database connection instance
     * @var PDO
     */
    private static ?PDO $db = null;

    /**
     * Initialize database connection
     * 
     * @return PDO Database connection
     * @throws PDOException If connection fails
     */
    private static function getDB(): PDO {
        if (self::$db === null) {
            require_once __DIR__ . '/../../core/database.php';
            self::$db = \core\Database::connection();
        }
        return self::$db;
    }

    /**
     * Create a new notification
     * 
     * @param int $userId Recipient user ID
     * @param string $type Notification type
     * @param string $message Notification content
     * @return int|false ID of created notification or false on failure
     */
    public static function create(int $userId, string $type, string $message): int|false {
        try {
            $db = self::getDB();
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, message)
                VALUES (:user_id, :type, :message)
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':type' => $type,
                ':message' => $message
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Notification create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notification by ID
     * 
     * @param int $id Notification ID
     * @return array|false Notification data or false if not found
     */
    public static function getById(int $id): array|false {
        try {
            $stmt = self::getDB()->prepare("
                SELECT * FROM notifications WHERE id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Notification getById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all notifications for a user
     * 
     * @param int $userId User ID
     * @param bool $unreadOnly Whether to only return unread notifications
     * @return array Array of notifications
     */
    public static function getByUserId(int $userId, bool $unreadOnly = false): array {
        try {
            $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
            if ($unreadOnly) {
                $sql .= " AND read_flag = FALSE";
            }
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = self::getDB()->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Notification getByUserId error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update a notification
     * 
     * @param int $id Notification ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public static function update(int $id, array $data): bool {
        try {
            $updates = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $stmt = self::getDB()->prepare("
                UPDATE notifications 
                SET " . implode(', ', $updates) . "
                WHERE id = :id
            ");
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Notification update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a notification
     * 
     * @param int $id Notification ID
     * @return bool Success status
     */
    public static function delete(int $id): bool {
        try {
            $stmt = self::getDB()->prepare("
                DELETE FROM notifications WHERE id = :id
            ");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Notification delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark notification as read
     * 
     * @param int $id Notification ID
     * @return bool Success status
     */
    public static function markAsRead(int $id): bool {
        return self::update($id, ['read_flag' => true]);
    }

    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public static function markAllAsRead(int $userId): bool {
        try {
            $stmt = self::getDB()->prepare("
                UPDATE notifications 
                SET read_flag = TRUE 
                WHERE user_id = :user_id AND read_flag = FALSE
            ");
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("Notification markAllAsRead error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Mark notification as unread
     *
     * @param int $id Notification ID
     * @return bool Success status
     */
    public static function markAsUnread(int $id): bool {
        return self::update($id, ['read_flag' => false]);
    }
}
