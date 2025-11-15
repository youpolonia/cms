<?php
/**
 * Media processing API routes
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../controllers/mediacontroller.php';

$db = \core\Database::connection();
$mediaController = new MediaController($db);

$request = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
    'params' => array_merge($_GET, $_POST),
    'user_id' => $_SESSION['user_id'] ?? 0
];

// Route the request
switch (true) {
    case $request['method'] === 'POST' && preg_match('#^/api/v1/media/process$#', $request['path']):
        $mediaController->processMedia($request);
        break;
        
    case $request['method'] === 'GET' && preg_match('#^/api/v1/media/status/(\d+)$#', $request['path'], $matches):
        $request['params']['id'] = $matches[1];
        $mediaController->getStatus($request);
        break;
        
    case $request['method'] === 'GET' && preg_match('#^/api/v1/media/tags/(\d+)$#', $request['path'], $matches):
        $request['params']['id'] = $matches[1];
        $mediaController->getTags($request);
        break;
        
    default:
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
