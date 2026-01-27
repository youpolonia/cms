<?php
declare(strict_types=1);

require_once __DIR__ . '/../../auth/authcontroller.php';
require_once __DIR__ . '/../../models/client.php';
require_once __DIR__ . '/../../includes/database/connection.php';
require_once __DIR__ . '/../../includes/auth/CSRFToken.php';

header('Content-Type: application/json');

$auth = new AuthController();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Validate CSRF token
if (!CSRFToken::validate($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$connection = new Connection();
$clientModel = new Client($connection);

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
switch (true) {
    case str_ends_with($requestUri, '/update_status') && $method === 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['client_id']) || empty($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            exit;
        }

        try {
            $success = $clientModel->update(
                (int)$input['client_id'],
                ['status' => $input['status']]
            );
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case str_ends_with($requestUri, '/delete') && $method === 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['client_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing client ID']);
            exit;
        }

        try {
            $success = $clientModel->delete((int)$input['client_id']);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
}
