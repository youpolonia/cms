<?php
/**
 * Image Studio API Router — /api/imagestudio/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-auth.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-credits.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-api-gateway.php';
require_once __DIR__ . '/../includes/class-imagestudio-core.php';
require_once __DIR__ . '/../includes/class-imagestudio-resize.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api/imagestudio/?#', '', $uri);
$path = trim($path, '/');

header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasApiGateway, SaasCredits};

try {
    $gw = new SaasApiGateway();
    $authResult = $gw->authenticate();
    if (!$authResult['success']) {
        http_response_code($authResult['code'] ?? 401);
        echo json_encode($authResult);
        exit;
    }
    $userId = $gw->getUserId();
    $studio = new \ImageStudioCore($userId);
    $credits = new SaasCredits();

    // GET /api/imagestudio/stats
    if ($method === 'GET' && $path === 'stats') {
        echo json_encode(['success' => true, 'stats' => $studio->getStats()]);
        exit;
    }

    // GET /api/imagestudio/presets
    if ($method === 'GET' && $path === 'presets') {
        echo json_encode(['success' => true, 'presets' => \ImageStudioResize::getPresets()]);
        exit;
    }

    // GET /api/imagestudio/images
    if ($method === 'GET' && $path === 'images') {
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $type = $_GET['type'] ?? null;
        echo json_encode(['success' => true, 'images' => $studio->getImages($limit, $offset, $type)]);
        exit;
    }

    // GET /api/imagestudio/images/{id}
    if ($method === 'GET' && preg_match('#^images/(\d+)$#', $path, $m)) {
        $img = $studio->getImage((int)$m[1]);
        if (!$img) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }
        echo json_encode(['success' => true, 'image' => $img]);
        exit;
    }

    // DELETE /api/imagestudio/images/{id}
    if ($method === 'DELETE' && preg_match('#^images/(\d+)$#', $path, $m)) {
        echo json_encode(['success' => $studio->deleteImage((int)$m[1])]);
        exit;
    }

    // POST /api/imagestudio/upload (free)
    if ($method === 'POST' && $path === 'upload') {
        if (empty($_FILES['image'])) {
            echo json_encode(['success' => false, 'error' => 'No image file provided']);
            exit;
        }
        $result = $studio->uploadImage($_FILES['image']);
        echo json_encode($result['ok'] ? ['success' => true, 'image' => $result['image']] : ['success' => false, 'error' => $result['error']]);
        exit;
    }

    // POST /api/imagestudio/remove-bg (1 credit)
    if ($method === 'POST' && $path === 'remove-bg') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (!$credits->hasCredits($userId, 'imagestudio', 1)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits']);
            exit;
        }
        $img = $studio->getImage((int)($data['image_id'] ?? 0));
        if (!$img) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }
        $result = $studio->removeBackground($img['file_path']);
        if ($result['ok']) {
            $credits->consume($userId, 'imagestudio', 1, 'Remove background');
            echo json_encode(['success' => true, 'image' => $result['image']]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed']);
        }
        exit;
    }

    // POST /api/imagestudio/alt-text (1 credit)
    if ($method === 'POST' && $path === 'alt-text') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (!$credits->hasCredits($userId, 'imagestudio', 1)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits']);
            exit;
        }
        $img = $studio->getImage((int)($data['image_id'] ?? 0));
        if (!$img) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }
        $result = $studio->generateAltText($img['file_path'], $data['product_name'] ?? '');
        if ($result['ok']) {
            $credits->consume($userId, 'imagestudio', 1, 'Generate alt text');
            echo json_encode(['success' => true, 'alt' => $result['alt'], 'id' => $result['id']]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed']);
        }
        exit;
    }

    // POST /api/imagestudio/enhance (2 credits)
    if ($method === 'POST' && $path === 'enhance') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (!$credits->hasCredits($userId, 'imagestudio', 2)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits']);
            exit;
        }
        $img = $studio->getImage((int)($data['image_id'] ?? 0));
        if (!$img) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }
        $result = $studio->enhanceImage($img['file_path'], $data['prompt'] ?? '');
        if ($result['ok']) {
            $credits->consume($userId, 'imagestudio', 2, 'Enhance image');
            echo json_encode(['success' => true, 'image' => $result['image']]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed']);
        }
        exit;
    }

    // POST /api/imagestudio/generate (3 credits)
    if ($method === 'POST' && $path === 'generate') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (!$credits->hasCredits($userId, 'imagestudio', 3)) {
            http_response_code(402);
            echo json_encode(['success' => false, 'error' => 'Insufficient credits']);
            exit;
        }
        $result = $studio->generateImage($data['prompt'] ?? '', $data['style'] ?? 'photo');
        if ($result['ok']) {
            $credits->consume($userId, 'imagestudio', 3, 'Generate image: ' . mb_substr($data['prompt'] ?? '', 0, 50));
            echo json_encode(['success' => true, 'image' => $result['image']]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed']);
        }
        exit;
    }

    // POST /api/imagestudio/resize (free)
    if ($method === 'POST' && $path === 'resize') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $img = $studio->getImage((int)($data['image_id'] ?? 0));
        if (!$img) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }
        $result = \ImageStudioResize::resize(
            $img['file_path'],
            (int)($data['width'] ?? 800),
            (int)($data['height'] ?? 600),
            $data['crop_mode'] ?? 'center',
            $data['format'] ?? '',
            (int)($data['quality'] ?? 90)
        );
        if ($result['ok']) {
            echo json_encode(['success' => true, 'result' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed']);
        }
        exit;
    }

    // POST /api/imagestudio/resize-preset (free)
    if ($method === 'POST' && $path === 'resize-preset') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $img = $studio->getImage((int)($data['image_id'] ?? 0));
        if (!$img) {
            echo json_encode(['success' => false, 'error' => 'Image not found']);
            exit;
        }
        $result = \ImageStudioResize::resizePreset($img['file_path'], $data['preset'] ?? '', $data['format'] ?? '', (int)($data['quality'] ?? 90));
        if ($result['ok']) {
            echo json_encode(['success' => true, 'result' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed']);
        }
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $path]);

} catch (\Throwable $e) {
    error_log('[ImageStudio API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
