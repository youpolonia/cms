<?php

namespace Includes\Services;

use Includes\Database\Connection;
use Includes\Config\ConfigLoader;

/**
 * Handles scheduled execution of notifications via cron simulation
 */
class ScheduledNotificationRunner {
    private const LOCK_FILE = __DIR__.'/../../storage/scheduled_notifications.lock';
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 60; // seconds
    
    /**
     * Execute scheduled notifications (to be called from cron)
     */
    public static function execute(): void {
        if (!self::acquireLock()) {
            return;
        }

        try {
            $unprocessed = self::getUnprocessedNotifications();
            foreach ($unprocessed as $notification) {
                self::processNotification($notification);
            }
        } finally {
            self::releaseLock();
        }
    }

    /**
     * Get unprocessed notifications from database
     */
    private static function getUnprocessedNotifications(): array {
        $db = \core\Database::connection();
        $query = "SELECT * FROM worker_notifications
                 WHERE is_read = 0
                 AND created_at <= NOW()
                 ORDER BY created_at ASC";

        return $db->query($query)->fetchAll();
    }

    /**
     * Process single notification with retry logic
     */
    private static function processNotification(array $notification): void {
        $attempt = 0;
        $success = false;
        
        while ($attempt < self::MAX_RETRIES && !$success) {
            try {
                $analysis = NotificationAnalyzer::analyze($notification['message']);
                self::deliverNotification($notification, $analysis);
                self::markAsProcessed($notification['notification_id']);
                $success = true;
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt < self::MAX_RETRIES) {
                    sleep(self::RETRY_DELAY);
                } else {
                    error_log("Failed to process notification {$notification['notification_id']}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Deliver notification through configured channels
     */
    private static function deliverNotification(array $notification, array $analysis): void {
        $config = ConfigLoader::get('notifications');
        $channels = $config['delivery_channels'] ?? ['database'];
        
        foreach ($channels as $channel) {
            // Implementation would vary per channel (email, sms, etc)
            // This is a simplified example
            if ($channel === 'database') {
                // Already handled by marking as read
                continue;
            }
        }
    }

    /**
     * Mark notification as processed in database
     */
    private static function markAsProcessed(int $notificationId): void {
        $db = \core\Database::connection();
        $stmt = $db->prepare(
            "UPDATE worker_notifications SET is_read = 1, read_at = NOW()
            WHERE notification_id = ?"
        );
        $stmt->execute([$notificationId]);
    }

    /**
     * Acquire file lock to prevent concurrent execution
     */
    private static function acquireLock(): bool {
        if (file_exists(self::LOCK_FILE)) {
            $lockTime = filemtime(self::LOCK_FILE);
            if (time() - $lockTime < 3600) { // 1 hour max lock time
                return false;
            }
        }
        
        return touch(self::LOCK_FILE);
    }

    /**
     * Release file lock
     */
    private static function releaseLock(): void {
        if (file_exists(self::LOCK_FILE)) {
            unlink(self::LOCK_FILE);
        }
    }
}
