<?php
require_once __DIR__ . '/../../includes/services/WorkerSupervisor.php';
require_once __DIR__ . '/../controllers/workercontroller.php';

// Initialize dependencies
require_once __DIR__ . '/../../../core/database.php';
$db = \core\Database::connection();
$controller = new WorkerController($db);

// Route the request
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if (preg_match('#/worker/([^/]+)/heartbeat$#', $path, $matches) && $method === 'POST') {
    $workerId = $matches[1];
    header('Content-Type: application/json');
    echo json_encode($controller->heartbeat($workerId));
} elseif ($path === '/worker/status' && $method === 'GET') {
    header('Content-Type: application/json');
    echo json_encode($controller->status());
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
