<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/includes/tenant_identification.php';
require_once __DIR__ . '/../admin/controllers/contentarchivalcontroller.php';

header('Content-Type: application/json');

try {
    $pdo = \core\Database::connection();
    $controller = new ContentArchivalController($pdo);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($method === 'POST' && $path === '/api/archival') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['content_id'])) {
            $result = $controller->archiveContent((int)$input['content_id']);
        } elseif (isset($input['content_ids'])) {
            $result = $controller->archiveMultiple($input['content_ids']);
        } else {
            throw new \InvalidArgumentException('Missing content_id or content_ids');
        }

        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
