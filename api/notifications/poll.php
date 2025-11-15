<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../core/database.php';
require_once __DIR__ . '/../../models/notification.php';
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/session_boot.php';

// Validate session and CSRF token
cms_session_start('public');
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

$db = \core\Database::connection();
$notification = new Notification($db);

// Get parameters
$lastId = $_GET['last_id'] ?? 0;
$timeout = min(30, (int)($_GET['timeout'] ?? 10)); // Max 30 seconds
$startTime = time();

try {
    // Check immediately first
    $newNotifications = $notification->getNewSince($_SESSION['user_id'], $lastId);
    if (!empty($newNotifications)) {
        echo json_encode(['notifications' => $newNotifications]);
        exit;
    }

    // Long polling loop
    while ((time() - $startTime) < $timeout) {
        $newNotifications = $notification->getNewSince($_SESSION['user_id'], $lastId);
        if (!empty($newNotifications)) {
            echo json_encode(['notifications' => $newNotifications]);
            exit;
        }
        sleep(1); // Check every second
    }

    // Timeout reached
    echo json_encode(['notifications' => []]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
