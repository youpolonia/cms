<?php
namespace Includes\Middleware;

class TenantValidator {
    /**
     * Validate content ownership for tenant
     * @param int $contentId
     * @param string $tenantId
     * @return bool
     */
    public static function validateContentOwnership(int $contentId, string $tenantId): bool {
        $content = ContentRepository::getById($contentId);
        return $content && $content['tenant_id'] === $tenantId;
    }

    /**
     * Validate version ownership for tenant
     * @param int $versionId
     * @param string $tenantId
     * @return bool
     */
    public static function validateVersionOwnership(int $versionId, string $tenantId): bool {
        $version = VersionRepository::getById($versionId);
        return $version && $version['tenant_id'] === $tenantId;
    }

    /**
     * Lock content to prevent concurrent modifications
     * @param int $contentId
     * @param string $tenantId
     * @return bool
     */
    public static function lockContent(int $contentId, string $tenantId): bool {
        if (!self::validateContentOwnership($contentId, $tenantId)) {
            return false;
        }
        return LockManager::acquire($contentId, $tenantId);
    }

    /**
     * Release content lock
     * @param int $contentId
     * @return void
     */
    public static function unlockContent(int $contentId): void {
        LockManager::release($contentId);
    }

    /**
     * Check if content is locked
     * @param int $contentId
     * @return bool
     */
    public static function isContentLocked(int $contentId): bool {
        return LockManager::isLocked($contentId);
    }

    /**
     * Validate X-Tenant-ID header
     * @param string $tenantId
     * @return bool
     */
    public static function validateTenantHeader(string $tenantId): bool {
        return TenantRepository::exists($tenantId);
    }
}
