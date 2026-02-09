<?php
/**
 * JTB AI API: Generate Single Pattern
 * Generates a single pattern with specified variant
 *
 * POST /api/jtb/ai/generate-pattern
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Require POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Get parameters
$patternName = trim($input['pattern_name'] ?? '');
$variant = trim($input['variant'] ?? 'default');
$style = $input['style'] ?? 'modern';
$industry = $input['industry'] ?? 'technology';

// Pattern name is required
if (empty($patternName)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'pattern_name is required']);
    exit;
}

// Verify required classes are loaded
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Generator')) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'AI Generator class not loaded. Check router.php configuration.'
    ]);
    exit;
}

try {
    // Build context
    $context = [
        'style' => $style,
        'industry' => $industry,
    ];

    // Generate single pattern
    $result = JTB_AI_Generator::generatePattern($patternName, $variant, $context);

    if (!$result['ok']) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'error' => $result['error'] ?? 'Pattern generation failed'
        ]);
        exit;
    }

    // Return success with the section
    echo json_encode([
        'ok' => true,
        'section' => $result['section'],
        'pattern' => $result['pattern'],
        'variant' => $result['variant'],
    ]);

} catch (\Exception $e) {
    error_log('JTB AI generate-pattern error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during pattern generation'
    ]);
}
