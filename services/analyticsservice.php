<?php
/**
 * Analytics Service
 * Business logic layer for analytics operations
 * Framework-free: pure PHP with no external dependencies
 */

require_once __DIR__ . '/../models/analyticsmodel.php';

class AnalyticsService
{
    private AnalyticsModel $model;
    private ?int $tenantId;

    public function __construct(PDO $db, ?int $tenantId = null)
    {
        $this->model = new AnalyticsModel($db);
        $this->tenantId = $tenantId;
    }

    /**
     * Track a page view with user agent parsing
     */
    public function trackPageView(string $pageUrl, ?string $pageTitle = null): int
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $deviceInfo = $this->parseUserAgent($userAgent);

        $data = [
            'page_url' => $pageUrl,
            'page_title' => $pageTitle,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'user_agent' => $userAgent,
            'ip_address' => $this->getClientIp(),
            'session_id' => session_id() ?: null,
            'user_id' => $_SESSION['user_id'] ?? null,
            'tenant_id' => $this->tenantId,
            'device_type' => $deviceInfo['device'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'country_code' => null,
            'duration_seconds' => 0
        ];

        return $this->model->recordPageView($data);
    }

    /**
     * Track a custom event
     */
    public function trackEvent(string $eventType, string $eventName, array $eventData = []): int
    {
        $data = [
            'event_type' => $eventType,
            'event_name' => $eventName,
            'event_data' => $eventData,
            'page_url' => $_SERVER['REQUEST_URI'] ?? null,
            'session_id' => session_id() ?: null,
            'user_id' => $_SESSION['user_id'] ?? null,
            'tenant_id' => $this->tenantId,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        return $this->model->recordEvent($data);
    }

    /**
     * Get dashboard overview data
     */
    public function getDashboardData(string $period = '7d'): array
    {
        $dates = $this->getDateRange($period);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Get summary stats
        $summary = $this->model->getSummaryStats($startDate, $endDate, $this->tenantId);

        // Get daily breakdown
        $dailyStats = $this->model->getPageViewsByDate($startDate, $endDate, $this->tenantId);

        // Get top pages
        $topPages = $this->model->getTopPages(10, $startDate, $endDate, $this->tenantId);

        // Get device breakdown
        $devices = $this->model->getDeviceBreakdown($startDate, $endDate, $this->tenantId);

        // Get browser breakdown
        $browsers = $this->model->getBrowserBreakdown($startDate, $endDate, $this->tenantId);

        // Get top referrers
        $referrers = $this->model->getTopReferrers(10, $startDate, $endDate, $this->tenantId);

        // Get active visitors
        $activeVisitors = $this->model->getActiveVisitors($this->tenantId);

        // Get event counts
        $events = $this->model->getEventCounts($startDate, $endDate, $this->tenantId);

        // Calculate comparison with previous period
        $comparison = $this->calculatePeriodComparison($period);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'label' => $this->getPeriodLabel($period)
            ],
            'summary' => [
                'total_views' => (int) ($summary['total_views'] ?? 0),
                'unique_visitors' => (int) ($summary['unique_visitors'] ?? 0),
                'total_sessions' => (int) ($summary['total_sessions'] ?? 0),
                'avg_duration' => round((float) ($summary['avg_duration'] ?? 0), 1),
                'active_visitors' => $activeVisitors
            ],
            'comparison' => $comparison,
            'daily_stats' => $dailyStats,
            'top_pages' => $topPages,
            'devices' => $this->formatDeviceData($devices),
            'browsers' => $browsers,
            'referrers' => $referrers,
            'events' => $events
        ];
    }

    /**
     * Get real-time stats
     */
    public function getRealTimeStats(): array
    {
        $now = date('Y-m-d H:i:s');
        $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $activeVisitors = $this->model->getActiveVisitors($this->tenantId);
        $recentPages = $this->model->getTopPages(5, $fiveMinutesAgo, $now, $this->tenantId);
        $hourlyViews = $this->model->getSummaryStats($oneHourAgo, $now, $this->tenantId);

        return [
            'active_visitors' => $activeVisitors,
            'views_last_hour' => (int) ($hourlyViews['total_views'] ?? 0),
            'recent_pages' => $recentPages,
            'timestamp' => $now
        ];
    }

    /**
     * Generate analytics report
     */
    public function generateReport(string $startDate, string $endDate, string $format = 'json'): array
    {
        $summary = $this->model->getSummaryStats($startDate, $endDate, $this->tenantId);
        $dailyStats = $this->model->getDailyStats($startDate, $endDate, $this->tenantId);
        $topPages = $this->model->getTopPages(20, $startDate, $endDate, $this->tenantId);
        $devices = $this->model->getDeviceBreakdown($startDate, $endDate, $this->tenantId);
        $browsers = $this->model->getBrowserBreakdown($startDate, $endDate, $this->tenantId);
        $referrers = $this->model->getTopReferrers(20, $startDate, $endDate, $this->tenantId);
        $events = $this->model->getEventCounts($startDate, $endDate, $this->tenantId);

        return [
            'report_generated' => date('Y-m-d H:i:s'),
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'summary' => $summary,
            'daily_breakdown' => $dailyStats,
            'top_pages' => $topPages,
            'device_breakdown' => $devices,
            'browser_breakdown' => $browsers,
            'top_referrers' => $referrers,
            'event_breakdown' => $events
        ];
    }

    /**
     * Aggregate daily stats (for cron job)
     */
    public function aggregateDailyStats(string $date = null): bool
    {
        $date = $date ?? date('Y-m-d', strtotime('-1 day'));
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';

        $stats = $this->model->getSummaryStats($startDate, $endDate, $this->tenantId);
        $topPages = $this->model->getTopPages(10, $startDate, $endDate, $this->tenantId);
        $topReferrers = $this->model->getTopReferrers(10, $startDate, $endDate, $this->tenantId);

        // Calculate bounce rate (single page sessions / total sessions)
        $bounceRate = $this->calculateBounceRate($startDate, $endDate);

        $aggregatedStats = [
            'total_views' => (int) ($stats['total_views'] ?? 0),
            'unique_visitors' => (int) ($stats['unique_visitors'] ?? 0),
            'total_sessions' => (int) ($stats['total_sessions'] ?? 0),
            'avg_duration' => round((float) ($stats['avg_duration'] ?? 0), 2),
            'bounce_rate' => $bounceRate,
            'desktop_views' => (int) ($stats['desktop_views'] ?? 0),
            'mobile_views' => (int) ($stats['mobile_views'] ?? 0),
            'tablet_views' => (int) ($stats['tablet_views'] ?? 0),
            'top_pages' => $topPages,
            'top_referrers' => $topReferrers
        ];

        return $this->model->saveDailyStats($date, $aggregatedStats, $this->tenantId);
    }

    /**
     * Clean up old analytics data
     */
    public function cleanup(int $daysToKeep = 90): int
    {
        return $this->model->cleanupOldData($daysToKeep);
    }

    /**
     * Parse user agent to extract device, browser, and OS info
     */
    private function parseUserAgent(string $userAgent): array
    {
        $result = [
            'device' => 'unknown',
            'browser' => null,
            'os' => null
        ];

        if (empty($userAgent)) {
            return $result;
        }

        // Detect device type
        if (preg_match('/bot|crawl|spider|slurp|googlebot|bingbot/i', $userAgent)) {
            $result['device'] = 'bot';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $userAgent)) {
            $result['device'] = 'mobile';
        } elseif (preg_match('/tablet|ipad|kindle|silk|playbook/i', $userAgent)) {
            $result['device'] = 'tablet';
        } else {
            $result['device'] = 'desktop';
        }

        // Detect browser
        if (preg_match('/Firefox\/[\d.]+/i', $userAgent)) {
            $result['browser'] = 'Firefox';
        } elseif (preg_match('/Edg\/[\d.]+/i', $userAgent)) {
            $result['browser'] = 'Edge';
        } elseif (preg_match('/Chrome\/[\d.]+/i', $userAgent) && !preg_match('/Edg/i', $userAgent)) {
            $result['browser'] = 'Chrome';
        } elseif (preg_match('/Safari\/[\d.]+/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $result['browser'] = 'Safari';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $result['browser'] = 'Internet Explorer';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $result['browser'] = 'Opera';
        }

        // Detect OS
        if (preg_match('/Windows NT 10/i', $userAgent)) {
            $result['os'] = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.3/i', $userAgent)) {
            $result['os'] = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6\.2/i', $userAgent)) {
            $result['os'] = 'Windows 8';
        } elseif (preg_match('/Windows NT 6\.1/i', $userAgent)) {
            $result['os'] = 'Windows 7';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $result['os'] = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $result['os'] = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $result['os'] = 'Linux';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $result['os'] = 'iOS';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $result['os'] = 'Android';
        }

        return $result;
    }

    /**
     * Get client IP address (handles proxies)
     */
    private function getClientIp(): ?string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    /**
     * Get date range from period string
     */
    private function getDateRange(string $period): array
    {
        $end = date('Y-m-d 23:59:59');

        switch ($period) {
            case '24h':
                $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
                break;
            case '7d':
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case '30d':
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case '90d':
                $start = date('Y-m-d 00:00:00', strtotime('-90 days'));
                break;
            case 'year':
                $start = date('Y-01-01 00:00:00');
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get human-readable period label
     */
    private function getPeriodLabel(string $period): string
    {
        return match($period) {
            '24h' => 'Last 24 Hours',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            '90d' => 'Last 90 Days',
            'year' => 'This Year',
            default => 'Last 7 Days'
        };
    }

    /**
     * Calculate period comparison
     */
    private function calculatePeriodComparison(string $period): array
    {
        $currentDates = $this->getDateRange($period);
        $currentStats = $this->model->getSummaryStats(
            $currentDates['start'],
            $currentDates['end'],
            $this->tenantId
        );

        // Calculate previous period
        $periodDays = match($period) {
            '24h' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'year' => 365,
            default => 7
        };

        $prevEnd = date('Y-m-d H:i:s', strtotime($currentDates['start'] . ' -1 second'));
        $prevStart = date('Y-m-d H:i:s', strtotime($prevEnd . ' -' . $periodDays . ' days'));

        $prevStats = $this->model->getSummaryStats($prevStart, $prevEnd, $this->tenantId);

        return [
            'views_change' => $this->calculatePercentChange(
                (int) ($prevStats['total_views'] ?? 0),
                (int) ($currentStats['total_views'] ?? 0)
            ),
            'visitors_change' => $this->calculatePercentChange(
                (int) ($prevStats['unique_visitors'] ?? 0),
                (int) ($currentStats['unique_visitors'] ?? 0)
            ),
            'sessions_change' => $this->calculatePercentChange(
                (int) ($prevStats['total_sessions'] ?? 0),
                (int) ($currentStats['total_sessions'] ?? 0)
            ),
            'duration_change' => $this->calculatePercentChange(
                (float) ($prevStats['avg_duration'] ?? 0),
                (float) ($currentStats['avg_duration'] ?? 0)
            )
        ];
    }

    /**
     * Calculate percentage change
     */
    private function calculatePercentChange(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Calculate bounce rate for a date range
     */
    private function calculateBounceRate(string $startDate, string $endDate): float
    {
        // Simplified bounce rate calculation
        // In a full implementation, you'd track session page counts
        return 0.0;
    }

    /**
     * Format device data for charts
     */
    private function formatDeviceData(array $devices): array
    {
        $total = array_sum(array_column($devices, 'count'));
        $formatted = [];

        foreach ($devices as $device) {
            $formatted[] = [
                'device_type' => $device['device_type'],
                'count' => (int) $device['count'],
                'percentage' => $total > 0 ? round(($device['count'] / $total) * 100, 1) : 0
            ];
        }

        return $formatted;
    }
}
