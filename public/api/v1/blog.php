<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
// Blog API Endpoint
require_once __DIR__ . '/../../../core/contentservice.php';

try {
    $tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? 'default';
    $contentService = new ContentService($tenantId);
    
    // Get latest blog posts
    $posts = $contentService->getLatestBlogPosts(10); // Limit to 10 posts
    
    if (empty($posts)) {
        http_response_code(404);
        echo json_encode(['error' => 'No blog posts found']);
        exit;
    }

    // Format response
    $response = [
        'status' => 'success',
        'data' => array_map(function($post) {
            return [
                'id' => $post['id'],
                'title' => $post['title'],
                'slug' => $post['slug'],
                'date' => $post['published_at'],
                'summary' => $post['summary'],
                'tags' => $post['tags'] ? explode(',', $post['tags']) : []
            ];
        }, $posts)
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Blog posts retrieval failed',
        'message' => $e->getMessage()
    ]);
}
