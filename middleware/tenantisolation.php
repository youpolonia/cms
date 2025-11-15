<?php
/**
 * Tenant Isolation Middleware
 * Validates tenant context and sets current tenant
 */

class TenantIsolation {
    /**
     * Validate tenant ID and hash
     * @param string $tenantId
     * @param string $tenantHash
     * @return bool
     */
    public static function validate(string $tenantId, string $tenantHash): bool {
        // In production, this would query a database or cache
        // For now, simulate validation with a simple hash check
        return hash_equals(self::generateExpectedHash($tenantId), $tenantHash);
    }

    /**
     * Generate expected hash for a tenant ID
     * @param string $tenantId
     * @return string
     */
    private static function generateExpectedHash(string $tenantId): string {
        // In production, use a proper secret key from config
        $secret = 'tenant_secret_key'; // Should be from config
        return hash_hmac('sha256', $tenantId, $secret);
    }

    /**
     * Set current tenant context
     * @param string $tenantId
     */
    public static function setCurrent(string $tenantId): void {
        $_SESSION['current_tenant'] = $tenantId;
    }

    /**
     * Get current tenant ID
     * @return string|null
     */
    public static function getCurrent(): ?string {
        return $_SESSION['current_tenant'] ?? null;
    }
}
