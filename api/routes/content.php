<?php
require_once __DIR__.'/../../includes/middleware/apiauthmiddleware.php';
require_once __DIR__ . '/../../controllers/contentversioncontroller.php';
require_once __DIR__.'/../../includes/middleware/tenantversionmiddleware.php';

class ContentRoutes {
    public static function handleRequest() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Version Control Endpoints
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/api/content/create-version') {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }
                
                if (!isset($input['content_id']) || !isset($input['content_data'])) {
                    throw new Exception('Missing required fields');
                }
                
                $contentId = Sanitization::filterInt($input['content_id']);
                TenantVersionMiddleware::verifyOwnership($contentId);
                $contentData = Sanitization::filterArray($input['content_data']);
                
                $controller = new ContentVersionController();
                $versionId = $controller->createVersion($contentId, $contentData);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'version_id' => $versionId
                ]);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/api/content/compare-versions') {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }
                
                if (!isset($input['version1_id']) || !isset($input['version2_id'])) {
                    throw new Exception('Missing required version IDs');
                }
                
                $version1Id = Sanitization::filterInt($input['version1_id']);
                $version2Id = Sanitization::filterInt($input['version2_id']);
                TenantVersionMiddleware::verifyOwnership($version1Id);
                TenantVersionMiddleware::verifyOwnership($version2Id);
                
                $controller = new ContentVersionController();
                $diff = $controller->compareVersions($version1Id, $version2Id);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'data' => $diff
                ]);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/api/content/rollback-version') {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }
                
                if (!isset($input['content_id']) || !isset($input['version_id'])) {
                    throw new Exception('Missing required fields');
                }
                
                $contentId = Sanitization::filterInt($input['content_id']);
                $versionId = Sanitization::filterInt($input['version_id']);
                TenantVersionMiddleware::verifyOwnership($contentId);
                
                $controller = new ContentVersionController();
                $success = $controller->rollbackVersion($contentId, $versionId);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'result' => $success
                ]);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

        // Content Locking Routes
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/api/content/lock') {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();

                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }

                if (!isset($input['content_id'])) {
                    throw new Exception('Missing required content ID');
                }

                $contentId = Sanitization::filterInt($input['content_id']);
                $tenantId = TenantIdentification::getCurrentTenantId();
                $userId = $authMiddleware->getAuthenticatedUserId();

                $result = ContentController::acquireLock($tenantId, $contentId, $userId);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/api/content/unlock') {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();

                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }

                if (!isset($input['content_id'])) {
                    throw new Exception('Missing required content ID');
                }

                $contentId = Sanitization::filterInt($input['content_id']);
                $tenantId = TenantIdentification::getCurrentTenantId();
                $userId = $authMiddleware->getAuthenticatedUserId();

                $result = ContentController::releaseLock($tenantId, $contentId, $userId);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($requestUri, '/api/content/lock-status') === 0) {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();

                $queryParams = [];
                parse_str(parse_url($requestUri, PHP_URL_QUERY), $queryParams);

                if (!isset($queryParams['content_id'])) {
                    throw new Exception('Missing required content ID');
                }

                $contentId = Sanitization::filterInt($queryParams['content_id']);
                $tenantId = TenantIdentification::getCurrentTenantId();

                $result = ContentController::checkLock($tenantId, $contentId);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }
    }
}

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($requestUri, '/api/content/list-versions') === 0) {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();

                $queryParams = [];
                parse_str(parse_url($requestUri, PHP_URL_QUERY), $queryParams);

                if (!isset($queryParams['content_id'])) {
                    throw new Exception('Missing required content ID');
                }

                $contentId = Sanitization::filterInt($queryParams['content_id']);
                TenantVersionMiddleware::verifyOwnership($contentId);
                $page = isset($queryParams['page']) ? Sanitization::filterInt($queryParams['page']) : 1;
                $perPage = isset($queryParams['per_page']) ? Sanitization::filterInt($queryParams['per_page']) : 10;

                $controller = new ContentVersionController();
                $versions = $controller->listVersions($contentId, $page, $perPage);

                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'data' => $versions
                ]);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }

ContentRoutes::handleRequest();
