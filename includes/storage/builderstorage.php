<?php
/**
 * Page Builder Storage Handler
 * 
 * Handles storage and retrieval of page builder content
 */
namespace CMS\Storage;

class BuilderStorage {
    /**
     * @var string Storage directory path
     */
    private $storagePath;

    /**
     * @param string $storagePath Path to storage directory
     */
    public function __construct(string $storagePath) {
        $this->storagePath = rtrim($storagePath, '/') . '/';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Save page builder content
     * @param string $pageId Page identifier
     * @param array $content Page content data
     * @param bool $isAutosave Whether this is an autosave
     * @return string Version ID
     */
    public function save(string $pageId, array $content, bool $isAutosave = false): string {
        $versionId = uniqid('v_');
        $filename = $this->getFilename($pageId, $versionId, $isAutosave);
        
        $compressed = gzencode(json_encode([
            'content' => $content,
            'created_at' => time(),
            'is_autosave' => $isAutosave
        ]), 9);

        file_put_contents($filename, $compressed);
        return $versionId;
    }

    /**
     * Load page builder content
     * @param string $pageId Page identifier
     * @param string $versionId Version identifier
     * @return array|null
     */
    public function load(string $pageId, string $versionId): ?array {
        $filename = $this->getFilename($pageId, $versionId);
        if (!file_exists($filename)) {
            return null;
        }

        $compressed = file_get_contents($filename);
        $data = json_decode(gzdecode($compressed), true);
        return $data['content'] ?? null;
    }

    /**
     * Get all versions for a page
     * @param string $pageId Page identifier
     * @return array
     */
    public function getVersions(string $pageId): array {
        $pattern = $this->storagePath . "page_{$pageId}_v_*.json";
        $files = glob($pattern);
        $versions = [];

        foreach ($files as $file) {
            $compressed = file_get_contents($file);
            $data = json_decode(gzdecode($compressed), true);
            $versionId = substr(basename($file), strlen("page_{$pageId}_"), -5);

            $versions[] = [
                'id' => $versionId,
                'created_at' => $data['created_at'] ?? 0,
                'is_autosave' => $data['is_autosave'] ?? false
            ];
        }

        // Sort by creation date (newest first)
        usort($versions, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $versions;
    }

    /**
     * Generate filename for storage
     * @param string $pageId
     * @param string $versionId
     * @param bool $isAutosave
     * @return string
     */
    private function getFilename(string $pageId, string $versionId, bool $isAutosave = false): string {
        $prefix = $isAutosave ? 'autosave_' : '';
        return $this->storagePath . "page_{$pageId}_{$prefix}{$versionId}.json";
    }

    /**
     * Clean up old autosave files (older than 7 days)
     * @return int Number of files deleted
     */
    public function cleanupOldAutosaves(): int {
        $deleted = 0;
        $files = glob($this->storagePath . "page_*_autosave_*.json");
        $now = time();
        $sevenDaysAgo = $now - (7 * 24 * 60 * 60);

        foreach ($files as $file) {
            try {
                $compressed = file_get_contents($file);
                $data = json_decode(gzdecode($compressed), true);
                
                if (isset($data['created_at']) && $data['created_at'] < $sevenDaysAgo) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue with other files
                error_log("Failed to process autosave file {$file}: " . $e->getMessage());
            }
        }

        return $deleted;
    }

    /**
     * Clean up old versions (non-autosave) keeping minimum versions per page
     * @param int $daysOld Delete versions older than this many days (default: 30)
     * @param int $keepMin Minimum versions to keep per page (default: 5)
     * @return int Number of files deleted
     */
    public function cleanupOldVersions(int $daysOld = 30, int $keepMin = 5): int {
        $deleted = 0;
        $now = time();
        $cutoffTime = $now - ($daysOld * 24 * 60 * 60);
        
        // Group files by page ID
        $filesByPage = [];
        $allFiles = glob($this->storagePath . "page_*_v_*.json");
        
        foreach ($allFiles as $file) {
            if (strpos($file, '_autosave_') !== false) {
                continue; // Skip autosaves
            }
            
            $pageId = $this->extractPageIdFromFilename($file);
            if ($pageId) {
                $filesByPage[$pageId][] = $file;
            }
        }

        foreach ($filesByPage as $pageId => $files) {
            // Sort files by creation date (newest first)
            usort($files, function($a, $b) {
                return filemtime($b) <=> filemtime($a);
            });

            $kept = 0;
            foreach ($files as $file) {
                try {
                    $compressed = file_get_contents($file);
                    $data = json_decode(gzdecode($compressed), true);
                    
                    if (isset($data['created_at'])) {
                        if ($kept < $keepMin) {
                            $kept++;
                            continue;
                        }
                        
                        if ($data['created_at'] < $cutoffTime) {
                            if (unlink($file)) {
                                $deleted++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    error_log("Failed to process version file {$file}: " . $e->getMessage());
                }
            }
        }

        return $deleted;
    }

    /**
     * Extract page ID from filename
     * @param string $filename
     * @return string|null
     */
    private function extractPageIdFromFilename(string $filename): ?string {
        $pattern = '/page_(.*?)_(?:autosave_)?v_.*\.json$/';
        if (preg_match($pattern, basename($filename), $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Run scheduled cleanup tasks (for cron)
     * @return array Stats about cleanup operations
     */
    public static function runScheduledCleanup(): array {
        $storage = new self(STORAGE_PATH); // STORAGE_PATH should be defined in config
        return [
            'autosaves_deleted' => $storage->cleanupOldAutosaves(),
            'versions_deleted' => $storage->cleanupOldVersions()
        ];
    }
}
