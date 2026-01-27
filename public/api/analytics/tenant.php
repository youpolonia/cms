<?php
require_once __DIR__ . '/../../../core/analyticsaggregator.php';
require_once __DIR__ . '/../../../api-gateway/middlewares/authmiddleware.php';

header('Content-Type: application/json');

// Authenticate request
$auth = new AuthMiddleware();
$tenantId = $auth->getTenantId();
$userId = $auth->getUserId();

// Parse request
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoint = str_replace('/api/analytics/tenant/', '', $path);

try {
    switch ($endpoint) {
        case 'content':
            $contentId = $_GET['content_id'] ?? null;
            $period = $_GET['period'] ?? 'daily';
            $startDate = isset($_GET['start_date']) ? new DateTime($_GET['start_date']) : null;
            $endDate = isset($_GET['end_date']) ? new DateTime($_GET['end_date']) : null;

            if (!$contentId) {
                throw new InvalidArgumentException('Content ID is required');
            }

            $metrics = AnalyticsAggregator::aggregateContentMetrics(
                $tenantId,
                $contentId,
                $period,
                $startDate,
                $endDate
            );
            break;

        case 'user':
            $period = $_GET['period'] ?? 'daily';
            $startDate = isset($_GET['start_date']) ? new DateTime($_GET['start_date']) : null;
            $endDate = isset($_GET['end_date']) ? new DateTime($_GET['end_date']) : null;

            $metrics = AnalyticsAggregator::aggregateUserMetrics(
                $tenantId,
                $userId,
                $period,
                $startDate,
                $endDate
            );
            break;

        default:
            throw new InvalidArgumentException('Invalid endpoint');
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $metrics
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
