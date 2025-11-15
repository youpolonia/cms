<?php
require_once __DIR__ . '/../../core/database.php';

class SecurityLog {
    /**
     * Logs a security event
     * @param string $eventType Event type identifier
     * @param int $userId Acting user ID
     * @param string $ipAddress IP address
     * @param array $details Additional event details
     * @param string $message Human-readable message
     */
    public static function logEvent(
        string $eventType,
        int $userId,
        string $ipAddress,
        array $details,
        string $message
    ): void {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action_type, ip_address, details, message, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $eventType,
            $ipAddress,
            json_encode($details),
            $message,
            Tenant::currentId(),
            date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get paginated audit logs with filters
     * @param array $filters
     * @param int $perPage
     * @param int $offset
     * @return array
     */
    public static function getLogs(array $filters, int $perPage, int $offset): array {
        $pdo = \core\Database::connection();
        $query = "SELECT al.*, u.username as user_name
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['date_from'])) {
            $query .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $query .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['tenant_id'])) {
            $query .= " AND al.tenant_id = ?";
            $params[] = $filters['tenant_id'];
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Count total logs matching filters
     * @param array $filters
     * @return int
     */
    public static function countLogs(array $filters): int {
        $pdo = \core\Database::connection();
        $query = "SELECT COUNT(*) FROM audit_logs WHERE 1=1";
        $params = [];
        
        // Apply filters
        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $query .= " AND action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['tenant_id'])) {
            $query .= " AND tenant_id = ?";
            $params[] = $filters['tenant_id'];
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get distinct action types for filter dropdown
     * @return array
     */
    public static function getActionTypes(): array {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT DISTINCT action_type FROM audit_logs ORDER BY action_type");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Export logs to CSV
     * @param array $filters
     */
    public static function exportToCsv(array $filters): void {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'ID', 'Timestamp', 'User ID', 'Username', 'Action Type',
            'IP Address', 'Message', 'Tenant ID', 'Details'
        ]);
        
        // Get all matching logs (no pagination)
        $logs = self::getLogs($filters, PHP_INT_MAX, 0);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['user_id'],
                $log['user_name'],
                $log['action_type'],
                $log['ip_address'],
                $log['message'],
                $log['tenant_id'],
                $log['details']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
