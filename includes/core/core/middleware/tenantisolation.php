<?php
/**
 * Tenant Isolation Middleware
 * Validates and sets tenant context for all requests
 */
class TenantIsolation {
    /**
     * Validate and set tenant context
     * @param array $request The HTTP request data
     * @return array|null Returns error response if invalid, null if valid
     */
    public static function handle(array $request) {
        if (empty($request['headers']['X-Tenant-Context'])) {
            return [
                'error' => [
                    'code' => 'TENANT_REQUIRED',
                    'message' => 'X-Tenant-Context header is required'
                ],
                'status' => 403
            ];
        }

        $tenantId = $request['headers']['X-Tenant-Context'];
        if (!self::validateTenant($tenantId)) {
            return [
                'error' => [
                    'code' => 'TENANT_INVALID',
                    'message' => 'Invalid tenant context',
                    'tenant_id' => $tenantId
                ],
                'status' => 403
            ];
        }

        self::setCurrentTenant($tenantId);
        return null;
    }

    private static function validateTenant(string $tenantId): bool {
        $repo = new TenantRepository(\core\Database::connection());
        return $repo->isValidTenant($tenantId);
    }

    private static function setCurrentTenant(string $tenantId): void {
        // Store in request context for API usage
        $GLOBALS['request_context']['current_tenant'] = $tenantId;
        
        // Maintain session compatibility for web requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['current_tenant'] = $tenantId;
        }
    }
}
