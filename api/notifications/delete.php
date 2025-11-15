<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json');
require_once __DIR__.'/../../api-gateway/middlewares/authmiddleware.php';
require_once __DIR__ . '/../../models/notification.php';

// Initialize auth middleware with same roles as mark.php
$auth = new AuthMiddleware(['user', 'admin', 'moderator']);
$auth->authenticate();

$notification = new Notification($db);

try {
    // Validate input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['notification_ids']) || !is_array($input['notification_ids'])) {
        throw new Exception('Missing or invalid notification_ids array');
    }

    // Sanitize IDs - must be integers
    $notificationIds = array_filter($input['notification_ids'], 'is_numeric');
    if (empty($notificationIds)) {
        throw new Exception('No valid notification IDs provided');
    }

    // Process each notification
    $results = [];
    foreach ($notificationIds as $id) {
        $results[$id] = $notification->softDelete($id);
    }

    echo json_encode([
        'success' => true,
        'results' => $results
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
