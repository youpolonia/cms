<?php
/**
 * Tenant Manager - Handles multi-tenant operations
 * 
 * @package CMS
 * @subpackage Tenant
 */

class TenantManager {
    /**
     * Get current tenant from request headers
     * @return array Tenant data
     */
    public static function getCurrentTenant(): array {
        $headers = getallheaders();
        $tenantId = $headers['X-Tenant-ID'] ?? null;
        
        if (!$tenantId) {
            throw new Exception('Tenant ID header missing');
        }

        return self::validateTenant($tenantId);
    }

    /**
     * Validate tenant ID against database
     * @param string $tenantId
     * @return array Tenant data
     * @throws Exception If invalid tenant
     */
    public static function validateTenant(string $tenantId): array {
        require_once __DIR__ . '/../../core/database.php';
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare("SELECT * FROM tenants WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tenant) {
            throw new Exception('Invalid tenant ID');
        }

        return $tenant;
    }

    /**
     * Get tenant-specific configuration
     * @param string $tenantId
     * @return array Merged configuration
     */
    public static function getTenantConfig(string $tenantId): array {
        $globalConfig = require_once __DIR__ . '/../../config/global.php';
        $tenantConfigFile = __DIR__ . "/../../tenants/{$tenantId}/config.php";
        
        $tenantConfig = file_exists($tenantConfigFile) 
            ? require_once $tenantConfigFile 
            : [];

        return array_merge($globalConfig, $tenantConfig);
    }
}
