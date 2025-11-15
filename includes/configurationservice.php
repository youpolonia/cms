<?php
/**
 * Configuration Service - Handles system configuration management
 *
 * Features:
 * - Framework-free implementation
 * - PSR-4 compliant
 * - Error handling and logging
 * - Configuration caching
 * - Singleton pattern
 */
class ConfigurationService {
    private static $instance = null;
    private static $configCache = [];
    private static $initialized = false;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Get singleton instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize configuration service
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        try {
            // Load base configuration
            self::loadConfigFile('config/base.php');
            self::$initialized = true;
        } catch (Exception $e) {
            Logger::error("ConfigurationService initialization failed: " . $e->getMessage());
            throw new RuntimeException("Configuration initialization failed");
        }
    }

    /**
     * Get configuration value
     */
    public static function get(string $key, $default = null) {
        self::ensureInitialized();
        
        return self::$configCache[$key] ?? $default;
    }

    /**
     * Set configuration value (runtime only)
     */
    public static function set(string $key, $value): void {
        self::ensureInitialized();
        self::$configCache[$key] = $value;
    }

    /**
     * Get all configuration values
     */
    public static function getAll(): array {
        self::ensureInitialized();
        return self::$configCache;
    }

    /**
     * Check if a configuration key exists
     */
    public static function has(string $key): bool {
        self::ensureInitialized();
        return isset(self::$configCache[$key]);
    }

    /**
     * Remove a configuration key
     */
    public static function remove(string $key): void {
        self::ensureInitialized();
        unset(self::$configCache[$key]);
    }

    /**
     * Load configuration from file
     */
    private static function loadConfigFile(string $path): void {
        if (!file_exists($path)) {
            throw new RuntimeException("Config file not found: $path");
        }

        $config = require_once $path;
        if (is_array($config)) {
            self::$configCache = array_merge(self::$configCache, $config);
        } else {
            throw new RuntimeException("Config file does not return an array: $path");
        }
    }

    /**
     * Ensure the service has been initialized
     */
    private static function ensureInitialized(): void {
        if (!self::$initialized) {
            throw new RuntimeException("ConfigurationService has not been initialized");
        }
    }
}
