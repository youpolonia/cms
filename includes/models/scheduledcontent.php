<?php
/**
 * ScheduledContent Model - Handles database operations for content scheduling
 */
class ScheduledContent {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get content ready to be published
     */
    public function getContentToPublish() {
        $now = date('Y-m-d H:i:s');
        $query = "SELECT * FROM scheduled_content 
                 WHERE publish_time <= ? AND status = 'scheduled' 
                 ORDER BY priority DESC, publish_time ASC";
        return $this->db->query($query, [$now])->fetchAll();
    }

    /**
     * Get content ready to be expired
     */
    public function getContentToExpire() {
        $now = date('Y-m-d H:i:s');
        $query = "SELECT * FROM scheduled_content 
                 WHERE expire_time <= ? AND status = 'published' 
                 ORDER BY priority DESC, expire_time ASC";
        return $this->db->query($query, [$now])->fetchAll();
    }

    /**
     * Create a new content schedule
     */
    public function createSchedule($contentId, $publishTime, $expireTime = null, $priority = 0) {
        $query = "INSERT INTO scheduled_content 
                 (content_id, publish_time, expire_time, priority, status) 
                 VALUES (?, ?, ?, ?, 'scheduled')";
        return $this->db->query($query, [
            $contentId, 
            $publishTime, 
            $expireTime, 
            $priority
        ]);
    }

    /**
     * Update content status
     */
    public function updateContentStatus($contentId, $status) {
        $query = "UPDATE scheduled_content SET status = ? WHERE content_id = ?";
        return $this->db->query($query, [$status, $contentId]);
    }

    /**
     * Check for scheduling conflicts
     */
    public function getConflictingSchedules($contentId, $publishTime, $expireTime = null) {
        $query = "SELECT * FROM scheduled_content 
                 WHERE content_id = ? 
                 AND (
                     (publish_time <= ? AND (expire_time IS NULL OR expire_time >= ?))
                     OR (expire_time >= ? AND publish_time <= ?)
                 )";
        return $this->db->query($query, [
            $contentId,
            $expireTime ?? $publishTime,
            $publishTime,
            $publishTime,
            $expireTime ?? $publishTime
        ])->fetchAll();
    }
}
