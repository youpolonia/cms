<?php
require_once __DIR__ . '/../../core/database.php';

class ContentEntry {
    protected $db;
    public function __construct() { $this->db = \core\Database::connection(); }

    /**
     * Get filtered entries with pagination
     * @param array $filters [type, status, date_from, date_to, search]
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param string $sortField Field to sort by
     * @param string $sortOrder ASC/DESC
     * @return array
     */
    public function getFilteredEntries(array $filters, int $page = 1, int $perPage = 20, string $sortField = 'created_at', string $sortOrder = 'DESC'): array {
        $offset = ($page - 1) * $perPage;
        $query = "SELECT * FROM content_entries WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        // Add sorting
        $query .= " ORDER BY $sortField $sortOrder LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $perPage;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total filtered entries (for pagination)
     * @param array $filters Same as getFilteredEntries
     * @return int
     */
    public function countFilteredEntries(array $filters): int {
        $query = "SELECT COUNT(*) FROM content_entries WHERE 1=1";
        $params = [];

        // Apply same filters as getFilteredEntries
        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $query .= " AND title LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Add other existing methods here as needed
}
