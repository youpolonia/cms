<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../api-gateway/middlewares/authmiddleware.php';
require_once __DIR__ . '/../../models/notification.php';

// Initialize auth middleware
$auth = new AuthMiddleware(['user', 'admin', 'moderator']); // Allowed roles
$auth->authenticate();

$notification = new Notification($db);

try {
    // Get filters from query params
    $filters = [
        'unread_only' => !empty($_GET['unread_only']),
        'category_id' => $_GET['category_id'] ?? null,
        'type_id' => $_GET['type_id'] ?? null,
        'limit' => min($_GET['limit'] ?? 50, 100), // Max 100 items
        'offset' => $_GET['offset'] ?? 0
    ];

    // Validate category_id if provided
    if ($filters['category_id'] && !is_numeric($filters['category_id'])) {
        throw new Exception('Invalid category ID');
    }

    // Get notifications with filters
    $notifications = $notification->getForUser($_SESSION['user_id'], $filters);
    
    echo json_encode([
        'success' => true,
        'data' => $notifications,
        'count' => count($notifications)
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
