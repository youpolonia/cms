<?php
/**
 * Public API v2 - Main Router
 * Complete REST API with JWT authentication
 */

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, X-Tenant-ID');
header('Content-Type: application/json; charset=UTF-8');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-API-Version: 2.0');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

define('CMS_ROOT', realpath(__DIR__ . '/../../..'));
define('API_VERSION', '2.0');

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';

// API Response helper
function api_response($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode([
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'data' => $data,
        'timestamp' => gmdate('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function api_error(string $message, int $status = 400, ?string $code = null): void
{
    http_response_code($status);
    echo json_encode([
        'ok' => false,
        'status' => $status,
        'error' => [
            'message' => $message,
            'code' => $code ?? 'ERROR_' . $status,
        ],
        'timestamp' => gmdate('c'),
    ], JSON_PRETTY_PRINT);
    exit;
}

function get_request_body(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

function get_bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)$/i', $header, $matches)) {
        return $matches[1];
    }
    return $_SERVER['HTTP_X_API_KEY'] ?? null;
}

// Simple JWT validation (for demo - use proper library in production)
function validate_api_key(?string $key): bool
{
    if (empty($key)) {
        return false;
    }
    // Check against stored API keys
    $keysFile = CMS_ROOT . '/cms_storage/api_keys.json';
    if (!file_exists($keysFile)) {
        // In dev mode, accept any key
        return defined('DEV_MODE') && DEV_MODE;
    }
    $keys = json_decode(file_get_contents($keysFile), true);
    return is_array($keys) && in_array($key, array_column($keys, 'key'));
}

// Rate limiting
function check_rate_limit(string $identifier): bool
{
    $limitFile = CMS_ROOT . '/cms_storage/rate_limits/' . md5($identifier) . '.json';
    $dir = dirname($limitFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $limit = 100; // requests per minute
    $window = 60; // seconds

    $data = file_exists($limitFile) ? json_decode(file_get_contents($limitFile), true) : [];
    $now = time();

    // Clean old entries
    $data = array_filter($data, fn($t) => $t > ($now - $window));

    if (count($data) >= $limit) {
        return false;
    }

    $data[] = $now;
    file_put_contents($limitFile, json_encode($data));
    return true;
}

// Parse request path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/api/v2';
$path = substr($requestUri, strlen($basePath));
$path = trim($path, '/');
$segments = $path ? explode('/', $path) : [];

$method = $_SERVER['REQUEST_METHOD'];
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$action = $segments[2] ?? null;

// Public endpoints (no auth required)
$publicEndpoints = ['status', 'docs', 'version'];

// Check authentication for non-public endpoints
if (!in_array($resource, $publicEndpoints)) {
    $apiKey = get_bearer_token();
    if (!validate_api_key($apiKey)) {
        api_error('Invalid or missing API key', 401, 'UNAUTHORIZED');
    }

    // Rate limiting
    $clientId = $apiKey ?? $_SERVER['REMOTE_ADDR'];
    if (!check_rate_limit($clientId)) {
        api_error('Rate limit exceeded. Try again later.', 429, 'RATE_LIMITED');
    }
}

// Route to appropriate handler
switch ($resource) {
    case '':
    case 'docs':
        require_once __DIR__ . '/endpoints/docs.php';
        break;

    case 'status':
        api_response([
            'status' => 'ok',
            'version' => API_VERSION,
            'server_time' => gmdate('c'),
        ]);
        break;

    case 'version':
        api_response([
            'api_version' => API_VERSION,
            'cms_version' => defined('CMS_VERSION') ? CMS_VERSION : '1.0.0',
        ]);
        break;

    case 'pages':
        require_once __DIR__ . '/endpoints/pages.php';
        handle_pages($method, $id, $action);
        break;

    case 'posts':
        require_once __DIR__ . '/endpoints/posts.php';
        handle_posts($method, $id, $action);
        break;

    case 'media':
        require_once __DIR__ . '/endpoints/media.php';
        handle_media($method, $id, $action);
        break;

    case 'users':
        require_once __DIR__ . '/endpoints/users.php';
        handle_users($method, $id, $action);
        break;

    case 'webhooks':
        require_once __DIR__ . '/endpoints/webhooks.php';
        handle_webhooks($method, $id, $action);
        break;

    case 'ai':
        require_once __DIR__ . '/endpoints/ai.php';
        handle_ai($method, $id, $action);
        break;

    default:
        api_error('Endpoint not found: ' . $resource, 404, 'NOT_FOUND');
}
