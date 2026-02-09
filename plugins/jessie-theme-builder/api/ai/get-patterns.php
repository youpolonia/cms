<?php
/**
 * JTB AI API: Get Patterns
 * Returns available composition patterns and their variants
 *
 * GET /api/jtb/ai/get-patterns
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify required classes are loaded
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Generator')) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'AI Generator class not loaded. Check router.php configuration.'
    ]);
    exit;
}

if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Composer')) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'AI Composer class not loaded. Check router.php configuration.'
    ]);
    exit;
}

try {
    // Get all available patterns
    $patterns = JTB_AI_Generator::getAvailablePatterns();

    // Get info for each pattern
    $patternInfo = [];
    foreach ($patterns as $category => $categoryPatterns) {
        foreach ($categoryPatterns as $patternName => $variants) {
            $info = JTB_AI_Generator::getPatternInfo($patternName);
            $patternInfo[$patternName] = array_merge($info, [
                'category' => $category,
                'variants' => $variants,
            ]);
        }
    }

    // Get composition sequences
    $sequences = JTB_AI_Composer::getCompositionSequences();

    echo json_encode([
        'ok' => true,
        'patterns' => $patterns,
        'pattern_info' => $patternInfo,
        'composition_sequences' => $sequences,
        'composition_intents' => [
            'product_launch' => 'Product/service launch page',
            'service_showcase' => 'Service presentation page',
            'brand_story' => 'About/brand story page',
            'saas_landing' => 'SaaS/tech landing page',
            'portfolio' => 'Portfolio/showcase page',
            'agency' => 'Agency presentation page',
        ],
    ]);

} catch (\Exception $e) {
    error_log('JTB AI get-patterns error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Failed to retrieve patterns'
    ]);
}
