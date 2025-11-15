<?php
declare(strict_types=1);

namespace Includes\Core;

use Exception;

class ConfigurationService
{
    private static array $cache = [];
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::loadCoreConfig();
        self::$initialized = true;
    }

    private static function loadCoreConfig(): void
    {
        try {
            $globalConfig = ConfigLoader::get('global');
            $tenantConfig = TenantManager::getTenantConfig();
            
            // Validate config structure before merging
            if (!self::validateConfig($globalConfig)) {
                throw new Exception("Invalid global configuration structure");
            }
            
            // Merge with tenant-specific overrides
            self::$cache = self::deepMergeConfigs($globalConfig, $tenantConfig);
            
            // Apply RBAC validation
            self::validateRBACConfig();
            
            // Cache the merged config
            self::cacheConfig();
        } catch (Exception $e) {
            error_log("ConfigurationService init failed: " . $e->getMessage());
            throw $e;
        }
    }

    private static function deepMergeConfigs(array $base, array $overrides): array
    {
        $result = $base;
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($result[$key])) {
                $result[$key] = self::deepMergeConfigs($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private static function validateConfig(array $config): bool
    {
        // Basic validation rules
        $requiredKeys = ['db', 'security', 'features'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                return false;
            }
        }
        return true;
    }

    private static function validateRBACConfig(): void
    {
        $roles = Auth::getAllRoles();
        foreach ($roles as $role) {
            if (!isset(self::$cache['rbac'][$role])) {
                throw new Exception("Missing RBAC configuration for role: $role");
            }
        }
    }

    private static function cacheConfig(): void
    {
        if (function_exists('apcu_store')) {
            apcu_store('cms_config', self::$cache, 3600);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        return self::$cache[$key] ?? $default;
    }

    public static function getForRole(string $key, string $role): mixed
    {
        self::init();
        $roleConfig = Auth::getRoleConfig($role);
        return $roleConfig[$key] ?? self::get($key);
    }

    public static function refresh(): void
    {
        self::$cache = [];
        self::$initialized = false;
        self::init();
    }

    public static function getAll(): array
    {
        self::init();
        return self::$cache;
    }

    public static function set(string $key, mixed $value): void
    {
        self::init();
        self::$cache[$key] = $value;
    }
}
