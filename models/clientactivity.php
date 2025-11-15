<?php
/**
 * Client Activity Model
 * Handles all client activity tracking operations
 */

class ClientActivity {
    protected $db;

    public function __construct() {
        global $db; // Using existing database connection
        $this->db = $db;
    }

    public function logActivity($clientId, $activityType, $details = [], $userId = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $this->db->prepare("
            INSERT INTO client_activities 
            (client_id, user_id, activity_type, activity_details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $detailsJson = json_encode($details);
        return $stmt->execute([$clientId, $userId, $activityType, $detailsJson, $ip, $userAgent]);
    }

    public function getActivities($clientId = null, $limit = 100, $startDate = null, $endDate = null) {
        $query = "
            SELECT ca.*, c.name as client_name
            FROM client_activities ca
            LEFT JOIN clients c ON ca.client_id = c.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($clientId !== null) {
            $query .= " AND ca.client_id = ?";
            $params[] = $clientId;
        }
        
        if ($startDate !== null) {
            $query .= " AND ca.created_at >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate !== null) {
            $query .= " AND ca.created_at <= ?";
            $params[] = $endDate;
        }
        
        $query .= " ORDER BY ca.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentActivities($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT ca.*, c.name as client_name
            FROM client_activities ca
            LEFT JOIN clients c ON ca.client_id = c.id
            ORDER BY ca.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
