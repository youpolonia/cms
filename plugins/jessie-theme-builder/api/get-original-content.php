<?php
/**
 * Get Original Content API Endpoint
 * GET /api/jtb/get-original-content/{post_id}?type=page|article
 *
 * Returns the original HTML content from pages or articles table
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication is already checked in router.php

// Get post_id from query string or URL
$postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
$postType = isset($_GET['type']) ? $_GET['type'] : 'page';

// Validate type
if (!in_array($postType, ['page', 'article'])) {
    $postType = 'page';
}

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

// Get content from correct table
$db = \core\Database::connection();
$table = ($postType === 'article') ? 'articles' : 'pages';

// Build query based on table
if ($postType === 'article') {
    $stmt = $db->prepare("SELECT id, title, content, featured_image, excerpt FROM {$table} WHERE id = ?");
} else {
    $stmt = $db->prepare("SELECT id, title, content FROM {$table} WHERE id = ?");
}

$stmt->execute([$postId]);
$post = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Post not found']);
    exit;
}

$content = $post['content'] ?? '';
$hasContent = !empty(trim(strip_tags($content)));

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'post_id' => $postId,
        'type' => $postType,
        'title' => $post['title'],
        'has_content' => $hasContent,
        'content_html' => $content,
        'content_length' => strlen($content),
        'featured_image' => $post['featured_image'] ?? null,
        'excerpt' => $post['excerpt'] ?? null
    ]
]);
