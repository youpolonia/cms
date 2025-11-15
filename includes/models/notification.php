<?php
/**
 * Notification model for admin notifications
 * 
 * @package CMS
 * @subpackage Models
 */

defined('CMS_ROOT') or die('No direct script access allowed');

require_once __DIR__ . '/../../core/database.php';

class Notification {
    // Legacy type constants mapped to type_ids
    const TYPE_INFO = 1;
    const TYPE_WARNING = 2;
    const TYPE_CRITICAL = 3;
    const TYPE_SUCCESS = 4;

    /**
     * Get type ID from legacy type string
     * @param string $type
     * @return int
     * @throws InvalidArgumentException
     */
    private static function getTypeId(string $type): int {
        $map = [
            'info' => self::TYPE_INFO,
            'warning' => self::TYPE_WARNING,
            'critical' => self::TYPE_CRITICAL,
            'success' => self::TYPE_SUCCESS
        ];
        
        if (!isset($map[$type])) {
            throw new InvalidArgumentException("Invalid notification type");
        }
        
        return $map[$type];
    }

    /**
     * Create a new notification (modern version)
     *
     * @param string $title
     * @param string $message
     * @param int $type_id
     * @param int|null $user_id
     * @param bool $is_global
     * @return bool
     */
    public static function create(
        string $title,
        string $message,
        int $type_id,
        ?int $user_id = null,
        bool $is_global = false
    ): bool {
        $db = \core\Database::connection();
        
        // Validate type_id exists
        $typeCheck = $db->prepare("SELECT 1 FROM notification_types WHERE id = ?");
        $typeCheck->execute([$type_id]);
        if (!$typeCheck->fetch()) {
            throw new InvalidArgumentException("Invalid notification type ID");
        }

        $stmt = $db->prepare(
            "INSERT INTO notifications
            (title, message, type_id, user_id, is_global)
            VALUES (?, ?, ?, ?, ?)"
        );
        
        return $stmt->execute([$title, $message, $type_id, $user_id, (int)$is_global]);
    }

    /**
     * Mark notification as read
     * 
     * @param int $notification_id
     * @return bool
     */
    public static function markAsRead(int $notification_id): bool {
        $db = \core\Database::connection();
        $stmt = $db->prepare(
            "UPDATE notifications
            SET is_read = 1, read_at = CURRENT_TIMESTAMP
            WHERE id = ?"
        );
        return $stmt->execute([$notification_id]);
    }

    /**
     * Get notifications for user
     * 
     * @param int|null $user_id
     * @param int $limit
     * @param int $offset
     * @param bool $unread_only
     * @return array
     */
    /**
     * Legacy create method supporting string types
     * @deprecated Will be removed in next major version
     */
    public static function createLegacy(
        string $title,
        string $message,
        string $type = 'info',
        ?int $user_id = null,
        bool $is_global = false
    ): bool {
        return self::create(
            $title,
            $message,
            self::getTypeId($type),
            $user_id,
            $is_global
        );
    }

    public static function getForUser(
        ?int $user_id,
        int $limit = 20,
        int $offset = 0,
        bool $unread_only = false
    ): array {
        $db = \core\Database::connection();
        $where = "(is_global = 1" . ($user_id ? " OR user_id = ?" : "") . ")";
        $params = $user_id ? [$user_id] : [];

        if ($unread_only) {
            $where .= " AND is_read = 0";
        }

        $stmt = $db->prepare(
            "SELECT
                n.*,
                nt.type_key as type,
                nt.description as type_description
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.id
            WHERE $where
            ORDER BY n.created_at DESC
            LIMIT ? OFFSET ?"
        );

        $params = array_merge($params, [$limit, $offset]);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count unread notifications for user
     * 
     * @param int|null $user_id
     * @return int
     */
    public static function countUnread(?int $user_id): int {
        $db = \core\Database::connection();
        $where = "(is_global = 1" . ($user_id ? " OR user_id = ?" : "") . ")";
        $params = $user_id ? [$user_id] : [];

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM notifications
            WHERE $where AND is_read = 0"
        );
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get notification analytics for user
     * @param int $user_id
     * @return array
     */
    public static function getAnalytics(int $user_id): array {
        $db = \core\Database::connection();
        
        $stmt = $db->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(is_read) as read_count,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count,
                nt.type_key as type,
                nt.description as type_description
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.id
            WHERE user_id = ? OR is_global = 1
            GROUP BY n.type_id"
        );
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete multiple notifications
     * @param array $ids
     * @return int Number of deleted rows
     */
    public static function bulkDelete(array $ids): int {
        if (empty($ids)) {
            return 0;
        }

        $db = \core\Database::connection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $db->prepare(
            "DELETE FROM notifications WHERE id IN ($placeholders)"
        );
        $stmt->execute($ids);
        
        return $stmt->rowCount();
    }

    /**
     * Get all notification types
     * @return array
     */
    public static function getTypes(): array {
        $db = \core\Database::connection();
        $stmt = $db->query(
            "SELECT id, type_key, description FROM notification_types ORDER BY id"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
