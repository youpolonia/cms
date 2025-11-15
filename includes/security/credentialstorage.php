<?php
namespace CMS\Security;

use CMS\Security\GdprDataHandler;

class CredentialStorage {
    const ENV_PREFIX = 'DB_';
    const ENCRYPTION_SALT = 'CMS_CREDENTIAL_STORAGE_v1';

    public static function getDatabaseCredentials(?string $tenantId = null): array {
        $creds = [
            'host' => self::getCredential('HOST', $tenantId),
            'port' => self::getCredential('PORT', $tenantId),
            'name' => self::getCredential('NAME', $tenantId),
            'user' => self::getCredential('USER', $tenantId),
            'pass' => self::getCredential('PASS', $tenantId),
            'charset' => 'utf8mb4'
        ];

        return array_filter($creds);
    }

    private static function getCredential(string $type, ?string $tenantId = null): ?string {
        // First try tenant-specific encrypted credential
        if ($tenantId) {
            $encrypted = self::getTenantCredential($type, $tenantId);
            if ($encrypted) {
                return GdprDataHandler::decryptField($encrypted);
            }
        }

        // Fallback to environment variable
        return $_ENV[self::ENV_PREFIX . $type] ?? null;
    }

    private static function getTenantCredential(string $type, string $tenantId): ?string {
        // Implementation would query tenant credential storage
        // Placeholder for actual implementation
        return null;
    }

    public static function storeTenantCredential(
        string $type, 
        string $value, 
        string $tenantId
    ): string {
        return GdprDataHandler::encryptField($value);
    }
}
