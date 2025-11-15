<?php
require_once __DIR__ . '/../core/contentfederator.php';
require_once __DIR__.'/../api-gateway/middlewares/authmiddleware.php';
require_once __DIR__.'/../middleware/ratelimiter.php';
require_once __DIR__.'/../middleware/TenantIsolation.php';

/**
 * Content Federation API Endpoints
 */
class FederationAPI {
    private static $rateLimiter;
    private static $authMiddleware;
    private static $tenantIsolation;

    public static function init(): void {
        self::$rateLimiter = new RateLimiter('federation', 100, 60); // 100 requests per minute
        self::$authMiddleware = new AuthMiddleware(['admin', 'content_manager']);
        self::$tenantIsolation = new TenantIsolation();
    }

    /**
     * Share content with another tenant
     * POST /federation/share
     */
    public static function handleShareRequest(PDO $pdo, array $requestData): array {
        try {
            $request = [
                'headers' => getallheaders(),
                'body' => $requestData
            ];
            
            $request = self::$tenantIsolation->handle($request);
            self::$authMiddleware->authenticate();
            self::$rateLimiter->check();

            return ContentFederator::apiShareContent($pdo, $request['body']);
        } catch (Exception $e) {
            return self::$tenantIsolation->errorResponse(
                $e->getCode() ?: 500,
                $e->getMessage(),
                ['operation' => 'share']
            );
        }
    }

    /**
     * List available federated content
     * GET /federation/list
     */
    public static function handleListRequest(PDO $pdo, array $filters = []): array {
        try {
            $request = [
                'headers' => getallheaders(),
                'query' => $filters
            ];
            
            $request = self::$tenantIsolation->handle($request);
            self::$authMiddleware->authenticate();
            self::$rateLimiter->check();

            return ContentFederator::apiListConflicts($pdo, $request['query']);
        } catch (Exception $e) {
            return self::$tenantIsolation->errorResponse(
                $e->getCode() ?: 500,
                $e->getMessage(),
                ['operation' => 'list']
            );
        }
    }

    /**
     * Synchronize content versions
     * POST /federation/sync
     */
    public static function handleSyncRequest(PDO $pdo, array $requestData): array {
        try {
            $request = [
                'headers' => getallheaders(),
                'body' => $requestData
            ];
            
            $request = self::$tenantIsolation->handle($request);
            self::$authMiddleware->authenticate();
            self::$rateLimiter->check();

            return ContentFederator::apiSyncVersions($pdo, $request['body']);
        } catch (Exception $e) {
            return self::$tenantIsolation->errorResponse(
                $e->getCode() ?: 500,
                $e->getMessage(),
                ['operation' => 'sync']
            );
        }
    }

    /**
     * Resolve content conflicts
     * POST /federation/resolve
     */
    public static function handleResolveRequest(PDO $pdo, array $requestData): array {
        try {
            $request = [
                'headers' => getallheaders(),
                'body' => $requestData
            ];
            
            $request = self::$tenantIsolation->handle($request);
            self::$authMiddleware->authenticate();
            self::$rateLimiter->check();

            return ContentFederator::apiResolveConflict($pdo, $request['body']);
        } catch (Exception $e) {
            return self::$tenantIsolation->errorResponse(
                $e->getCode() ?: 500,
                $e->getMessage(),
                ['operation' => 'resolve']
            );
        }
    }
}

// Initialize the API
FederationAPI::init();

// Route the request based on path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$pdo = DatabaseConnection::getPDO();

try {
    $requestData = json_decode(file_get_contents('php://input'), true) ?? [];
    
    switch (true) {
        case $path === '/federation/share' && $method === 'POST':
            $response = FederationAPI::handleShareRequest($pdo, $requestData);
            break;
        case $path === '/federation/list' && $method === 'GET':
            $response = FederationAPI::handleListRequest($pdo, $_GET);
            break;
        case $path === '/federation/sync' && $method === 'POST':
            $response = FederationAPI::handleSyncRequest($pdo, $requestData);
            break;
        case $path === '/federation/resolve' && $method === 'POST':
            $response = FederationAPI::handleResolveRequest($pdo, $requestData);
            break;
        default:
            http_response_code(404);
            $response = ['error' => 'Endpoint not found'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
