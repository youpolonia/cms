<?php
/**
 * Load API Endpoint
 * GET /api/jtb/load/{post_id}?type=page|article
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

// Get post_id and type from query string or URL
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

// Check if post exists and get title from correct table
$db = \core\Database::connection();
$table = ($postType === 'article') ? 'articles' : 'pages';

// Build query based on table (articles has different columns)
if ($postType === 'article') {
    $stmt = $db->prepare("SELECT id, title, slug, content, featured_image, excerpt FROM {$table} WHERE id = ?");
} else {
    $stmt = $db->prepare("SELECT id, title, slug, content FROM {$table} WHERE id = ?");
}

$stmt->execute([$postId]);
$post = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Post not found']);
    exit;
}

// Get JTB content
$content = JTB_Builder::getContent($postId);
$hasJtbContent = ($content !== null);

if ($content === null) {
    $content = JTB_Builder::getEmptyContent();
}

// Get CSS cache
$stmt = $db->prepare("SELECT css_cache FROM jtb_pages WHERE post_id = ?");
$stmt->execute([$postId]);
$jtbPage = $stmt->fetch(\PDO::FETCH_ASSOC);

$cssCache = $jtbPage ? ($jtbPage['css_cache'] ?? '') : '';

// Check for original content to import (only if no JTB content)
$originalContent = null;
if (!$hasJtbContent) {
    $originalHtml = $post['content'] ?? '';
    $hasOriginalContent = !empty(trim(strip_tags($originalHtml)));

    if ($hasOriginalContent) {
        $originalContent = [
            'has_content' => true,
            'content_length' => strlen($originalHtml),
            'featured_image' => $post['featured_image'] ?? null,
            'excerpt' => $post['excerpt'] ?? null
        ];
    }
}

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'post_id' => $postId,
        'post_type' => $postType,
        'post_title' => $post['title'],
        'post_slug' => $post['slug'] ?? '',
        'content' => $content,
        'css_cache' => $cssCache,
        'has_content' => $hasJtbContent,
        'original_content' => $originalContent
    ]
]);
