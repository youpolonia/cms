<?php
/**
 * System Event Trigger Handler
 * Processes built-in system events like content publishing
 */
class SystemEventTrigger {
    private const VALID_EVENTS = [
        'content_published',
        'user_registered',
        'content_updated',
        'user_logged_in'
    ];

    public static function evaluate(array $params): array {
        $eventType = $params['event_type'] ?? '';
        $eventData = $params['event_data'] ?? [];

        if (!in_array($eventType, self::VALID_EVENTS, true)) {
            return [
                'matched' => false,
                'output' => ['error' => 'Invalid system event type']
            ];
        }

        return [
            'matched' => true,
            'output' => [
                'event_type' => $eventType,
                'data' => $eventData,
                'timestamp' => time()
            ]
        ];
    }
}
