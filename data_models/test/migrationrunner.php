<?php

namespace Database\Test;

class MigrationRunner {
    private static $pdo;
    private static $config;

    public static function init() {
        self::$config = []; // legacy alt DB config removed
        self::connect();
    }

    private static function connect() {
        try {
            self::$pdo = \core\Database::connection();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Test database connection failed: " . $e->getMessage());
        }
    }

    public static function runMigration($migrationFile) {
        require_once $migrationFile;
        
        try {
            // Apply migration
            apply_migration();
            
            // Verify rollback
            rollback_migration();
            
            // Re-apply to leave clean state
            apply_migration();
            
            return true;
        } catch (\Exception $e) {
            error_log("Migration test failed: " . $e->getMessage());
            return false;
        }
    }

    public static function testEdgeCases() {
        // Test empty table
        self::$pdo->exec("CREATE TABLE IF NOT EXISTS test_empty (id INT)");
        self::$pdo->exec("DROP TABLE test_empty");
        
        // Test large data
        self::$pdo->exec("CREATE TABLE IF NOT EXISTS test_large (data LONGTEXT)");
        $largeData = str_repeat('a', 1000000);
        $stmt = self::$pdo->prepare("INSERT INTO test_large (data) VALUES (?)");
        $stmt->execute([$largeData]);
        self::$pdo->exec("DROP TABLE test_large");
    }
}
