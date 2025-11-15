<?php
require_once __DIR__.'/../core/database.php';

class VersionModel {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function getDb() {
        return $this->db;
    }

    /**
     * Get version by ID
     */
    public function getById($versionId, $tenantId) {
        TenantValidator::validate($tenantId);
        $query = "SELECT * FROM content_versions WHERE id = ? AND tenant_id = ?";
        $result = $this->db->query($query, [$versionId, $tenantId]);
        
        if (empty($result)) {
            throw new Exception("Version not found");
        }
        
        return $result[0];
    }

    /**
     * Get current content for a content item
     */
    public function getCurrentContent($contentId) {
        $query = "SELECT * FROM contents WHERE id = ?";
        $result = $this->db->query($query, [$contentId]);
        
        if (empty($result)) {
            throw new Exception("Content not found");
        }
        
        return $result[0];
    }

    /**
     * Get all versions for a content item
     */
    public function getVersionsForContent($contentId, $tenantId) {
        TenantValidator::validate($tenantId);
        $query = "SELECT * FROM content_versions WHERE content_id = ? AND tenant_id = ? ORDER BY created_at DESC";
        return $this->db->query($query, [$contentId, $tenantId]);
    }

    /**
     * Restore a content version
     */
    public function restoreVersion($contentId, $content, $versionId, $tenantId) {
        TenantValidator::validate($tenantId);
        // Verify version belongs to tenant before restoration
        $version = $this->getById($versionId, $tenantId);
        $query = "UPDATE contents SET content = ?, version_id = ? WHERE id = ? AND tenant_id = ?";
        return $this->db->query($query, [$content, $versionId, $contentId, $tenantId]);
    }

    /**
     * Log restoration activity with enhanced details
     */
    public function logRestoration($versionId, $userId, $tenantId, $options = []) {
        TenantValidator::validate($tenantId);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $reason = $options['reason'] ?? '';
        $changes = $options['changes'] ?? '';
        $status = $options['status'] ?? 'completed';

        $query = "INSERT INTO restoration_log
                 (version_id, user_id, restored_at, ip_address, user_agent,
                  reason, changes, status)
                 VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
        return $this->db->query($query, [
            $versionId,
            $userId,
            $ip,
            $userAgent,
            $reason,
            $changes,
            $status
        ]);
    }

    /**
     * Get restoration log entries
     */
    public function getRestorationLogs($tenantId, $filters = []) {
        TenantValidator::validate($tenantId);
        $where = [];
        $params = [];
        
        if (!empty($filters['version_id'])) {
            $where[] = "version_id = ?";
            $params[] = $filters['version_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "restored_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "restored_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $limit = isset($filters['limit']) ? "LIMIT " . (int)$filters['limit'] : '';
        $offset = isset($filters['offset']) ? "OFFSET " . (int)$filters['offset'] : '';
        
        $query = "SELECT l.*, u.username, v.content_id
                 FROM restoration_log l
                 LEFT JOIN users u ON l.user_id = u.id
                 LEFT JOIN content_versions v ON l.version_id = v.id
                 $whereClause
                 ORDER BY restored_at DESC
                 $limit $offset";
                 
        return $this->db->query($query, $params);
    }

    /**
     * Get restoration log count for pagination
     */
    public function getRestorationLogCount($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['version_id'])) {
            $where[] = "version_id = ?";
            $params[] = $filters['version_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $query = "SELECT COUNT(*) as count FROM restoration_log $whereClause";
        $result = $this->db->query($query, $params);
        
        return $result[0]['count'] ?? 0;
    }
    /**
     * Get filtered versions with pagination
     */
    public function getFilteredVersions($filters = []) {
        $where = [];
        $params = [];
        
        // Content type filter
        if (!empty($filters['content_type'])) {
            $where[] = "content_type = ?";
            $params[] = $filters['content_type'];
        }
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(content LIKE ? OR content_type LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        // Build WHERE clause
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Sorting
        $sort = in_array($filters['sort'] ?? '', ['id', 'content_type', 'user_id', 'created_at'])
            ? $filters['sort']
            : 'created_at';
        $order = strtolower($filters['order'] ?? '') === 'asc' ? 'ASC' : 'DESC';
        
        // Pagination
        $limit = "LIMIT " . (int)($filters['limit'] ?? 20);
        $offset = !empty($filters['offset']) ? "OFFSET " . (int)$filters['offset'] : '';
        
        $query = "SELECT v.*, u.username
                 FROM content_versions v
                 LEFT JOIN users u ON v.user_id = u.id
                 $whereClause
                 ORDER BY $sort $order
                 $limit $offset";
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Get count of filtered versions for pagination
     */
    public function getFilteredVersionsCount($filters = []) {
        $where = [];
        $params = [];
        
        // Content type filter
        if (!empty($filters['content_type'])) {
            $where[] = "content_type = ?";
            $params[] = $filters['content_type'];
        }
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(content LIKE ? OR content_type LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $query = "SELECT COUNT(*) as count
                 FROM content_versions
                 $whereClause";
        
        $result = $this->db->query($query, $params);
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Get distinct content types for filter dropdown
     */
    public function getContentTypes() {
        $query = "SELECT DISTINCT content_type
                 FROM content_versions
                 ORDER BY content_type";
        $results = $this->db->query($query);
        
        return array_column($results, 'content_type');
    }
}
