<?php
/**
 * Parse HTML API Endpoint
 * POST /api/jtb/parse-html
 *
 * Converts HTML to JTB content structure
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

// Get HTML content
$html = isset($_POST['html']) ? $_POST['html'] : '';

// Also accept JSON body
if (empty($html)) {
    $rawInput = file_get_contents('php://input');
    $jsonInput = json_decode($rawInput, true);
    if ($jsonInput && isset($jsonInput['html'])) {
        $html = $jsonInput['html'];
    }
}

if (empty($html)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'HTML content is required']);
    exit;
}

// Get options
$options = [];
if (isset($_POST['options'])) {
    $options = is_array($_POST['options']) ? $_POST['options'] : json_decode($_POST['options'], true);
}

// Load parser
$parserPath = dirname(__DIR__) . '/includes/parser/class-jtb-html-parser.php';
if (!file_exists($parserPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Parser not found']);
    exit;
}

require_once $parserPath;

// Parse HTML
try {
    $parser = new JTB_HTML_Parser();
    $result = $parser->parse($html, $options);

    if (isset($result['error'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $result['error']]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'content' => $result['content'],
            'css' => $result['css'] ?? '',
            'mode' => $result['mode'] ?? 'generic',
            'stats' => $result['stats'] ?? [],
        ]
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Parse error: ' . $e->getMessage()]);
}
