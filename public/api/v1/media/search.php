<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../../auth/workerauthcontroller.php';

header('Content-Type: application/json');

// Authentication check
if (!\Includes\Auth\WorkerAuthController::validateApiRequest()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Validate required headers
if (!isset($_SERVER['HTTP_X_TENANT_CONTEXT'])) {
    http_response_code(403);
    die(json_encode(['error' => 'X-Tenant-Context header required']));
}

// Validate query parameter
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    http_response_code(400);
    die(json_encode(['error' => 'Search query parameter "q" is required']));
}

$tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'];
$query = trim($_GET['q']);

// Sanitize input
$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

try {
    // Load required classes
    require_once __DIR__.'/../../../media/ai/mediaaisearch.php';
    require_once __DIR__.'/../../../../modules/mediagallery/mediaregistry.php';

    // Perform semantic search
    $results = MediaAISearch::semanticSearch($query, $tenantId);

    // Format response
    $response = array_map(function($media) {
        return [
            'filename' => $media['filename'],
            'description' => $media['description'],
            'tags' => $media['tags'],
            'upload_date' => $media['upload_date'],
            'size' => $media['size']
        ];
    }, $results);

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to perform media search',
        'message' => $e->getMessage()
    ]);
}
