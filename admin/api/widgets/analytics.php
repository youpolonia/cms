<?php
require_once __DIR__ . '/../../../config/bootstrap.php';

header('Content-Type: application/json');

$tenantId = get_current_tenant_id();
$cacheKey = "analytics_widget_{$tenantId}";
$cache = new FileCache();

if ($data = $cache->get($cacheKey)) {
    echo json_encode($data);
    exit;
}

$analyticsData = AnalyticsRepository::getSummaryForTenant($tenantId);
$data = [
    'metrics' => [
        'pageViews' => $analyticsData['page_views'],
        'uniqueVisitors' => $analyticsData['unique_visitors'],
        'avgTime' => $analyticsData['avg_time_on_page']
    ],
    'trends' => AnalyticsRepository::getWeeklyTrends($tenantId)
];

$cache->set($cacheKey, $data, 300); // Cache for 5 minutes
echo json_encode($data);
