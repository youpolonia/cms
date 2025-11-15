<?php
/**
 * Tenant-specific AI configuration handler
 */
class TenantAIConfig {
    private static $tenantConfigs = [];
    private static $defaultConfig = [];

    /**
     * Set default configuration for all tenants
     */
    public static function setDefaults(array $config): void {
        self::$defaultConfig = $config;
    }

    /**
     * Get configuration for specific tenant
     */
    public static function getConfig(string $tenantId): array {
        if (!isset(self::$tenantConfigs[$tenantId])) {
            return self::$defaultConfig;
        }

        return array_merge(
            self::$defaultConfig,
            self::$tenantConfigs[$tenantId]
        );
    }

    /**
     * Update tenant-specific configuration
     */
    public static function updateConfig(
        string $tenantId, 
        array $config
    ): void {
        // Validate required fields
        $required = ['model_id', 'api_key'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        self::$tenantConfigs[$tenantId] = $config;
    }

    /**
     * Get all tenant configurations
     */
    public static function getAllConfigs(): array {
        return self::$tenantConfigs;
    }

    /**
     * Reset tenant configuration
     */
    public static function resetConfig(string $tenantId): void {
        unset(self::$tenantConfigs[$tenantId]);
    }
}
