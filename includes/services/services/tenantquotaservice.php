<?php
declare(strict_types=1);

/**
 * Tenant Quota Management Service
 * Handles quota tracking and enforcement
 */
class TenantQuotaService {
    public static function checkQuota(string $tenantId, string $resourceType, float $amount): bool {
        $usage = self::getCurrentUsage($tenantId, $resourceType);
        $quota = self::getQuotaLimit($tenantId, $resourceType);
        
        return ($usage + $amount) <= $quota;
    }

    public static function recordUsage(string $tenantId, string $resourceType, float $amount): void {
        DB::query("
            UPDATE tenant_usage 
            SET usage = usage + ? 
            WHERE tenant_id = ? AND resource_type = ?
        ", [$amount, $tenantId, $resourceType]);
    }

    public static function getCurrentUsage(string $tenantId, string $resourceType): float {
        $result = DB::queryOne("
            SELECT usage FROM tenant_usage 
            WHERE tenant_id = ? AND resource_type = ?
        ", [$tenantId, $resourceType]);
        
        return (float)($result['usage'] ?? 0);
    }

    public static function getQuotaLimit(string $tenantId, string $resourceType): float {
        $tenant = DB::queryOne("SELECT * FROM tenants WHERE id = ?", [$tenantId]);
        return match($resourceType) {
            'cpu' => (float)$tenant['cpu_quota'],
            'memory' => (float)$tenant['memory_quota'],
            'storage' => (float)$tenant['storage_quota'],
            'requests' => (float)$tenant['request_quota'],
            default => 0
        };
    }

    public static function resetUsage(string $tenantId): void {
        DB::query("UPDATE tenant_usage SET usage = 0 WHERE tenant_id = ?", [$tenantId]);
    }
}
