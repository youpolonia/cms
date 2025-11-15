<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Analytics Service - Core analytics processing
 */
class AnalyticsService {
    /**
     * Get version comparison metrics
     */
    public static function getVersionComparison(int $versionId1, int $versionId2): array {
        $pdo = \core\Database::connection();
        
        $version1 = $db->query("SELECT * FROM content_performance_enhanced 
                              WHERE version_id = ? ORDER BY created_at DESC LIMIT 1", [$versionId1]);
        $version2 = $db->query("SELECT * FROM content_performance_enhanced 
                              WHERE version_id = ? ORDER BY created_at DESC LIMIT 1", [$versionId2]);

        if (!$version1 || !$version2) {
            throw new Exception("One or both versions not found");
        }

        return [
            'time_on_page_diff' => $version1['time_on_page'] - $version2['time_on_page'],
            'scroll_depth_diff' => $version1['scroll_depth'] - $version2['scroll_depth'],
            'engagement_diff' => $version1['engagement_score'] - $version2['engagement_score'],
            'version1_metrics' => $version1,
            'version2_metrics' => $version2
        ];
    }

    /**
     * Track content engagement
     */
    public static function trackEngagement(int $versionId, array $metrics): bool {
        $pdo = \core\Database::connection();
        
        return $db->insert('content_performance_enhanced', [
            'version_id' => $versionId,
            'time_on_page' => $metrics['time_on_page'] ?? 0,
            'scroll_depth' => $metrics['scroll_depth'] ?? 0,
            'engagement_score' => self::calculateEngagementScore($metrics)
        ]);
    }

    private static function calculateEngagementScore(array $metrics): float {
        // Weighted calculation of engagement score
        return ($metrics['time_on_page'] * 0.4) +
               ($metrics['scroll_depth'] * 0.5) +
               ($metrics['interactions'] * 0.1);
    }

    /**
     * Track page view
     */
    public static function trackPageView(array $data): bool {
        $pdo = \core\Database::connection();
        return $db->insert('analytics_page_views', [
            'session_id' => $data['session_id'],
            'page_url' => $data['page_url'],
            'referrer_url' => $data['referrer_url'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'device_type' => $data['device_type'] ?? null
        ]);
    }

    /**
     * Track click event
     */
    public static function trackClick(array $data): bool {
        $pdo = \core\Database::connection();
        return $db->insert('analytics_click_events', [
            'session_id' => $data['session_id'],
            'element_id' => $data['element_id'] ?? null,
            'element_class' => $data['element_class'] ?? null,
            'element_type' => $data['element_type'] ?? null,
            'page_url' => $data['page_url']
        ]);
    }

    /**
     * Log performance metric
     */
    public static function logPerformance(array $data): bool {
        $pdo = \core\Database::connection();
        return $db->insert('analytics_performance_metrics', [
            'endpoint' => $data['endpoint'],
            'response_time' => $data['response_time'],
            'status_code' => $data['status_code'] ?? null,
            'error_message' => $data['error_message'] ?? null
        ]);
    }

    /**
     * Get aggregated metrics for reporting
     */
    public static function getAggregatedMetrics(string $period): array {
        $pdo = \core\Database::connection();
        $query = "SELECT
            COUNT(*) as page_views,
            COUNT(DISTINCT session_id) as unique_visitors,
            AVG(response_time) as avg_response_time
            FROM analytics_page_views p
            LEFT JOIN analytics_performance_metrics m ON p.page_url = m.endpoint
            WHERE p.view_time >= ?";
        
        $date = match($period) {
            'day' => date('Y-m-d 00:00:00'),
            'week' => date('Y-m-d 00:00:00', strtotime('-7 days')),
            'month' => date('Y-m-d 00:00:00', strtotime('-30 days')),
            default => date('Y-m-d 00:00:00', strtotime('-7 days'))
        };

        return $db->query($query, [$date])->fetch();
    }
}
