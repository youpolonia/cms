<?php

class TenantManager {
    private const LOG_FILE = 'logs/tenant_manager.log';

    /**
     * Register a new tenant with full isolation
     * @param string $name Tenant name
     * @param string $email Tenant admin email
     * @param array $quotas Array of quota values
     * @return string Tenant ID
     * @throws Exception If registration fails
     */
    public static function registerTenant(string $name, string $email, array $quotas = []): string {
        try {
            self::log("Starting tenant registration for: $email");
            
            // Insert tenant record
            $tenantId = DB::insert("
                INSERT INTO tenants (name, email, cpu_quota, memory_quota, storage_quota, request_quota)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [
                $name,
                $email,
                $quotas['cpu'] ?? 100,
                $quotas['memory'] ?? 1024,
                $quotas['storage'] ?? 10000,
                $quotas['requests'] ?? 100000
            ]);

            // Initialize tenant-specific tables
            self::initializeTenantTables($tenantId);
            
            self::log("Successfully registered tenant ID: $tenantId");
            return $tenantId;
        } catch (PDOException $e) {
            $error = "Tenant registration failed for $email: " . $e->getMessage();
            self::log($error, 'ERROR');
            throw new Exception($error);
        }
    }

    /**
     * Initialize all tenant-specific tables
     */
    private static function initializeTenantTables(string $tenantId): void {
        try {
            // Initialize usage tracking
            DB::insert("
                INSERT INTO tenant_usage (tenant_id, resource_type, usage)
                VALUES (?, 'cpu', 0), (?, 'memory', 0), (?, 'storage', 0), (?, 'requests', 0)
            ", [$tenantId, $tenantId, $tenantId, $tenantId]);

            // Initialize tenant settings
            DB::insert("
                INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
                VALUES (?, 'is_active', '1'), (?, 'created_at', NOW())
            ", [$tenantId, $tenantId]);
        } catch (PDOException $e) {
            $error = "Failed to initialize tables for tenant $tenantId: " . $e->getMessage();
            self::log($error, 'ERROR');
            throw new Exception($error);
        }
    }

    /**
     * Validate tenant credentials and scope
     */
    public static function validateTenant(string $tenantId): bool {
        try {
            $result = DB::select("
                SELECT id FROM tenants 
                WHERE id = ? AND is_active = 1
            ", [$tenantId]);
            return !empty($result);
        } catch (PDOException $e) {
            self::log("Tenant validation failed for $tenantId: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Get tenant details with usage stats
     */
    public static function getTenant(string $tenantId): array {
        try {
            $tenant = DB::select("
                SELECT t.*, 
                       tu_cpu.usage as cpu_usage,
                       tu_mem.usage as memory_usage,
                       tu_stor.usage as storage_usage,
                       tu_req.usage as request_usage
                FROM tenants t
                LEFT JOIN tenant_usage tu_cpu ON t.id = tu_cpu.tenant_id AND tu_cpu.resource_type = 'cpu'
                LEFT JOIN tenant_usage tu_mem ON t.id = tu_mem.tenant_id AND tu_mem.resource_type = 'memory'
                LEFT JOIN tenant_usage tu_stor ON t.id = tu_stor.tenant_id AND tu_stor.resource_type = 'storage'
                LEFT JOIN tenant_usage tu_req ON t.id = tu_req.tenant_id AND tu_req.resource_type = 'requests'
                WHERE t.id = ?
            ", [$tenantId]);
            
            if (!$tenant) {
                throw new Exception("Tenant not found");
            }
            
            return $tenant[0];
        } catch (PDOException $e) {
            $error = "Failed to get tenant $tenantId: " . $e->getMessage();
            self::log($error, 'ERROR');
            throw new Exception($error);
        }
    }

    /**
     * Check if tenant has available quota
     */
    public static function checkQuota(string $tenantId, string $resourceType, float $amount): bool {
        try {
            return TenantQuotaService::checkQuota($tenantId, $resourceType, $amount);
        } catch (Exception $e) {
            self::log("Quota check failed for tenant $tenantId: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Scope a query to a specific tenant
     */
    public static function scopeQuery(string $tenantId, string $table): string {
        try {
            if (!self::validateTenant($tenantId)) {
                throw new Exception("Invalid tenant scope");
            }

            // Check if table supports tenant isolation
            $tenantTables = ['content_blocks', 'content_pages', 'content_templates'];
            if (!in_array($table, $tenantTables)) {
                throw new Exception("Table does not support tenant isolation");
            }

            return "SELECT * FROM {$table} WHERE tenant_id = '{$tenantId}'";
        } catch (Exception $e) {
            self::log("Query scoping failed: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }

    /**
     * Get all active tenants
     */
    public static function getActiveTenants(): array {
        try {
            return DB::select("
                SELECT id, name, email 
                FROM tenants 
                WHERE is_active = 1
            ");
        } catch (PDOException $e) {
            self::log("Failed to get active tenants: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }

    /**
     * Deactivate a tenant (soft delete)
     */
    public static function deactivateTenant(string $tenantId): bool {
        try {
            $result = DB::update("
                UPDATE tenants 
                SET is_active = 0 
                WHERE id = ?
            ", [$tenantId]);
            
            if ($result) {
                self::log("Deactivated tenant: $tenantId");
            }
            return $result;
        } catch (PDOException $e) {
            self::log("Failed to deactivate tenant $tenantId: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Log messages to tenant manager log file
     */
    private static function log(string $message, string $level = 'INFO'): void {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents(self::LOG_FILE, $logEntry, FILE_APPEND);
    }
}
