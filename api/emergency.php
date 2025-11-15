<?php
require_once __DIR__.'/../core/logger/EmergencyLogger.php';
require_once __DIR__.'/../auth/authcontroller.php';
require_once __DIR__.'/../auth/ratelimiter.php';

header('Content-Type: application/json');

require_once __DIR__ . '/../core/database.php';
$db = \core\Database::connection();
$logger = new \Includes\Auth\Services\EmergencyLogger($db);
$auth = new \Includes\Auth\AuthController($db);
$rateLimiter = new \Includes\Auth\RateLimiter($db);

$response = ['success' => false];

try {
    if (!isset($_SERVER['HTTP_X_API_KEY']) || !$auth->validateApiKey($_SERVER['HTTP_X_API_KEY'])) {
        throw new Exception('Invalid API key', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'activate':
            if ($rateLimiter->tooManyAttempts('emergency_activate')) {
                throw new Exception('Too many activation attempts', 429);
            }

            $rateLimiter->hit('emergency_activate');
            activateEmergencyMode($input['reason'] ?? '');
            $logger->log('INFO', 'Emergency mode activated', ['reason' => $input['reason'] ?? '']);
            $response = ['success' => true, 'message' => 'Emergency mode activated'];
            break;

        case 'deactivate':
            deactivateEmergencyMode();
            $logger->log('INFO', 'Emergency mode deactivated');
            $response = ['success' => true, 'message' => 'Emergency mode deactivated'];
            break;

        case 'status':
            $response = [
                'success' => true,
                'active' => isEmergencyModeActive(),
                'logs' => $logger->getRecentLogs(10)
            ];
            break;

        default:
            throw new Exception('Invalid action', 400);
    }
} catch (Exception $e) {
    $logger->log('ERROR', $e->getMessage(), ['trace' => $e->getTrace()]);
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

function activateEmergencyMode(string $reason): void {
    // Implementation for activating emergency mode
    file_put_contents(__DIR__.'/../cms_storage/emergency_active.flag', $reason);
}

function deactivateEmergencyMode(): void {
    // Implementation for deactivating emergency mode
    if (file_exists(__DIR__.'/../cms_storage/emergency_active.flag')) {
        unlink(__DIR__.'/../cms_storage/emergency_active.flag');
    }
}

function isEmergencyModeActive(): bool {
    return file_exists(__DIR__.'/../cms_storage/emergency_active.flag');
}
