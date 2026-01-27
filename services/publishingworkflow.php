<?php
/**
 * Content Publishing Workflow Manager
 */
class PublishingWorkflow {
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    private $contentHandler;
    private $versionControl;

    public function __construct() {
        $this->contentHandler = new ContentFileHandler();
        $this->versionControl = new VersionController();
    }

    /**
     * Update content publishing status
     */
    public function setStatus(string $contentId, string $status, ?DateTime $schedule = null): bool {
        $validStatuses = [self::STATUS_DRAFT, self::STATUS_PENDING, 
                         self::STATUS_PUBLISHED, self::STATUS_ARCHIVED];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $content = $this->contentHandler->load($contentId);
        $content['publishing']['status'] = $status;
        $content['publishing']['scheduled_at'] = $schedule?->format('c');

        return $this->contentHandler->save($contentId, $content);
    }

    /**
     * Publish content immediately
     */
    public function publishNow(string $contentId): bool {
        $this->versionControl->createVersion($contentId, 'Published');
        return $this->setStatus($contentId, self::STATUS_PUBLISHED);
    }

    /**
     * Revert to previous version
     */
    public function revert(string $contentId, int $versionId): bool {
        if ($this->versionControl->restoreVersion($contentId, $versionId)) {
            return $this->setStatus($contentId, self::STATUS_DRAFT);
        }
        return false;
    }
}
