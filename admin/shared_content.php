<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/core/auth.php';
require_once __DIR__ . '/../includes/core/db.php';
require_once __DIR__ . '/../includes/core/VersionControl.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

class SharedContentAPI {
    private static $db;
    private static $auth;
    private static $versionControl;

    public static function init(): void {
        self::$db = DB::getInstance();
        self::$auth = new Auth();
        self::$versionControl = new VersionControl();
    }

    /**
     * Share content between sites
     * @param int $contentId
     * @param array $targetSiteIds
     * @param array $permissions
     * @return array
     */
    public static function shareContent(int $contentId, array $targetSiteIds, array $permissions): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        if (!self::$auth->hasPermission('content_share')) {
            return ['error' => 'Permission denied'];
        }

        // Validate content exists
        $content = self::$db->querySingle("SELECT * FROM content WHERE id = ?", [$contentId]);
        if (!$content) {
            return ['error' => 'Content not found'];
        }

        // Process sharing for each target site
        $results = [];
        foreach ($targetSiteIds as $siteId) {
            // Check if sharing already exists
            $existing = self::$db->querySingle(
                "SELECT id FROM shared_content 
                 WHERE source_content_id = ? AND target_site_id = ?",
                [$contentId, $siteId]
            );

            if (!$existing) {
                $shareId = self::$db->insert('shared_content', [
                    'source_content_id' => $contentId,
                    'target_site_id' => $siteId,
                    'permissions' => json_encode($permissions),
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $results[$siteId] = $shareId ? 'shared' : 'failed';
            } else {
                $results[$siteId] = 'already_shared';
            }
        }

        return ['results' => $results];
    }

    /**
     * Update sharing permissions
     * @param int $shareId
     * @param array $newPermissions
     * @return array
     */
    public static function updatePermissions(int $shareId, array $newPermissions): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        if (!self::$auth->hasPermission('content_share_manage')) {
            return ['error' => 'Permission denied'];
        }

        $updated = self::$db->update('shared_content',
            ['permissions' => json_encode($newPermissions)],
            ['id' => $shareId]
        );

        return $updated
            ? ['success' => true]
            : ['error' => 'Failed to update permissions'];
    }

    /**
     * Get sync status for shared content
     * @param int $shareId
     * @return array
     */
    public static function getSyncStatus(int $shareId): array {
        $status = self::$db->querySingle(
            "SELECT status, last_sync, sync_errors
             FROM shared_content WHERE id = ?",
            [$shareId]
        );

        return $status ?: ['error' => 'Share record not found'];
    }

    /**
     * Resolve content conflicts
     * @param int $shareId
     * @param string $resolution (keep_source|keep_target|merge)
     * @return array
     */
    public static function resolveConflict(int $shareId, string $resolution): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $share = self::$db->querySingle(
            "SELECT * FROM shared_content WHERE id = ?",
            [$shareId]
        );

        if (!$share) {
            return ['error' => 'Share record not found'];
        }

        // Get version history for conflict resolution
        $versions = self::$versionControl->getContentVersions($share['source_content_id']);

        switch ($resolution) {
            case 'keep_source':
                // Implement source version preservation
                break;
            case 'keep_target':
                // Implement target version preservation
                break;
            case 'merge':
                // Implement merge logic
                break;
            default:
                return ['error' => 'Invalid resolution method'];
        }

        return ['success' => true, 'resolution' => $resolution];
    }

    /**
     * List available sites for content sharing
     * @return array
     */
    public static function listShareableSites(): array {
        return self::$db->queryAll(
            "SELECT id, name FROM sites
             WHERE id != ? AND status = 'active'",
            [self::$auth->getCurrentSiteId()]
        );
    }
}
