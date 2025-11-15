<?php

/**
 * Search Index Task
 * 
 * Creates and maintains search index files for content discovery.
 * Part of the CMS maintenance system for search functionality.
 */
class SearchIndexTask
{
    /**
     * Execute search index rebuilding operations
     * 
     * Creates search index directory and metadata file
     * 
     * @return bool True on successful index creation, false on failure
     */
    public static function run(): bool
    {
        $index_dir = '/var/www/html/cms/search_index';
        $meta_file = $index_dir . '/index.meta';
        $written = 0;
        
        // Ensure directory exists
        if (!is_dir($index_dir)) {
            if (!@mkdir($index_dir, 0755, false)) {
                // Log failure
                $timestamp = date('Y-m-d H:i:s');
                $log_line = "[{$timestamp}] SearchIndexTask executed failed (written=0)";
                error_log($log_line . PHP_EOL, 3, __DIR__ . '/../../logs/migrations.log');
                return false;
            }
        }
        
        // Create/refresh metadata file
        $metadata = json_encode(['rebuilt_at' => date('Y-m-d H:i:s')]);
        if (@file_put_contents($meta_file, $metadata) !== false) {
            $written = 1;
        }
        
        // Log the result
        $timestamp = date('Y-m-d H:i:s');
        if ($written > 0) {
            $log_line = "[{$timestamp}] SearchIndexTask executed ok (written={$written})";
            error_log($log_line . PHP_EOL, 3, __DIR__ . '/../../logs/migrations.log');
            return true;
        } else {
            $log_line = "[{$timestamp}] SearchIndexTask executed failed (written=0)";
            error_log($log_line . PHP_EOL, 3, __DIR__ . '/../../logs/migrations.log');
            return false;
        }
    }
}
