<?php
namespace Core;

class ConfigLoader {
    private static $cache = [];
    private static $cacheTime = 3600; // 1 hour cache
    private const CONFIG_PATH = __DIR__ . '/../../config';
    
    /**
     * Load configuration with tenant overrides
     * 
     * @param string $configName Configuration file name (without extension)
     * @param string|null $tenantId Tenant identifier
     * @return array Merged configuration
     */
    public static function load(string $configName, ?string $tenantId = null): array {
        $cacheKey = $configName . ($tenantId ? '_' . $tenantId : '');
        
        // Check cache first
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Load base config
        $baseConfig = self::loadConfigFile(self::CONFIG_PATH . "/{$configName}.php");
        
        // Load tenant overrides if specified
        $tenantConfig = [];
        if ($tenantId && file_exists(self::CONFIG_PATH . "/tenants/{$tenantId}/{$configName}.php")) {
            $tenantConfig = self::loadConfigFile(self::CONFIG_PATH . "/tenants/{$tenantId}/{$configName}.php");
        }

        // Merge configurations (tenant overrides base)
        $merged = array_replace_recursive($baseConfig, $tenantConfig);
        
        // Cache the result
        self::$cache[$cacheKey] = $merged;
        
        return $merged;
    }

    /**
     * Load configuration file safely
     * @param string $path Path to config file
     * @return array Configuration array
     */
    private static function loadConfigFile(string $path): array {
        if (!file_exists($path)) {
            return [];
        }
        
        $config = require_once $path;
        return is_array($config) ? $config : [];
    }

    /**
     * Clear configuration cache
     * @return void
     */
    public static function clearCache(): void {
        self::$cache = [];
    }
}
