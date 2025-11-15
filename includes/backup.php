<?php
/**
 * CMS Backup Utility
 * Handles core file backups within shared hosting constraints
 */

class BackupManager {
    const BACKUP_DIR = '/var/www/html/cms/backups';
    const CORE_DIRS = ['config', 'includes', 'models', 'routes'];
    const CORE_FILES = [];

    public static function createBackup() {
        $backupDir = self::BACKUP_DIR . '/' . date('Y-m-d_H-i-s');
        if (!mkdir($backupDir, 0755, true)) {
            throw new Exception("Failed to create backup directory");
        }

        // Copy core directories
        foreach (self::CORE_DIRS as $dir) {
            self::copyDirectory("/var/www/html/cms/$dir", "$backupDir/$dir");
        }

        // Copy core files
        foreach (self::CORE_FILES as $file) {
            copy("/var/www/html/cms/$file", "$backupDir/$file");
        }

        return $backupDir;
    }

    private static function copyDirectory($source, $dest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target);
                }
            } else {
                // Skip executable files
                if (!self::isExecutable($item)) {
                    copy($item, $target);
                }
            }
        }
    }

    private static function isExecutable($filePath): bool {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $executableExts = ['php', 'sh', 'py', 'pl', 'exe', 'bat'];
        return in_array($ext, $executableExts) || is_executable($filePath);
    }
}

// Example usage:
// $backupPath = BackupManager::createBackup();
// echo "Backup created at: $backupPath";
