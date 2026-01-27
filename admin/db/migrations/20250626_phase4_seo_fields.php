<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Phase 4 SEO Fields Migration
 * Adds SEO meta fields to content_entries table
 */
class Migration_20250626_Phase4_SEO_Fields {
    
    /**
     * Execute the migration
     */
    public static function execute(): void {
        $db = self::getDatabaseConnection();
        
        try {
            $db->beginTransaction();
            
            $sql = "ALTER TABLE content_entries 
                    ADD COLUMN meta_title VARCHAR(255) NULL AFTER content,
                    ADD COLUMN meta_description TEXT NULL AFTER meta_title,
                    ADD COLUMN canonical_url VARCHAR(255) NULL AFTER meta_description";
                    
            $db->exec($sql);
            $db->commit();
            
            self::logSuccess('SEO fields added to content_entries table');
        } catch (PDOException $e) {
            $db->rollBack();
            error_log('Database error');
            http_response_code(500);
            self::logError('Database error');
            exit;
        }
    }
    
    /**
     * Get database connection
     */
    private static function getDatabaseConnection(): PDO {
        require_once __DIR__ . '/../../../core/database.php';
        return \core\Database::connection();
    }
    
    /**
     * Log success message
     */
    private static function logSuccess(string $message): void {
        file_put_contents(
            __DIR__ . '/../../memory-bank/migration_logs.log',
            date('Y-m-d H:i:s') . ' - SUCCESS: ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * Log error message
     */
    private static function logError(string $message): void {
        file_put_contents(
            __DIR__ . '/../../memory-bank/migration_logs.log',
            date('Y-m-d H:i:s') . ' - ERROR: ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
}
