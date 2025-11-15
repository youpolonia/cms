<?php
/**
 * Content Conflict Resolution Service
 * Handles conflict resolution during content synchronization
 */

require_once __DIR__ . '/../../core/database.php';
require_once CMS_ROOT . '/includes/contentmanager.php';
require_once CMS_ROOT . '/includes/content/conflictdetector.php';

class ConflictResolver {
    private $db;
    private $contentManager;
    private $conflictDetector;

    public function __construct() {
        $this->db = \core\Database::connection();
        $this->contentManager = new ContentManager();
        $this->conflictDetector = new ConflictDetector();
    }

    /**
     * Resolve content conflicts
     * @param int $sourceContentId Source content ID
     * @param int $targetContentId Target content ID
     * @param string $strategy Resolution strategy
     * @param int $userId User performing the resolution
     * @return array Resolution result
     */
    public function resolveConflict($sourceContentId, $targetContentId, $strategy, $userId) {
        $conflictCheck = $this->conflictDetector->detectConflicts($sourceContentId, $targetContentId);
        
        if ($conflictCheck['status'] !== 'success') {
            return $conflictCheck;
        }

        $sourceContent = $this->contentManager->getContent($sourceContentId);
        $targetContent = $this->contentManager->getContent($targetContentId);

        switch ($strategy) {
            case 'merge':
                $resolvedContent = $this->mergeContent(
                    $sourceContent['content'],
                    $targetContent['content']
                );
                break;

            case 'source':
                $resolvedContent = $sourceContent['content'];
                break;

            case 'target':
                $resolvedContent = $targetContent['content'];
                break;

            case 'newer':
                $resolvedContent = $this->resolveByNewerVersion(
                    $sourceContent,
                    $targetContent
                );
                break;

            default:
                return [
                    'status' => 'error',
                    'message' => 'Invalid resolution strategy'
                ];
        }

        // Update target content with resolved version
        $result = $this->contentManager->updateContent($targetContentId, [
            'title' => $sourceContent['title'],
            'content' => $resolvedContent,
            'version_note' => "Conflict resolved using {$strategy} strategy"
        ], $userId);

        return [
            'status' => 'success',
            'content_id' => $result,
            'strategy' => $strategy,
            'resolved_at' => date('Y-m-d H:i:s')
        ];
    }

    private function mergeContent($sourceContent, $targetContent) {
        // Simple merge strategy - combine arrays with source taking precedence
        return array_merge($targetContent, $sourceContent);
    }

    private function resolveByNewerVersion($sourceContent, $targetContent) {
        $sourceTime = strtotime($sourceContent['updated_at']);
        $targetTime = strtotime($targetContent['updated_at']);
        
        return $sourceTime > $targetTime 
            ? $sourceContent['content']
            : $targetContent['content'];
    }
}
