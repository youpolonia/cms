<?php
require_once __DIR__.'/../core/database.php';

class VersionMetadata {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    /**
     * Get metadata for a specific version
     */
    public function getByVersionId($versionId) {
        $query = "SELECT * FROM version_metadata WHERE version_id = ?";
        $result = $this->db->query($query, [$versionId]);
        
        if (empty($result)) {
            return null;
        }
        
        return $result[0];
    }

    /**
     * Get all metadata for a content item's versions
     */
    public function getForContent($contentId) {
        $query = "SELECT vm.* 
                 FROM version_metadata vm
                 JOIN content_versions cv ON vm.version_id = cv.id
                 WHERE cv.content_id = ?
                 ORDER BY cv.created_at DESC";
        return $this->db->query($query, [$contentId]);
    }

    /**
     * Create new version metadata
     */
    public function create($versionId, $userId, $metadata = []) {
        $fields = ['version_id', 'user_id', 'created_at'];
        $placeholders = ['?', '?', 'NOW()'];
        $params = [$versionId, $userId];
        
        // Add custom metadata fields
        foreach ($metadata as $key => $value) {
            $fields[] = $key;
            $placeholders[] = '?';
            $params[] = $value;
        }
        
        $query = "INSERT INTO version_metadata (" . implode(', ', $fields) . ")
                 VALUES (" . implode(', ', $placeholders) . ")";
        
        return $this->db->query($query, $params);
    }

    /**
     * Update version metadata
     */
    public function update($versionId, $metadata) {
        $updates = [];
        $params = [];
        
        foreach ($metadata as $key => $value) {
            $updates[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $versionId;
        
        $query = "UPDATE version_metadata 
                 SET " . implode(', ', $updates) . "
                 WHERE version_id = ?";
        
        return $this->db->query($query, $params);
    }

    /**
     * Get change history for a version
     */
    public function getChangeHistory($versionId) {
        $query = "SELECT * FROM version_change_history 
                 WHERE version_id = ?
                 ORDER BY changed_at DESC";
        return $this->db->query($query, [$versionId]);
    }

    /**
     * Log a change to version metadata
     */
    public function logChange($versionId, $userId, $field, $oldValue, $newValue) {
        $query = "INSERT INTO version_change_history 
                 (version_id, user_id, field, old_value, new_value, changed_at)
                 VALUES (?, ?, ?, ?, ?, NOW())";
        
        return $this->db->query($query, [
            $versionId, $userId, $field, $oldValue, $newValue
        ]);
    }
}
