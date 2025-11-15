<?php
/**
 * Blog Post View Handler
 * Displays single blog post
 */

// Get post ID from URL
$postId = (int)($_GET['id'] ?? 0);

// Get post data (placeholder - will be replaced with DB call)
$post = $postId > 0 
    ? ['id' => $postId, 'title' => 'Sample Post', 'content' => 'Full post content...']
    : null;

if (!$post) {
    header('Location: ?action=list');
    exit;
}

// Load view template
require_once __DIR__ . '/../../views/modules/blog/view.php';
