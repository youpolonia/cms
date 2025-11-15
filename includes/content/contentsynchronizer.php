<?php
/**
 * Content Synchronization Service
 * Handles cross-site content synchronization
 */

require_once __DIR__ . '/../../core/database.php';
require_once CMS_ROOT . '/includes/contentmanager.php';

class ContentSynchronizer {
    private $db;
    private $contentManager;

    public function __construct() {
        $this->db = \core\Database::connection();
        $this->contentManager = new ContentManager();
    }

    /**
     * Synchronize content between sites
     * @param int $sourceContentId Content ID to sync from
     * @param int $targetSiteId Site ID to sync to
     * @param int $userId User performing the sync
     * @return array Sync result with status and details
     */
    public function syncContent($sourceContentId, $targetSiteId, $userId) {
        try {
            // Get source content
            $sourceContent = $this->contentManager->getContent($sourceContentId);
            if (!$sourceContent) {
                throw new Exception("Source content not found");
            }

            // Check if sync is allowed between these sites
            $this->verifySyncPermission($sourceContent['site_id'], $targetSiteId);

            // Create/update content in target site
            $targetContentId = $this->findExistingSyncTarget($sourceContentId, $targetSiteId);
            
            if ($targetContentId) {
                // Update existing synchronized content
                $result = $this->contentManager->updateContent($targetContentId, [
                    'title' => $sourceContent['title'],
                    'content' => $sourceContent['content'],
                    'version_note' => "Synced from site {$sourceContent['site_id']}"
                ], $userId);
            } else {
                // Create new synchronized content
                $result = $this->contentManager->createContent([
                    'title' => $sourceContent['title'],
                    'content' => $sourceContent['content'],
                    'content_type' => $sourceContent['content_type'],
                    'site_id' => $targetSiteId
                ], $userId);
            }

            return [
                'status' => 'success',
                'content_id' => $result,
                'action' => $targetContentId ? 'updated' : 'created'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function verifySyncPermission($sourceSiteId, $targetSiteId) {
        $relation = $this->db->fetch(
            "SELECT * FROM site_content_sharing 
             WHERE source_site_id = ? AND target_site_id = ?",
            [$sourceSiteId, $targetSiteId]
        );

        if (!$relation) {
            throw new Exception("Content sharing not allowed between these sites");
        }
    }

    private function findExistingSyncTarget($sourceContentId, $targetSiteId) {
        return $this->db->fetchColumn(
            "SELECT target_content_id FROM content_sync_mapping
             WHERE source_content_id = ? AND target_site_id = ?",
            [$sourceContentId, $targetSiteId]
        );
    }
}
