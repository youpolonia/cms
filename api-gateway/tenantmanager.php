<?php
/**
 * Tenant Manager for API Gateway
 * 
 * Responsibilities:
 * - Tenant identification from request headers
 * - Tenant validation against database
 * - Configuration management with inheritance
 */
class TenantManager {
    /**
     * Get current tenant from request headers
     * 
     * @param array $headers HTTP request headers
     * @return string|null Tenant ID or null if not found
     */
    public static function getCurrentTenant(array $headers): ?string {
        return $headers['X-Tenant-ID'] ?? null;
    }

    /**
     * Validate tenant exists in database
     * 
     * @param PDO $pdo Database connection
     * @param string $tenantId Tenant ID to validate
     * @return bool Validation result
     */
    public static function validateTenant(PDO $pdo, string $tenantId): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM tenants WHERE id = ?");
        $stmt->execute([$tenantId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get merged configuration for tenant
     * 
     * @param PDO $pdo Database connection
     * @param string $tenantId Tenant ID
     * @return array Merged configuration
     */
    public static function getTenantConfig(PDO $pdo, string $tenantId): array {
        // Get global config
        $globalConfig = [];
        if (file_exists(__DIR__ . '/../config/global.php')) {
            $globalConfig = require __DIR__ . '/../config/global.php';
            if (!is_array($globalConfig)) {
                $globalConfig = [];
            }
        }
        
        // Get tenant-specific config
        $tenantConfig = [];
        try {
            $stmt = $pdo->prepare("SELECT config FROM tenant_configs WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
            $configJson = $stmt->fetchColumn();
            if ($configJson) {
                $tenantConfig = json_decode($configJson, true) ?? [];
            }
        } catch (PDOException $e) {
            error_log("Tenant config error: " . $e->getMessage());
        }
        
        return array_merge($globalConfig, $tenantConfig);
    }
}
