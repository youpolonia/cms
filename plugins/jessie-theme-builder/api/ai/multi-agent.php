<?php
/**
 * JTB AI Multi-Agent Endpoint
 *
 * Proxy endpoint that routes to generate-website.php with action=multi-agent
 * This maintains MVC routing compatibility while reusing the comprehensive
 * multi-agent handling in generate-website.php
 *
 * POST /api/jtb/ai/multi-agent
 *
 * Steps:
 * - start: Initialize session
 * - mockup: Generate HTML mockup
 * - mockup-iterate: Iterate on mockup
 * - accept: Accept mockup and start build
 * - build: Run build step (architect, content, stylist, seo, images, assemble)
 * - status: Get session status
 * - result: Get final website
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Debug logging
$debugLog = function($msg) {
    file_put_contents('/tmp/jtb_multiagent_debug.log', "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n", FILE_APPEND);
};

$debugLog("=== Multi-Agent Request Started ===");

// Parse request
$input = file_get_contents('php://input');
$debugLog("Input length: " . strlen($input));
$data = json_decode($input, true);
$debugLog("JSON decode result: " . ($data ? "OK, step=" . ($data['step'] ?? 'null') : "FAILED"));

if (!$data) {
    $data = $_POST;
}

if (empty($data)) {
    jtb_json_response(false, [], 'Invalid request data', 400);
    exit;
}

// Check if MultiAgent class is available
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_MultiAgent')) {
    jtb_json_response(false, [], 'JTB_AI_MultiAgent class not loaded', 500);
    exit;
}

// Get step from request
$step = $data['step'] ?? 'start';

// Route to appropriate method
switch ($step) {

    // ========================================
    // Start new multi-agent session
    // ========================================
    case 'start':
        $prompt = $data['prompt'] ?? '';
        if (empty($prompt)) {
            jtb_json_response(false, [], 'Prompt is required', 400);
            exit;
        }

        $result = JTB_AI_MultiAgent::startSession($prompt, [
            'industry' => $data['industry'] ?? 'general',
            'style' => $data['style'] ?? 'modern',
            'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact'],
            'options' => $data['options'] ?? []
        ]);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Session start failed', 500);
            exit;
        }

        jtb_json_response(true, $result);
        break;

    // ========================================
    // Generate mockup
    // ========================================
    case 'mockup':
        $sessionId = $data['session_id'] ?? '';
        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }

        $result = JTB_AI_MultiAgent::generateMockup($sessionId);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Mockup generation failed', 500);
            exit;
        }

        jtb_json_response(true, $result);
        break;

    // ========================================
    // Iterate on mockup
    // ========================================
    case 'mockup-iterate':
        $sessionId = $data['session_id'] ?? '';
        $instruction = $data['instruction'] ?? '';

        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }
        if (empty($instruction)) {
            jtb_json_response(false, [], 'Instruction is required', 400);
            exit;
        }

        $result = JTB_AI_MultiAgent::iterateMockup($sessionId, $instruction);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Mockup iteration failed', 500);
            exit;
        }

        jtb_json_response(true, $result);
        break;

    // ========================================
    // Accept mockup and start build
    // ========================================
    case 'accept':
        $sessionId = $data['session_id'] ?? '';
        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }

        $result = JTB_AI_MultiAgent::acceptMockup($sessionId);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Mockup acceptance failed', 500);
            exit;
        }

        jtb_json_response(true, $result);
        break;

    // ========================================
    // Run build step
    // ========================================
    case 'build':
        $sessionId = $data['session_id'] ?? '';
        $buildStep = $data['build_step'] ?? '';

        $debugLog("=== BUILD step: {$buildStep}, session: {$sessionId} ===");

        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }
        if (empty($buildStep)) {
            jtb_json_response(false, [], 'Build step is required', 400);
            exit;
        }

        // Parse step (e.g., "content:home" -> step="content", page="home")
        $parts = explode(':', $buildStep);
        $step = $parts[0];
        $page = $parts[1] ?? null;

        $debugLog("Calling runBuildStep...");
        $debugLog("Calling runBuildStep: step=$step, page=" . ($page ?? 'null'));
        $result = JTB_AI_MultiAgent::runBuildStep($sessionId, $step, $page);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Build step failed', 500);
            exit;
        }

        jtb_json_response(true, $result);
        break;

    // ========================================
    // Get session status
    // ========================================
    case 'status':
        $sessionId = $data['session_id'] ?? '';
        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }

        $session = JTB_AI_MultiAgent::getSession($sessionId);
        if (!$session) {
            jtb_json_response(false, [], 'Session not found or expired', 404);
            exit;
        }

        jtb_json_response(true, [
            'session_id' => $sessionId,
            'status' => $session['status'],
            'phase' => $session['phase'],
            'steps' => $session['steps'],
            'current_step_index' => $session['current_step_index'],
            'stats' => $session['stats'],
            'has_mockup' => !empty($session['mockup_html']),
            'has_website' => !empty($session['final_website'])
        ]);
        break;

    // ========================================
    // Get final result
    // ========================================
    case 'result':
        $sessionId = $data['session_id'] ?? '';
        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }

        $session = JTB_AI_MultiAgent::getSession($sessionId);
        if (!$session) {
            jtb_json_response(false, [], 'Session not found or expired', 404);
            exit;
        }

        if ($session['status'] !== 'complete') {
            jtb_json_response(false, [], 'Website generation not complete. Current status: ' . $session['status'], 400);
            exit;
        }

        jtb_json_response(true, [
            'session_id' => $sessionId,
            'website' => $session['final_website'],
            'stats' => $session['stats']
        ]);
        break;

    // ========================================
    // Get mockup (for preview)
    // ========================================
    case 'get-mockup':
        $sessionId = $data['session_id'] ?? '';
        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }

        $session = JTB_AI_MultiAgent::getSession($sessionId);
        if (!$session) {
            jtb_json_response(false, [], 'Session not found or expired', 404);
            exit;
        }

        if (empty($session['mockup_html'])) {
            jtb_json_response(false, [], 'No mockup available. Generate mockup first.', 400);
            exit;
        }

        jtb_json_response(true, [
            'session_id' => $sessionId,
            'mockup_html' => $session['mockup_html'],
            'structure' => $session['mockup_structure'],
            'iterations' => $session['mockup_iterations'] ?? []
        ]);
        break;

    // ========================================
    // Quick mockup (start + mockup in one call)
    // ========================================
    case 'quick-mockup':
        $debugLog("=== QUICK-MOCKUP started ===");
        $prompt = $data['prompt'] ?? '';
        if (empty($prompt)) {
            $debugLog("ERROR: Prompt is empty");
            jtb_json_response(false, [], 'Prompt is required', 400);
            exit;
        }
        $debugLog("Prompt: " . substr($prompt, 0, 100));
        $debugLog("Industry: " . ($data['industry'] ?? 'general'));
        $debugLog("Style: " . ($data['style'] ?? 'modern'));
        $debugLog("Pages: " . json_encode($data['pages'] ?? []));

        // Start session
        try {
            $debugLog("Calling JTB_AI_MultiAgent::startSession...");
            $sessionResult = JTB_AI_MultiAgent::startSession($prompt, [
                'industry' => $data['industry'] ?? 'general',
                'style' => $data['style'] ?? 'modern',
                'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact'],
                'options' => $data['options'] ?? []
            ]);
            $debugLog("startSession result: ok=" . ($sessionResult['ok'] ? 'true' : 'false'));
        } catch (\Throwable $e) {
            $debugLog("EXCEPTION in startSession: " . $e->getMessage());
            $debugLog("Stack trace: " . $e->getTraceAsString());
            jtb_json_response(false, [], 'Exception in startSession: ' . $e->getMessage(), 500);
            exit;
        }

        if (!$sessionResult['ok']) {
            $debugLog("startSession failed: " . ($sessionResult['error'] ?? 'unknown'));
            jtb_json_response(false, [], $sessionResult['error'] ?? 'Session start failed', 500);
            exit;
        }
        $debugLog("Session ID: " . $sessionResult['session_id']);

        // Generate mockup
        try {
            $debugLog("Calling JTB_AI_MultiAgent::generateMockup...");
            $mockupResult = JTB_AI_MultiAgent::generateMockup($sessionResult['session_id']);
            $debugLog("generateMockup result: ok=" . ($mockupResult['ok'] ? 'true' : 'false'));
        } catch (\Throwable $e) {
            $debugLog("EXCEPTION in generateMockup: " . $e->getMessage());
            $debugLog("Stack trace: " . $e->getTraceAsString());
            jtb_json_response(false, [], 'Exception in generateMockup: ' . $e->getMessage(), 500);
            exit;
        }

        if (!$mockupResult['ok']) {
            $debugLog("generateMockup failed: " . ($mockupResult['error'] ?? 'unknown'));
            jtb_json_response(false, [], $mockupResult['error'] ?? 'Mockup generation failed', 500);
            exit;
        }

        $debugLog("=== QUICK-MOCKUP completed successfully ===");
        jtb_json_response(true, array_merge($mockupResult, [
            'industry' => $sessionResult['industry'],
            'style' => $sessionResult['style'] ?? 'modern',
            'pages' => $sessionResult['pages']
        ]));
        break;

    // ========================================
    // Unknown step
    // ========================================
    default:
        jtb_json_response(false, [], "Unknown step: {$step}. Valid steps: start, mockup, mockup-iterate, accept, build, status, result, get-mockup, quick-mockup", 400);
        break;
}
