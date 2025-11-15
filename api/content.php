<?php
require_once __DIR__ . '/../includes/core/Api.php';
require_once __DIR__ . '/../includes/editor/blockmanager.php';

header('Content-Type: application/json');

$api = new Api();
$api->authenticate();

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    switch ($path) {
        case '/api/blocks':
            if ($method === 'GET') {
                $blocks = BlockManager::getBlockTypes();
                echo json_encode(['data' => $blocks]);
            }
            break;

        case '/api/content':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $result = BlockManager::saveBlock($input);
                echo json_encode(['success' => $result]);
            }
            break;

        case '/api/ai/suggest':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                // TODO: Implement AI suggestion endpoint
                echo json_encode(['success' => false, 'message' => 'Not implemented']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
