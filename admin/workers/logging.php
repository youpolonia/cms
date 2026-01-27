<?php
/**
 * Worker Activity Logging Controller
 *
 * @package CMS
 * @subpackage Admin\Workers
 */

// Prevent direct access
defined('CMS_ROOT') or die('No direct script access allowed');

require_once __DIR__ . '/bootstrap.php';

class WorkerActivityLogger {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    /**
     * Creates a new activity log entry
     * 
     * @param string $worker_id Worker identifier
     * @param string $action_type Type of action performed
     * @param string|null $details Additional details about the action
     * @return bool True on success, false on failure
     */
    public function createLog(string $worker_id, string $action_type, ?string $details = null): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO worker_activity_logs 
            (worker_id, action_type, details) 
            VALUES (:worker_id, :action_type, :details)"
        );

        return $stmt->execute([
            ':worker_id' => $worker_id,
            ':action_type' => $action_type,
            ':details' => $details
        ]);
    }

    /**
     * Gets recent activity logs for dashboard display
     * 
     * @param int $limit Number of logs to retrieve
     * @return array Array of recent activity logs
     */
    public function getRecentLogs(int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT l.*, w.username 
             FROM worker_activity_logs l
             JOIN workers w ON l.worker_id = w.worker_id
             ORDER BY l.timestamp DESC 
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all activity logs with pagination
     * 
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array Array of logs and pagination info
     */
    public function getAllLogs(int $page = 1, int $per_page = 25): array {
        $offset = ($page - 1) * $per_page;

        // Get total count
        $count_stmt = $this->db->query("SELECT COUNT(*) FROM worker_activity_logs");
        $total = $count_stmt->fetchColumn();

        // Get paginated logs
        $stmt = $this->db->prepare(
            "SELECT l.*, w.username 
             FROM worker_activity_logs l
             JOIN workers w ON l.worker_id = w.worker_id
             ORDER BY l.timestamp DESC 
             LIMIT :offset, :per_page"
        );
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'logs' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'last_page' => ceil($total / $per_page)
            ]
        ];
    }
}
