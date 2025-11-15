<?php
/**
 * Content Scheduler - Handles automated content publishing and expiration
 */
class Scheduler {
    private $db;
    private $contentModel;

    public function __construct($db) {
        $this->db = $db;
        require_once __DIR__ . '/../models/ScheduledContent.php';
        $this->contentModel = new ScheduledContent($db);
    }

    /**
     * Process scheduled content - to be called by cron job
     */
    public function processScheduledContent() {
        // Get content ready to publish
        $toPublish = $this->contentModel->getContentToPublish();
        
        // Get content ready to expire
        $toExpire = $this->contentModel->getContentToExpire();
        
        // Process publishing
        foreach ($toPublish as $content) {
            $this->publishContent($content);
        }
        
        // Process expiration
        foreach ($toExpire as $content) {
            $this->expireContent($content);
        }
        
        return count($toPublish) + count($toExpire);
    }

    /**
     * Publish scheduled content
     */
    private function publishContent($content) {
        // Update content status to published
        $this->contentModel->updateContentStatus($content['id'], 'published');
        
        // Additional publishing logic can be added here
    }

    /**
     * Expire scheduled content
     */
    private function expireContent($content) {
        // Update content status to expired
        $this->contentModel->updateContentStatus($content['id'], 'expired');
        
        // Additional expiration logic can be added here
    }

    /**
     * Schedule content for publishing/expiration
     */
    public function scheduleContent($contentId, $publishTime, $expireTime = null, $priority = 0) {
        return $this->contentModel->createSchedule($contentId, $publishTime, $expireTime, $priority);
    }

    /**
     * Check for scheduling conflicts
     */
    public function checkConflicts($contentId, $publishTime, $expireTime = null) {
        return $this->contentModel->getConflictingSchedules($contentId, $publishTime, $expireTime);
    }
}
