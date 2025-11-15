<?php
/**
 * Security Log Model
 * Handles audit logging for security events
 */

require_once __DIR__ . '/../../../core/database.php';
class SecurityLog {
    private static $table = 'security_logs';

    /**
     * Log a security event
     * @param string $eventType Type of event (login, permission_change, etc)
     * @param int $userId User ID associated with event
     * @param string $ipAddress IP address of request
     * @param string $details Additional event details
     * @return bool True if logged successfully
     */
    public static function logEvent(string $eventType, int $userId, string $ipAddress, string $details = ''): bool {
        $db = \core\Database::connection();
        $stmt = $db->prepare("INSERT INTO ".self::$table." 
            (event_type, user_id, ip_address, details, created_at) 
            VALUES (?, ?, ?, ?, NOW())");
        
        return $stmt->execute([$eventType, $userId, $ipAddress, $details]);
    }

    /**
     * Get security logs with optional filters
     * @param array $filters Optional filters (event_type, user_id, date_range)
     * @param int $limit Maximum records to return
     * @return array Array of log records
     */
    public static function getLogs(array $filters = [], int $limit = 100): array {
        $db = \core\Database::connection();
        $query = "SELECT * FROM ".self::$table." WHERE 1=1";
        $params = [];
        
        if (!empty($filters['event_type'])) {
            $query .= " AND event_type = ?";
            $params[] = $filters['event_type'];
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_range'])) {
            $query .= " AND created_at BETWEEN ? AND ?";
            $params = array_merge($params, $filters['date_range']);
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
