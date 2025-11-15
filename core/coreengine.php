<?php
/**
 * Core Engine Implementation
 * Framework-free PHP with static methods
 */
class CoreEngine {
    /**
     * Initialize core engine
     */
    public static function init() {
        self::verifyEnvironment();
        self::setupTenantIsolation();
    }

    /**
     * Verify system requirements
     */
    private static function verifyEnvironment() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            throw new Exception('PHP 8.1 or higher required');
        }

        // Check database connection
        if (!self::testDatabaseConnection()) {
            throw new Exception('Database connection failed');
        }
    }

    /**
     * Test database connection
     */
    private static function testDatabaseConnection() {
        try {
            $conn = \core\Database::connection();
            return true;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Setup tenant isolation
     */
    private static function setupTenantIsolation() {
        require_once __DIR__ . '/tenantmanager.php';
        TenantManager::init();
    }

    /**
     * Run migrations
     */
    public static function runMigrations() {
        require_once __DIR__ . '/migrationrunner.php';
        MigrationRunner::executeAll();
    }
}
