<?php
declare(strict_types=1);

require_once __DIR__ . '/contentpublisher.php';

/**
 * Content Scheduler
 * Handles scheduled publishing of content
 */
class ContentScheduler {
    
    public static function handlePublishRequest(array $data): array {
        if (empty($data['content_id']) || empty($data['publish_at'])) {
            return ['success' => false, 'error' => 'Missing required fields'];
        }

        $contentId = (int)$data['content_id'];
        $publishAt = date('Y-m-d H:i:s', strtotime($data['publish_at']));
        
        try {
            $result = db_query(
                'INSERT INTO content_schedules (content_id, publish_at) VALUES (?, ?)',
                [$contentId, $publishAt]
            );
            
            return [
                'success' => (bool)$result,
                'schedule_id' => $result ? db_last_insert_id() : null
            ];
        } catch (\Exception $e) {
            error_log('schedule create error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Scheduling failed'];
        }
    }

    public static function checkPendingContent(): void {
        $now = date('Y-m-d H:i:s');
        $pending = db_query(
            'SELECT * FROM content_schedules WHERE publish_at <= ? AND status = \'pending\'',
            [$now]
        );

        foreach ($pending as $schedule) {
            try {
                $published = self::publishContent($schedule['content_id']);
                db_query(
                    'UPDATE content_schedules SET status = ? WHERE id = ?',
                    [$published ? 'published' : 'failed', $schedule['id']]
                );
            } catch (\Exception $e) {
                error_log("Failed to publish scheduled content: " . $e->getMessage());
            }
        }
    }

    private static function publishContent(int $contentId): bool {
        try {
            // Delegate state change to centralized ContentPublisher
            ContentPublisher::init();
            return (bool) ContentPublisher::publish($contentId);
        } catch (\Exception $e) {
            error_log("Failed to publish content {$contentId}: " . $e->getMessage());
            return false;
        }
    }

    public static function getUpcomingSchedules(int $limit = 10): array {
        return db_query(
            'SELECT * FROM content_schedules 
             WHERE publish_at > NOW() 
             ORDER BY publish_at ASC 
             LIMIT ?',
            [$limit]
        );
    }
}
