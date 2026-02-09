<?php
/**
 * Multi-Agent Pipeline Test Script
 * Run via browser: http://localhost/plugins/jessie-theme-builder/test-multiagent.php
 */

define('CMS_ROOT', dirname(dirname(__DIR__)));
require_once CMS_ROOT . '/core/bootstrap.php';

$pluginPath = __DIR__;
require_once $pluginPath . '/includes/class-jtb-element.php';
require_once $pluginPath . '/includes/class-jtb-registry.php';
require_once $pluginPath . '/includes/class-jtb-fields.php';
require_once $pluginPath . '/includes/class-jtb-templates.php';
require_once $pluginPath . '/includes/class-jtb-renderer.php';
require_once $pluginPath . '/includes/class-jtb-css.php';
require_once $pluginPath . '/includes/class-jtb-global-settings.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-core.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-styles.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-multiagent.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-agent-mockup.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-agent-architect.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-agent-content.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-agent-stylist.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-agent-seo.php';
require_once $pluginPath . '/includes/ai/class-jtb-ai-agent-images.php';
require_once $pluginPath . '/includes/class-html-to-jtb-parser.php';

use JessieThemeBuilder\JTB_AI_MultiAgent;
use JessieThemeBuilder\JTB_AI_Core;

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Multi-Agent Test</title></head>";
echo "<body style='font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0;'>";

function out($msg, $type = 'info') {
    $colors = ['info' => '#e2e8f0', 'ok' => '#4ade80', 'fail' => '#f87171', 'warn' => '#fbbf24'];
    $color = $colors[$type] ?? $colors['info'];
    echo "<div style='color: {$color};'>" . htmlspecialchars($msg) . "</div>";
    ob_flush(); flush();
}

out("=== JTB MULTI-AGENT PIPELINE TEST ===");
out("");

$testPrompt = "PetPal - modern pet care. Dog walking, pet sitting, grooming.";
$testOptions = [
    'industry' => 'services', 'style' => 'modern',
    'pages' => ['home', 'about', 'services', 'contact'],
    'ai_provider' => 'deepseek', 'ai_model' => 'deepseek-v3'
];

out("Prompt: {$testPrompt}");
out("Provider: deepseek / deepseek-v3");
out("");

// 1. AI Check
out("1. Checking AI Core...");
$ai = JTB_AI_Core::getInstance();
out("   " . ($ai->isConfigured() ? "OK" : "FAIL"), $ai->isConfigured() ? 'ok' : 'fail');

// 2. Pexels
out("2. Checking Pexels...");
$db = \core\Database::connection();
$stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = 'pexels_api_key'");
$stmt->execute();
$pexelsKey = $stmt->fetchColumn();
out("   " . ($pexelsKey ? "OK" : "WARN - no key"), $pexelsKey ? 'ok' : 'warn');

// 3. Start
out("3. Starting session...");
$result = JTB_AI_MultiAgent::startSession($testPrompt, $testOptions);
if (!$result['ok']) { out("FAIL: " . ($result['error'] ?? '?'), 'fail'); exit; }
$sessionId = $result['session_id'];
out("   Session: {$sessionId}", 'ok');

// 4. Mockup
out("4. Generating mockup (30-60s)...");
$start = microtime(true);
$result = JTB_AI_MultiAgent::generateMockup($sessionId);
$time = round(microtime(true) - $start, 1);
if (!$result['ok']) { out("FAIL: " . ($result['error'] ?? '?'), 'fail'); exit; }
out("   OK - " . strlen($result['mockup_html'] ?? '') . " bytes in {$time}s", 'ok');

// 5. Approve
out("5. Approving...");
JTB_AI_MultiAgent::approveMockup($sessionId);
out("   OK", 'ok');

// 6. Build
out("6. Building...");
$session = JTB_AI_MultiAgent::getSession($sessionId);
foreach ($session['steps'] ?? [] as $step) {
    $start = microtime(true);
    $result = JTB_AI_MultiAgent::runBuildStep($sessionId);
    $time = round(microtime(true) - $start, 1);
    out("   [{$step}] " . ($result['ok'] ? "OK" : "FAIL") . " ({$time}s)", $result['ok'] ? 'ok' : 'fail');
    if ($result['status'] === 'complete') break;
}

// Summary
out("");
out("=== SUMMARY ===");
$session = JTB_AI_MultiAgent::getSession($sessionId);
$website = $session['final_website'] ?? null;
if ($website) {
    out("Header: " . count($website['header']['sections'] ?? []) . " sections");
    out("Footer: " . count($website['footer']['sections'] ?? []) . " sections");
    out("Pages: " . count($website['pages'] ?? []));
    $pexels = 0;
    foreach ($session['images'] ?? [] as $img) if (!empty($img['pexels_url'])) $pexels++;
    out("Pexels images: {$pexels}");
} else {
    out("No website generated", 'fail');
}
out("");
out("Session: /tmp/jtb_sessions/{$sessionId}.json");
echo "</body></html>";
