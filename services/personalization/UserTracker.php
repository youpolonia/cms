<?php
declare(strict_types=1);

/**
 * User Behavior Tracking Service
 * Logs and analyzes user interactions for personalization
 */
class UserTracker {
    private static array $trackedEvents = [];
    private static bool $enabled = true;

    /**
     * Track user interaction event
     */
    public static function trackEvent(
        string $eventType,
        int $userId,
        string $itemId,
        array $metadata = []
    ): void {
        if (!self::$enabled) {
            return;
        }

        self::$trackedEvents[] = [
            'timestamp' => time(),
            'event_type' => $eventType,
            'user_id' => $userId,
            'item_id' => $itemId,
            'metadata' => $metadata
        ];
    }

    /**
     * Get recent events for user
     */
    public static function getUserEvents(int $userId, int $limit = 50): array {
        return array_slice(
            array_filter(
                self::$trackedEvents,
                fn($e) => $e['user_id'] === $userId
            ),
            0,
            $limit
        );
    }

    /**
     * Enable/disable tracking
     */
    public static function setTrackingStatus(bool $enabled): void {
        self::$enabled = $enabled;
    }
}
