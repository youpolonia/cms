<?php
/**
 * Framework-free Configuration Service
 * Implements secure configuration management without Laravel dependencies
 */
class ConfigurationService {
    private static $config = [];
    private static $instance = null;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
            self::loadConfig();
        }
        return self::$instance;
    }

    /**
     * Load configuration from file
     */
    private static function loadConfig() {
        $configPath = __DIR__ . '/../config/app.php';
        if (file_exists($configPath)) {
            self::$config = require_once $configPath;
        }
    }

    /**
     * Get configuration value by key
     */
    public static function get($key, $default = null) {
        return self::$config[$key] ?? $default;
    }

    /**
     * Set configuration value (runtime only)
     */
    public static function set($key, $value) {
        self::$config[$key] = $value;
    }

    /**
     * Check if config key exists
     */
    public static function has($key) {
        return isset(self::$config[$key]);
    }
}
