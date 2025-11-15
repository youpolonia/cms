<?php
require_once __DIR__.'/../includes/controllers/auth/authcontroller.php';
require_once __DIR__.'/../includes/controllers/authtestcontroller.php';
require_once __DIR__.'/../middleware/ratelimiter.php';

header('Content-Type: application/json');

try {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Simple router
    switch ($requestUri) {
        case '/api/auth/test':
            if ($requestMethod === 'GET') {
                echo json_encode(AuthTestController::test());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;

        case '/api/auth/login':
            if ($requestMethod === 'POST') {
                $ip = $_SERVER['REMOTE_ADDR'];
                if (!RateLimiter::check($ip)) {
                    http_response_code(429);
                    echo json_encode(['error' => 'Too many login attempts. Please try again later.']);
                    exit;
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode(AuthController::login($data));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;

        case '/api/auth/register':
            if ($requestMethod === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                echo json_encode(AuthController::register($data));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
