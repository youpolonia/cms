<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Content State Migration
 * Adds state (draft/scheduled/published/archived) and publish timestamps to content_entries table
 */
class Migration_20250701_Content_State {
    
    /**
     * Execute the migration
     */
    public static function execute(): void {
        $db = self::getDatabaseConnection();
        
        try {
            $db->beginTransaction();
            
            $sql = "ALTER TABLE content_entries 
                    ADD COLUMN state ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft' AFTER canonical_url,
                    ADD COLUMN published_at DATETIME NULL AFTER state,
                    ADD COLUMN scheduled_for DATETIME NULL AFTER published_at";
                    
            $db->exec($sql);
            $db->commit();
            
            self::logSuccess('Content state fields added to content_entries table');
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
