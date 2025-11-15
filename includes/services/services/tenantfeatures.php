<?php
namespace Includes\Services;

class TenantFeatures {
    private static $features = [
        'analytics_dashboard' => false,
        'advanced_editor' => false,
        'custom_themes' => false
    ];

    /**
     * Set features configuration for a tenant
     * @param string $tenantId
     * @param array $featuresConfig
     */
    public static function configure(string $tenantId, array $featuresConfig): void {
        foreach ($featuresConfig as $feature => $enabled) {
            if (array_key_exists($feature, self::$features)) {
                self::$features[$feature] = $enabled;
            }
        }
    }

    /**
     * Check if feature is enabled for tenant
     * @param string $tenantId
     * @param string $feature
     * @return bool
     */
    public static function isEnabled(string $tenantId, string $feature): bool {
        return self::$features[$feature] ?? false;
    }

    /**
     * Get all features configuration
     * @return array
     */
    public static function getAllFeatures(): array {
        return self::$features;
    }
}
