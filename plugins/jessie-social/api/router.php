<?php
/**
 * Social Media Scheduler API — /api/social/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-auth.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-credits.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-api-gateway.php';
require_once __DIR__ . '/../includes/class-social-core.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^/api/social/?#', '', $uri), '/');
header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasApiGateway, SaasCredits};
use Plugins\JessieSocial\SocialCore;

try {
    $gw = new SaasApiGateway();
    $auth = $gw->authenticate();
    if (!$auth['success']) { http_response_code($auth['code'] ?? 401); echo json_encode($auth); exit; }
    $userId = $gw->getUserId();
    $core = new SocialCore($userId);

    if ($method === 'GET' && $path === 'stats') { echo json_encode(['success' => true, 'stats' => $core->getStats()]); exit; }
    if ($method === 'GET' && $path === 'accounts') { echo json_encode(['success' => true, 'accounts' => $core->getAccounts()]); exit; }
    if ($method === 'POST' && $path === 'accounts') { $d = json_decode(file_get_contents('php://input'), true) ?: $_POST; echo json_encode(['success' => true, 'id' => $core->connectAccount($d)]); exit; }
    if ($method === 'DELETE' && preg_match('#^accounts/(\d+)$#', $path, $m)) { echo json_encode(['success' => $core->disconnectAccount((int)$m[1])]); exit; }
    if ($method === 'GET' && $path === 'posts') { $status = $_GET['status'] ?? null; echo json_encode(['success' => true, 'posts' => $core->getPosts(50, $status)]); exit; }
    if ($method === 'GET' && preg_match('#^posts/(\d+)$#', $path, $m)) { $p = $core->getPost((int)$m[1]); echo json_encode($p ? ['success' => true, 'post' => $p] : ['success' => false, 'error' => 'Not found']); exit; }
    if ($method === 'POST' && $path === 'posts') { $d = json_decode(file_get_contents('php://input'), true) ?: $_POST; echo json_encode(['success' => true, 'id' => $core->createPost($d)]); exit; }
    if ($method === 'POST' && preg_match('#^posts/(\d+)$#', $path, $m)) { $d = json_decode(file_get_contents('php://input'), true) ?: $_POST; echo json_encode(['success' => $core->updatePost((int)$m[1], $d)]); exit; }
    if ($method === 'DELETE' && preg_match('#^posts/(\d+)$#', $path, $m)) { echo json_encode(['success' => $core->deletePost((int)$m[1])]); exit; }
    if ($method === 'GET' && $path === 'calendar') { $start = $_GET['start'] ?? date('Y-m-01'); $end = $_GET['end'] ?? date('Y-m-t'); echo json_encode(['success' => true, 'events' => $core->getCalendar($start, $end)]); exit; }
    if ($method === 'GET' && $path === 'templates') { echo json_encode(['success' => true, 'templates' => $core->getTemplates()]); exit; }
    if ($method === 'POST' && $path === 'templates') { $d = json_decode(file_get_contents('php://input'), true) ?: $_POST; echo json_encode(['success' => true, 'id' => $core->saveTemplate($d)]); exit; }
    if ($method === 'POST' && $path === 'generate') {
        $d = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId, 'socialmanager', 2)) { http_response_code(402); echo json_encode(['success' => false, 'error' => 'Insufficient credits']); exit; }
        $result = $core->generatePost($d['topic'] ?? '', $d['platform'] ?? 'twitter', $d['tone'] ?? 'professional', $d['language'] ?? 'en');
        if ($result['success']) $credits->consume($userId, 'socialmanager', 2, 'Generate post: ' . mb_substr($d['topic'] ?? '', 0, 50));
        echo json_encode($result); exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Not found: ' . $path]);
} catch (\Throwable $e) {
    error_log('[Social API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
