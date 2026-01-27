<?php
/**
 * Analytics Model
 * Handles all database operations for analytics data
 * Framework-free: pure PDO with prepared statements
 */

class AnalyticsModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Record a page view
     */
    public function recordPageView(array $data): int
    {
        $sql = "INSERT INTO page_views
                (page_url, page_title, referrer, user_agent, ip_address, session_id,
                 user_id, tenant_id, device_type, browser, os, country_code, duration_seconds)
                VALUES
                (:page_url, :page_title, :referrer, :user_agent, :ip_address, :session_id,
                 :user_id, :tenant_id, :device_type, :browser, :os, :country_code, :duration_seconds)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':page_url' => $data['page_url'] ?? '',
            ':page_title' => $data['page_title'] ?? null,
            ':referrer' => $data['referrer'] ?? null,
            ':user_agent' => $data['user_agent'] ?? null,
            ':ip_address' => $data['ip_address'] ?? null,
            ':session_id' => $data['session_id'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':tenant_id' => $data['tenant_id'] ?? null,
            ':device_type' => $data['device_type'] ?? 'unknown',
            ':browser' => $data['browser'] ?? null,
            ':os' => $data['os'] ?? null,
            ':country_code' => $data['country_code'] ?? null,
            ':duration_seconds' => $data['duration_seconds'] ?? 0
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Record a custom analytics event
     */
    public function recordEvent(array $data): int
    {
        $sql = "INSERT INTO analytics_events
                (event_type, event_name, event_data, page_url, session_id,
                 user_id, tenant_id, ip_address, user_agent)
                VALUES
                (:event_type, :event_name, :event_data, :page_url, :session_id,
                 :user_id, :tenant_id, :ip_address, :user_agent)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':event_type' => $data['event_type'] ?? 'custom',
            ':event_name' => $data['event_name'] ?? '',
            ':event_data' => isset($data['event_data']) ? json_encode($data['event_data']) : null,
            ':page_url' => $data['page_url'] ?? null,
            ':session_id' => $data['session_id'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':tenant_id' => $data['tenant_id'] ?? null,
            ':ip_address' => $data['ip_address'] ?? null,
            ':user_agent' => $data['user_agent'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get page views for a date range
     */
    public function getPageViews(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $sql = "SELECT * FROM page_views
                WHERE created_at BETWEEN :start_date AND :end_date";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get page view count by date
     */
    public function getPageViewsByDate(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $sql = "SELECT DATE(created_at) as view_date, COUNT(*) as view_count,
                       COUNT(DISTINCT session_id) as unique_sessions,
                       COUNT(DISTINCT ip_address) as unique_visitors
                FROM page_views
                WHERE created_at BETWEEN :start_date AND :end_date";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " GROUP BY DATE(created_at) ORDER BY view_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top pages by views
     */
    public function getTopPages(int $limit = 10, string $startDate = null, string $endDate = null, ?int $tenantId = null): array
    {
        $sql = "SELECT page_url, page_title, COUNT(*) as view_count,
                       COUNT(DISTINCT session_id) as unique_sessions,
                       AVG(duration_seconds) as avg_duration
                FROM page_views WHERE 1=1";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " GROUP BY page_url, page_title ORDER BY view_count DESC LIMIT " . (int) $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get top referrers
     */
    public function getTopReferrers(int $limit = 10, string $startDate = null, string $endDate = null, ?int $tenantId = null): array
    {
        $sql = "SELECT referrer, COUNT(*) as count
                FROM page_views
                WHERE referrer IS NOT NULL AND referrer != ''";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " GROUP BY referrer ORDER BY count DESC LIMIT " . (int) $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get device breakdown
     */
    public function getDeviceBreakdown(string $startDate = null, string $endDate = null, ?int $tenantId = null): array
    {
        $sql = "SELECT device_type, COUNT(*) as count
                FROM page_views WHERE 1=1";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " GROUP BY device_type ORDER BY count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get browser breakdown
     */
    public function getBrowserBreakdown(string $startDate = null, string $endDate = null, ?int $tenantId = null): array
    {
        $sql = "SELECT browser, COUNT(*) as count
                FROM page_views WHERE browser IS NOT NULL";
        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " GROUP BY browser ORDER BY count DESC LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStats(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $sql = "SELECT
                    COUNT(*) as total_views,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(DISTINCT session_id) as total_sessions,
                    AVG(duration_seconds) as avg_duration,
                    SUM(CASE WHEN device_type = 'desktop' THEN 1 ELSE 0 END) as desktop_views,
                    SUM(CASE WHEN device_type = 'mobile' THEN 1 ELSE 0 END) as mobile_views,
                    SUM(CASE WHEN device_type = 'tablet' THEN 1 ELSE 0 END) as tablet_views
                FROM page_views
                WHERE created_at BETWEEN :start_date AND :end_date";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get events by type
     */
    public function getEventsByType(string $eventType, string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $sql = "SELECT * FROM analytics_events
                WHERE event_type = :event_type
                AND created_at BETWEEN :start_date AND :end_date";
        $params = [
            ':event_type' => $eventType,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get event counts grouped by name
     */
    public function getEventCounts(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $sql = "SELECT event_type, event_name, COUNT(*) as count
                FROM analytics_events
                WHERE created_at BETWEEN :start_date AND :end_date";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " GROUP BY event_type, event_name ORDER BY count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save or update daily stats
     */
    public function saveDailyStats(string $date, array $stats, ?int $tenantId = null): bool
    {
        $sql = "INSERT INTO analytics_daily_stats
                (stat_date, tenant_id, total_views, unique_visitors, total_sessions,
                 avg_duration, bounce_rate, desktop_views, mobile_views, tablet_views,
                 top_pages, top_referrers)
                VALUES
                (:stat_date, :tenant_id, :total_views, :unique_visitors, :total_sessions,
                 :avg_duration, :bounce_rate, :desktop_views, :mobile_views, :tablet_views,
                 :top_pages, :top_referrers)
                ON DUPLICATE KEY UPDATE
                    total_views = VALUES(total_views),
                    unique_visitors = VALUES(unique_visitors),
                    total_sessions = VALUES(total_sessions),
                    avg_duration = VALUES(avg_duration),
                    bounce_rate = VALUES(bounce_rate),
                    desktop_views = VALUES(desktop_views),
                    mobile_views = VALUES(mobile_views),
                    tablet_views = VALUES(tablet_views),
                    top_pages = VALUES(top_pages),
                    top_referrers = VALUES(top_referrers),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':stat_date' => $date,
            ':tenant_id' => $tenantId,
            ':total_views' => $stats['total_views'] ?? 0,
            ':unique_visitors' => $stats['unique_visitors'] ?? 0,
            ':total_sessions' => $stats['total_sessions'] ?? 0,
            ':avg_duration' => $stats['avg_duration'] ?? 0,
            ':bounce_rate' => $stats['bounce_rate'] ?? 0,
            ':desktop_views' => $stats['desktop_views'] ?? 0,
            ':mobile_views' => $stats['mobile_views'] ?? 0,
            ':tablet_views' => $stats['tablet_views'] ?? 0,
            ':top_pages' => isset($stats['top_pages']) ? json_encode($stats['top_pages']) : null,
            ':top_referrers' => isset($stats['top_referrers']) ? json_encode($stats['top_referrers']) : null
        ]);
    }

    /**
     * Get daily stats for a date range
     */
    public function getDailyStats(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $sql = "SELECT * FROM analytics_daily_stats
                WHERE stat_date BETWEEN :start_date AND :end_date";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $sql .= " ORDER BY stat_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update content stats
     */
    public function updateContentStats(int $contentId, ?int $tenantId = null): bool
    {
        $sql = "INSERT INTO analytics_content_stats
                (content_id, tenant_id, total_views, unique_views, avg_duration, last_viewed_at)
                SELECT
                    :content_id,
                    :tenant_id,
                    COUNT(*),
                    COUNT(DISTINCT session_id),
                    AVG(duration_seconds),
                    MAX(created_at)
                FROM page_views
                WHERE page_url LIKE CONCAT('%/content/', :content_id_like, '%')";

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id_filter";
        }

        $sql .= " ON DUPLICATE KEY UPDATE
                    total_views = VALUES(total_views),
                    unique_views = VALUES(unique_views),
                    avg_duration = VALUES(avg_duration),
                    last_viewed_at = VALUES(last_viewed_at),
                    updated_at = CURRENT_TIMESTAMP";

        $params = [
            ':content_id' => $contentId,
            ':tenant_id' => $tenantId,
            ':content_id_like' => $contentId
        ];

        if ($tenantId !== null) {
            $params[':tenant_id_filter'] = $tenantId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get real-time active visitors (last 5 minutes)
     */
    public function getActiveVisitors(?int $tenantId = null): int
    {
        $sql = "SELECT COUNT(DISTINCT session_id) as active_count
                FROM page_views
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $params = [];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['active_count'] ?? 0);
    }

    /**
     * Clean up old page view data
     */
    public function cleanupOldData(int $daysToKeep = 90): int
    {
        $sql = "DELETE FROM page_views WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':days' => $daysToKeep]);
        return $stmt->rowCount();
    }
}
