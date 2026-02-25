<?php
/**
 * Copywriter API Router — /api/copywriter/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-auth.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-credits.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-api-gateway.php';
require_once __DIR__ . '/../includes/class-copywriter-core.php';
require_once __DIR__ . '/../includes/class-copywriter-platform.php';
require_once __DIR__ . '/../includes/class-copywriter-brand.php';
require_once __DIR__ . '/../includes/class-copywriter-bulk.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api/copywriter/?#', '', $uri);
$path = trim($path, '/');

header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasApiGateway, SaasCredits};
use Plugins\JessieCopywriter\{CopywriterCore, CopywriterPlatform, CopywriterBrand, CopywriterBulk};

try {
    // All endpoints require auth
    $gw = new SaasApiGateway();
    $authResult = $gw->authenticate();
    if (!$authResult['success']) {
        http_response_code($authResult['code'] ?? 401);
        echo json_encode($authResult);
        exit;
    }
    $userId = $gw->getUserId();
    $core = new CopywriterCore();

    // GET /api/copywriter/platforms
    if ($method === 'GET' && $path === 'platforms') {
        echo json_encode(['success' => true, 'platforms' => CopywriterPlatform::getLabels()]);
        exit;
    }

    // GET /api/copywriter/stats
    if ($method === 'GET' && $path === 'stats') {
        echo json_encode(['success' => true, 'stats' => $core->getStats($userId)]);
        exit;
    }

    // GET /api/copywriter/history
    if ($method === 'GET' && $path === 'history') {
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $platform = $_GET['platform'] ?? null;
        echo json_encode(['success' => true, 'history' => $core->getHistory($userId, $limit, $offset, $platform)]);
        exit;
    }

    // GET /api/copywriter/content/{id}
    if ($method === 'GET' && preg_match('#^content/(\d+)$#', $path, $m)) {
        $item = $core->getContent((int)$m[1], $userId);
        if (!$item) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Content not found']);
            exit;
        }
        echo json_encode(['success' => true, 'content' => $item]);
        exit;
    }

    // POST /api/copywriter/generate (costs 3 credits)
    if ($method === 'POST' && $path === 'generate') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId, 'copywriter', 3)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits (need 3)']);
            exit;
        }
        $result = $core->generate($userId, $data);
        if ($result['success']) {
            $credits->consume($userId, 'copywriter', 3, 'Generate: ' . ($data['name'] ?? ''));
        }
        echo json_encode($result);
        exit;
    }

    // POST /api/copywriter/rewrite (costs 2 credits)
    if ($method === 'POST' && $path === 'rewrite') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId, 'copywriter', 2)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits (need 2)']);
            exit;
        }
        $result = $core->rewrite($userId, $data);
        if ($result['success']) {
            $credits->consume($userId, 'copywriter', 2, 'Rewrite: ' . ($data['mode'] ?? 'professional'));
        }
        echo json_encode($result);
        exit;
    }

    // GET /api/copywriter/brands
    if ($method === 'GET' && $path === 'brands') {
        $brand = new CopywriterBrand();
        echo json_encode(['success' => true, 'brands' => $brand->list($userId)]);
        exit;
    }

    // POST /api/copywriter/brands
    if ($method === 'POST' && $path === 'brands') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $brand = new CopywriterBrand();
        echo json_encode($brand->create($userId, $data));
        exit;
    }

    // PUT /api/copywriter/brands/{id}
    if ($method === 'POST' && preg_match('#^brands/(\d+)$#', $path, $m)) {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $brand = new CopywriterBrand();
        echo json_encode($brand->update((int)$m[1], $userId, $data));
        exit;
    }

    // DELETE /api/copywriter/brands/{id}
    if ($method === 'DELETE' && preg_match('#^brands/(\d+)$#', $path, $m)) {
        $brand = new CopywriterBrand();
        $ok = $brand->delete((int)$m[1], $userId);
        echo json_encode(['success' => $ok]);
        exit;
    }

    // POST /api/copywriter/batch
    if ($method === 'POST' && $path === 'batch') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $bulk = new CopywriterBulk();
        echo json_encode($bulk->createBatch($userId, $data['products'] ?? [], $data['platform'] ?? 'general', $data['tone'] ?? 'professional', $data['brand_id'] ?? null));
        exit;
    }

    // GET /api/copywriter/batches
    if ($method === 'GET' && $path === 'batches') {
        $bulk = new CopywriterBulk();
        echo json_encode(['success' => true, 'batches' => $bulk->getBatches($userId)]);
        exit;
    }

    // GET /api/copywriter/batch/{id}
    if ($method === 'GET' && preg_match('#^batch/(\d+)$#', $path, $m)) {
        $bulk = new CopywriterBulk();
        $batch = $bulk->getBatch((int)$m[1], $userId);
        if (!$batch) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Batch not found']);
            exit;
        }
        echo json_encode(['success' => true, 'batch' => $batch]);
        exit;
    }

    // 404
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $path]);

} catch (\Throwable $e) {
    error_log('[Copywriter API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
