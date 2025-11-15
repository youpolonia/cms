<?php
namespace Includes;

class TenantIsolationMiddleware {
    public static function handle(array $request): array {
        $tenantId = $request['headers']['X-Tenant-Context'] ?? null;
        
        if (!self::validateTenant($tenantId)) {
            return [
                'error' => [
                    'code' => 'TENANT_VIOLATION',
                    'message' => 'Invalid tenant context',
                    'tenant_id' => $tenantId,
                    'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
                ],
                'status' => 403
            ];
        }

        $request['tenant_id'] = $tenantId;
        return $request;
    }

    private static function validateTenant(?string $tenantId): bool {
        return $tenantId && preg_match('/^[a-f0-9]{32}$/', $tenantId) === 1;
    }
}
