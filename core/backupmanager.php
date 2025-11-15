<?php
/**
 * BackupManager - Handles system backups and exports
 * 
 * @package CMS
 * @subpackage Core
 */

namespace CMS\Core;

/**
 * Class BackupManager
 * 
 * Provides functionality for creating system backups, exporting settings/content,
 * and generating downloadable ZIP archives.
 */
class BackupManager
{
    /**
     * @var string Backup directory path
     */
    private string $backupDir;

    /**
     * @var array System settings to be included in backups
     */
    private array $settings;

    /**
     * BackupManager constructor
     * 
     * @param string $backupDir Path to store backup files
     * @param array $settings System settings to require_once in backups
     */
    public function __construct(string $backupDir, array $settings = [])
    {
        $this->backupDir = rtrim($backupDir, '/') . '/';
        $this->settings = $settings;
    }

    /**
     * Export system settings as JSON file
     *
     * @return string|false Path to saved JSON file or false on failure
     */
    public function exportSettings(): string|false
    {
        try {
            // Get config settings from system files (if they exist)
            $systemSettings = [];
            
            // Check config.php
            if (file_exists(__DIR__ . '/../../config.php')) {
                $config = require_once __DIR__ . '/../../config.php';
                if (is_array($config)) {
                    $systemSettings = array_merge($systemSettings, $config);
                }
            }

            // Check bootstrap.php but don't require_once in backups
            if (file_exists(__DIR__ . '/../../bootstrap.php')) {
                // File exists but we explicitly exclude it from backups
                error_log("BackupManager: bootstrap.php detected but excluded from backup");
            }

            // Merge settings (instance settings take precedence)
            $allSettings = array_merge($systemSettings, $this->settings);

            // Filter sensitive data
            $filteredSettings = array_filter($allSettings, function($value, $key) {
                $sensitiveKeys = ['db_', 'password', 'secret', 'key', 'token'];
                foreach ($sensitiveKeys as $sensitive) {
                    if (str_contains(strtolower($key), $sensitive)) {
                        return false;
                    }
                }
                return true;
            }, ARRAY_FILTER_USE_BOTH);

            // Create backups directory if needed
            if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0755, true)) {
                return false;
            }

            // Generate filename with timestamp
            $timestamp = date('Ymd_His');
            $filename = $this->backupDir . "settings_{$timestamp}.json";

            // Save JSON to file
            $json = json_encode($filteredSettings, JSON_PRETTY_PRINT);
            if ($json === false || file_put_contents($filename, $json) === false) {
                return false;
            }

            return $filename;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Export content and content types as JSON
     *
     * @return string|false Path to saved JSON file or false on failure
     */
    public function exportContent(): string|false
    {
        try {
            // Create backups directory if needed
            if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0755, true)) {
                return false;
            }

            // Generate filename with timestamp (consistent with exportSettings)
            $timestamp = date('Ymd_His');
            $filename = $this->backupDir . "content_{$timestamp}.json";

            // Get content items in batches
            $contentItems = [];
            $batchSize = 100;
            $offset = 0;
            
            do {
                $batch = $this->getContentBatch($offset, $batchSize);
                $contentItems = array_merge($contentItems, $this->filterContentFields($batch));
                $offset += $batchSize;
            } while (!empty($batch));

            // Get content types
            $contentTypes = $this->getContentTypes();
            $filteredTypes = $this->filterContentFields($contentTypes);

            // Prepare export data
            $exportData = [
                'content_items' => $contentItems,
                'content_types' => $filteredTypes,
                'exported_at' => date('c'),
                'version' => 1
            ];

            // Save JSON to file
            $json = json_encode($exportData, JSON_PRETTY_PRINT);
            if ($json === false || file_put_contents($filename, $json) === false) {
                return false;
            }

            return $filename;
        } catch (\Exception $e) {
            error_log("BackupManager exportContent failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a batch of content items
     *
     * @param int $offset Query offset
     * @param int $limit Number of items to fetch
     * @return array Batch of content items
     */
    private function getContentBatch(int $offset, int $limit): array
    {
        try {
            // Get ContentManager instance (assuming it's available via DI or static access)
            $contentManager = $this->getContentManager();
            
            // Query content in batches using LIMIT/OFFSET
            $query = "SELECT * FROM contents ORDER BY id ASC LIMIT ? OFFSET ?";
            $results = $contentManager->getDb()->fetchAll($query, [$limit, $offset]);
            
            return $results ?: [];
        } catch (\Exception $e) {
            error_log("BackupManager getContentBatch failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all content types
     *
     * @return array Content type definitions
     */
    private function getContentTypes(): array
    {
        try {
            // Get ContentTypeManager instance (assuming it's available)
            $typeManager = $this->getContentTypeManager();
            return $typeManager->getContentTypes();
        } catch (\Exception $e) {
            error_log("BackupManager getContentTypes failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ContentManager instance
     *
     * @return ContentManager
     */
    private function getContentManager(): ContentManager
    {
        // Implementation depends on how the CMS handles dependencies
        // This could be via DI container, static access, or other means
        return ContentManager::getInstance(); // Example static access
    }

    /**
     * Get ContentTypeManager instance
     *
     * @return ContentTypeManager
     */
    private function getContentTypeManager(): ContentTypeManager
    {
        // Implementation depends on how the CMS handles dependencies
        return ContentTypeManager::getInstance(); // Example static access
    }

    /**
     * Filter sensitive fields from content/type data
     *
     * @param array $data Content data to filter
     * @return array Filtered content data
     */
    private function filterContentFields(array $data): array
    {
        $sensitiveFields = ['password', 'secret', 'token', 'api_key', 'private'];
        
        return array_map(function($item) use ($sensitiveFields) {
            foreach ($sensitiveFields as $field) {
                if (isset($item[$field])) {
                    unset($item[$field]);
                }
            }
            return $item;
        }, $data);
    }

    /**
     * Create ZIP archive from selected files
     *
     * @param array $files List of files to require_once in ZIP
     * @return string|false Path to created ZIP file or false on failure
     */
    public function createZip(array $files): string|false
    {
        try {
            // Validate files exist
            foreach ($files as $file) {
                if (!file_exists($file)) {
                    error_log("BackupManager: File not found - " . $file);
                    return false;
                }
            }

            // Create backups directory if needed
            if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0755, true)) {
                error_log("BackupManager: Failed to create backup directory");
                return false;
            }

            // Generate timestamped filename
            $timestamp = date('Ymd_His');
            $zipPath = $this->backupDir . "backup_{$timestamp}.zip";

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                error_log("BackupManager: Failed to create ZIP archive");
                return false;
            }

            // Add files to ZIP
            foreach ($files as $file) {
                $localName = basename($file);
                if (!$zip->addFile($file, $localName)) {
                    error_log("BackupManager: Failed to add file to ZIP: " . $file);
                    $zip->close();
                    @unlink($zipPath); // Cleanup failed ZIP
                    return false;
                }
            }

            if (!$zip->close()) {
                error_log("BackupManager: Failed to finalize ZIP archive");
                @unlink($zipPath); // Cleanup failed ZIP
                return false;
            }

            return $zipPath;
        } catch (\Exception $e) {
            error_log("BackupManager createZip error: " . $e->getMessage());
            if (isset($zipPath) && file_exists($zipPath)) {
                @unlink($zipPath); // Cleanup on exception
            }
            return false;
        }
    }

    /**
     * Generate timestamped backup file combining settings and content
     *
     * @return string|false Path to created zip file or false on failure
     */
    public function generateTimestampedBackup(): string|false
    {
        try {
            // Generate exports
            $settingsFile = $this->exportSettings();
            $contentFile = $this->exportContent();
            
            if ($settingsFile === false || $contentFile === false) {
                error_log("BackupManager: Failed to generate export files");
                return false;
            }

            // Create zip archive
            $zipPath = $this->createZip([$settingsFile, $contentFile]);
            if ($zipPath === false) {
                error_log("BackupManager: Failed to create zip archive");
                return false;
            }

            // Clean up temporary files
            @unlink($settingsFile);
            @unlink($contentFile);

            return $zipPath;
        } catch (\Exception $e) {
            error_log("BackupManager generateTimestampedBackup error: " . $e->getMessage());
            
            // Clean up any created files on error
            if (isset($settingsFile) && file_exists($settingsFile)) {
                @unlink($settingsFile);
            }
            if (isset($contentFile) && file_exists($contentFile)) {
                @unlink($contentFile);
            }
            if (isset($zipPath) && file_exists($zipPath)) {
                @unlink($zipPath);
            }
            
            return false;
        }
    }
}
