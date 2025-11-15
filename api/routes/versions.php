<?php
require_once __DIR__.'/../../includes/middleware/apiauthmiddleware.php';
require_once __DIR__.'/../../services/versioncomparator.php';

class VersionRoutes {
    public static function handleRequest() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // v1 API endpoint: /api/v1/version/compare?version1=X&version2=Y
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($requestUri, '/api/v1/version/compare') === 0) {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                $version1Id = (int)($_GET['version1'] ?? 0);
                $version2Id = (int)($_GET['version2'] ?? 0);
                
                $comparator = new VersionComparator();
                $diffResult = $comparator->compareVersions(
                    ['id' => $version1Id],
                    ['id' => $version2Id]
                );
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'changes' => $diffResult['fields_changed'],
                        'stats' => [
                            'similarity' => $diffResult['similarity_score']
                        ]
                    ]
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
        
        // Original version comparison endpoint: /api/versions/compare/{version1}/{version2}
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/versions/compare/(\d+)/(\d+)$#', $requestUri, $matches)) {
            try {
                // Authentication check
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                // Extract version IDs from URL
                $version1Id = (int)$matches[1];
                $version2Id = (int)$matches[2];
                
                // Use the VersionComparator to compare versions
                $comparator = new VersionComparator();
                $diffResult = $comparator->compareVersions(
                    ['id' => $version1Id],
                    ['id' => $version2Id]
                );
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'changes' => $diffResult['fields_changed'],
                        'stats' => [
                            'similarity' => $diffResult['similarity_score']
                        ]
                    ]
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
        
        // HTML-aware diff endpoint: /api/versions/html-diff/{version1}/{version2}
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/api/versions/html-diff/(\d+)/(\d+)$#', $requestUri, $matches)) {
            try {
                // Authentication check
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                // Extract version IDs from URL
                $version1Id = (int)$matches[1];
                $version2Id = (int)$matches[2];
                
                // Get version content
                $comparator = new VersionComparator();
                $version1 = $comparator->getVersionContent($version1Id);
                $version2 = $comparator->getVersionContent($version2Id);
                
                if (!$version1 || !$version2) {
                    throw new Exception("One or both versions not found");
                }
                
                // Generate HTML-aware diff
                $diffResult = $comparator->getHtmlDiff($version1['body'], $version2['body']);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'changes' => $diffResult['fields_changed'],
                        'stats' => [
                            'similarity' => $diffResult['similarity_score']
                        ],
                        'is_html' => true
                    ]
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
    }
}

VersionRoutes::handleRequest();
