<?php
declare(strict_types=1);

class AnalyticsCollector {
    private const MAX_PAYLOAD_SIZE = 10240; // 10KB
    private static array $validEventTypes = [
        'page_view',
        'status_transition',
        'api_call',
        'custom_event',
        'content_view',
        'user_engagement',
        'time_based_event'
    ];

    public static function trackEvent(string $eventType, array $eventData, string $tenantId, ?string $userId = null): bool {
        if (!self::validateEvent($eventType, $eventData)) {
            return false;
        }

        $payload = self::preparePayload($eventType, $eventData);
        if (strlen($payload) > self::MAX_PAYLOAD_SIZE) {
            return false;
        }

        $data = [
            'tenant_id' => $tenantId,
            'event_type' => $eventType,
            'event_data' => $payload,
            'user_id' => $userId,
            'session_id' => session_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        return DB::insert('tenant_analytics_events', $data);
    }

    public static function trackStatusTransition(string $entityType, int $entityId, string $fromStatus, string $toStatus, string $tenantId, ?string $userId = null, ?string $reason = null): bool {
        $eventData = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason
        ];

        return self::trackEvent('status_transition', $eventData, $tenantId, $userId);
    }

    private static function preparePayload(string $eventType, array $eventData): string {
        // Add timestamp to all events
        $eventData['timestamp'] = time();
        return json_encode($eventData, JSON_UNESCAPED_UNICODE);
    }

    public static function trackContentView(
        string $contentId,
        int $duration,
        float $completion = 0.0,
        string $tenantId,
        ?string $userId = null
    ): bool {
        $eventData = [
            'content_id' => $contentId,
            'duration' => $duration,
            'completion' => $completion
        ];
        return self::trackEvent('content_view', $eventData, $tenantId, $userId);
    }

    public static function trackUserEngagement(
        string $engagementType,
        string $targetId,
        array $metadata,
        string $tenantId,
        ?string $userId = null
    ): bool {
        $eventData = [
            'engagement_type' => $engagementType,
            'target_id' => $targetId,
            'metadata' => $metadata
        ];
        return self::trackEvent('user_engagement', $eventData, $tenantId, $userId);
    }

    public static function trackTimedEvent(
        string $eventName,
        int $duration,
        array $additionalData,
        string $tenantId,
        ?string $userId = null
    ): bool {
        $eventData = [
            'event_name' => $eventName,
            'duration' => $duration
        ] + $additionalData;
        return self::trackEvent('time_based_event', $eventData, $tenantId, $userId);
    }

    private static function validateEvent(string $eventType, array $eventData): bool {
        if (!in_array($eventType, self::$validEventTypes)) {
            return false;
        }

        return match($eventType) {
            'status_transition' => isset($eventData['entity_type'], $eventData['entity_id'], $eventData['from_status'], $eventData['to_status']),
            'content_view' => isset($eventData['content_id'], $eventData['duration']),
            'user_engagement' => isset($eventData['engagement_type'], $eventData['target_id']),
            'time_based_event' => isset($eventData['event_name'], $eventData['duration']),
            default => !empty($eventData)
        };
    }
}
