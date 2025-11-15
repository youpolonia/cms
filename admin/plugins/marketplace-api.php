<?php
require_once __DIR__ . '/pluginmarketplace.php';

header('Content-Type: application/json');

$marketplace = PluginMarketplace::getInstance();
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoint = str_replace('/admin/plugins/marketplace-api.php', '', $requestUri);

try {
    switch ($endpoint) {
        case '/products':
            if ($requestMethod === 'GET') {
                echo json_encode($marketplace->getProducts());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;

        case '/cart':
            if ($requestMethod === 'GET') {
                echo json_encode($marketplace->getCart());
            } elseif ($requestMethod === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (isset($data['productId'])) {
                    $success = $marketplace->addToCart($data['productId']);
                    echo json_encode(['success' => $success]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'productId is required']);
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;

        case '/purchase':
            if ($requestMethod === 'POST') {
                $result = $marketplace->processPurchase();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
