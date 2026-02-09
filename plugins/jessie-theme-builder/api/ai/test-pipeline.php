<?php
/**
 * Multi-Agent Pipeline Test Endpoint
 * GET /api/jtb/ai/test-pipeline
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: text/html; charset=utf-8');
ob_implicit_flush(true);

echo "<!DOCTYPE html><html><head><title>Multi-Agent Test</title></head>";
echo "<body style='font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0;'>";

function out($msg, $type = 'info') {
    $colors = ['info' => '#e2e8f0', 'ok' => '#4ade80', 'fail' => '#f87171', 'warn' => '#fbbf24'];
    echo "<div style='color: " . ($colors[$type] ?? '#e2e8f0') . ";'>" . htmlspecialchars($msg) . "</div>";
    if (ob_get_level()) ob_flush();
    flush();
}

out("=== JTB MULTI-AGENT PIPELINE TEST ===");
out("");

$testPrompt = "PetPal - modern pet care service. Dog walking, pet sitting, grooming, vet referrals.";
$testOptions = [
    'industry' => 'services',
    'style' => 'modern', 
    'pages' => ['home', 'about', 'services', 'contact'],
    'ai_provider' => 'deepseek',
    'ai_model' => 'deepseek-v3'
];

out("Prompt: {$testPrompt}");
out("Provider: deepseek-v3");
out("");

// 1. AI Check
out("1. Checking AI Core...");
$ai = JTB_AI_Core::getInstance();
out("   " . ($ai->isConfigured() ? "OK - configured" : "FAIL"), $ai->isConfigured() ? 'ok' : 'fail');

// 2. Pexels
out("2. Checking Pexels API...");
$db = \core\Database::connection();
$stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = 'pexels_api_key'");
$stmt->execute();
$pexelsKey = $stmt->fetchColumn();
out("   " . ($pexelsKey ? "OK - key found" : "WARN - no key"), $pexelsKey ? 'ok' : 'warn');

// 3. Start Session
out("3. Starting session...");
$result = JTB_AI_MultiAgent::startSession($testPrompt, $testOptions);
if (!$result['ok']) {
    out("   FAIL: " . ($result['error'] ?? 'Unknown'), 'fail');
    echo "</body></html>";
    exit;
}
$sessionId = $result['session_id'];
out("   Session: {$sessionId}", 'ok');

// 4. Mockup
out("");
out("4. Generating mockup (this takes 30-60 seconds)...");
$start = microtime(true);
$result = JTB_AI_MultiAgent::generateMockup($sessionId);
$time = round(microtime(true) - $start, 1);
if (!$result['ok']) {
    out("   FAIL: " . ($result['error'] ?? 'Unknown'), 'fail');
    echo "</body></html>";
    exit;
}
out("   OK - " . strlen($result['mockup_html'] ?? '') . " bytes in {$time}s", 'ok');

// 5. Accept Mockup
out("5. Accepting mockup...");
$result = JTB_AI_MultiAgent::acceptMockup($sessionId);
out("   " . ($result['ok'] ? "OK" : "FAIL: " . ($result['error'] ?? '')), $result['ok'] ? 'ok' : 'fail');
if (!$result['ok']) {
    echo "</body></html>";
    exit;
}

// 6. Build Steps
out("");
out("6. Running build pipeline...");
$session = JTB_AI_MultiAgent::getSession($sessionId);
$steps = $session['steps'] ?? [];
$totalTime = 0;

foreach ($steps as $buildStep) {
    // Parse step (e.g., "content:home" -> step="content", page="home")
    $parts = explode(':', $buildStep);
    $step = $parts[0];
    $page = $parts[1] ?? null;
    
    $start = microtime(true);
    $result = JTB_AI_MultiAgent::runBuildStep($sessionId, $step, $page);
    $time = round(microtime(true) - $start, 1);
    $totalTime += $time;
    
    $status = $result['ok'] ? 'OK' : 'FAIL';
    $tokens = $result['tokens_used'] ?? 0;
    out("   [{$status}] {$buildStep} ({$time}s, {$tokens} tokens)", $result['ok'] ? 'ok' : 'fail');
    
    if (!$result['ok']) {
        out("      Error: " . ($result['error'] ?? 'Unknown'), 'fail');
    }
    
    if (($result['status'] ?? '') === 'complete') break;
}

// Summary
out("");
out("=== SUMMARY ===");
out("Total build time: {$totalTime}s");

$session = JTB_AI_MultiAgent::getSession($sessionId);
$website = $session['final_website'] ?? null;

if ($website) {
    out("");
    $hSections = count($website['header']['sections'] ?? []);
    $fSections = count($website['footer']['sections'] ?? []);
    $pageCount = count($website['pages'] ?? []);
    
    out("Header: {$hSections} section(s)", 'ok');
    out("Footer: {$fSections} section(s)", 'ok');
    out("Pages: {$pageCount}", 'ok');
    
    foreach ($website['pages'] ?? [] as $name => $page) {
        $sCount = count($page['sections'] ?? []);
        out("  - {$name}: {$sCount} sections");
    }
    
    // Images
    $images = $session['images'] ?? [];
    $pexelsCount = 0;
    foreach ($images as $img) {
        if (!empty($img['pexels_url'])) $pexelsCount++;
    }
    out("");
    out("Images total: " . count($images));
    out("Pexels images: {$pexelsCount}", $pexelsCount > 0 ? 'ok' : 'warn');
    
    // SEO
    $seoPages = count($website['seo'] ?? []) - 1;
    out("SEO pages: {$seoPages}");
    
} else {
    out("No website generated - check /tmp/jtb_multiagent_debug.log", 'fail');
}

out("");
out("Session file: /tmp/jtb_sessions/{$sessionId}.json");
out("");
out("=== TEST COMPLETE ===");

echo "</body></html>";
