<?php
/**
 * Notification Manager - Handles user notifications
 */
class NotificationManager {
    private static ?PDO $db = null;

    /**
     * Initialize database connection
     */
    private static function initDB(): void {
        if (self::$db === null) {
            try {
                self::$db = \core\Database::connection();
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Add a new notification for user
     */
    public static function add(int $userId, string $type, string $message): bool {
        self::initDB();
        
        try {
            $stmt = self::$db->prepare("
                INSERT INTO notifications 
                (user_id, type, message, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NULL)
            ");
            return $stmt->execute([$userId, $type, $message]);
        } catch (PDOException $e) {
            error_log("Notification add failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all notifications for user
     */
    public static function getAll(int $userId): array {
        self::initDB();
        
        try {
            $stmt = self::$db->prepare("
                SELECT id, type, message, is_read, created_at 
                FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Notification fetch failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $id): bool {
        self::initDB();
        
        try {
            $stmt = self::$db->prepare("
                UPDATE notifications 
                SET is_read = 1, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Notification mark as read failed: " . $e->getMessage());
            return false;
        }
    }
}
