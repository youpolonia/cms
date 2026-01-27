<?php
declare(strict_types=1);

class TenantIdentification {
    private const HEADER_NAME = 'X-Tenant-Context';
    private const ERROR_HEADER_MISSING = 'TENANT_HEADER_MISSING';
    private const ERROR_INVALID_TENANT = 'INVALID_TENANT';
    private const ERROR_PERMISSION_DENIED = 'PERMISSION_DENIED';

    public function handle(array $request): array {
        if (empty($request['headers'][self::HEADER_NAME])) {
            throw new ApiException(
                'Tenant identification header required',
                400,
                self::ERROR_HEADER_MISSING
            );
        }

        $tenantId = $this->validateTenant($request['headers'][self::HEADER_NAME]);
        $this->verifyTenantPermissions($tenantId, $request);
        $request['tenant_id'] = $tenantId;

        return $request;
    }

    private function validateTenant(string $tenantId): string {
        $tenant = TenantRepository::getById($tenantId);
        
        if (!$tenant || !$tenant->isActive()) {
            throw new ApiException(
                'Invalid or inactive tenant',
                403,
                self::ERROR_INVALID_TENANT
            );
        }

        $this->logTenantAccess($tenantId);
        return $tenantId;
    }

    private function verifyTenantPermissions(string $tenantId, array $request): void {
        $endpoint = $request['path'] ?? '';
        $method = $request['method'] ?? 'GET';
        
        if (!PermissionService::checkAccess($tenantId, $endpoint, $method)) {
            throw new ApiException(
                'Permission denied for this resource',
                403,
                self::ERROR_PERMISSION_DENIED
            );
        }
    }

    private function logTenantAccess(string $tenantId): void {
        $analyticsData = [
            'tenant_id' => $tenantId,
            'timestamp' => time(),
            'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        try {
            // First try database logging
            AnalyticsRepository::logAccess($analyticsData);
        } catch (\Exception $dbException) {
            try {
                // Fallback to file logging if database fails
                $logPath = __DIR__ . '/../../storage/logs/analytics_fallback.log';
                file_put_contents(
                    $logPath,
                    json_encode($analyticsData) . PHP_EOL,
                    FILE_APPEND
                );
            } catch (\Exception $fileException) {
                // Final fallback to error log if all else fails
                error_log('Failed to log analytics: ' . $fileException->getMessage());
            }
        }
    }
}
