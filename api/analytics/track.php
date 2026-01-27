<?php
/**
 * Analytics Tracking API Endpoint
 * Receives page views and events from frontend JavaScript
 * Framework-free: pure PHP
 */

define('CMS_ROOT', dirname(dirname(__DIR__)));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/services/analyticsservice.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Get action type
$action = $input['action'] ?? 'page_view';

try {
    $db = \core\Database::connection();
    $tenantId = $input['tenant_id'] ?? null;
    $service = new AnalyticsService($db, $tenantId);

    switch ($action) {
        case 'page_view':
            if (empty($input['page_url'])) {
                http_response_code(400);
                echo json_encode(['error' => 'page_url is required']);
                exit;
            }

            $viewId = $service->trackPageView(
                $input['page_url'],
                $input['page_title'] ?? null
            );

            echo json_encode([
                'success' => true,
                'view_id' => $viewId
            ]);
            break;

        case 'event':
            if (empty($input['event_type']) || empty($input['event_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'event_type and event_name are required']);
                exit;
            }

            $eventId = $service->trackEvent(
                $input['event_type'],
                $input['event_name'],
                $input['event_data'] ?? []
            );

            echo json_encode([
                'success' => true,
                'event_id' => $eventId
            ]);
            break;

        case 'duration':
            // Update page view duration (for when user leaves page)
            if (empty($input['view_id']) || !isset($input['duration'])) {
                http_response_code(400);
                echo json_encode(['error' => 'view_id and duration are required']);
                exit;
            }

            $stmt = $db->prepare("UPDATE page_views SET duration_seconds = ? WHERE id = ?");
            $stmt->execute([(int) $input['duration'], (int) $input['view_id']]);

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action: ' . $action]);
    }
} catch (PDOException $e) {
    error_log('Analytics tracking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    error_log('Analytics tracking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
