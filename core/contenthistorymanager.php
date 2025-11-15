<?php
/**
 * Content Versioning Manager
 * Handles saving, retrieving, and restoring content versions
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../core/auditlogger.php';

class ContentHistoryManager {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    /**
     * Save a new version of content
     * @param int $contentId - ID of content being versioned
     * @param int $authorId - ID of user creating version
     * @param array $dataArray - Content data to version
     * @return int|false - Version number or false on failure
     */
    public function saveVersion(int $contentId, int $authorId, array $dataArray) {
        // Get next version number
        $versionNumber = $this->getNextVersionNumber($contentId);
        
        try {
            require_once __DIR__ . '/notificationmanager.php';
            $stmt = $this->db->prepare(
                "INSERT INTO content_versions 
                (content_id, version_number, author_id, data_json) 
                VALUES (?, ?, ?, ?)"
            );
            
            $jsonData = json_encode($dataArray, JSON_UNESCAPED_UNICODE);
            
            $stmt->execute([
                $contentId,
                $versionNumber,
                $authorId,
                $jsonData
            ]);
            
            $notification = new NotificationManager();
            $notification->create(
                'content_version_saved',
                "New version {$versionNumber} saved for content ID {$contentId}",
                $authorId
            );

            return $versionNumber;
        } catch (PDOException $e) {
            error_log("Failed to save version: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all versions for content
     * @param int $contentId - ID of content
     * @return array - Array of version records
     */
    public function getVersions(int $contentId): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, version_number, author_id, created_at 
                FROM content_versions 
                WHERE content_id = ? 
                ORDER BY version_number DESC"
            );
            
            $stmt->execute([$contentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get versions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get specific version data
     * @param int $contentId - ID of content
     * @param int $versionNumber - Version number to retrieve
     * @return array|null - Version data or null if not found
     */
    public function getVersion(int $contentId, int $versionNumber): ?array {
        try {
            $stmt = $this->db->prepare(
                "SELECT data_json 
                FROM content_versions 
                WHERE content_id = ? AND version_number = ?"
            );
            
            $stmt->execute([$contentId, $versionNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? json_decode($result['data_json'], true) : null;
        } catch (PDOException $e) {
            error_log("Failed to get version: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Restore a specific version
     * @param int $contentId - ID of content to restore
     * @param int $versionNumber - Version number to restore
     * @return bool - True on success, false on failure
     */
    public function restoreVersion(int $contentId, int $versionNumber): bool {
        $versionData = $this->getVersion($contentId, $versionNumber);
        if (!$versionData) return false;

        require_once __DIR__ . '/notificationmanager.php';
        $notification = new NotificationManager();
        $notification->create(
            'content_version_restored',
            "Version {$versionNumber} restored for content ID {$contentId}",
            $_SESSION['user_id'] ?? 0
        );

        // Log the version restoration
        AuditLogger::log(
            $_SESSION['user_id'] ?? 0,
            'restore_version',
            'content',
            $contentId,
            "Restored version {$versionNumber}"
        );

        // Implementation depends on content storage system
        // This would need to be integrated with the main content storage
        // For now, just return the data to be handled by caller
        return $versionData;
    }

    /**
     * Get next version number for content
     * @param int $contentId - ID of content
     * @return int - Next version number
     */
    private function getNextVersionNumber(int $contentId): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT MAX(version_number) as max_version 
                FROM content_versions 
                WHERE content_id = ?"
            );
            
            $stmt->execute([$contentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['max_version'] ? $result['max_version'] + 1 : 1;
        } catch (PDOException $e) {
            error_log("Failed to get version number: " . $e->getMessage());
            return 1;
        }
    }
}
