<?php
/**
 * Blog List Handler
 * Displays paginated list of blog posts
 */

// Get current page
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Get posts (placeholder - will be replaced with DB call)
$posts = [
    ['id' => 1, 'title' => 'First Post', 'excerpt' => 'Sample excerpt...'],
    ['id' => 2, 'title' => 'Second Post', 'excerpt' => 'Another excerpt...']
];

// Calculate total pages (placeholder)
$totalPages = 5;

// Load view template
require_once __DIR__ . '/../../views/modules/blog/list.php';
