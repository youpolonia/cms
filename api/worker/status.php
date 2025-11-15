<?php
declare(strict_types=1);

require_once __DIR__.'/../../includes/auth/jwt.php';
require_once __DIR__ . '/../../models/worker.php';

use Includes\Auth\JWT;
use CMS\Models\Worker;
use CMS\Includes\Api\ApiCache;

header('Content-Type: application/json');

try {
    // Validate JWT
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        throw new RuntimeException('Missing or invalid Authorization header');
    }

    $token = $matches[1];
    $payload = JWT::validateToken($token);

    // Get worker status
    $db = \core\Database::connection();
    $cache = new ApiCache('worker_status');
    $workerModel = new Worker($db, $cache);
    
    $workers = $workerModel->getAll();
    
    echo json_encode([
        'status' => 'success',
        'data' => $workers,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
