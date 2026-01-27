<?php
/**
 * Analytics API Controller
 */
class AnalyticsController {
    /**
     * Get version comparison data
     */
    public static function getComparison(int $version1, int $version2): array {
        return AnalyticsService::getVersionComparison($version1, $version2);
    }

    /**
     * Record engagement metrics
     */
    public static function recordEngagement(array $data): bool {
        return AnalyticsService::trackEngagement(
            $data['version_id'],
            $data['metrics']
        );
    }

    /**
     * Record page view
     */
    public static function recordPageView(array $data): bool {
        return AnalyticsService::trackPageView($data);
    }

    /**
     * Record click event
     */
    public static function recordClick(array $data): bool {
        return AnalyticsService::trackClick($data);
    }

    /**
     * Log performance metric
     */
    public static function logPerformance(array $data): bool {
        return AnalyticsService::logPerformance($data);
    }

    /**
     * Get analytics report
     */
    public static function getReport(string $period = 'week'): array {
        return [
            'metrics' => AnalyticsService::getAggregatedMetrics($period),
            'period' => $period,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
