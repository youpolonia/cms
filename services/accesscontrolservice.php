<?php
/**
 * Access Control Service
 */
class AccessControlService {
    private $db;

    public function __construct() {
        require_once __DIR__ . '/../core/database.php';
        $this->db = \core\Database::connection();
    }

    public function checkDashboardAccess(int $userId): bool {
        // Check if user has at least view permissions
        $query = "SELECT 1 FROM user_permissions 
                 WHERE user_id = ? AND permission = 'view_dashboard'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        
        return (bool)$stmt->get_result()->fetch_row();
    }

    public function canGenerateReports(int $userId): bool {
        // Check report generation permissions
        $query = "SELECT 1 FROM user_permissions 
                 WHERE user_id = ? AND permission = 'generate_reports'";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        
        return (bool)$stmt->get_result()->fetch_row();
    }

    public function getUserPermissions(int $userId): array {
        $query = "SELECT permission FROM user_permissions 
                 WHERE user_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission'];
        }
        return $permissions;
    }
}
