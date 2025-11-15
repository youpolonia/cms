<?php
require_once __DIR__ . '/database.php';

/**
 * Content Version Model - Handles version control operations
 */
class ContentVersion {
    /**
     * Create new version
     * @param int $contentId
     * @param string $contentData
     * @param string $changeNotes
     * @return array|false
     */
    public static function create($contentId, $contentData, $changeNotes = '') {
        $pdo = \core\Database::connection();
        $compressed = self::compressData($contentData);
        
        $stmt = $db->prepare("INSERT INTO content_versions 
            (content_id, version_data, change_notes, created_at) 
            VALUES (?, ?, ?, NOW())");
            
        if ($stmt->execute([$contentId, $compressed, $changeNotes])) {
            return [
                'id' => $db->lastInsertId(),
                'content_id' => $contentId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return false;
    }

    /**
     * Get specific version
     * @param int $versionId
     * @return array|false
     */
    public static function get($versionId) {
        $pdo = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM content_versions WHERE id = ?");
        $stmt->execute([$versionId]);
        
        if ($version = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $version['version_data'] = self::decompressData($version['version_data']);
            return $version;
        }
        return false;
    }

    /**
     * List versions for content
     * @param int $contentId
     * @param int $limit
     * @return array
     */
    public static function listForContent($contentId, $limit = 50) {
        $pdo = \core\Database::connection();
        $stmt = $db->prepare("SELECT id, content_id, change_notes, created_at 
            FROM content_versions 
            WHERE content_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?");
        $stmt->execute([$contentId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compress version data
     * @param string $data
     * @return string
     */
    private static function compressData($data) {
        if (function_exists('gzcompress')) {
            return gzcompress($data);
        }
        return $data;
    }

    /**
     * Decompress version data
     * @param string $data
     * @return string
     */
    private static function decompressData($data) {
        if (function_exists('gzuncompress')) {
            return gzuncompress($data);
        }
        return $data;
    }
}
