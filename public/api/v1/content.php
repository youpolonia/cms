<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
// Content API Endpoint
require_once __DIR__ . '/../../../core/contentservice.php';

try {
    $tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? 'default';
    $contentService = new ContentService($tenantId);
    
    // Get published content
    $content = $contentService->getPublishedContent();
    
    if (empty($content)) {
        http_response_code(404);
        echo json_encode(['error' => 'No published content found']);
        exit;
    }

    // Filter and format response
    $response = [
        'status' => 'success',
        'data' => array_map(function($item) {
            return [
                'id' => $item['id'],
                'type' => $item['type'],
                'title' => $item['title'],
                'slug' => $item['slug'],
                'content' => $item['content'],
                'published_at' => $item['published_at']
            ];
        }, $content)
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Content retrieval failed',
        'message' => $e->getMessage()
    ]);
}
