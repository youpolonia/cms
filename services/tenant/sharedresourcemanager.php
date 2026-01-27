<?php
declare(strict_types=1);

class SharedResourceManager {
    private static array $resources = [];

    public static function getResource(string $tenantId, string $resourceType): mixed {
        self::validateTenantAccess($tenantId);
        
        if (!isset(self::$resources[$tenantId][$resourceType])) {
            self::$resources[$tenantId][$resourceType] = self::loadResource($tenantId, $resourceType);
        }

        return self::$resources[$tenantId][$resourceType];
    }

    private static function validateTenantAccess(string $tenantId): void {
        if (!preg_match('/^[a-z0-9\-]{36}$/', $tenantId)) {
            throw new InvalidArgumentException('Invalid tenant identifier');
        }
        // BREAKPOINT: Add tenant access validation logic
    }

    private static function loadResource(string $tenantId, string $resourceType): mixed {
        $resourcePath = "storage/tenants/{$tenantId}/{$resourceType}.json";
        if (!file_exists($resourcePath)) {
            throw new RuntimeException("Resource {$resourceType} not found for tenant {$tenantId}");
        }

        $data = file_get_contents($resourcePath);
        return json_decode($data, true);
    }

    public static function clearCache(string $tenantId): void {
        unset(self::$resources[$tenantId]);
    }
}
