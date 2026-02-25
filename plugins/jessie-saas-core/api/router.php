<?php
/**
 * SaaS Core API Router — /api/saas/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once __DIR__ . '/../includes/class-saas-auth.php';
require_once __DIR__ . '/../includes/class-saas-credits.php';
require_once __DIR__ . '/../includes/class-saas-api-gateway.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/api/saas/?#', '', $uri);
$path = trim($path, '/');

header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasAuth, SaasCredits, SaasApiGateway};

try {
    // ── Public endpoints (no auth) ──
    
    // POST /api/saas/register
    if ($method === 'POST' && $path === 'register') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $auth = new SaasAuth();
        $result = $auth->register(
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['name'] ?? '',
            $data['company'] ?? ''
        );
        http_response_code($result['success'] ? 201 : 400);
        echo json_encode($result);
        exit;
    }
    
    // POST /api/saas/login
    if ($method === 'POST' && $path === 'login') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $auth = new SaasAuth();
        $result = $auth->login($data['email'] ?? '', $data['password'] ?? '');
        http_response_code($result['success'] ? 200 : 401);
        echo json_encode($result);
        exit;
    }
    
    // POST /api/saas/forgot-password
    if ($method === 'POST' && $path === 'forgot-password') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $auth = new SaasAuth();
        $result = $auth->requestPasswordReset($data['email'] ?? '');
        echo json_encode($result);
        exit;
    }
    
    // POST /api/saas/reset-password
    if ($method === 'POST' && $path === 'reset-password') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $auth = new SaasAuth();
        $result = $auth->resetPassword($data['token'] ?? '', $data['password'] ?? '');
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }
    
    // GET /api/saas/plans/{service}
    if ($method === 'GET' && preg_match('#^plans/([a-z]+)$#', $path, $m)) {
        $credits = new SaasCredits();
        echo json_encode(['success' => true, 'plans' => $credits->getPlans($m[1])]);
        exit;
    }
    
    // ── Protected endpoints (auth required) ──
    $gw = new SaasApiGateway();
    $authResult = $gw->authenticate();
    if (!$authResult['success']) {
        http_response_code($authResult['code'] ?? 401);
        echo json_encode($authResult);
        exit;
    }
    $userId = $gw->getUserId();
    
    // GET /api/saas/me
    if ($method === 'GET' && $path === 'me') {
        $auth = new SaasAuth();
        $user = $auth->getUser($userId);
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }
    
    // PUT /api/saas/profile
    if ($method === 'POST' && $path === 'profile') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $auth = new SaasAuth();
        $auth->updateProfile($userId, $data);
        echo json_encode(['success' => true]);
        exit;
    }
    
    // POST /api/saas/regenerate-key
    if ($method === 'POST' && $path === 'regenerate-key') {
        $auth = new SaasAuth();
        $newKey = $auth->regenerateApiKey($userId);
        echo json_encode(['success' => true, 'api_key' => $newKey]);
        exit;
    }
    
    // GET /api/saas/subscriptions
    if ($method === 'GET' && $path === 'subscriptions') {
        $credits = new SaasCredits();
        echo json_encode(['success' => true, 'subscriptions' => $credits->getAllSubscriptions($userId)]);
        exit;
    }
    
    // GET /api/saas/usage/{service}
    if ($method === 'GET' && preg_match('#^usage/([a-z]+)$#', $path, $m)) {
        $credits = new SaasCredits();
        $period = $_GET['period'] ?? 'month';
        echo json_encode(['success' => true, 'usage' => $credits->getUsageStats($userId, $m[1], $period)]);
        exit;
    }
    
    // POST /api/saas/upgrade
    if ($method === 'POST' && $path === 'upgrade') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $credits = new SaasCredits();
        $result = $credits->upgradePlan($userId, $data['service'] ?? '', (int)($data['plan_id'] ?? 0), $data['billing_cycle'] ?? 'monthly');
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }
    
    // POST /api/saas/logout
    if ($method === 'POST' && $path === 'logout') {
        $auth = new SaasAuth();
        $auth->logout();
        echo json_encode(['success' => true]);
        exit;
    }
    
    // 404
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found: ' . $path]);
    
} catch (\Throwable $e) {
    error_log('[SaaS API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
