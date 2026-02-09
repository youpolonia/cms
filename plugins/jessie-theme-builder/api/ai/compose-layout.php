<?php
/**
 * JTB AI API: Compose Layout
 * Generates layouts using the compositional pattern system
 *
 * POST /api/jtb/ai/compose-layout
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Capture PHP errors/warnings before they corrupt JSON
$phpErrors = [];
set_error_handler(function($severity, $message, $file, $line) use (&$phpErrors) {
    $phpErrors[] = [
        'severity' => $severity,
        'message' => $message,
        'file' => basename($file),
        'line' => $line
    ];
    return true; // Don't execute default error handler
});

// Clean any output buffer
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

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
$prompt = trim($input['prompt'] ?? '');
$compositionIntent = $input['composition_intent'] ?? null;
$style = $input['style'] ?? 'modern';
$industry = $input['industry'] ?? null;
$pageType = $input['page_type'] ?? null;

// NEW: AST mode flag - enables AI-driven layout generation
// When true, AI actually decides the page structure
// When false, uses legacy hardcoded patterns
// DEFAULT IS NOW TRUE - AI makes all layout decisions!
$useAST = $input['use_ast'] ?? true;

// NEW: AI provider and model selection
// Get default provider from ai_settings.json (no hardcodes!)
$settingsPath = CMS_ROOT . '/config/ai_settings.json';
$defaultProvider = 'anthropic';
if (file_exists($settingsPath)) {
    $settings = @json_decode(file_get_contents($settingsPath), true);
    if (!empty($settings['default_provider'])) {
        $defaultProvider = $settings['default_provider'];
    }
}
$aiProvider = $input['ai_provider'] ?? $defaultProvider;
$aiModel = $input['ai_model'] ?? null; // null = use provider's default

// Either prompt or composition_intent is required
if (empty($prompt) && empty($compositionIntent)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Either prompt or composition_intent is required']);
    exit;
}

// DEBUG: Log what we received - use file_put_contents for reliability
$debugLog = "/tmp/jtb_debug.log";
file_put_contents($debugLog, '[JTB COMPOSE-LAYOUT] use_ast from input: ' . var_export($input['use_ast'] ?? 'NOT_SET', true) . "\n", FILE_APPEND);
file_put_contents($debugLog, '[JTB COMPOSE-LAYOUT] useAST variable: ' . var_export($useAST, true) . "\n", FILE_APPEND);
file_put_contents($debugLog, '[JTB COMPOSE-LAYOUT] ai_provider: ' . $aiProvider . "\n", FILE_APPEND);

try {
    // DEBUG: Log to file
    file_put_contents('/tmp/jtb_endpoint.log', "[compose-layout] ENTRY - useAST=" . var_export($useAST, true) . ", prompt=" . substr($prompt, 0, 50) . "\n", FILE_APPEND);

    // Build options
    $options = [
        'style' => $style,
        'use_ast' => $useAST, // NEW: Pass AST flag to generator
        'ai_provider' => $aiProvider, // NEW: AI provider
        'ai_model' => $aiModel, // NEW: AI model
    ];

    if ($compositionIntent) {
        $options['composition_intent'] = $compositionIntent;
    }
    if ($industry) {
        $options['industry'] = $industry;
    }
    if ($pageType) {
        $options['page_type'] = $pageType;
    }

    // Generate composed layout with quality validation
    $result = JTB_AI_Generator::generateWithValidation([
        'prompt' => $prompt ?: 'Create a page',
        'options' => $options,
    ]);

    // Extract quality data before sending response
    $quality = $result['_quality'] ?? null;
    unset($result['_quality']);

    if (!$result['ok']) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'error' => $result['error'] ?? 'Composition failed',
            'quality' => $quality,
        ]);
        exit;
    }

    // Clean output buffer
    $bufferOutput = ob_get_clean();

    // Return success with quality data
    $response = [
        'ok' => true,
        'layout' => [
            'version' => '1.0',
            'content' => $result['sections']
        ],
        'composition_intent' => $result['composition_intent'] ?? null,
        'patterns_used' => $result['patterns_used'] ?? [],
        'context' => $result['context'] ?? null,
        'quality' => $quality,
        // NEW: AST-related metadata
        'generation_mode' => $useAST ? 'ast' : 'legacy',
        'source' => $result['source'] ?? ($useAST ? 'ast' : 'composer'),
        'ai_provider' => $result['provider'] ?? $aiProvider,
        'ai_model' => $result['model'] ?? $aiModel,
        'provider' => $result['provider'] ?? null,
        'ast' => $useAST ? ($result['ast'] ?? null) : null,
    ];

    // Add debug info if there were PHP errors or buffer output
    if (!empty($phpErrors)) {
        $response['_php_errors'] = $phpErrors;
    }
    if (!empty($bufferOutput)) {
        $response['_buffer_output'] = substr($bufferOutput, 0, 500);
    }

    echo json_encode($response);

} catch (\Throwable $e) {
    $bufferOutput = ob_get_clean();
    error_log('JTB AI compose-layout error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        '_exception_file' => basename($e->getFile()) . ':' . $e->getLine(),
        '_php_errors' => $phpErrors ?? [],
        '_buffer_output' => $bufferOutput ? substr($bufferOutput, 0, 500) : null,
    ]);
}

// Restore error handler
restore_error_handler();
