<?php

class DashboardVisualizer {
    private static ?DashboardVisualizer $instance = null;
    private $analyticsCollector;
    private $cache = [];

    private function __construct() {
        $this->analyticsCollector = AnalyticsCollector::getInstance();
    }

    public static function getInstance(): DashboardVisualizer {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getEventSummary(string $tenantId, int $days = 7): array {
        $cacheKey = "summary_{$tenantId}_{$days}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $events = $this->analyticsCollector->getEvents($tenantId, 1000);
        $summary = [
            'total_events' => count($events),
            'event_types' => [],
            'daily_counts' => []
        ];

        $now = new DateTime();
        $dateRange = new DatePeriod(
            (clone $now)->sub(new DateInterval("P{$days}D")),
            new DateInterval('P1D'),
            $now
        );

        // Initialize daily counts
        foreach ($dateRange as $date) {
            $summary['daily_counts'][$date->format('Y-m-d')] = 0;
        }

        // Process events
        foreach ($events as $event) {
            $eventDate = (new DateTime($event['timestamp']))->format('Y-m-d');
            $summary['event_types'][$event['event_type']] = 
                ($summary['event_types'][$event['event_type']] ?? 0) + 1;
            
            if (isset($summary['daily_counts'][$eventDate])) {
                $summary['daily_counts'][$eventDate]++;
            }
        }

        $this->cache[$cacheKey] = $summary;
        return $summary;
    }

    public function clearCache(): void {
        $this->cache = [];
    }
}
