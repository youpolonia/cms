<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../models/Notification.php';

$notification = new Notification($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', $path);
$id = $parts[3] ?? null;

try {
    switch ($method) {
        case 'GET':
            $filters = [];
            if (!empty($_GET['unread_only'])) {
                $filters['unread_only'] = true;
            }
            
            $notifications = $notification->getForUser($_SESSION['user_id'], $filters);
            echo json_encode($notifications);
            break;

        case 'POST':
            if ($id === 'read') {
                $notificationId = $parts[4] ?? null;
                if ($notificationId) {
                    $success = $notification->markAsRead($notificationId);
                    echo json_encode(['success' => $success]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Notification ID required']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
