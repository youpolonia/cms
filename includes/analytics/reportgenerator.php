<?php
/**
 * Generates analytics reports from collected data
 */
class ReportGenerator {
    private $redis;
    private $db;

    public function __construct($redisConnection, $databaseConnection) {
        $this->redis = $redisConnection;
        $this->db = $databaseConnection;
    }

    public function generateDailyReport(DateTime $date): array {
        $start = (clone $date)->setTime(0, 0, 0)->getTimestamp();
        $end = (clone $date)->setTime(23, 59, 59)->getTimestamp();

        // Get raw events from Redis
        $events = $this->getEventsFromRedis($start, $end);

        return [
            'date' => $date->format('Y-m-d'),
            'page_views' => $this->countEvents($events, 'page_view'),
            'unique_visitors' => $this->countUniqueVisitors($events),
            'performance_metrics' => $this->aggregatePerformanceMetrics($events),
            'engagement_stats' => $this->calculateEngagementStats($events)
        ];
    }

    private function getEventsFromRedis(int $start, int $end): array {
        $events = [];
        $queueLength = $this->redis->lLen('analytics_events');
        
        for ($i = 0; $i < $queueLength; $i++) {
            $event = json_decode($this->redis->lIndex('analytics_events', $i), true);
            if ($event && $event['timestamp'] >= $start && $event['timestamp'] <= $end) {
                $events[] = $event;
            }
        }

        return $events;
    }

    private function countEvents(array $events, string $type): int {
        return count(array_filter($events, fn($e) => $e['type'] === $type));
    }

    private function countUniqueVisitors(array $events): int {
        $sessions = array_unique(array_column($events, 'session_id'));
        return count(array_filter($sessions));
    }

    private function aggregatePerformanceMetrics(array $events): array {
        $metrics = [];
        foreach ($events as $event) {
            if ($event['type'] === 'performance_metric') {
                $metric = $event['payload']['metric'];
                $metrics[$metric][] = $event['payload']['value'];
            }
        }

        $results = [];
        foreach ($metrics as $name => $values) {
            $results[$name] = [
                'count' => count($values),
                'avg' => array_sum($values) / count($values),
                'min' => min($values),
                'max' => max($values)
            ];
        }

        return $results;
    }

    private function calculateEngagementStats(array $events): array {
        $stats = [];
        foreach ($events as $event) {
            if ($event['type'] === 'user_engagement') {
                $action = $event['payload']['action'];
                $stats[$action] = ($stats[$action] ?? 0) + 1;
            }
        }
        return $stats;
    }
}
