<?php
/**
 * Content Versioning Migration (Archive)
 * 
 * Creates content_versions table and adds state column to content table
 * This is an archived version of the original migration
 */

// Include database connection
require_once __DIR__ . '/../../core/database.php';

class Migration_0006_content_versioning {
    /**
     * Apply the migration
     * 
     * @return bool Success status
     */
    public static function migrate() {
        $db = \core\Database::connection();
        
        try {
            // Create content_versions table
            $db->query("CREATE TABLE IF NOT EXISTS content_versions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_id INT NOT NULL,
                version INT NOT NULL,
                data JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (content_id) REFERENCES content(id)
            )");

            // Add state column to content table
            $db->query("ALTER TABLE content ADD COLUMN state ENUM('draft','review','published') DEFAULT 'draft'");

            return true;
        } catch (Exception $e) {
            error_log("Migration failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revert the migration
     * 
     * @return bool Success status
     */
    public static function revert() {
        $db = \core\Database::connection();
        
        try {
            $db->query("DROP TABLE IF EXISTS content_versions");
            $db->query("ALTER TABLE content DROP COLUMN state");
            return true;
        } catch (Exception $e) {
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test the migration
     * 
     * @return array Test results
     */
    public static function test() {
        try {
            // Apply migration
            $migrateResult = self::migrate();
            
            // Test content_versions table exists
            $db = \core\Database::connection();
            $tableCheck = $db->query("SHOW TABLES LIKE 'content_versions'")->num_rows > 0;
            
            // Test content table has state column
            $columnCheck = $db->query("SHOW COLUMNS FROM content LIKE 'state'")->num_rows > 0;
            
            // Clean up test
            self::revert();
            
            return [
                'success' => $migrateResult && $tableCheck && $columnCheck,
                'details' => [
                    'migration_applied' => $migrateResult,
                    'table_created' => $tableCheck,
                    'column_added' => $columnCheck
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Web-accessible test endpoint
if (isset($_GET['test_migration'])) {
    header('Content-Type: application/json');
    echo json_encode(Migration_0006_content_versioning::test());
    exit;
}
