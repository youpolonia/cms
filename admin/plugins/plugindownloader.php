<?php
/**
 * FTP-safe Plugin Download Handler
 */

require_once __DIR__ . '/../../core/csrf.php';

csrf_boot('admin');
class PluginDownloader {
    /**
     * Download plugin package
     * @param string $pluginId Plugin identifier
     * @param string $downloadUrl Remote package URL
     * @param string $targetDir Installation directory
     * @return array Result with status and message
     */
    public static function download(string $pluginId, string $downloadUrl, string $targetDir): array {
        try {
            // Validate target directory
            if (!is_dir($targetDir)) {
                throw new Exception("Target directory does not exist");
            }

            // Create temporary file
            require_once __DIR__ . '/../../core/tmp_sandbox.php';
            $tempFile = tempnam(cms_tmp_dir(), 'plugin_');
            if ($tempFile === false) {
                throw new Exception("Could not create temporary file");
            }

            // Initialize download
            $fp = fopen($tempFile, 'w+');
            $ch = curl_init($downloadUrl);
            
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $downloadSize, $downloaded) {
                if ($downloadSize > 0) {
                    $progress = round(($downloaded / $downloadSize) * 100);
                    // Could log progress to database or session
                }
            });

            // Execute download
            if (!curl_exec($ch)) {
                throw new Exception("Download failed: " . curl_error($ch));
            }

            // Verify download
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                throw new Exception("Invalid HTTP response: {$httpCode}");
            }

            // Close resources
            curl_close($ch);
            fclose($fp);

            // Verify file integrity (placeholder - would verify checksum)
            if (filesize($tempFile) === 0) {
                throw new Exception("Downloaded file is empty");
            }

            return [
                'status' => true,
                'message' => 'Download completed',
                'temp_file' => $tempFile
            ];
        } catch (Exception $e) {
            // Clean up on error
            if (isset($fp)) fclose($fp);
            if (isset($ch)) curl_close($ch);
            if (isset($tempFile) && file_exists($tempFile)) unlink($tempFile);

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Install downloaded plugin
     * @param string $tempFile Downloaded package
     * @param string $targetDir Installation directory
     * @return bool True if successful
     */
    public static function install(string $tempFile, string $targetDir): bool {
        try {
            // Extract package (simplified - would use ZipArchive)
            // ...

            // Clean up
            unlink($tempFile);
            return true;
        } catch (Exception $e) {
            error_log("Plugin installation failed: {$e->getMessage()}");
            return false;
        }
    }
}
