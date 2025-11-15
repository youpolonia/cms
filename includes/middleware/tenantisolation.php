<?php
declare(strict_types=1);

class TenantIsolation {
    public static function handle(array $request): array {
        // Verify tenant context exists
        if (!isset($request['headers']['X-Tenant-ID'])) {
            throw new RuntimeException('Tenant context required');
        }

        $tenantId = self::sanitizeTenantId($request['headers']['X-Tenant-ID']);
        
        // Set tenant context for downstream processing
        $request['tenant'] = [
            'id' => $tenantId,
            'resources' => self::loadTenantResources($tenantId)
        ];

        return $request;
    }

    private static function sanitizeTenantId(string $id): string {
        if (!preg_match('/^[a-z0-9\-]{36}$/', $id)) {
            throw new InvalidArgumentException('Invalid tenant identifier');
        }
        return $id;
    }

    private static function loadTenantResources(string $tenantId): array {
        $resources = [];
        // Load tenant-specific resources from storage
        // BREAKPOINT: Continue resource loading implementation
        return $resources;
    }
}
