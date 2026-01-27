<?php
declare(strict_types=1);

class ArchiveService {
    private const MAX_ARCHIVED_VERSIONS = 100;
    private const COMPRESSION_LEVEL = 6;
    
    public static function archiveContentVersion(int $contentId, int $versionId): array {
        try {
            $db = \core\Database::connection();
            
            // Verify version exists and is not already archived
            $stmt = $db->prepare("
                SELECT status FROM content_versions 
                WHERE id = ? AND content_id = ?
            ");
            $stmt->execute([$versionId, $contentId]);
            $version = $stmt->fetch();
            
            if (!$version) {
                return ['success' => false, 'error' => 'Version not found'];
            }
            
            if ($version['status'] === 'archived') {
                return ['success' => false, 'error' => 'Version already archived'];
            }
            
            // Archive the version
            $stmt = $db->prepare("
                UPDATE content_versions 
                SET status = 'archived' 
                WHERE id = ? AND content_id = ?
            ");
            $stmt->execute([$versionId, $contentId]);
            
            // Apply retention policy
            self::applyRetentionPolicy($contentId);
            
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Archive failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private static function applyRetentionPolicy(int $contentId): void {
        $db = \core\Database::connection();
        
        // Get count of archived versions
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM content_versions 
            WHERE content_id = ? AND status = 'archived'
        ");
        $stmt->execute([$contentId]);
        $count = (int)$stmt->fetchColumn();
        
        // Delete oldest if over limit
        if ($count > self::MAX_ARCHIVED_VERSIONS) {
            $stmt = $db->prepare("
                DELETE FROM content_versions 
                WHERE id IN (
                    SELECT id FROM content_versions 
                    WHERE content_id = ? AND status = 'archived' 
                    ORDER BY created_at ASC 
                    LIMIT ?
                )
            ");
            $stmt->execute([$contentId, $count - self::MAX_ARCHIVED_VERSIONS]);
        }
    }
    
    public static function compressArchivedData(int $versionId): array {
        try {
            $db = \core\Database::connection();
            
            // Get version data
            $stmt = $db->prepare("
                SELECT version_data FROM content_versions 
                WHERE id = ? AND status = 'archived'
            ");
            $stmt->execute([$versionId]);
            $data = $stmt->fetchColumn();
            
            if (!$data) {
                return ['success' => false, 'error' => 'Version not found or not archived'];
            }
            
            // Compress the data
            $compressed = gzcompress($data, self::COMPRESSION_LEVEL);
            
            // Update with compressed data
            $stmt = $db->prepare("
                UPDATE content_versions 
                SET version_data = ? 
                WHERE id = ?
            ");
            $stmt->execute([$compressed, $versionId]);
            
            return ['success' => true, 'compressed_size' => strlen($compressed)];
        } catch (Exception $e) {
            error_log("Compression failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
