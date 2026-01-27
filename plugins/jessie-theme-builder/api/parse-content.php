<?php
/**
 * Parse Content API Endpoint
 * POST /api/jtb/parse-content
 *
 * Parses HTML content and returns JTB structure
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

// Authentication is already checked in router.php

// Get input (support both form data and JSON)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

$postId = isset($input['post_id']) ? (int) $input['post_id'] : 0;
$postType = $input['type'] ?? 'page';
$method = $input['method'] ?? 'modules'; // modules, code, fresh
$layout = $input['layout'] ?? 'classic'; // Layout for articles: classic, hero, magazine, minimal

// Validate type
if (!in_array($postType, ['page', 'article'])) {
    $postType = 'page';
}

// Validate method
if (!in_array($method, ['modules', 'code', 'fresh'])) {
    $method = 'modules';
}

// Validate layout
$validLayouts = ['classic', 'hero', 'magazine', 'minimal'];
if (!in_array($layout, $validLayouts)) {
    $layout = 'classic';
}

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

// If fresh, return empty content
if ($method === 'fresh') {
    echo json_encode([
        'success' => true,
        'data' => [
            'content' => [
                'version' => '1.0',
                'content' => []
            ],
            'modules_count' => 0,
            'method' => 'fresh',
            'warnings' => []
        ]
    ]);
    exit;
}

// Load the parser and layouts
require_once __DIR__ . '/../includes/class-jtb-content-parser.php';
require_once __DIR__ . '/../includes/class-jtb-article-layouts.php';

// Get content from correct table
$db = \core\Database::connection();
$table = ($postType === 'article') ? 'articles' : 'pages';

// Build query based on table - also get title for articles
if ($postType === 'article') {
    $stmt = $db->prepare("SELECT id, title, content, featured_image FROM {$table} WHERE id = ?");
} else {
    $stmt = $db->prepare("SELECT id, content FROM {$table} WHERE id = ?");
}

$stmt->execute([$postId]);
$post = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Post not found']);
    exit;
}

$html = $post['content'] ?? '';
$featuredImage = $post['featured_image'] ?? null;
$title = $post['title'] ?? null;

$warnings = [];

// Parse based on method
try {
    if ($method === 'code') {
        $content = JTB_Content_Parser::parseAsCode($html);
        $modulesCount = 1;
    } else {
        // For articles, use layout system
        if ($postType === 'article') {
            // Parse HTML to modules first (without wrapping in structure)
            $modules = JTB_Content_Parser::parseToModules($html);

            // Apply selected layout
            $content = JTB_Article_Layouts::applyLayout($layout, $modules, $featuredImage, $title);
        } else {
            // For pages, use standard parsing
            $content = JTB_Content_Parser::parse($html, $featuredImage);
        }

        // Count modules
        $modulesCount = 0;
        if (!empty($content['content'])) {
            foreach ($content['content'] as $section) {
                if (!empty($section['children'])) {
                    foreach ($section['children'] as $row) {
                        if (!empty($row['children'])) {
                            foreach ($row['children'] as $column) {
                                $modulesCount += count($column['children'] ?? []);
                            }
                        }
                    }
                }
            }
        }

        // Add warnings for complex elements
        if (strpos($html, '<table') !== false) {
            $warnings[] = 'Tables were converted to code blocks';
        }
        if (strpos($html, '<form') !== false) {
            $warnings[] = 'Forms were converted to code blocks';
        }
        if (strpos($html, '<iframe') !== false && strpos($html, 'youtube') === false && strpos($html, 'vimeo') === false) {
            $warnings[] = 'Some iframes were converted to code blocks';
        }
    }

    $response = [
        'success' => true,
        'data' => [
            'content' => $content,
            'modules_count' => $modulesCount,
            'method' => $method,
            'warnings' => $warnings
        ]
    ];

    // Add layout info for articles
    if ($postType === 'article') {
        $response['data']['layout'] = $layout;
    }

    echo json_encode($response);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Parse error: ' . $e->getMessage()
    ]);
}
