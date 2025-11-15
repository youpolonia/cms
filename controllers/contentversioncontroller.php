<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/database.php';

class ContentVersionController {
    protected $db;
    public function __construct() { $this->db = \core\Database::connection(); }

    public function listVersions(int $contentId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, version, created_at, created_by 
                FROM content_versions 
                WHERE content_id = :content_id
                ORDER BY version DESC
            ");
            $stmt->execute([':content_id' => $contentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listing versions: " . $e->getMessage());
            throw new Exception("Failed to retrieve versions");
        }
    }

    public function getVersionContent(int $contentId, int $version): array {
        try {
            $stmt = $this->db->prepare("
                SELECT content_data as data, meta_data as meta
                FROM content_versions
                WHERE content_id = :content_id AND version = :version
            ");
            $stmt->execute([
                ':content_id' => $contentId,
                ':version' => $version
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                throw new Exception("Version not found");
            }

            return [
                'data' => $result['data'],
                'meta' => json_decode($result['meta'], true) ?: []
            ];
        } catch (PDOException $e) {
            error_log("Error getting version content: " . $e->getMessage());
            throw new Exception("Failed to retrieve version content");
        }
    }

    public function rollbackToVersion(int $contentId, int $version): bool {
        try {
            $this->db->beginTransaction();

            // Get the version to rollback to
            $versionData = $this->getVersionContent($contentId, $version);

            // Create new version from the old content
            $stmt = $this->db->prepare("
                INSERT INTO content_versions 
                (content_id, version, content_data, meta_data, created_by)
                SELECT 
                    :content_id, 
                    MAX(version) + 1, 
                    :content_data, 
                    :meta_data,
                    :user_id
                FROM content_versions
                WHERE content_id = :content_id
            ");
            
            $result = $stmt->execute([
                ':content_id' => $contentId,
                ':content_data' => $versionData['data'],
                ':meta_data' => json_encode($versionData['meta']),
                ':user_id' => $_SESSION['user_id'] ?? 0
            ]);

            if (!$result) {
                throw new Exception("Failed to create rollback version");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error rolling back version: " . $e->getMessage());
            throw new Exception("Failed to rollback version");
        }
    }
}
