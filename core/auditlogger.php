<?php
/**
 * Audit Logger
 * 
 * Provides static methods for recording and retrieving audit logs
 */
class AuditLogger {
    /**
     * Log an audit event
     * 
     * @param int $userId User ID performing the action
     * @param string $action Action performed (e.g., 'create', 'update', 'delete')
     * @param string $targetType Type of target (e.g., 'user', 'page', 'post')
     * @param int|null $targetId ID of the target (optional)
     * @param string $message Additional message (optional)
     * @return bool True on success, false on failure
     */
    public static function log(int $userId, string $action, string $targetType, ?int $targetId = null, string $message = ''): bool {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                INSERT INTO audit_logs 
                (user_id, action, target_type, target_id, message, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $userId,
                $action,
                $targetType,
                $targetId,
                $message
            ]);
        } catch (PDOException $e) {
            error_log("Audit log failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get audit logs with optional filters
     * 
     * @param array $filters Associative array of filters (e.g., ['user_id' => 1, 'action' => 'create'])
     * @return array Array of audit log entries
     */
    public static function getLogs(array $filters = []): array {
        try {
            $db = \core\Database::connection();
            $where = [];
            $params = [];
            
            foreach ($filters as $field => $value) {
                $where[] = "$field = ?";
                $params[] = $value;
            }
            
            $query = "SELECT * FROM audit_logs";
            if (!empty($where)) {
                $query .= " WHERE " . implode(' AND ', $where);
            }
            $query .= " ORDER BY created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get audit logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get audit logs for a specific user
     * 
     * @param int $userId User ID to filter by
     * @return array Array of audit log entries for the user
     */
    public static function getLogsByUser(int $userId): array {
        return self::getLogs(['user_id' => $userId]);
    }
}
