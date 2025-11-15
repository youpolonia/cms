<?php
/**
 * Redis-based event collector for CMS analytics
 */
class EventCollector {
    private $redis;
    private $queueName = 'analytics_events';
    private $eventTypes = [
        'page_view',
        'performance_metric',
        'user_engagement',
        'content_interaction'
    ];

    public function __construct($redisConnection) {
        $this->redis = $redisConnection;
    }

    public function trackEvent(string $eventType, array $payload, ?int $userId = null): bool {
        if (!in_array($eventType, $this->eventTypes)) {
            throw new InvalidArgumentException("Invalid event type: $eventType");
        }

        $event = [
            'timestamp' => microtime(true),
            'type' => $eventType,
            'payload' => $payload,
            'user_id' => $userId,
            'session_id' => session_id()
        ];

        return $this->redis->rPush(
            $this->queueName,
            json_encode($event)
        ) > 0;
    }

    public function getPendingCount(): int {
        return $this->redis->lLen($this->queueName);
    }

    public function trackPerformanceMetric(string $metricName, float $value, array $tags = []): bool {
        return $this->trackEvent('performance_metric', [
            'metric' => $metricName,
            'value' => $value,
            'tags' => $tags
        ]);
    }

    public function trackUserEngagement(string $action, string $element, array $context = []): bool {
        return $this->trackEvent('user_engagement', [
            'action' => $action,
            'element' => $element,
            'context' => $context
        ]);
    }
}
