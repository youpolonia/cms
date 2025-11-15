<?php
/**
 * API Router
 * Handles routing for version approval endpoints
 */
require_once __DIR__ . '/version_approval_controller.php';

class ApiRouter {
    /**
     * Route API requests
     * @param string $path Request path
     * @param array $request Request data
     * @return array Response
     */
    public static function route(string $path, array $request): array {
        try {
            // Version approval endpoints
            if (str_starts_with($path, '/api/versions')) {
                return self::routeVersionApproval($path, $request);
            }

            throw new RuntimeException('Endpoint not found');
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 404
            ];
        }
    }

    /**
     * Route version approval requests
     * @param string $path
     * @param array $request
     * @return array
     */
    private static function routeVersionApproval(string $path, array $request): array {
        switch ($path) {
            case '/api/versions/submit':
                return VersionApprovalController::submitForApproval($request);
            
            case '/api/versions/approve':
                return VersionApprovalController::approveVersion($request);
            
            case '/api/versions/pending':
                return VersionApprovalController::getPendingApprovals($request);
            
            default:
                throw new RuntimeException('Version approval endpoint not found');
        }
    }
}

// Handle incoming request
if (php_sapi_name() !== 'cli') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $request = array_merge($_GET, $_POST);
    
    header('Content-Type: application/json');
    echo json_encode(ApiRouter::route($path, $request));
}
