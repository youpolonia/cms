<?php
/**
 * Audit Log Service - Handles logging and retrieval of system audit events
 */
class AuditLogService {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Get filtered audit logs
     */
    public function getLogs(array $filters): array {
        $query = "SELECT * FROM audit_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $query .= " AND action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }

        $query .= " ORDER BY created_at DESC";
        $query .= " LIMIT " . ($filters['limit'] ?? 50);
        $query .= " OFFSET " . ($filters['offset'] ?? 0);

        return $this->db->query($query, $params)->fetchAll();
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): array {
        return [
            'total_actions' => $this->getTotalActions(),
            'actions_by_type' => $this->getActionsByType(),
            'recent_users' => $this->getRecentActiveUsers()
        ];
    }

    private function getTotalActions(): int {
        return $this->db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    }

    private function getActionsByType(): array {
        return $this->db->query("
            SELECT action, COUNT(*) as count 
            FROM audit_logs 
            GROUP BY action
            ORDER BY count DESC
            LIMIT 5
        ")->fetchAll();
    }

    private function getRecentActiveUsers(): array {
        return $this->db->query("
            SELECT user_id, MAX(created_at) as last_action 
            FROM audit_logs 
            GROUP BY user_id
            ORDER BY last_action DESC
            LIMIT 5
        ")->fetchAll();
    }

    /**
     * Clear logs older than specified days
     */
    public function clearOldLogs(int $daysToKeep): int {
        $result = $this->db->query(
            "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$daysToKeep]
        );
        return $result->rowCount();
    }
}
