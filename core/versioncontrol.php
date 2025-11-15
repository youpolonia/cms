<?php
/**
 * Version Control System for CMS
 * 
 * Provides basic versioning operations for content and files
 */
class VersionControl {
    const VERSION_DIR = 'cms_storage/versions/';
    
    /**
     * Create a new version
     * @param string $contentType Type of content being versioned (page, post, etc)
     * @param string $contentId Unique identifier for the content
     * @param mixed $contentData The content data to version
     * @param string $comment Version comment/description
     * @return string Version ID
     */
    public static function createVersion(string $contentType, string $contentId, $contentData, string $comment = ''): string {
        $versionId = uniqid('v_');
        $versionPath = self::getVersionPath($contentType, $contentId, $versionId);
        
        $versionData = [
            'created_at' => date('Y-m-d H:i:s'),
            'content_type' => $contentType,
            'content_id' => $contentId,
            'data' => $contentData,
            'comment' => $comment
        ];
        
        if (!file_exists(dirname($versionPath))) {
            mkdir(dirname($versionPath), 0755, true);
        }
        
        file_put_contents($versionPath, json_encode($versionData));
        self::updateHistory($contentType, $contentId, $versionId, 'create');
        return $versionId;
    }
    
    /**
     * Get version data
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @param string $versionId Version identifier
     * @return array|null Version data or null if not found
     */
    public static function getVersion(string $contentType, string $contentId, string $versionId): ?array {
        $versionPath = self::getVersionPath($contentType, $contentId, $versionId);
        if (!file_exists($versionPath)) {
            return null;
        }
        
        return json_decode(file_get_contents($versionPath), true);
    }
    
    /**
     * List all versions for content
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @return array List of version IDs and metadata
     */
    public static function listVersions(string $contentType, string $contentId): array {
        $contentDir = self::getContentDir($contentType, $contentId);
        if (!file_exists($contentDir)) {
            return [];
        }
        
        $versions = [];
        foreach (scandir($contentDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $versionPath = $contentDir . '/' . $file;
            $versionData = json_decode(file_get_contents($versionPath), true);
            if ($versionData) {
                $versions[] = [
                    'id' => str_replace('.json', '', $file),
                    'created_at' => $versionData['created_at'],
                    'comment' => $versionData['comment']
                ];
            }
        }
        
        return $versions;
    }
    
    /**
     * Delete a version
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @param string $versionId Version identifier
     * @return bool True if deleted, false if not found
     */
    public static function deleteVersion(string $contentType, string $contentId, string $versionId): bool {
        $versionPath = self::getVersionPath($contentType, $contentId, $versionId);
        if (!file_exists($versionPath)) {
            return false;
        }
        
        if (unlink($versionPath)) {
            self::updateHistory($contentType, $contentId, $versionId, 'delete');
            return true;
        }
        return false;
    }
    
    private static function getContentDir(string $contentType, string $contentId): string {
        return self::VERSION_DIR . $contentType . '/' . $contentId;
    }
    
    private static function getVersionPath(string $contentType, string $contentId, string $versionId): string {
        return self::getContentDir($contentType, $contentId) . '/' . $versionId . '.json';
    }

    /**
     * Compare two versions and return differences
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @param string $versionId1 First version ID
     * @param string $versionId2 Second version ID
     * @return array Array of differences
     */
    public static function diffVersions(string $contentType, string $contentId, string $versionId1, string $versionId2): array {
        $version1 = self::getVersion($contentType, $contentId, $versionId1);
        $version2 = self::getVersion($contentType, $contentId, $versionId2);

        if (!$version1 || !$version2) {
            throw new \InvalidArgumentException("One or both versions not found");
        }

        return self::arrayRecursiveDiff($version1['data'], $version2['data']);
    }

    /**
     * Create patch data between two versions
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @param string $fromVersionId Source version ID
     * @param string $toVersionId Target version ID
     * @return array Patch data
     */
    public static function createPatch(string $contentType, string $contentId, string $fromVersionId, string $toVersionId): array {
        $diff = self::diffVersions($contentType, $contentId, $fromVersionId, $toVersionId);
        return [
            'from_version' => $fromVersionId,
            'to_version' => $toVersionId,
            'created_at' => date('Y-m-d H:i:s'),
            'changes' => $diff
        ];
    }

    /**
     * Apply patch to content data
     * @param array $contentData Original content data
     * @param array $patch Patch data from createPatch()
     * @return array Patched content data
     */
    public static function applyPatch(array $contentData, array $patch): array {
        foreach ($patch['changes'] as $key => $value) {
            if ($value === null) {
                unset($contentData[$key]);
            } else {
                $contentData[$key] = $value;
            }
        }
        return $contentData;
    }

    /**
     * Recursively compute array differences
     * @param array $array1 First array
     * @param array $array2 Second array
     * @return array Differences
     */
    public static function arrayRecursiveDiff(array $array1, array $array2): array {
        $diff = [];
        
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value) && is_array($array2[$key])) {
                    $recursiveDiff = self::arrayRecursiveDiff($value, $array2[$key]);
                    if (!empty($recursiveDiff)) {
                        $diff[$key] = $recursiveDiff;
                    }
                } elseif ($value !== $array2[$key]) {
                    $diff[$key] = $array2[$key];
                }
            } else {
                $diff[$key] = null; // Key exists in array1 but not array2
            }
        }
        
        foreach ($array2 as $key => $value) {
            if (!array_key_exists($key, $array1)) {
                $diff[$key] = $value; // Key exists in array2 but not array1
            }
        }
        
        return $diff;
    }

    /**
     * Get complete version history metadata for content
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @return array Version history metadata
     */
    public static function getVersionHistory(string $contentType, string $contentId): array {
        $historyFile = self::getContentDir($contentType, $contentId) . '/_history.json';
        if (!file_exists($historyFile)) {
            return [];
        }
        return json_decode(file_get_contents($historyFile), true) ?: [];
    }

    /**
     * Purge versions older than specified days
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @param int $daysToKeep Number of days to keep versions
     * @return int Number of versions deleted
     */
    public static function purgeOldVersions(string $contentType, string $contentId, int $daysToKeep = 30): int {
        $cutoff = strtotime("-$daysToKeep days");
        $deleted = 0;
        $versions = self::listVersions($contentType, $contentId);
        
        foreach ($versions as $version) {
            if (strtotime($version['created_at']) < $cutoff) {
                if (self::deleteVersion($contentType, $contentId, $version['id'])) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }

    /**
     * Get storage statistics
     * @return array Storage usage statistics
     */
    public static function getStorageStats(): array {
        $totalSize = 0;
        $versionCount = 0;
        $contentTypes = [];
        
        if (!file_exists(self::VERSION_DIR)) {
            return [
                'total_size' => 0,
                'version_count' => 0,
                'content_types' => []
            ];
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::VERSION_DIR)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'json') {
                $totalSize += $file->getSize();
                $versionCount++;
                
                $pathParts = explode('/', $file->getPath());
                $contentType = $pathParts[count($pathParts) - 2];
                $contentTypes[$contentType] = ($contentTypes[$contentType] ?? 0) + 1;
            }
        }
        
        return [
            'total_size' => $totalSize,
            'version_count' => $versionCount,
            'content_types' => $contentTypes
        ];
    }

    /**
     * Update history metadata after version operations
     * @param string $contentType Type of content
     * @param string $contentId Content identifier
     * @param string $versionId Version ID
     * @param string $operation Operation performed (create/delete)
     */
    private static function updateHistory(string $contentType, string $contentId, string $versionId, string $operation): void {
        $historyFile = self::getContentDir($contentType, $contentId) . '/_history.json';
        $history = file_exists($historyFile) ? json_decode(file_get_contents($historyFile), true) : [];
        
        if ($operation === 'create') {
            $version = self::getVersion($contentType, $contentId, $versionId);
            $history[$versionId] = [
                'created_at' => $version['created_at'],
                'comment' => $version['comment'],
                'size' => filesize(self::getVersionPath($contentType, $contentId, $versionId))
            ];
        } elseif ($operation === 'delete') {
            unset($history[$versionId]);
        }
        
        file_put_contents($historyFile, json_encode($history));
    }
}
