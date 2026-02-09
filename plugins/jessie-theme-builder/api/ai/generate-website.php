<?php
/**
 * JTB AI Generate Website Endpoint
 *
 * Generates a COMPLETE WEBSITE in one request using JTB_AI_Website class.
 * Supports multiple actions: generate, brand-kit, competitor, variants,
 * regenerate-content, translate, progressive.
 *
 * NO HARDCODED DOCUMENTATION - uses JTB_AI_Schema for all module schemas.
 *
 * POST /api/jtb/ai/generate-website
 *
 * @package JessieThemeBuilder
 * @since 2026-02-04
 * @updated 2026-02-05 - Added 6 new actions (8.1-8.6)
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// TEMP DEBUG
file_put_contents('/tmp/api_debug.log', date('Y-m-d H:i:s') . " - API called\n", FILE_APPEND);

// Increase PHP execution time for AI generation (may take 60-120s)
set_time_limit(300);

// Ensure method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jtb_json_response(false, [], 'Method not allowed', 405);
    exit;
}

// Parse request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

if (empty($data)) {
    jtb_json_response(false, [], 'Invalid request data', 400);
    exit;
}

// Set response header
header('Content-Type: application/json');

// Check if JTB_AI_Website is available
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Website')) {
    jtb_json_response(false, [], 'JTB_AI_Website class not loaded', 500);
    exit;
}

// Route by action
$action = $data['action'] ?? 'generate';

// DEBUG LOG TO FILE
file_put_contents('/tmp/api_debug.log', date('H:i:s') . ' ai_provider=' . ($data['ai_provider'] ?? 'NULL') . ' ai_model=' . ($data['ai_model'] ?? 'NULL') . "\n", FILE_APPEND);
error_log('ai_provider from POST: ' . ($data['ai_provider'] ?? 'NOT SET'));
error_log('ai_model from POST: ' . ($data['ai_model'] ?? 'NOT SET'));
error_log('provider from POST: ' . ($data['provider'] ?? 'NOT SET'));
error_log('model from POST: ' . ($data['model'] ?? 'NOT SET'));

switch ($action) {

    // ========================================
    // DEFAULT: Generate full website
    // ========================================
    case 'generate':
        $prompt = $data['prompt'] ?? '';
        if (empty($prompt)) {
            jtb_json_response(false, [], 'Prompt is required', 400);
            exit;
        }

        // Set provider override if specified by user
        if (!empty($data['ai_provider'])) {
            $ai = JTB_AI_Core::getInstance();
            $ai->setProvider($data['ai_provider']);
        }

        $result = JTB_AI_Website::generate($prompt, [
            'industry' => $data['industry'] ?? 'general',
            'style' => $data['style'] ?? 'modern',
            'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact'],
            'model' => $data['ai_model'] ?? $data['model'] ?? null
        ]);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Generation failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'website' => $result['website'],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // 8.1: Brand Kit Extraction
    // ========================================
    case 'brand-kit':
        $url = $data['url'] ?? '';
        if (empty($url)) {
            jtb_json_response(false, [], 'URL is required for brand kit extraction', 400);
            exit;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/\.(png|jpg|jpeg|gif|svg|webp)$/i', $url)) {
            jtb_json_response(false, [], 'Invalid URL format', 400);
            exit;
        }

        $result = JTB_AI_Website::extractBrandKit($url);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Brand kit extraction failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'brand_kit' => $result['brand_kit'],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // 8.2: Competitor Analysis
    // ========================================
    case 'competitor':
        $url = $data['url'] ?? '';
        $prompt = $data['prompt'] ?? '';

        if (empty($url)) {
            jtb_json_response(false, [], 'Competitor URL is required', 400);
            exit;
        }
        if (empty($prompt)) {
            jtb_json_response(false, [], 'Business description (prompt) is required', 400);
            exit;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            jtb_json_response(false, [], 'Invalid competitor URL format', 400);
            exit;
        }

        $result = JTB_AI_Website::analyzeCompetitor($url, $prompt, [
            'industry' => $data['industry'] ?? 'general',
            'style' => $data['style'] ?? 'modern',
            'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact']
        ]);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Competitor analysis failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'website' => $result['website'],
            'competitor_analysis' => $result['competitor_analysis'] ?? [],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // 8.3: A/B Variants
    // ========================================
    case 'variants':
        $sectionType = $data['section_type'] ?? '';
        if (empty($sectionType)) {
            jtb_json_response(false, [], 'Section type is required', 400);
            exit;
        }

        $count = (int)($data['count'] ?? 3);
        $context = $data['context'] ?? [];
        if (is_string($context)) {
            $context = json_decode($context, true) ?? [];
        }

        $result = JTB_AI_Website::generateVariants($sectionType, $context, $count);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Variant generation failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'variants' => $result['variants'],
            'section_type' => $result['section_type'],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // 8.4: Content Regeneration
    // ========================================
    case 'regenerate-content':
        $moduleType = $data['module_type'] ?? '';
        $instruction = $data['instruction'] ?? '';

        if (empty($moduleType)) {
            jtb_json_response(false, [], 'Module type is required', 400);
            exit;
        }
        if (empty($instruction)) {
            jtb_json_response(false, [], 'Instruction is required (e.g., "make more professional")', 400);
            exit;
        }

        $attrs = $data['attrs'] ?? [];
        if (is_string($attrs)) {
            $attrs = json_decode($attrs, true) ?? [];
        }

        $context = $data['context'] ?? [];
        if (is_string($context)) {
            $context = json_decode($context, true) ?? [];
        }

        $result = JTB_AI_Website::regenerateContent($moduleType, $attrs, $instruction, $context);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Content regeneration failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'attrs' => $result['attrs'],
            'changed_fields' => $result['changed_fields'] ?? [],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // 8.5: Multi-language Translation
    // ========================================
    case 'translate':
        $website = $data['website'] ?? null;
        $language = $data['language'] ?? '';

        if (empty($website) || !is_array($website)) {
            jtb_json_response(false, [], 'Website data is required', 400);
            exit;
        }
        if (empty($language)) {
            jtb_json_response(false, [], 'Target language is required', 400);
            exit;
        }

        $result = JTB_AI_Website::translateWebsite($website, $language, $data['options'] ?? []);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Translation failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'website' => $result['website'],
            'language' => $result['language'],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // 8.6: Progressive Enhancement
    // ========================================
    case 'progressive':
        $prompt = $data['prompt'] ?? '';
        if (empty($prompt)) {
            jtb_json_response(false, [], 'Prompt is required', 400);
            exit;
        }

        $result = JTB_AI_Website::progressiveGenerate($prompt, [
            'industry' => $data['industry'] ?? 'general',
            'style' => $data['style'] ?? 'modern',
            'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact']
        ]);

        if (!$result['ok']) {
            jtb_json_response(false, [], $result['error'] ?? 'Progressive generation failed', 500);
            exit;
        }

        jtb_json_response(true, [
            'stages' => $result['stages'],
            'final_website' => $result['final_website'],
            'stats' => $result['stats'] ?? []
        ]);
        break;

    // ========================================
    // MULTI-AGENT: Start session
    // ========================================
    case 'multi-agent':
        $subStep = $data['step'] ?? 'start';

        switch ($subStep) {
            // Start new multi-agent session
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

            // Quick mockup: start session + generate mockup in one call
            case 'quick-mockup':
                $prompt = $data['prompt'] ?? '';
                if (empty($prompt)) {
                    jtb_json_response(false, [], 'Prompt is required', 400);
                    exit;
                }

                // Step 1: Start session
                $sessionResult = JTB_AI_MultiAgent::startSession($prompt, [
                    'industry' => $data['industry'] ?? 'general',
                    'style' => $data['style'] ?? 'modern',
                    'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact'],
                    'options' => $data['options'] ?? [],
                    'ai_provider' => $data['ai_provider'] ?? null,
                    'ai_model' => $data['ai_model'] ?? null,
                    'language' => $data['language'] ?? ''
                ]);

                if (!$sessionResult['ok']) {
                    jtb_json_response(false, [], $sessionResult['error'] ?? 'Session start failed', 500);
                    exit;
                }

                // Step 2: Generate mockup
                $mockupResult = JTB_AI_MultiAgent::generateMockup($sessionResult['session_id']);

                if (!$mockupResult['ok']) {
                    jtb_json_response(false, [], $mockupResult['error'] ?? 'Mockup generation failed', 500);
                    exit;
                }

                // Combine results
                jtb_json_response(true, array_merge($mockupResult, [
                    'session_id' => $sessionResult['session_id']
                ]));
                break;

            // Generate mockup (requires existing session)

    // ========================================
    // SAVE WEBSITE: Save generated website to CMS
    // ========================================
    case 'save-website':
        $sessionId = $data['session_id'] ?? '';
        $mapping = $data['mapping'] ?? [];
        
        if (empty($sessionId)) {
            jtb_json_response(false, [], 'Session ID is required', 400);
            exit;
        }
        
        if (empty($mapping)) {
            jtb_json_response(false, [], 'Mapping data is required', 400);
            exit;
        }
        
        $session = JTB_AI_MultiAgent::getSession($sessionId);
        if (!$session) {
            jtb_json_response(false, [], 'Session not found or expired', 404);
            exit;
        }
        
        $website = $session['final_website'] ?? null;
        if (!$website) {
            jtb_json_response(false, [], 'No website data in session', 400);
            exit;
        }
        
        $savedCount = 0;
        $errors = [];
        $savedItems = [];
        
        try {
            $db = \core\Database::connection();
            
            // Save Header
            if (!empty($mapping['header']['target_id']) && !empty($mapping['header']['content'])) {
                $headerId = (int)$mapping['header']['target_id'];
                $headerContent = json_encode([
                    'version' => '1.0',
                    'content' => $mapping['header']['content']['sections'] ?? []
                ]);
                
                $stmt = $db->prepare("UPDATE jtb_templates SET content = ?, updated_at = NOW() WHERE id = ? AND type = 'header'");
                if ($stmt->execute([$headerContent, $headerId])) {
                    $savedCount++;
                    $savedItems[] = "Header #$headerId";
                }
            }
            
            // Save Footer
            if (!empty($mapping['footer']['target_id']) && !empty($mapping['footer']['content'])) {
                $footerId = (int)$mapping['footer']['target_id'];
                $footerContent = json_encode([
                    'version' => '1.0',
                    'content' => $mapping['footer']['content']['sections'] ?? []
                ]);
                
                $stmt = $db->prepare("UPDATE jtb_templates SET content = ?, updated_at = NOW() WHERE id = ? AND type = 'footer'");
                if ($stmt->execute([$footerContent, $footerId])) {
                    $savedCount++;
                    $savedItems[] = "Footer #$footerId";
                }
            }
            
            // Save Pages
            foreach ($mapping['pages'] ?? [] as $pageKey => $pageMapping) {
                $pageContent = $pageMapping['content'] ?? null;
                if (!$pageContent) continue;
                
                $contentJson = json_encode([
                    'version' => '1.0',
                    'content' => $pageContent['sections'] ?? []
                ]);
                
                if ($pageMapping['create_new']) {
                    // Create new page
                    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $pageKey));
                    $title = ucfirst($pageKey);
                    
                    $stmt = $db->prepare("
                        INSERT INTO posts (title, slug, content, status, created_at, updated_at) 
                        VALUES (?, ?, '', 'draft', NOW(), NOW())
                    ");
                    
                    if ($stmt->execute([$title, $slug])) {
                        $newPageId = $db->lastInsertId();
                        
                        // Save JTB content to jtb_pages
                        $stmt = $db->prepare("
                            INSERT INTO jtb_pages (post_id, content, version, created_at, updated_at)
                            VALUES (?, ?, 1, NOW(), NOW())
                            ON DUPLICATE KEY UPDATE content = VALUES(content), version = version + 1, updated_at = NOW()
                        ");
                        $stmt->execute([$newPageId, $contentJson]);
                        
                        $savedCount++;
                        $savedItems[] = "New page: $title (/$slug)";
                    }
                } else if (!empty($pageMapping['target_id'])) {
                    // Update existing page
                    $pageId = (int)$pageMapping['target_id'];
                    
                    $stmt = $db->prepare("
                        INSERT INTO jtb_pages (post_id, content, version, created_at, updated_at)
                        VALUES (?, ?, 1, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE content = VALUES(content), version = version + 1, updated_at = NOW()
                    ");
                    
                    if ($stmt->execute([$pageId, $contentJson])) {
                        $savedCount++;
                        $savedItems[] = "Page #$pageId";
                    }
                }
            }
            
            jtb_json_response(true, [
                'saved_count' => $savedCount,
                'saved_items' => $savedItems,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            jtb_json_response(false, [], 'Database error: ' . $e->getMessage(), 500);
        }
        break;

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

            // Iterate on mockup
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

            // Accept mockup and start build
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

            // Run build step
            case 'build':
                $sessionId = $data['session_id'] ?? '';
                $buildStep = $data['build_step'] ?? '';

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

                $result = JTB_AI_MultiAgent::runBuildStep($sessionId, $step, $page);

                if (!$result['ok']) {
                    jtb_json_response(false, [], $result['error'] ?? 'Build step failed', 500);
                    exit;
                }

                jtb_json_response(true, $result);
                break;

            // Get session status
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
                    'stats' => $session['stats']
                ]);
                break;

            // Get final result
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
                    jtb_json_response(false, [], 'Website generation not complete', 400);
                    exit;
                }

                jtb_json_response(true, [
                    'website' => $session['final_website'],
                    'stats' => $session['stats']
                ]);
                break;

            default:
                jtb_json_response(false, [], "Unknown multi-agent step: {$subStep}. Valid steps: start, mockup, mockup-iterate, accept, build, status, result", 400);
                break;
        }
        break;

    // ========================================
    // MOCKUP: Quick mockup generation (shortcut)
    // ========================================
    case 'mockup':
        $prompt = $data['prompt'] ?? '';
        if (empty($prompt)) {
            jtb_json_response(false, [], 'Prompt is required', 400);
            exit;
        }

        // Start session and generate mockup in one call
        $sessionResult = JTB_AI_MultiAgent::startSession($prompt, [
            'industry' => $data['industry'] ?? 'general',
            'style' => $data['style'] ?? 'modern',
            'pages' => $data['pages'] ?? ['home', 'about', 'services', 'contact'],
            'options' => $data['options'] ?? []
        ]);

        if (!$sessionResult['ok']) {
            jtb_json_response(false, [], $sessionResult['error'] ?? 'Session start failed', 500);
            exit;
        }

        $mockupResult = JTB_AI_MultiAgent::generateMockup($sessionResult['session_id']);

        if (!$mockupResult['ok']) {
            jtb_json_response(false, [], $mockupResult['error'] ?? 'Mockup generation failed', 500);
            exit;
        }

        jtb_json_response(true, $mockupResult);
        break;

    // ========================================
    // Unknown action
    // ========================================
    default:
        jtb_json_response(false, [], "Unknown action: {$action}. Valid actions: generate, brand-kit, competitor, variants, regenerate-content, translate, progressive, multi-agent, mockup", 400);
        break;
}
