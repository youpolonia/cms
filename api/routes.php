<?php
// API Routing for Multisite CMS
// Version: 2.0 - Framework-free implementation
// Date: 2025-05-29

require_once __DIR__.'/../includes/tenant_identification.php';
require_once __DIR__.'/../includes/api_error_handler.php';
require_once __DIR__ . '/../api/controllers/contentcontroller.php';
require_once __DIR__ . '/statustransitionsapi.php';

class StatusTransitionsRoutes {
    public static function handleRequest() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $tenantId = TenantIdentification::getCurrentTenantId();
        $statusTransitionsAPI = new StatusTransitionsAPI();
        
        // Status transitions endpoints
        if (str_starts_with($requestUri, '/api/status-transitions')) {
            $id = null;
            if (preg_match('/\/api\/status-transitions\/(\d+)/', $requestUri, $matches)) {
                $id = $matches[1];
            }
            
            switch ($requestMethod) {
                case 'GET':
                    if ($id) {
                        $statusTransitionsAPI->getTransition($id);
                    } else {
                        $statusTransitionsAPI->listTransitions();
                    }
                    break;
                case 'POST':
                    $statusTransitionsAPI->createTransition();
                    break;
                case 'PUT':
                    if ($id) {
                        $statusTransitionsAPI->updateTransition($id);
                    }
                    break;
                case 'DELETE':
                    if ($id) {
                        $statusTransitionsAPI->deleteTransition($id);
                    }
                    break;
            }
            exit;
        }
    }
}

// Main request handler
StatusTransitionsRoutes::handleRequest();
ContentRoutes::handleRequest();

class ContentRoutes {
    public static function handleRequest() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // API Version 1 Routes
        if (str_starts_with($requestUri, '/api/v1/content')) {
            $tenantId = TenantIdentification::getCurrentTenantId();
            
            // Content endpoints
            if ($requestMethod === 'GET' && $requestUri === '/api/v1/content') {
                return ContentController::read($tenantId);
            }
            
            if ($requestMethod === 'GET' && preg_match('#^/api/v1/content/(\d+)$#', $requestUri, $matches)) {
                return ContentController::read($tenantId, $matches[1]);
            }
            
            // Cross-site operations
            if ($requestMethod === 'POST' && $requestUri === '/api/v1/content/cross-site') {
                $input = json_decode(file_get_contents('php://input'), true);
                return ContentController::crossSiteOperation($tenantId, $input);
            }
            
            // Bulk operations
            if ($requestMethod === 'POST' && $requestUri === '/api/v1/content/bulk') {
                $input = json_decode(file_get_contents('php://input'), true);
                return ContentController::bulkOperation($tenantId, $input);
            }

            // State transitions
            if ($requestMethod === 'PUT' && preg_match('#^/api/v1/content/(\d+)/state$#', $requestUri, $matches)) {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!isset($input['state'])) {
                    throw new Exception('State parameter required', 400);
                }
                return ContentController::changeState($tenantId, $matches[1], $input['state']);
            }
        }
// API Version 1 Routes for Themes
    if (str_starts_with($requestUri, '/api/v1/themes')) {
        $tenantId = TenantIdentification::getCurrentTenantId();
        if ($requestMethod === 'GET' && $requestUri === '/api/v1/themes') {
            return ThemesController::index($tenantId);
        }
        if ($requestMethod === 'GET' && preg_match('#^/api/v1/themes/(\d+)$#', $requestUri, $matches)) {
            return ThemesController::show($tenantId, $matches[1]);
        }
        if ($requestMethod === 'POST' && $requestUri === '/api/v1/themes') {
            $input = json_decode(file_get_contents('php://input'), true);
            return ThemesController::create($tenantId, $input);
        }
        if ($requestMethod === 'DELETE' && preg_match('#^/api/v1/themes/(\d+)$#', $requestUri, $matches)) {
            return ThemesController::destroy($tenantId, $matches[1]);
        }
    }

    // API Version 1 Routes for Mode
    if ($requestMethod === 'POST' && $requestUri === '/api/v1/mode') {
        $input = json_decode(file_get_contents('php://input'), true);
        return ModeController::update($input);
    }
        
        // Apply tenant isolation
        TenantIdentification::enforceTenantIsolation();
    }
}

ContentRoutes::handleRequest();
