<?php
require_once __DIR__ . '/../core/contentversion.php';
require_once __DIR__ . '/../core/auth.php';

header('Content-Type: application/json');

// Verify authentication
if (!Auth::checkToken()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Route requests
try {
    switch ($method) {
        case 'POST':
            if ($pathParts[1] === 'versions' && count($pathParts) === 2) {
                // Create new version
                $input = json_decode(file_get_contents('php://input'), true);
                $result = ContentVersion::create(
                    $input['content_id'],
                    $input['content_data'],
                    $input['change_notes'] ?? ''
                );
                echo json_encode($result);
            } elseif ($pathParts[1] === 'versions' && $pathParts[2] === 'restore' && isset($pathParts[3])) {
                $versionId = (int)$pathParts[3];
                $result = ContentVersion::restore($versionId);
                echo json_encode($result);
            }
            break;

        case 'GET':
            if ($pathParts[1] === 'versions' && isset($pathParts[2]) && count($pathParts) === 3) {
                // Get specific version
                $versionId = (int)$pathParts[2];
                $version = ContentVersion::get($versionId);
                echo json_encode($version ?: ['error' => 'Version not found']);
            } elseif ($pathParts[1] === 'versions' && $pathParts[2] === 'content' && isset($pathParts[3])) {
                // List versions for content
                $contentId = (int)$pathParts[3];
                $versions = ContentVersion::listForContent($contentId);
                echo json_encode($versions);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
