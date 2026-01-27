<?php
require_once __DIR__ . '/contententry.php';
require_once __DIR__ . '/../../core/contentpublisher.php';

class ContentModel extends ContentEntry {
    /**
     * Publish content entry
     * @param int $contentId
     * @return bool
     */
    public function publish(int $contentId): bool {
        ContentPublisher::init();
        return ContentPublisher::publish($contentId);
    }

    /**
     * Archive content entry
     * @param int $contentId
     * @return bool
     */
    public function archive(int $contentId): bool {
        ContentPublisher::init();
        return ContentPublisher::archive($contentId);
    }

    /**
     * Schedule content for publishing
     * @param int $contentId
     * @param string $publishDate MySQL datetime format
     * @return bool
     */
    public function schedule(int $contentId, string $publishDate): bool {
        $stmt = $this->db->prepare(
            "UPDATE content_entries
            SET scheduled_for = ?
            WHERE id = ?"
        );
        return $stmt->execute([$publishDate, $contentId]);
    }

    /**
     * Get content state
     * @param int $contentId
     * @return string|null
     */
    public function getState(int $contentId): ?string {
        $stmt = $this->db->prepare(
            "SELECT state FROM content_entries WHERE id = ?"
        );
        $stmt->execute([$contentId]);
        return $stmt->fetchColumn();
    }
}
