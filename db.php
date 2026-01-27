<?php
/**
 * Database connection handler using centralized core Database
 * Secure, FTP-deployable implementation for PHP 8.1+
 */

require_once __DIR__ . '/core/database.php';

class DatabaseConnection {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = \core\Database::connection();
        }
        return self::$instance;
    }
}

// Helper function for easy access (check if not already defined)
if (!function_exists('db')) {
    function db(): PDO {
        return \core\Database::connection();
    }
}
