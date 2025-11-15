<?php
namespace Tenant;

class Validation {
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public static function isValidTenantId(string $tenantId): bool {
        return preg_match(self::UUID_PATTERN, $tenantId) === 1;
    }

    public static function sanitizePath(string $path): string {
        return preg_replace('/[^a-zA-Z0-9_\-\.\/]/', '', $path);
    }

    public static function verifyTenantAccess(string $tenantId): bool {
        // TODO: Implement actual tenant verification
        return self::isValidTenantId($tenantId);
    }
}
