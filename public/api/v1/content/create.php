<?php
require_once __DIR__ . '/../../../../config.php';
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

// Get and validate JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON input']));
}

// Validate required fields
if (!isset($input['type']) || !in_array($input['type'], ['page', 'blog'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid content type']));
}

if (!isset($input['title']) || empty(trim($input['title']))) {
    http_response_code(400);
    die(json_encode(['error' => 'Title is required']));
}

if (!isset($input['content']) || empty(trim($input['content']))) {
    http_response_code(400);
    die(json_encode(['error' => 'Content is required']));
}

// Sanitize input
$title = htmlspecialchars(trim($input['title']), ENT_QUOTES, 'UTF-8');
$content = htmlspecialchars(trim($input['content']), ENT_QUOTES, 'UTF-8');
$author = isset($input['author']) ? htmlspecialchars(trim($input['author']), ENT_QUOTES, 'UTF-8') : '';
$tags = isset($input['tags']) ? array_map(function($tag) {
    return htmlspecialchars(trim($tag), ENT_QUOTES, 'UTF-8');
}, $input['tags']) : [];

// Generate slug if not provided
$slug = isset($input['slug']) && !empty(trim($input['slug'])) 
    ? preg_replace('/[^a-z0-9-]/', '-', strtolower(trim($input['slug'])))
    : preg_replace('/[^a-z0-9-]/', '-', strtolower($title));

// Create content directory if needed
$contentDir = __DIR__.'/../../../content/'.$input['type'];
if (!file_exists($contentDir)) {
    mkdir($contentDir, 0755, true);
}

// Prepare content data
$contentData = [
    'title' => $title,
    'slug' => $slug,
    'content' => $content,
    'author' => $author,
    'tags' => $tags,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

// Save to file
$filePath = $contentDir.'/'.$slug.'.json';
if (file_put_contents($filePath, json_encode($contentData, JSON_PRETTY_PRINT)) !== false) {
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Content created successfully',
        'slug' => $slug,
        'path' => $filePath
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save content']);
}
