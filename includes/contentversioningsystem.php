<?php
/**
 * ContentVersioningSystem - Framework-free content versioning
 * Manages version history for CMS content
 */
class ContentVersioningSystem {
    private static $dbConnection;
    private static $config = [
        'max_versions' => 100,
        'auto_prune' => true
    ];

    /**
     * Initialize service with database connection and configuration
     * @param object $dbConnection Database connection (must support prepared statements)
     * @param array $config Configuration options
     */
    public static function init($dbConnection, $config = []) {
        self::$dbConnection = $dbConnection;
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Create new version of content
     * @param int $contentId
     * @param string $contentType
     * @param array $contentData
     * @param string $author
     * @return int|bool Version ID or false on failure
     */
    public static function createVersion($contentId, $contentType, $contentData, $author) {
        if (!self::$dbConnection) {
            error_log('Database connection not initialized');
            return false;
        }

        $serializedData = json_encode($contentData);
        if ($serializedData === false) {
            error_log('Failed to serialize content data');
            return false;
        }

        $query = "INSERT INTO content_versions 
                 (content_id, content_type, content_data, author, created_at) 
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = self::$dbConnection->prepare($query);
        if (!$stmt) {
            error_log('Prepare failed: ' . self::$dbConnection->error);
            return false;
        }
        
        $stmt->bind_param('isss', $contentId, $contentType, $serializedData, $author);
        if (!$stmt->execute()) {
            error_log('Execute failed: ' . $stmt->error);
            return false;
        }
        
        if (self::$config['auto_prune']) {
            self::pruneOldVersions($contentId, $contentType);
        }
        
        return $stmt->insert_id;
    }

    /**
     * Get specific version of content
     * @param int $versionId
     * @return array|bool Version data or false if not found
     */
    public static function getVersion($versionId) {
        if (!self::$dbConnection) {
            error_log('Database connection not initialized');
            return false;
        }

        $query = "SELECT * FROM content_versions WHERE id = ?";
        $stmt = self::$dbConnection->prepare($query);
        if (!$stmt) {
            error_log('Prepare failed: ' . self::$dbConnection->error);
            return false;
        }
        
        $stmt->bind_param('i', $versionId);
        if (!$stmt->execute()) {
            error_log('Execute failed: ' . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return false;
        }

        $version = $result->fetch_assoc();
        $version['content_data'] = json_decode($version['content_data'], true);
        return $version;
    }

    /**
     * List all versions for content
     * @param int $contentId
     * @param string $contentType
     * @return array List of versions
     */
    public static function listVersions($contentId, $contentType) {
        if (!self::$dbConnection) {
            error_log('Database connection not initialized');
            return [];
        }

        $query = "SELECT id, author, created_at 
                 FROM content_versions 
                 WHERE content_id = ? AND content_type = ?
                 ORDER BY created_at DESC
                 LIMIT ?";
        
        $limit = self::$config['max_versions'];
        $stmt = self::$dbConnection->prepare($query);
        if (!$stmt) {
            error_log('Prepare failed: ' . self::$dbConnection->error);
            return [];
        }
        
        $stmt->bind_param('isi', $contentId, $contentType, $limit);
        if (!$stmt->execute()) {
            error_log('Execute failed: ' . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Restore content to specific version
     * @param int $versionId
     * @return bool True if restored successfully
     */
    public static function restoreVersion($versionId) {
        $version = self::getVersion($versionId);
        if (!$version) {
            return false;
        }

        $query = "UPDATE contents 
                 SET content_data = ?
                 WHERE id = ?";
        
        $stmt = self::$dbConnection->prepare($query);
        if (!$stmt) {
            error_log('Prepare failed: ' . self::$dbConnection->error);
            return false;
        }
        
        $contentData = json_encode($version['content_data']);
        $stmt->bind_param('si', $contentData, $version['content_id']);
        return $stmt->execute();
    }

    /**
     * Prune old versions beyond the configured limit
     * @param int $contentId
     * @param string $contentType
     */
    private static function pruneOldVersions($contentId, $contentType) {
        $query = "DELETE FROM content_versions 
                 WHERE content_id = ? AND content_type = ?
                 AND id NOT IN (
                     SELECT id FROM (
                         SELECT id FROM content_versions
                         WHERE content_id = ? AND content_type = ?
                         ORDER BY created_at DESC
                         LIMIT ?
                     ) AS keepers
                 )";
        
        $stmt = self::$dbConnection->prepare($query);
        if (!$stmt) {
            error_log('Prune prepare failed: ' . self::$dbConnection->error);
            return;
        }
        
        $limit = self::$config['max_versions'];
        $stmt->bind_param('isisi', $contentId, $contentType, $contentId, $contentType, $limit);
        if (!$stmt->execute()) {
            error_log('Prune execute failed: ' . $stmt->error);
        }
    }
}
