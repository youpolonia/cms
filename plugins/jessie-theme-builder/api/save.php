<?php
/**
 * Save API Endpoint
 * POST /api/jtb/save
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication and CSRF are checked in router.php

// Get post_id
$postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

// Get content
$content = isset($_POST['content']) ? $_POST['content'] : '';

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Content is required']);
    exit;
}

// Validate JSON
$contentArray = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON content: ' . json_last_error_msg()]);
    exit;
}

// Check if post exists
$db = \core\Database::connection();
$stmt = $db->prepare("SELECT id FROM pages WHERE id = ?");
$stmt->execute([$postId]);

if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Post not found']);
    exit;
}

// Save content
try {
    $result = JTB_Builder::saveContent($postId, $contentArray);

    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => [
                'message' => 'Content saved successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save content']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
