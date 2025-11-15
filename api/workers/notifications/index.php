<?php
require_once __DIR__ . '/../../../admin/controllers/workernotificationscontroller.php';

$controller = new WorkerNotificationsController();
$workerId = $_GET['worker_id'] ?? null;

if (!$workerId) {
    http_response_code(400);
    die(json_encode(['error' => 'Worker ID required']));
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['stats'])) {
            echo json_encode($controller->stats($workerId));
        } else {
            echo json_encode($controller->index($workerId));
        }
        break;
        
    case 'POST':
        echo json_encode($controller->create());
        break;
        
    case 'PUT':
        $notificationId = $_GET['id'] ?? null;
        if (!$notificationId) {
            http_response_code(400);
            die(json_encode(['error' => 'Notification ID required']));
        }
        echo json_encode($controller->markAsRead($notificationId));
        break;
        
    default:
        http_response_code(405);
        die(json_encode(['error' => 'Method not allowed']));
}
