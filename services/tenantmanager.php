<?php
declare(strict_types=1);

/**
 * Tenant Manager Service
 * Handles tenant identification, validation and configuration
 */
class TenantManager {
    private static ?PDO $db = null;
    private static array $configCache = [];
    
    /**
     * Initialize with database connection
     */
    public static function init(PDO $db): void {
        self::$db = $db;
    }
    
    /**
     * Get current tenant from request headers
     */
    public static function getCurrentTenant(): ?array {
        if (!isset($_SERVER['HTTP_X_TENANT_ID'])) {
            return null;
        }
        
        $tenantId = (int)$_SERVER['HTTP_X_TENANT_ID'];
        return self::validateTenant($tenantId);
    }
    
    /**
     * Validate tenant ID exists in database
     */
    public static function validateTenant(int $tenantId): ?array {
        if (self::$db === null) {
            throw new RuntimeException('Database connection not initialized');
        }
        
        $stmt = self::$db->prepare('SELECT * FROM tenants WHERE id = ?');
        $stmt->execute([$tenantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get merged configuration for tenant
     */
    public static function getTenantConfig(int $tenantId): array {
        if (isset(self::$configCache[$tenantId])) {
            return self::$configCache[$tenantId];
        }
        
        $tenant = self::validateTenant($tenantId);
        if (!$tenant) {
            throw new InvalidArgumentException('Invalid tenant ID');
        }
        
        // Load configurations
        $globalConfig = require __DIR__ . '/../config/global.php';
        $tenantConfig = json_decode($tenant['config'] ?? '[]', true);
        $siteConfig = [];
        
        if (file_exists(__DIR__ . "/../config/tenants/{$tenantId}.php")) {
            $siteConfig = require __DIR__ . "/../config/tenants/{$tenantId}.php";
        }
        
        // Merge with tenant config having highest priority
        self::$configCache[$tenantId] = array_merge(
            $globalConfig,
            $tenantConfig,
            $siteConfig
        );
        
        return self::$configCache[$tenantId];
    }
    
    /**
     * Clear configuration cache
     */
    public static function clearCache(): void {
        self::$configCache = [];
    }
}
