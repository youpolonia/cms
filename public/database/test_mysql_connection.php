<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/database.php';

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

try {
    $db = \core\Database::connection();
    $stmt = $db->query("SHOW TABLES LIKE 'admin_users'");
    $result = $stmt->fetchAll();

    header('Content-Type: application/json');
    if (empty($result)) {
        echo json_encode(['error' => 'admin_users table not found'], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['success' => true, 'tables' => $result], JSON_PRETTY_PRINT);
    }
} catch (Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed'
    ], JSON_PRETTY_PRINT);
}
