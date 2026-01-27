<?php
function getTenantList(): array {
    global $db;
    
    try {
        $stmt = $db->query("SELECT id, name FROM tenants ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Tenant list fetch failed: " . $e->getMessage());
        return [];
    }
}

function getDashboardMetrics(): array {
    global $db;
    $tenantFilter = $_SESSION['analytics_tenant'] ?? '';
    $dateRange = getDateRange();

    try {
        $sql = "SELECT 
            COUNT(DISTINCT user_id) as unique_visitors,
            COUNT(*) as page_views,
            ROUND(AVG(time_on_page), 2) as avg_time
        FROM analytics_page_views
        WHERE created_at BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $dateRange['start'],
            ':end_date' => $dateRange['end']
        ];

        if ($tenantFilter) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantFilter;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log("Dashboard metrics fetch failed: " . $e->getMessage());
        return [];
    }
}

function getTimeSeriesData(): array {
    global $db;
    $tenantFilter = $_SESSION['analytics_tenant'] ?? '';
    $dateRange = getDateRange();

    try {
        $sql = "SELECT 
            DATE(created_at) as date,
            COUNT(DISTINCT user_id) as unique_visitors,
            COUNT(*) as page_views
        FROM analytics_page_views
        WHERE created_at BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $dateRange['start'],
            ':end_date' => $dateRange['end']
        ];

        if ($tenantFilter) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantFilter;
        }

        $sql .= " GROUP BY DATE(created_at) ORDER BY date";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Time series data fetch failed: " . $e->getMessage());
        return [];
    }
}

function getEngagementData(): array {
    global $db;
    $tenantFilter = $_SESSION['analytics_tenant'] ?? '';
    $dateRange = getDateRange();

    try {
        $sql = "SELECT 
            page_path,
            COUNT(*) as views,
            ROUND(AVG(time_on_page), 2) as avg_time
        FROM analytics_page_views
        WHERE created_at BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $dateRange['start'],
            ':end_date' => $dateRange['end']
        ];

        if ($tenantFilter) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantFilter;
        }

        $sql .= " GROUP BY page_path ORDER BY views DESC LIMIT 10";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Engagement data fetch failed: " . $e->getMessage());
        return [];
    }
}

function getDateRange(): array {
    $range = $_SESSION['analytics_range'] ?? 'week';
    $now = new DateTime();

    switch ($range) {
        case 'today':
            $start = (clone $now)->setTime(0, 0);
            break;
        case 'week':
            $start = (clone $now)->modify('-7 days');
            break;
        case 'month':
            $start = (clone $now)->modify('-30 days');
            break;
        case 'custom':
            $start = new DateTime($_SESSION['analytics_custom_start'] ?? 'now');
            $end = new DateTime($_SESSION['analytics_custom_end'] ?? 'now');
            break;
        default:
            $start = (clone $now)->modify('-7 days');
    }

    if ($range !== 'custom') {
        $end = clone $now;
    }

    return [
        'start' => $start->format('Y-m-d H:i:s'),
        'end' => $end->format('Y-m-d H:i:s')
    ];
}
