<?php
// Security check
if (!defined('CMS_ADMIN')) {
    exit('Direct access not allowed');
}

header('Content-Type: application/json');

// Validate CSRF token
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid CSRF token']));
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$slug = $input['slug'] ?? '';
$content_id = $input['content_id'] ?? null;

if (empty($slug)) {
    http_response_code(400);
    die(json_encode(['error' => 'Slug is required']));
}

try {
    // Check slug availability
    $query = "SELECT id FROM content_entries WHERE slug = ?";
    $params = [$slug];
    
    if ($content_id) {
        $query .= " AND id != ?";
        $params[] = $content_id;
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'available' => !$existing,
        'slug' => $slug
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['error' => 'Internal error']);
}
