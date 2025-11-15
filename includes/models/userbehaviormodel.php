<?php

class UserBehaviorModel {
    const EVENT_TYPES = [
        'page_view',
        'content_click', 
        'scroll',
        'time_spent',
        'conversion'
    ];

    public static function logEvent(array $eventData): bool {
        $requiredFields = ['user_id', 'session_id', 'event_type'];
        
        foreach ($requiredFields as $field) {
            if (!isset($eventData[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        if (!in_array($eventData['event_type'], self::EVENT_TYPES)) {
            throw new InvalidArgumentException("Invalid event type");
        }

        $query = "INSERT INTO user_behavior_events 
                 (user_id, session_id, event_type, content_id, metadata)
                 VALUES (?, ?, ?, ?, ?)";

        return DatabaseConnection::execute(
            $query,
            [
                $eventData['user_id'],
                $eventData['session_id'],
                $eventData['event_type'],
                $eventData['content_id'] ?? null,
                json_encode($eventData['metadata'] ?? [])
            ]
        );
    }

    public static function getUserEvents(int $userId, int $limit = 100): array {
        $query = "SELECT * FROM user_behavior_events 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ?";
        
        return DatabaseConnection::fetchAll($query, [$userId, $limit]);
    }
}
