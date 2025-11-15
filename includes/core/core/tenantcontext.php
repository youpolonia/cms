<?php
declare(strict_types=1);

/**
 * Tenant Context Management
 * Stores and manages current tenant context
 */
class TenantContext {
    private static ?array $currentTenant = null;

    public static function set(array $tenantData): void {
        self::$currentTenant = $tenantData;
    }

    public static function get(): ?array {
        return self::$currentTenant;
    }

    public static function getId(): ?string {
        return self::$currentTenant['id'] ?? null;
    }

    public static function getResources(): array {
        return self::$currentTenant['resources'] ?? [];
    }

    public static function reset(): void {
        self::$currentTenant = null;
    }

    public static function trackResourceUsage(string $type, float $amount): void {
        if (!isset(self::$currentTenant['usage'][$type])) {
            self::$currentTenant['usage'][$type] = 0;
        }
        self::$currentTenant['usage'][$type] += $amount;
    }
}
