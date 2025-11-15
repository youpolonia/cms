<?php
declare(strict_types=1);

class AnalyticsAggregator {
    private const AGGREGATION_PERIODS = ['daily', 'weekly', 'monthly'];
    private const CONTENT_METRICS = ['views', 'avg_time', 'completion_rate'];
    private const USER_METRICS = ['sessions', 'page_views', 'return_rate'];

    public static function aggregateContentMetrics(
        string $tenantId,
        string $contentId,
        string $period = 'daily',
        ?DateTimeInterface $startDate = null,
        ?DateTimeInterface $endDate = null
    ): array {
        self::validatePeriod($period);
        $dateRange = self::prepareDateRange($startDate, $endDate, $period);

        $query = "SELECT 
            COUNT(*) as views,
            AVG(JSON_EXTRACT(event_data, '$.duration')) as avg_time,
            AVG(JSON_EXTRACT(event_data, '$.completion')) as completion_rate
            FROM tenant_analytics_events
            WHERE tenant_id = :tenant_id
            AND event_type = 'content_view'
            AND JSON_EXTRACT(event_data, '$.content_id') = :content_id
            {$dateRange['where']}";

        $params = [
            'tenant_id' => $tenantId,
            'content_id' => $contentId
        ] + $dateRange['params'];

        return DB::query($query, $params);
    }

    public static function aggregateUserMetrics(
        string $tenantId,
        string $userId,
        string $period = 'daily',
        ?DateTimeInterface $startDate = null,
        ?DateTimeInterface $endDate = null
    ): array {
        self::validatePeriod($period);
        $dateRange = self::prepareDateRange($startDate, $endDate, $period);

        $query = "SELECT
            COUNT(DISTINCT session_id) as sessions,
            COUNT(*) as page_views,
            SUM(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as return_rate
            FROM tenant_analytics_events
            WHERE tenant_id = :tenant_id
            AND user_id = :user_id
            {$dateRange['where']}
            GROUP BY " . self::getGroupByClause($period);

        $params = [
            'tenant_id' => $tenantId,
            'user_id' => $userId
        ] + $dateRange['params'];

        try {
            return DB::query($query, $params);
        } catch (Exception $e) {
            error_log("Analytics aggregation failed: " . $e->getMessage());
            return [
                'sessions' => 0,
                'page_views' => 0,
                'return_rate' => 0
            ];
        }
    }

    private static function validatePeriod(string $period): void {
        if (!in_array($period, self::AGGREGATION_PERIODS)) {
            throw new InvalidArgumentException("Invalid period: {$period}");
        }
    }

    private static function prepareDateRange(
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
        string $period
    ): array {
        $where = '';
        $params = [];

        if ($startDate) {
            $where .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate->format('Y-m-d H:i:s');
        }

        if ($endDate) {
            $where .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate->format('Y-m-d H:i:s');
        }

        return ['where' => $where, 'params' => $params];
    }

    private static function getGroupByClause(string $period): string {
        return match($period) {
            'daily' => "DATE(created_at)",
            'weekly' => "YEARWEEK(created_at)",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
        };
    }
}
