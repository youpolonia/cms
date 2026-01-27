<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../includes/db.php';
require_once __DIR__.'/../../includes/services/notificationservice.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

$lastId = $_GET['last_id'] ?? 0;
$timeout = 30; // seconds
$start = time();

while (time() - $start < $timeout) {
    NotificationService::initialize($db);
    $notifications = NotificationService::getUnreadNotifications($userId, $lastId);

    if (!empty($notifications)) {
        echo json_encode([
            'notifications' => $notifications,
            'last_id' => $notifications[0]['notification_id']
        ]);
        exit;
    }
    
    sleep(1); // Check every second
}

echo json_encode(['notifications' => [], 'last_id' => $lastId]);
