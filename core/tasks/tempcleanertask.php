<?php

/**
 * Temp Files Cleaner Task
 * 
 * Removes temporary files older than 7 days from the temp directory.
 * Part of the CMS maintenance system for managing temporary file accumulation.
 */
class TempCleanerTask
{
    /**
     * Execute temp files cleanup
     * 
     * Removes files older than 7 days (604800 seconds) from /var/www/html/cms/temp
     * 
     * @return bool True on successful execution, false if directory missing/unreadable
     */
    public static function run(): bool
    {
        $temp_dir = '/var/www/html/cms/temp';
        
        $removed_count = 0;
        $success = true;
        
        // Check if directory exists and is readable
        if (!is_dir($temp_dir) || !is_readable($temp_dir)) {
            $success = false;
        } else {
            $seven_days_ago = time() - 604800; // 7 days in seconds
            
            $files = @glob($temp_dir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $file_time = @filemtime($file);
                        if ($file_time !== false && $file_time < $seven_days_ago) {
                            if (@unlink($file)) {
                                $removed_count++;
                            }
                        }
                    }
                }
            }
        }
        
        // Log execution result
        $timestamp = date('Y-m-d H:i:s');
        $status = $success ? 'ok' : 'failed';
        $log_line = "[{$timestamp}] TempCleanerTask executed {$status} (removed={$removed_count})";
        error_log($log_line . PHP_EOL, 3, 'logs/migrations.log');
        
        return $success;
    }
}
