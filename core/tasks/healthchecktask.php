<?php

/**
 * Health Check Task
 * 
 * Performs system health checks including directory existence,
 * writability, and disk space availability.
 */
class HealthCheckTask
{
    /**
     * Execute health check operations
     * 
     * @return bool True if all checks pass, false otherwise
     */
    public static function run(): bool
    {
        $project_root = __DIR__ . '/../../';
        $directories = ['logs', 'temp', 'sessions', 'backups', 'search_index'];
        
        // Check directory existence
        $dirs_exist = 0;
        foreach ($directories as $dir) {
            if (is_dir($project_root . $dir)) {
                $dirs_exist++;
            }
        }
        
        // Check writability
        $dirs_writable = 0;
        foreach ($directories as $dir) {
            $path = $project_root . $dir;
            if (is_dir($path) && is_writable($path)) {
                $dirs_writable++;
            }
        }
        
        // Check free space (100MB threshold)
        $free_space = disk_free_space($project_root);
        $free_ok = $free_space !== false && $free_space >= (100 * 1024 * 1024);
        
        // Determine overall status
        $total_dirs = count($directories);
        $all_ok = ($dirs_exist === $total_dirs) && ($dirs_writable === $total_dirs) && $free_ok;
        
        // Build summary
        $dirs_summary = "{$dirs_exist}/{$total_dirs}";
        $writable_summary = "{$dirs_writable}/{$total_dirs}";
        $free_summary = $free_ok ? "1/1" : "0/1";
        
        // Log the result
        $timestamp = date('Y-m-d H:i:s');
        $status = $all_ok ? 'ok' : 'failed';
        $log_line = "[{$timestamp}] HealthCheckTask executed {$status} (dirs={$dirs_summary}, writable={$writable_summary}, free={$free_summary})";
        error_log($log_line . PHP_EOL, 3, __DIR__ . '/../../logs/migrations.log');
        
        return $all_ok;
    }
}
