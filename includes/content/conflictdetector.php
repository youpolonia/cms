<?php
/**
 * Content Conflict Detection Service
 * Detects conflicts during content synchronization
 */

require_once __DIR__ . '/../../core/database.php';
require_once CMS_ROOT . '/includes/contentmanager.php';

class ConflictDetector {
    private $db;
    private $contentManager;

    public function __construct() {
        $this->db = \core\Database::connection();
        $this->contentManager = new ContentManager();
    }

    /**
     * Detect conflicts between source and target content
     * @param int $sourceContentId Source content ID
     * @param int $targetContentId Target content ID (optional)
     * @param int $targetSiteId Target site ID (if no target content exists)
     * @return array Conflict detection results
     */
    public function detectConflicts($sourceContentId, $targetContentId = null, $targetSiteId = null) {
        $sourceContent = $this->contentManager->getContent($sourceContentId);
        if (!$sourceContent) {
            return [
                'status' => 'error',
                'message' => 'Source content not found'
            ];
        }

        $targetContent = null;
        if ($targetContentId) {
            $targetContent = $this->contentManager->getContent($targetContentId);
        } elseif ($targetSiteId) {
            $targetContentId = $this->findExistingSyncTarget($sourceContentId, $targetSiteId);
            if ($targetContentId) {
                $targetContent = $this->contentManager->getContent($targetContentId);
            }
        }

        if (!$targetContent) {
            return [
                'status' => 'no_conflict',
                'message' => 'No existing target content - no conflict possible'
            ];
        }

        // Check for timestamp conflicts
        $timestampConflict = $this->checkTimestampConflict(
            $sourceContent['updated_at'],
            $targetContent['updated_at']
        );

        // Check for content hash conflicts
        $contentConflict = $this->checkContentConflict(
            $sourceContent['content'],
            $targetContent['content']
        );

        return [
            'status' => 'success',
            'conflicts' => [
                'timestamp' => $timestampConflict,
                'content' => $contentConflict
            ],
            'source_version' => $sourceContent['version_id'],
            'target_version' => $targetContent['version_id']
        ];
    }

    private function checkTimestampConflict($sourceUpdated, $targetUpdated) {
        $sourceTime = strtotime($sourceUpdated);
        $targetTime = strtotime($targetUpdated);
        
        // Consider conflict if target was modified more recently than source
        return $targetTime > $sourceTime;
    }

    private function checkContentConflict($sourceContent, $targetContent) {
        $sourceHash = md5(json_encode($sourceContent));
        $targetHash = md5(json_encode($targetContent));
        
        return $sourceHash !== $targetHash;
    }

    private function findExistingSyncTarget($sourceContentId, $targetSiteId) {
        return $this->db->fetchColumn(
            "SELECT target_content_id FROM content_sync_mapping
             WHERE source_content_id = ? AND target_site_id = ?",
            [$sourceContentId, $targetSiteId]
        );
    }
}
