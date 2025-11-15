<?php

/**
 * Backup Task
 * 
 * Creates backup archives of config and memory-bank directories.
 * Part of the CMS maintenance system for creating system backups.
 */
class BackupTask
{
    /**
     * Execute backup creation
     * 
     * Creates a timestamped zip archive containing config and memory-bank files
     * 
     * @return bool True on successful backup creation, false on failure
     */
    public static function run(): bool
    {
        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            return false;
        }
        
        $backup_dir = '/var/www/html/cms/backups';
        $config_dir = '/var/www/html/cms/config';
        $memory_dir = '/var/www/html/cms/memory-bank';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($backup_dir)) {
            if (!@mkdir($backup_dir, 0755, true)) {
                return false;
            }
        }
        
        // Generate timestamped filename
        $timestamp = date('Ymd_His');
        $backup_file = $backup_dir . '/backup_' . $timestamp . '.zip';
        
        // Create zip archive
        $zip = new ZipArchive();
        if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
            return false;
        }
        
        // Add config files (non-recursive)
        if (is_dir($config_dir)) {
            $config_files = @glob($config_dir . '/*');
            if ($config_files !== false) {
                foreach ($config_files as $file) {
                    if (is_file($file)) {
                        $relative_name = 'config/' . basename($file);
                        $zip->addFile($file, $relative_name);
                    }
                }
            }
        }
        
        // Add memory-bank files (non-recursive)
        if (is_dir($memory_dir)) {
            $memory_files = @glob($memory_dir . '/*');
            if ($memory_files !== false) {
                foreach ($memory_files as $file) {
                    if (is_file($file)) {
                        $relative_name = 'memory-bank/' . basename($file);
                        $zip->addFile($file, $relative_name);
                    }
                }
            }
        }
        
        // Close and save the archive
        $result = $zip->close();
        
        // Log the backup execution
        $timestamp_log = date('Y-m-d H:i:s');
        if ($result) {
            $backup_filename = basename($backup_file);
            $log_line = "[{$timestamp_log}] BackupTask executed ok (file={$backup_filename})";
        } else {
            $log_line = "[{$timestamp_log}] BackupTask executed failed";
        }
        error_log($log_line . PHP_EOL, 3, dirname(__DIR__, 2) . '/logs/backup_manager.log');
        
        return $result;
    }
}
