<?php
declare(strict_types=1);

class TenantIsolation {
    private const TENANT_PATTERN = '/^tenant_[a-z0-9]{32}$/';

    public static function handle(array $request): array {
        if (!isset($request['headers']['X-Tenant-Context'])) {
            return self::errorResponse('TENANT_VIOLATION', 'Missing tenant context');
        }

        $tenantId = $request['headers']['X-Tenant-Context'];

        if (!preg_match(self::TENANT_PATTERN, $tenantId)) {
            return self::errorResponse('TENANT_VIOLATION', 'Invalid tenant format');
        }

        if (!self::validate($tenantId)) {
            return self::errorResponse('TENANT_VIOLATION', 'Tenant not found');
        }

        $request['tenant'] = $tenantId;
        return $request;
    }

    public static function validate(string $tenantId, string $tenantHash = ''): bool {
        require_once __DIR__ . '/../../includes/tenant_identification.php';
        
        if (!empty($tenantHash)) {
            return TenantValidator::validateWithHash($tenantId, $tenantHash);
        }
        return TenantValidator::isValid($tenantId);
    }

    public static function errorResponse(string $code, string $message, array $details = []): array {
        return [
            'error' => [
                'code' => $code,
                'message' => $message,
                'tenant_id' => $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? null,
                'timestamp' => date('c'),
                'details' => $details
            ],
            'status' => 403
        ];
    }
}
