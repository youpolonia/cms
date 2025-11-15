<?php
/**
 * Database Configuration - Staging Environment
 * Framework-free PHP 8.1+ implementation
 */

class DatabaseConfig {
    public static function getConnection() {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'name' => $_ENV['DB_NAME'] ?? 'cms_staging',
            'user' => $_ENV['DB_USER'] ?? 'staging_user',
            'pass' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4'
        ];
    }
}
