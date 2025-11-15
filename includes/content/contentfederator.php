<?php
/**
 * Content Federator - Handles cross-site content sharing
 * 
 * @package CMS
 * @subpackage Content
 */

require_once __DIR__ . '/../../includes/tenant/tenantmanager.php';

class ContentFederator {
    /**
     * Share content with target sites
     * @param string $contentId
     * @param array $targetTenants
     * @return array Federation results
     */
    public static function shareContent(string $contentId, array $targetTenants): array {
        $results = [];
        
        foreach ($targetTenants as $tenantId) {
            try {
                $tenant = TenantManager::validateTenant($tenantId);
                $results[$tenantId] = self::syncContent($contentId, $tenant);
            } catch (Exception $e) {
                $results[$tenantId] = [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Sync content version to target tenant
     * @param string $contentId
     * @param array $targetTenant
     * @return array Sync result
     */
    private static function syncContent(string $contentId, array $targetTenant): array {
        // Get source content
        $sourceContent = self::getContent($contentId);
        
        // Store in target tenant
        $storagePath = self::getStoragePath($targetTenant['tenant_id'], $contentId);
        file_put_contents($storagePath, json_encode($sourceContent));

        return [
            'status' => 'success',
            'content_id' => $contentId,
            'version' => $sourceContent['version']
        ];
    }

    /**
     * Get content from source
     * @param string $contentId
     * @return array Content data
     */
    private static function getContent(string $contentId): array {
        // Implementation would query database or filesystem
        return [
            'id' => $contentId,
            'version' => md5(time()),
            'content' => 'Sample content',
            'metadata' => []
        ];
    }

    /**
     * Get storage path for tenant content
     * @param string $tenantId
     * @param string $contentId
     * @return string Full path
     */
    private static function getStoragePath(string $tenantId, string $contentId): string {
        $basePath = __DIR__ . "/../../tenants/{$tenantId}/federated_content";
        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }
        return "{$basePath}/{$contentId}.json";
    }
}
