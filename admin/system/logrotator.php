<?php
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

/**
 * LogRotator - Handles log file rotation based on size
 */
class LogRotator {
    const MAX_SIZE = 5242880; // 5MB in bytes
    const MAX_BACKUPS = 5;

    /**
     * Rotates log file if it exceeds max size
     * @param string $filePath Path to log file
     * @return bool True if rotation occurred, false otherwise
     */
    public static function rotateIfNeeded(string $filePath): bool {
        if (!file_exists($filePath)) {
            return false;
        }

        if (filesize($filePath) < self::MAX_SIZE) {
            return false;
        }

        return self::rotate($filePath);
    }

    /**
     * Performs log rotation
     * @param string $filePath Path to log file
     * @return bool True if rotation succeeded
     */
    private static function rotate(string $filePath): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        try {
            // Delete oldest backup if we have max backups
            $oldestBackup = $filePath . '.' . self::MAX_BACKUPS;
            if (file_exists($oldestBackup)) {
                unlink($oldestBackup);
            }

            // Shift existing backups
            for ($i = self::MAX_BACKUPS - 1; $i >= 1; $i--) {
                $current = $filePath . '.' . $i;
                $next = $filePath . '.' . ($i + 1);
                if (file_exists($current)) {
                    rename($current, $next);
                }
            }

            // Move current log to backup.1
            rename($filePath, $filePath . '.1');
            return true;
        } catch (Exception $e) {
            error_log("Log rotation failed: " . $e->getMessage());
            return false;
        }
    }
}
