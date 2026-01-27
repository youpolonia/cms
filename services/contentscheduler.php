<?php
/**
 * Content Scheduling Service
 */
class ContentScheduler {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function scheduleContent($contentId, $publishTime, $userId) {
        $query = "UPDATE content_items 
                 SET scheduled_publish_at = :publishTime,
                     scheduled_by = :userId
                 WHERE id = :contentId";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':publishTime', $publishTime);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':contentId', $contentId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Content scheduling failed: " . $e->getMessage());
            return false;
        }
    }

    public function getScheduledContent() {
        $query = "SELECT * FROM content_items 
                 WHERE scheduled_publish_at IS NOT NULL
                 AND scheduled_publish_at <= NOW()";

        try {
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch scheduled content: " . $e->getMessage());
            return [];
        }
    }

    public function publishScheduledContent() {
        $scheduledItems = $this->getScheduledContent();
        foreach ($scheduledItems as $item) {
            $this->publishItem($item['id']);
        }
        return count($scheduledItems);
    }

    private function publishItem($contentId) {
        $query = "UPDATE content_items 
                 SET published = 1,
                     scheduled_publish_at = NULL,
                     scheduled_by = NULL
                 WHERE id = :contentId";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':contentId', $contentId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Failed to publish content $contentId: " . $e->getMessage());
            return false;
        }
    }
}
