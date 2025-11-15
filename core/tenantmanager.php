<?php
/**
 * Tenant Isolation Manager
 */
class TenantManager {
    private static $currentTenantId = null;
    private static $tenantConfigCache = [];

    /**
     * Get current tenant ID (read-only access)
     */
    public static function getTenantId(): ?string {
        return self::$currentTenantId;
    }

    /**
     * Get tenant config cache (read-only access)
     */
    public static function getConfigCache(): array {
        return self::$tenantConfigCache;
    }

    /**
     * Initialize tenant isolation
     */
    public static function init(PDO $pdo) {
        self::detectTenant();
        self::applyTenantScope($pdo);
    }

    /**
     * Detect tenant from request
     * @param array|null $headers Optional request headers
     */
    private static function detectTenant(array $headers = null) {
        $clientIp = self::getClientIp();

        // Check for tenant ID in headers first
        if ($headers && isset($headers['X-Tenant-ID'])) {
            self::$currentTenantId = $headers['X-Tenant-ID'];
            $_SESSION['tenant_id'] = self::$currentTenantId;
            $_SESSION['tenant_ip'] = $clientIp;
            return;
        }

        // Check for tenant ID in session with IP validation
        if (isset($_SESSION['tenant_id'])) {
            if (!isset($_SESSION['tenant_ip'])) {
                unset($_SESSION['tenant_id']);
                self::$currentTenantId = 1;
                return;
            }
            
            if ($_SESSION['tenant_ip'] !== $clientIp) {
                unset($_SESSION['tenant_id']);
                unset($_SESSION['tenant_ip']);
                self::$currentTenantId = 1;
                return;
            }

            self::$currentTenantId = $_SESSION['tenant_id'];
            return;
        }

        // Check for tenant ID in request
        if (isset($_REQUEST['tenant_id'])) {
            self::$currentTenantId = $_REQUEST['tenant_id'];
            $_SESSION['tenant_id'] = self::$currentTenantId;
            $_SESSION['tenant_ip'] = $clientIp;
            return;
        }

        // Default to primary tenant
        self::$currentTenantId = 1;
    }

    /**
     * Apply tenant scope to database queries
     */
    private static function applyTenantScope(PDO $pdo) {
        // Will be implemented in QueryBuilder
    }

    /**
     * Get current tenant ID
     * @param array|null $headers Optional request headers
     * @return string|int
     */
    public static function getCurrentTenant(array $headers = null) {
        if ($headers) {
            self::detectTenant($headers);
        }
        return self::$currentTenantId;
    }

    /**
     * Validate tenant exists and is active
     */
    public static function validateTenant(PDO $pdo, $tenantId): bool {
        // Validate tenant exists and is active
        $stmt = $pdo->prepare("SELECT 1 FROM tenants WHERE id = ? AND is_active = 1");
        $stmt->execute([$tenantId]);
        if (!$stmt->fetch()) {
            return false;
        }

        // Validate IP consistency if session exists
        if (isset($_SESSION['tenant_id']) && $_SESSION['tenant_id'] == $tenantId) {
            $clientIp = self::getClientIp();
            if (!isset($_SESSION['tenant_ip']) || $_SESSION['tenant_ip'] !== $clientIp) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get tenant configuration with inheritance
     * @return array Merged configuration (global + tenant overrides)
     */
    public static function getTenantConfig(PDO $pdo, $tenantId = null): array {
        $tenantId = $tenantId ?? self::$currentTenantId;

        if (isset(self::$tenantConfigCache[$tenantId])) {
            return self::$tenantConfigCache[$tenantId];
        }

        // Get global config
        $globalConfig = [];
        $stmt = $pdo->query("SELECT config_key, config_value FROM global_config");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $globalConfig[$row['config_key']] = $row['config_value'];
        }

        // Get tenant-specific overrides
        $tenantConfig = [];
        $stmt = $pdo->prepare("SELECT config_key, config_value FROM tenant_config WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tenantConfig[$row['config_key']] = $row['config_value'];
        }
    
        // Merge with tenant config overriding global
        $config = array_merge($globalConfig, $tenantConfig);
        self::$tenantConfigCache[$tenantId] = $config;

        return $config;
    }

    /**
     * Set tenant for current request
     */
    public static function setTenant($tenantId) {
        self::$currentTenantId = $tenantId;
        $_SESSION['tenant_id'] = $tenantId;
        $_SESSION['tenant_ip'] = self::getClientIp();
    }
    
    /**
     * Get client IP address
     * @return string Client IP address
     */
    public static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Clear tenant config cache
     */
    public static function clearCache($tenantId = null) {
        if ($tenantId) {
            unset(self::$tenantConfigCache[$tenantId]);
        } else {
            self::$tenantConfigCache = [];
        }
    }

}
