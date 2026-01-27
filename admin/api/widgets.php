<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();
require_once CMS_ROOT . '/includes/filecache.php';

header('Content-Type: application/json');

$widgetType = basename($_SERVER['REQUEST_URI']);
$cacheKey = "widget_{$widgetType}";
$cache = new FileCache();
$cacheTtl = 300; // 5 minutes

// Check if we have cached data
if ($cachedData = $cache->get($cacheKey)) {
    echo json_encode($cachedData);
    exit;
}

// Generate fresh data based on widget type
$data = [];
switch ($widgetType) {
    case 'client-activity':
        require_once CMS_ROOT . '/includes/middleware/ActivityTracker.php';
        $activityTracker = new ActivityTracker();
        $data = [
            'html' => $activityTracker->getRecentClientActivityHtml(10)
        ];
        break;

    case 'client-status':
        require_once CMS_ROOT . '/models/Client.php';
        $statusCounts = Client::getStatusCounts();
        $data = [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts),
            'colors' => ['#36a2eb', '#ff6384', '#ffcd56', '#4bc0c0', '#9966ff']
        ];
        break;

    case 'ai-insights':
        require_once CMS_ROOT . '/includes/ai/ContentGenerator.php';
        $ai = new ContentGenerator();
        $data = [
            'html' => $ai->generateDashboardInsights()
        ];
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid widget type']);
        exit;
}

// Cache the data
$cache->set($cacheKey, $data, $cacheTtl);

echo json_encode($data);
