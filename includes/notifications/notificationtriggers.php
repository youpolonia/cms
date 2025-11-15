<?php
/**
 * Notification Triggers for CMS
 * Handles system-wide notification triggers in pure PHP
 */

class NotificationTriggers {
    /**
     * Trigger notification for new activity log entry
     * @param int $userId User ID who performed the action
     * @param string $activityType Type of activity
     * @param string $description Activity description
     * @param array $metadata Additional activity data
     * @return bool True if notification created successfully
     */
    public static function triggerActivityLogNotification(
        int $userId,
        string $activityType,
        string $description,
        array $metadata = []
    ): bool {
        try {
            // Check user notification preferences
            if (!self::shouldNotifyUser($userId, 'activity_log')) {
                return false;
            }

            // Create notification record
            $notificationId = self::createNotificationRecord([
                'user_id' => $userId,
                'type' => 'activity_log',
                'title' => "New Activity: $activityType",
                'message' => $description,
                'metadata' => json_encode($metadata),
                'status' => 'unread'
            ]);

            return $notificationId !== false;
        } catch (Exception $e) {
            error_log("Failed to trigger activity log notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger notification for important system event
     * @param string $eventType System event type
     * @param string $message Event message
     * @param array $affectedUsers Array of user IDs to notify
     * @param array $metadata Additional event data
     * @return int Count of successful notifications
     */
    public static function triggerSystemEventNotification(
        string $eventType,
        string $message,
        array $affectedUsers,
        array $metadata = []
    ): int {
        $successCount = 0;

        foreach ($affectedUsers as $userId) {
            try {
                // Check user notification preferences
                if (!self::shouldNotifyUser($userId, 'system_event')) {
                    continue;
                }

                // Create notification record
                $notificationId = self::createNotificationRecord([
                    'user_id' => $userId,
                    'type' => 'system_event',
                    'title' => "System Event: $eventType",
                    'message' => $message,
                    'metadata' => json_encode($metadata),
                    'status' => 'unread'
                ]);

                if ($notificationId !== false) {
                    $successCount++;
                }
            } catch (Exception $e) {
                error_log("Failed to trigger system event notification for user $userId: " . $e->getMessage());
            }
        }

        return $successCount;
    }

    /**
     * Trigger scheduled reminder notification
     * @param int $userId User ID to notify
     * @param string $reminderType Type of reminder
     * @param string $message Reminder message
     * @param string $dueDate When the reminder is due
     * @return bool True if notification created successfully
     */
    public static function triggerScheduledReminder(
        int $userId,
        string $reminderType,
        string $message,
        string $dueDate
    ): bool {
        try {
            // Check user notification preferences
            if (!self::shouldNotifyUser($userId, 'scheduled_reminder')) {
                return false;
            }

            // Create notification record
            $notificationId = self::createNotificationRecord([
                'user_id' => $userId,
                'type' => 'scheduled_reminder',
                'title' => "Reminder: $reminderType",
                'message' => $message,
                'metadata' => json_encode(['due_date' => $dueDate]),
                'status' => 'unread'
            ]);

            return $notificationId !== false;
        } catch (Exception $e) {
            error_log("Failed to trigger scheduled reminder for user $userId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user should receive notification of given type
     * @param int $userId User ID
     * @param string $notificationType Type of notification
     * @return bool True if user should be notified
     */
    private static function shouldNotifyUser(int $userId, string $notificationType): bool {
        global $db;
        
        try {
            $stmt = $db->prepare(
                "SELECT notification_preferences
                 FROM user_settings
                 WHERE user_id = ?
                 AND notification_type = ?"
            );
            
            $stmt->execute([$userId, $notificationType]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prefs && isset($prefs['notification_preferences'])) {
                return (bool)json_decode($prefs['notification_preferences'])->enabled;
            }
            
            // Default to enabled if no preference exists
            return true;
        } catch (PDOException $e) {
            error_log("Failed to check user notification preferences: " . $e->getMessage());
            // Fail safe - allow notification if preference check fails
            return true;
        }
    }

    /**
     * Create notification record in database
     * @param array $data Notification data
     * @return int|false Notification ID or false on failure
     */
    private static function createNotificationRecord(array $data): int|false {
        global $db;
        
        try {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            $params = array_values($data);
            
            $stmt = $db->prepare(
                "INSERT INTO notifications ($columns) VALUES ($values)"
            );
            
            if ($stmt->execute($params)) {
                return $db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Notification record creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger notification for content submission
     * @param int $authorId Author user ID
     * @param int $contentId Content ID
     * @param array $reviewerIds Array of reviewer user IDs
     * @return int Count of successful notifications
     */
    public static function triggerContentSubmissionNotification(
        int $authorId,
        int $contentId,
        array $reviewerIds
    ): int {
        $successCount = 0;
        
        foreach ($reviewerIds as $reviewerId) {
            try {
                if (!self::shouldNotifyUser($reviewerId, 'content_submission')) {
                    continue;
                }

                $notificationId = self::createNotificationRecord([
                    'user_id' => $reviewerId,
                    'type' => 'content_submission',
                    'title' => "New Content Submitted",
                    'message' => "Content #$contentId submitted for review",
                    'metadata' => json_encode([
                        'content_id' => $contentId,
                        'author_id' => $authorId
                    ]),
                    'status' => 'unread'
                ]);

                if ($notificationId !== false) {
                    $successCount++;
                }
            } catch (Exception $e) {
                error_log("Failed to trigger content submission notification: " . $e->getMessage());
            }
        }

        return $successCount;
    }

    /**
     * Trigger notification for content approval/rejection
     * @param int $authorId Author user ID
     * @param int $contentId Content ID
     * @param string $status 'approved' or 'rejected'
     * @param string|null $feedback Optional feedback
     * @return bool True if notification created successfully
     */
    public static function triggerContentStatusNotification(
        int $authorId,
        int $contentId,
        string $status,
        ?string $feedback = null
    ): bool {
        try {
            if (!self::shouldNotifyUser($authorId, 'content_status')) {
                return false;
            }

            $message = "Content #$contentId was $status";
            if ($feedback) {
                $message .= " with feedback: $feedback";
            }

            $notificationId = self::createNotificationRecord([
                'user_id' => $authorId,
                'type' => 'content_status',
                'title' => "Content $status",
                'message' => $message,
                'metadata' => json_encode([
                    'content_id' => $contentId,
                    'status' => $status
                ]),
                'status' => 'unread'
            ]);

            return $notificationId !== false;
        } catch (Exception $e) {
            error_log("Failed to trigger content status notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger notification for cache clearance
     * @param array $adminIds Array of admin user IDs to notify
     * @param string $cacheType Type of cache cleared
     * @return int Count of successful notifications
     */
    public static function triggerCacheClearNotification(
        array $adminIds,
        string $cacheType
    ): int {
        $successCount = 0;
        
        foreach ($adminIds as $adminId) {
            try {
                if (!self::shouldNotifyUser($adminId, 'cache_clear')) {
                    continue;
                }

                $notificationId = self::createNotificationRecord([
                    'user_id' => $adminId,
                    'type' => 'cache_clear',
                    'title' => "Cache Cleared",
                    'message' => "$cacheType cache was cleared",
                    'metadata' => json_encode([
                        'cache_type' => $cacheType,
                        'timestamp' => time()
                    ]),
                    'status' => 'unread'
                ]);

                if ($notificationId !== false) {
                    $successCount++;
                }
            } catch (Exception $e) {
                error_log("Failed to trigger cache clear notification: " . $e->getMessage());
            }
        }

        return $successCount;
    }

    /**
     * Trigger notification for version changes
     * @param array $adminIds Array of admin user IDs to notify
     * @param int $contentId Content ID
     * @param string $version New version number
     * @return int Count of successful notifications
     */
    public static function triggerVersionChangeNotification(
        array $adminIds,
        int $contentId,
        string $version
    ): int {
        $successCount = 0;
        
        foreach ($adminIds as $adminId) {
            try {
                if (!self::shouldNotifyUser($adminId, 'version_change')) {
                    continue;
                }

                $notificationId = self::createNotificationRecord([
                    'user_id' => $adminId,
                    'type' => 'version_change',
                    'title' => "Version Changed",
                    'message' => "Content #$contentId updated to version $version",
                    'metadata' => json_encode([
                        'content_id' => $contentId,
                        'version' => $version
                    ]),
                    'status' => 'unread'
                ]);

                if ($notificationId !== false) {
                    $successCount++;
                }
            } catch (Exception $e) {
                error_log("Failed to trigger version change notification: " . $e->getMessage());
            }
        }

        return $successCount;
    }
}
