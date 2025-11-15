<?php
/**
 * LogModel - System logging functionality
 */
class LogModel {
    /**
     * Database connection instance
     * @var PDO|null
     */
    private static $db = null;

    /**
     * Log an event to the system_logs table
     * @param string $type Event type/category
     * @param string $message Log message
     * @param int|null $userId Associated user ID (optional)
     * @return bool True on success, false on failure
     */
    public static function logEvent(string $type, string $message, ?int $userId = null): bool {
        $db = self::getDbConnection();
        if (!$db) {
            return false;
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO system_logs 
                (log_type, message, user_id, created_at) 
                VALUES (:type, :message, :user_id, NOW())
            ");
            
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Silent failure - logging shouldn't break application
            return false;
        }
    }

    /**
     * Get latest system logs
     * @param int $limit Number of logs to return (default: 100)
     * @return array Array of log entries
     */
    public static function getLatestLogs(int $limit = 100): array {
        $db = self::getDbConnection();
        if (!$db) {
            return [];
        }

        try {
            $stmt = $db->prepare("
                SELECT log_type, message, user_id, created_at 
                FROM system_logs 
                ORDER BY created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get database connection
     * @return PDO|null
     */
    private static function getDbConnection(): ?PDO {
        if (self::$db === null) {
            try {
                require_once __DIR__ . '/../../config.php';
                self::$db = \core\Database::connection();
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            } catch (PDOException $e) {
                return null;
            }
        }
        return self::$db;
    }
}
