<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/database/connection.php';
require_once __DIR__ . '/../../includes/models/user.php';
require_once __DIR__ . '/../../services/authservice.php';
require_once __DIR__ . '/../controllers/authcontroller.php';

// Initialize database connection
require_once __DIR__ . '/../../../core/database.php';
$db = \core\Database::connection();

$authController = new AuthController($db);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathSegments = explode('/', trim($path, '/'));

    // Route: /api/v1/auth/register
    if ($method === 'POST' && end($pathSegments) === 'register') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($authController->register($data));
        exit;
    }

    // Route: /api/v1/auth/login
    if ($method === 'POST' && end($pathSegments) === 'login') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($authController->login($data));
        exit;
    }

    // Route: /api/v1/auth/logout
    if ($method === 'POST' && end($pathSegments) === 'logout') {
        echo json_encode($authController->logout());
        exit;
    }

    // Route: /api/v1/auth/me
    if ($method === 'GET' && end($pathSegments) === 'me') {
        echo json_encode($authController->getCurrentUser());
        exit;
    }

    // Route: /api/v1/auth/profile
    if ($method === 'PUT' && end($pathSegments) === 'profile') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($authController->updateProfile($data));
        exit;
    }

    // Route: /api/v1/auth/change-password
    if ($method === 'POST' && end($pathSegments) === 'change-password') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($authController->changePassword($data));
        exit;
    }

    // Route: /api/v1/auth/request-reset
    if ($method === 'POST' && end($pathSegments) === 'request-reset') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($authController->requestPasswordReset($data));
        exit;
    }

    // Route: /api/v1/auth/reset-password
    if ($method === 'POST' && end($pathSegments) === 'reset-password') {
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($authController->resetPassword($data));
        exit;
    }

    // No matching route
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
}
