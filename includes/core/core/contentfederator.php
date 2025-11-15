<?php
declare(strict_types=1);

/**
 * Content Federator - Handles cross-site content sharing
 */
final class ContentFederator
{
    private static array $sharedContent = [];
    private static array $subscriptions = [];

    public static function shareContent(
        string $contentId,
        string $content,
        array $permissions = []
    ): bool {
        if (!self::validateContent($content) || !self::validatePermissions($permissions)) {
            return false;
        }

        self::$sharedContent[$contentId] = [
            'content' => $content,
            'permissions' => $permissions,
            'timestamp' => time()
        ];

        return true;
    }

    public static function getContent(string $contentId): ?array
    {
        if (!isset(self::$sharedContent[$contentId])) {
            return null;
        }

        return self::$sharedContent[$contentId];
    }

    public static function subscribe(string $tenantId, string $contentId): bool
    {
        if (!TenantManager::validateTenantId($tenantId)) {
            return false;
        }

        self::$subscriptions[$contentId][] = $tenantId;
        return true;
    }

    private static function validateContent(string $content): bool
    {
        return strlen($content) <= 10000 && !empty(trim($content));
    }

    private static function validatePermissions(array $permissions): bool
    {
        $validKeys = ['read', 'write', 'share'];
        foreach ($permissions as $key => $value) {
            if (!in_array($key, $validKeys) || !is_bool($value)) {
                return false;
            }
        }
        return true;
    }
}
