<?php
/**
 * AI Content Creator PRO
 * Full content generation with Research integration, title generator, and smart prefill
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/ai_content.php';
require_once __DIR__ . '/../core/ai_models.php';

if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$DRAFT_DIR = __DIR__ . '/../cms_storage/ai_drafts';
if (!is_dir($DRAFT_DIR)) { @mkdir($DRAFT_DIR, 0775, true); }

$aiConfig = ai_config_load();
$aiConfigured = !empty($aiConfig['api_key']);

// URL PARAMETER HANDLING - Accept data from Research module
$prefill = [
    'keyword' => trim($_GET['keyword'] ?? ''),
    'title' => trim($_GET['title'] ?? ''),
    'keywords' => trim($_GET['keywords'] ?? ''),
    'phrases' => trim($_GET['phrases'] ?? ''),
    'headings' => str_replace('|', "\n", trim($_GET['headings'] ?? '')),
    'wordcount' => intval($_GET['wordcount'] ?? 0)
];
$hasPrefill = !empty($prefill['keyword']) || !empty($prefill['title']) || !empty($prefill['keywords']);

$lengthOptions = [
    'short' => ['label' => 'Short (~300 words)', 'target' => 300],
    'medium' => ['label' => 'Medium (~600 words)', 'target' => 600],
    'long' => ['label' => 'Long (~1000 words)', 'target' => 1000],
    'very_long' => ['label' => 'Very Long (~1500+ words)', 'target' => 1500]
];

// Add custom "recommended" option if we have a specific word count from SEO Research
$recommendedLength = 'medium';
if ($prefill['wordcount'] > 0) {
    // Add recommended option with exact value
    $lengthOptions = [
        'recommended' => ['label' => '⭐ SEO Recommended (~' . $prefill['wordcount'] . ' words)', 'target' => $prefill['wordcount']]
    ] + $lengthOptions;
    $recommendedLength = 'recommended';
}

// AJAX HANDLERS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax'])) {
    header('Content-Type: application/json');
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';

    // TITLE GENERATOR
    if ($action === 'generate_titles') {
        if (!$aiConfigured) { echo json_encode(['ok' => false, 'error' => 'AI not configured']); exit; }
        $keyword = trim($_POST['keyword'] ?? '');
        if (empty($keyword)) { echo json_encode(['ok' => false, 'error' => 'Keyword required']); exit; }

        // Get provider/model from request (uses form values)
        $provider = $_POST['provider'] ?? 'openai';
        $selectedModel = $_POST['model'] ?? 'gpt-5.2-mini';

        // Validate provider and model
        if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
            $provider = 'openai';
        }
        if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
            $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2-mini';
        }

        $sysPrompt = "You are an expert headline writer. Generate exactly 6 compelling, SEO-friendly article titles for the given keyword. Output each title on a new line, without numbering, quotes, or any formatting.";
        $result = ai_universal_generate($provider, $selectedModel, $sysPrompt, "Generate 6 article titles for: {$keyword}", [
            'max_tokens' => 500,
            'temperature' => 0.8
        ]);
        
        if ($result['ok']) {
            $content = $result['content'];
            // Remove markdown code blocks
            $content = preg_replace('/```(?:json)?\s*/i', '', $content);
            $content = str_replace('```', '', $content);
            // Try JSON parse first
            $titles = json_decode(trim($content), true);
            if (!is_array($titles)) {
                // Parse line by line
                $lines = explode("\n", $content);
                $titles = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    // Remove numbering (1. 2. - * etc)
                    $line = preg_replace('/^[\d]+[\.\)]\s*/', '', $line);
                    $line = preg_replace('/^[-\*•]\s*/', '', $line);
                    // Remove quotes and trailing commas
                    $line = trim($line, " \t\n\r\0\x0B\"'`,[]");
                    if (strlen($line) > 10) $titles[] = $line;
                }
            } else {
                // Clean JSON array items
                $titles = array_map(fn($t) => trim($t, " \t\n\r\0\x0B\"'`,"), $titles);
            }
            $titles = array_filter($titles, fn($t) => strlen($t) > 10);
            echo json_encode(['ok' => true, 'titles' => array_values(array_slice($titles, 0, 8))]);
        } else { echo json_encode($result); }
        exit;
    }
    
    // GENERATE CONTENT
    if ($action === 'generate') {
        if (!$aiConfigured) { echo json_encode(['ok' => false, 'error' => 'AI not configured']); exit; }
        
        $contentType = $_POST['content_type'] ?? 'article';
        $topic = trim($_POST['topic'] ?? '');
        $mainKeyword = trim($_POST['main_keyword'] ?? '');
        $keywords = trim($_POST['keywords'] ?? '');
        $phrases = trim($_POST['phrases'] ?? '');
        $headings = trim($_POST['headings'] ?? '');
        $tone = $_POST['tone'] ?? 'professional';
        $language = $_POST['language'] ?? 'en';
        $length = $_POST['length'] ?? 'medium';
        $targetWords = intval($_POST['target_words'] ?? 0);
        $customPrompt = trim($_POST['custom_prompt'] ?? '');
        $provider = $_POST['ai_provider'] ?? 'openai';
        $selectedModel = $_POST['ai_model'] ?? 'gpt-5.2-mini';

        // Validate provider and model selection using central config
        if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
            $provider = 'openai';
        }
        if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
            $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2-mini';
        }

        if (empty($topic)) { echo json_encode(['ok' => false, 'error' => 'Title is required']); exit; }
        
        // Detect model class for appropriate prompting strategy
        $isReasoningModel = ai_is_reasoning_model($selectedModel);
        
        // SYSTEM PROMPTS - Different strategies for reasoning vs non-reasoning models
        if ($isReasoningModel) {
            // GPT-5.x, O-series, GPT-4.1 - Balance readability with natural human variation
            $systemPrompts = [
                'article' => "You are an experienced journalist who writes clearly but never robotically.

READABILITY WITHOUT UNIFORMITY:
- Use simple words most of the time, but sprinkle in a few sophisticated ones naturally
- Keep most sentences short (10-18 words), but deliberately include some longer ones (22-28 words) for rhythm
- CRITICAL: Vary sentence length wildly within each paragraph. Example pattern: 8, 22, 11, 26, 9 words - NOT 15, 16, 14, 17, 15

HUMAN WRITING PATTERNS (what makes text feel human):
- Occasionally start sentences with 'And' or 'But' - it's conversational
- Use dashes for asides—like this one—instead of always using commas
- Include rhetorical questions now and then. Why? They break monotony.
- Add brief personal observations: 'In my experience...', 'The truth is...'
- Vary paragraph length dramatically: 1 sentence, then 4 sentences, then 2

VOCABULARY (avoid AI patterns):
- Never use the same adjective twice in the article
- Replace common words with unexpected but clear synonyms occasionally
- Mix formal ('consequently') with casual ('so') in the same piece
- Use specific numbers and names instead of vague references

STRUCTURE VARIATION:
- Each H2 section should have DIFFERENT internal structure
- Some sections: short intro → detailed example → conclusion
- Other sections: question → answer → implications
- Never make all sections follow the same template

AVOID AI TELLS:
- No perfectly parallel sentence structures
- No repetitive transition words
- No uniform paragraph lengths (mix 2, 5, 3, 1, 4 sentences)
- No generic openings like 'In today's world' or 'When it comes to'

FORMAT: ## for H2 headings. Prose paragraphs only.",

                'blog_post' => "You are a blogger who writes like you talk - clear but unpredictable.

CONVERSATIONAL BUT VARIED:
- Short sentences mostly, but throw in a longer one when the thought needs room to breathe
- Mix 'I think' with 'research shows' - personal AND factual
- Interrupt yourself sometimes. Like this. Then continue.

HUMAN QUIRKS:
- Start some sentences with 'And', 'But', 'So'
- Use contractions: don't, won't, it's, you're
- Ask questions. Answer them. Or leave them hanging.
- Occasionally use incomplete sentences. For emphasis.

PARAGRAPH RHYTHM:
- One sentence paragraph for impact.
- Then maybe three or four sentences diving deeper into the idea, exploring it from angles, giving examples.
- Back to short.

FORMAT: ## for sections.",

                'product_description' => "Write compelling product copy. Lead with transformation. Sensory language. Specific benefits with numbers.",
                'email' => "Write emails that get responses. Hook first sentence. One idea per paragraph. Clear single CTA.",
                'social_media' => "Write scroll-stopping social content. Hook in first 5 words. End with engagement prompt.",
                'landing_page' => "Write conversion copy. Lead with pain. Solution as relief. Social proof. Micro-CTA per section.",
                'faq' => "Write clear FAQ answers. Question in user language. Direct solution first.",
                'meta_description' => "Write SEO meta descriptions under 155 characters. Primary keyword natural."
            ];
        } else {
            // GPT-4o and older - simpler prompts, rely on API parameters
            $systemPrompts = [
                'article' => "You are an expert content writer. Write naturally like a human journalist.

STRUCTURE:
- Start with engaging introduction (no heading), 2-3 paragraphs
- Create logical H2 sections based on topics provided
- End with Conclusion section

STYLE:
- Prose paragraphs, no bullet points
- Mix short and long sentences
- Use contractions naturally
- Be specific with examples

FORMAT: ## for H2 headings. Plain paragraphs, no bullets.",

                'blog_post' => "You are a friendly blogger. Write conversationally.

STYLE:
- Use 'I' and 'you' often
- Keep paragraphs short (2-4 sentences)
- Use contractions and casual language

FORMAT: ## for sections. Short paragraphs.",

                'product_description' => "Write compelling product descriptions. Focus on benefits, use simple words.",
                'email' => "Write persuasive emails. Short paragraphs, clear CTA.",
                'social_media' => "Write engaging social media content with relevant hashtags.",
                'landing_page' => "Write conversion-focused landing page copy. Benefits first, clear CTAs.",
                'faq' => "Generate clear FAQ content. Direct questions, concise answers.",
                'meta_description' => "Write SEO meta descriptions under 160 characters."
            ];
        }
        $sysPrompt = $systemPrompts[$contentType] ?? $systemPrompts['article'];
        
        // Build user prompt - also varies by model class
        $wordTarget = $targetWords > 0 ? $targetWords : (['short'=>300,'medium'=>600,'long'=>1000,'very_long'=>1500][$length] ?? 600);
        
        $userPrompt = "ARTICLE TITLE: {$topic}\n\n";
        $userPrompt .= "TARGET LENGTH: approximately {$wordTarget} words\n\n";
        
        if ($isReasoningModel) {
            // Reasoning models - simple, clear structure
            $userPrompt .= "STRUCTURE:\n";
            $userPrompt .= "- Opening: 2-3 paragraphs introducing the topic (no heading)\n";
            $userPrompt .= "- Body: 5-7 H2 sections developing the topic\n";
            $userPrompt .= "- Conclusion: Key takeaways and next steps\n\n";
            
            if ($headings) {
                $userPrompt .= "TOPICS TO COVER (create clear H2 headings):\n{$headings}\n\n";
            }
            
            $userPrompt .= "QUALITY: Write clearly but not robotically. Vary your rhythm. Sound human, not templated.\n\n";
        } else {
            $userPrompt .= "STRUCTURE:\n";
            $userPrompt .= "1. Introduction (no heading): 2-3 paragraphs\n";
            $userPrompt .= "2. Main body: 5-7 H2 sections\n";
            $userPrompt .= "3. Conclusion: key takeaways\n\n";
            
            if ($headings) {
                $userPrompt .= "TOPICS TO COVER:\n{$headings}\n\n";
            }
        }
        
        if ($mainKeyword) $userPrompt .= "PRIMARY KEYWORD: {$mainKeyword} (use 3-5 times naturally)\n";
        if ($keywords) $userPrompt .= "SECONDARY KEYWORDS: {$keywords} (use each 1-2 times)\n\n";
        if ($phrases) $userPrompt .= "PHRASES TO INCLUDE: {$phrases}\n\n";
        
        $userPrompt .= "TONE: {$tone}\n";
        $userPrompt .= "LANGUAGE: {$language}\n\n";
        
        if ($customPrompt) $userPrompt .= "ADDITIONAL INSTRUCTIONS: {$customPrompt}\n";

        // Use universal generate for multi-provider support
        $result = ai_universal_generate($provider, $selectedModel, $sysPrompt, $userPrompt, [
            'max_tokens' => max(2000, intval($wordTarget * 1.5)),
            'temperature' => 0.7,
            'frequency_penalty' => 0.15,
            'presence_penalty' => 0.1
        ]);
        echo json_encode($result);
        exit;
    }

    // SAVE DRAFT
    if ($action === 'save_draft') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $contentType = $_POST['content_type'] ?? 'article';
        if (empty($title) || empty($content)) { echo json_encode(['ok' => false, 'error' => 'Title and content required']); exit; }
        
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-') ?: 'draft-' . bin2hex(random_bytes(4));
        $counter = 1; $baseSlug = $slug;
        while (file_exists($DRAFT_DIR . '/' . $slug . '.json')) { $slug = $baseSlug . '-' . (++$counter); }
        
        $draft = ['title' => $title, 'content' => $content, 'content_type' => $contentType, 'created_at' => gmdate('c')];
        $ok = file_put_contents($DRAFT_DIR . '/' . $slug . '.json', json_encode($draft, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['ok' => $ok !== false, 'file' => $slug . '.json']);
        exit;
    }
    
    // LIST DRAFTS
    if ($action === 'list_drafts') {
        $drafts = [];
        foreach (glob($DRAFT_DIR . '/*.json') as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) $drafts[] = ['file' => basename($file), 'title' => $data['title'] ?? 'Untitled', 'content_type' => $data['content_type'] ?? 'article', 'created_at' => $data['created_at'] ?? ''];
        }
        usort($drafts, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        echo json_encode(['ok' => true, 'drafts' => $drafts]);
        exit;
    }
    
    // LOAD DRAFT
    if ($action === 'load_draft') {
        $file = basename($_POST['file'] ?? '');
        $path = $DRAFT_DIR . '/' . $file;
        if (!file_exists($path)) { echo json_encode(['ok' => false, 'error' => 'Not found']); exit; }
        echo json_encode(['ok' => true, 'draft' => json_decode(file_get_contents($path), true)]);
        exit;
    }
    
    // DELETE DRAFT
    if ($action === 'delete_draft') {
        $file = basename($_POST['file'] ?? '');
        $ok = file_exists($DRAFT_DIR . '/' . $file) && unlink($DRAFT_DIR . '/' . $file);
        echo json_encode(['ok' => $ok]);
        exit;
    }
    
    // CREATE ARTICLE
    if ($action === 'create_article') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        if (empty($title) || empty($content)) { echo json_encode(['ok' => false, 'error' => 'Title and content required']); exit; }
        
        $content = markdown_to_html($content);
        try {
            $db = \core\Database::connection();
            $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
            $stmt = $db->prepare("SELECT id FROM articles WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) $slug .= '-' . bin2hex(random_bytes(3));
            
            $stmt = $db->prepare("INSERT INTO articles (title, slug, content, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $slug, $content, $status]);
            echo json_encode(['ok' => true, 'id' => $db->lastInsertId(), 'slug' => $slug]);
        } catch (Exception $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()]); }
        exit;
    }
    
    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}


// HELPER FUNCTIONS
function markdown_to_html(string $text): string {
    $lines = explode("\n", $text);
    $html = [];
    $inList = false;
    $listType = '';
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed)) { if ($inList) { $html[] = $listType === 'ul' ? '</ul>' : '</ol>'; $inList = false; } continue; }
        if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $m)) {
            if ($inList) { $html[] = $listType === 'ul' ? '</ul>' : '</ol>'; $inList = false; }
            $html[] = "<h" . strlen($m[1]) . ">" . esc($m[2]) . "</h" . strlen($m[1]) . ">";
            continue;
        }
        if (preg_match('/^[-*+]\s+(.+)$/', $trimmed, $m)) {
            if (!$inList || $listType !== 'ul') { if ($inList) $html[] = '</ol>'; $html[] = '<ul>'; $inList = true; $listType = 'ul'; }
            $html[] = '<li>' . format_inline(esc($m[1])) . '</li>';
            continue;
        }
        if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $m)) {
            if (!$inList || $listType !== 'ol') { if ($inList) $html[] = '</ul>'; $html[] = '<ol>'; $inList = true; $listType = 'ol'; }
            $html[] = '<li>' . format_inline(esc($m[1])) . '</li>';
            continue;
        }
        if ($inList) { $html[] = $listType === 'ul' ? '</ul>' : '</ol>'; $inList = false; }
        $html[] = '<p>' . format_inline(esc($trimmed)) . '</p>';
    }
    if ($inList) $html[] = $listType === 'ul' ? '</ul>' : '</ol>';
    return implode("\n", $html);
}

function format_inline(string $text): string {
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    return $text;
}

function ai_generate_with_system(string $systemPrompt, string $userPrompt, array $config, array $options = []): array {
    $apiKey = $config['api_key'];
    $model = $options['model'] ?? $config['model'] ?? 'gpt-5.2';
    $baseUrl = rtrim(!empty($config['base_url']) ? $config['base_url'] : 'https://api.openai.com/v1', '/');
    
    $maxTokens = $options['max_tokens'] ?? 6000;
    $temperature = $options['temperature'] ?? 0.7;
    $frequencyPenalty = $options['frequency_penalty'] ?? 0.3;
    $presencePenalty = $options['presence_penalty'] ?? 0.1;
    
    // Build payload - newer models (o-series, GPT-5.x, GPT-4.1.x) use max_completion_tokens
    $useNewTokenParam = preg_match('/^(o[1-4]|gpt-[45]\.|gpt-5$)/', $model);
    $isGPT5 = preg_match('/^gpt-5/', $model);
    
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ]
    ];
    
    // Newer models don't support temperature, frequency_penalty, presence_penalty
    if ($useNewTokenParam) {
        // GPT-5.x uses reasoning tokens that count against completion limit
        // Need much higher limit (reasoning can use 2000-8000 tokens before output)
        $payload['max_completion_tokens'] = $isGPT5 ? max($maxTokens * 4, 16000) : $maxTokens;
    } else {
        $payload['max_tokens'] = $maxTokens;
        $payload['temperature'] = $temperature;
        $payload['frequency_penalty'] = $frequencyPenalty;
        $payload['presence_penalty'] = $presencePenalty;
    }
    
    $ch = curl_init($baseUrl . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 180
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) return ['ok' => false, 'error' => 'Connection error: ' . $error];
    if ($httpCode !== 200) {
        $data = json_decode($response, true);
        return ['ok' => false, 'error' => $data['error']['message'] ?? 'HTTP ' . $httpCode];
    }
    
    $data = json_decode($response, true);

    // Debug logging to CMS log file
    $logFile = __DIR__ . '/../logs/ai_debug.log';
    $logMsg = date('[Y-m-d H:i:s] ') . 'Model: ' . $payload['model'] . ', HTTP: ' . $httpCode . "\n";
    $logMsg .= 'Response keys: ' . json_encode(array_keys($data ?? [])) . "\n";
    $logMsg .= 'Full response (first 5000 chars): ' . substr($response, 0, 5000) . "\n\n";
    file_put_contents($logFile, $logMsg, FILE_APPEND | LOCK_EX);

    // Extract content from various response formats:
    // 1. Chat Completions (legacy): choices[0].message.content
    // 2. Responses API (GPT-5.x): output_text or output[0].content[0].text
    // 3. O-series reasoning: choices[0].message.content (with reasoning_content separate)
    $content = null;

    // Try Chat Completions format first (works for GPT-4o, GPT-4.1, and some GPT-5 calls)
    if (isset($data['choices'][0]['message']['content'])) {
        $content = $data['choices'][0]['message']['content'];
        error_log('[AI_CONTENT_CREATOR] Found content via: choices[0].message.content');
    }
    // Responses API format: output_text (GPT-5.x preferred)
    elseif (isset($data['output_text']) && !empty($data['output_text'])) {
        $content = $data['output_text'];
        error_log('[AI_CONTENT_CREATOR] Found content via: output_text');
    }
    // Responses API format: output array (GPT-5.x structured)
    elseif (isset($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (isset($item['type']) && $item['type'] === 'message' && isset($item['content'])) {
                foreach ($item['content'] as $contentItem) {
                    if (isset($contentItem['type']) && $contentItem['type'] === 'output_text' && isset($contentItem['text'])) {
                        $content = $contentItem['text'];
                        error_log('[AI_CONTENT_CREATOR] Found content via: output[].content[].text');
                        break 2;
                    }
                    // Also try direct text field
                    if (isset($contentItem['text'])) {
                        $content = $contentItem['text'];
                        error_log('[AI_CONTENT_CREATOR] Found content via: output[].content[].text (direct)');
                        break 2;
                    }
                }
            }
            // Direct content in output item
            if (isset($item['content']) && is_string($item['content'])) {
                $content = $item['content'];
                error_log('[AI_CONTENT_CREATOR] Found content via: output[].content (string)');
                break;
            }
        }
    }
    // Legacy text field (older completions API)
    elseif (isset($data['choices'][0]['text'])) {
        $content = $data['choices'][0]['text'];
        error_log('[AI_CONTENT_CREATOR] Found content via: choices[0].text');
    }
    // Direct content field (some API variants)
    elseif (isset($data['content']) && is_string($data['content'])) {
        $content = $data['content'];
        error_log('[AI_CONTENT_CREATOR] Found content via: content (root)');
    }

    if (!$content) {
        error_log('[AI_CONTENT_CREATOR] EMPTY! Full response: ' . substr($response, 0, 3000));
        return ['ok' => false, 'error' => 'Empty response from ' . $model . ' - unexpected response format'];
    }

    return ['ok' => true, 'content' => trim($content), 'usage' => $data['usage'] ?? null];
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Content Creator PRO - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--accent2:#b4befe;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--teal:#94e2d5;--border:#313244}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.grid-2{display:grid;grid-template-columns:1fr 380px;gap:24px}
@media(max-width:1100px){.grid-2{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:12px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;font-family:inherit;transition:all .2s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.form-group textarea{min-height:80px;resize:vertical}
.form-hint{font-size:12px;color:var(--muted);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.input-with-btn{display:flex;gap:8px}
.input-with-btn input{flex:1}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s;border:none;font-family:inherit}
.btn-primary{background:var(--accent);color:#1e1e2e}
.btn-primary:hover{background:var(--accent2)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed}
.btn-success{background:var(--success);color:#1e1e2e}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.btn-danger{background:rgba(243,139,168,.15);color:var(--danger)}
.btn-sm{padding:8px 14px;font-size:13px}
.btn-block{width:100%}
.prefill-banner{background:linear-gradient(135deg,rgba(166,227,161,.15),rgba(137,180,250,.15));border:1px solid rgba(166,227,161,.3);border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:14px}
.prefill-banner .icon{font-size:24px}
.prefill-banner .text strong{color:var(--success)}
.readonly-field{background:var(--bg)!important;border-color:var(--success)!important;color:var(--success)!important}
.title-suggestions{background:var(--bg);border:1px solid var(--border);border-radius:10px;margin-top:8px;max-height:200px;overflow-y:auto;display:none}
.title-suggestions.show{display:block}
.title-suggestion{padding:12px 16px;cursor:pointer;border-bottom:1px solid var(--border);transition:all .15s;font-size:13px}
.title-suggestion:last-child{border-bottom:none}
.title-suggestion:hover{background:var(--bg3);color:var(--accent)}
.content-type-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px}
.content-type{padding:12px 8px;background:var(--bg);border:2px solid var(--border);border-radius:10px;cursor:pointer;text-align:center;transition:all .2s;font-size:12px}
.content-type:hover{border-color:var(--accent)}
.content-type.active{border-color:var(--accent);background:rgba(137,180,250,.1)}
.content-type .icon{font-size:20px;margin-bottom:4px}
.output-area{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:20px;min-height:250px;max-height:450px;overflow-y:auto;white-space:pre-wrap;font-size:14px;line-height:1.8}
.output-area.empty{color:var(--muted);display:flex;align-items:center;justify-content:center;text-align:center}
.output-area h1,.output-area h2,.output-area h3{color:var(--accent);margin:16px 0 8px}
.word-stats{display:flex;gap:16px;padding:12px 16px;background:var(--bg);border-radius:10px;margin-top:12px;font-size:13px;align-items:center;flex-wrap:wrap}
.word-stats .stat{display:flex;align-items:center;gap:6px}
.word-stats .good{color:var(--success)}
.word-stats .warn{color:var(--warning)}
.word-stats .bad{color:var(--danger)}
.draft-list{max-height:350px;overflow-y:auto}
.draft-item{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border);transition:all .2s}
.draft-item:hover{background:var(--bg3)}
.draft-item:last-child{border-bottom:none}
.draft-info{flex:1;min-width:0}
.draft-title{font-weight:500;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px}
.draft-meta{font-size:11px;color:var(--muted)}
.draft-actions{display:flex;gap:6px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px}
.alert-warning{background:rgba(249,226,175,.15);color:var(--warning);border:1px solid rgba(249,226,175,.3)}
.alert-success{background:rgba(166,227,161,.15);color:var(--success);border:1px solid rgba(166,227,161,.3)}
.loading{display:inline-block;width:18px;height:18px;border:2px solid var(--bg3);border-top-color:var(--accent);border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:none;align-items:center;justify-content:center;z-index:200;padding:20px}
.modal-overlay.show{display:flex}
.modal{background:var(--bg2);border-radius:16px;width:100%;max-width:450px}
.modal-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-body{padding:20px}
.modal-close{background:none;border:none;color:var(--muted);font-size:20px;cursor:pointer}
.btn-group{display:flex;gap:10px;margin-top:16px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '✍️',
    'title' => 'AI Content Creator PRO',
    'description' => 'Generate SEO-optimized content with AI',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent), var(--purple)',
    'actions' => $aiConfigured ? [] : [['type' => 'link', 'url' => '/admin/ai-settings', 'text' => '⚙️ Configure AI', 'class' => 'secondary']]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<?php if (!$aiConfigured): ?>
<div class="alert alert-warning">⚠️ AI not configured. <a href="/admin/ai-settings.php" style="color:var(--warning);text-decoration:underline">Configure API keys</a> to enable generation.</div>
<?php endif; ?>

<?php if ($hasPrefill): ?>
<div class="prefill-banner">
    <span class="icon">📊</span>
    <div class="text"><strong>Pre-filled from SEO Research</strong> — Data imported from keyword analysis. Review and adjust as needed.</div>
</div>
<?php endif; ?>

<div class="grid-2">
<div>
<div class="card">
<div class="card-head"><span class="card-title">📝 Generate Content</span></div>
<div class="card-body">

<div class="content-type-grid">
<div class="content-type active" data-type="article"><div class="icon">📄</div><div class="name">Article</div></div>
<div class="content-type" data-type="blog_post"><div class="icon">📝</div><div class="name">Blog</div></div>
<div class="content-type" data-type="product_description"><div class="icon">🛍️</div><div class="name">Product</div></div>
<div class="content-type" data-type="landing_page"><div class="icon">🚀</div><div class="name">Landing</div></div>
</div>

<form id="generateForm">
<input type="hidden" name="content_type" id="contentType" value="article">
<input type="hidden" name="target_words" id="targetWords" value="<?= esc($prefill['wordcount']) ?>">

<div class="form-group">
    <label>Title *</label>
    <div class="input-with-btn">
        <input type="text" id="topic" name="topic" placeholder="Enter title or generate from keyword" value="<?= esc($prefill['title']) ?>" required>
        <button type="button" class="btn btn-secondary btn-sm" id="genTitlesBtn" <?= !$aiConfigured ? 'disabled' : '' ?>>🎯 Generate</button>
    </div>
    <div class="title-suggestions" id="titleSuggestions"></div>
</div>

<div class="form-group">
    <label>Main Keyword</label>
    <input type="text" id="mainKeyword" name="main_keyword" placeholder="Primary SEO keyword" value="<?= esc($prefill['keyword']) ?>" <?= $prefill['keyword'] ? 'class="readonly-field" readonly' : '' ?>>
    <?php if ($prefill['keyword']): ?><div class="form-hint">✅ From SEO Research</div><?php endif; ?>
</div>

<div class="form-group">
    <label>Keywords (comma separated)</label>
    <textarea id="keywords" name="keywords" rows="2" placeholder="keyword1, keyword2, keyword3"><?= esc($prefill['keywords']) ?></textarea>
</div>

<div class="form-group">
    <label>Must-Use Phrases</label>
    <textarea id="phrases" name="phrases" rows="2" placeholder="Phrases to include naturally in content"><?= esc($prefill['phrases']) ?></textarea>
    <?php if ($prefill['phrases']): ?><div class="form-hint">✅ From SEO Research</div><?php endif; ?>
</div>

<div class="form-group">
    <label>Suggested H2 Headings</label>
    <textarea id="headings" name="headings" rows="3" placeholder="One heading per line"><?= esc($prefill['headings']) ?></textarea>
    <?php if ($prefill['headings']): ?><div class="form-hint">✅ From SEO Research</div><?php endif; ?>
</div>

<div class="form-row">
<div class="form-group">
    <label>Tone</label>
    <select id="tone" name="tone">
        <option value="professional">Professional</option>
        <option value="casual">Casual & Friendly</option>
        <option value="formal">Formal</option>
        <option value="persuasive">Persuasive</option>
        <option value="informative">Informative</option>
    </select>
</div>
<div class="form-group">
    <label>Length</label>
    <select id="length" name="length">
        <?php foreach ($lengthOptions as $key => $opt): ?>
        <option value="<?= $key ?>" <?= $key === $recommendedLength ? 'selected' : '' ?>><?= $opt['label'] ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>

<div class="form-row">
<div class="form-group">
    <label>Language</label>
    <select id="language" name="language">
        <option value="en">English</option>
        <option value="pl">Polish</option>
        <option value="de">German</option>
        <option value="es">Spanish</option>
        <option value="fr">French</option>
    </select>
</div>
<div class="form-group">
    <label>🤖 AI Provider & Model</label>
    <?= ai_render_dual_selector('ai_provider', 'ai_model', 'openai', 'gpt-5.2-mini') ?>
    <div class="form-hint">Select provider and model. GPT-4.1-mini recommended for best quality/speed balance.</div>
</div>
</div>

<div class="form-group">
    <button type="submit" class="btn btn-primary btn-block" id="generateBtn" <?= !$aiConfigured ? 'disabled' : '' ?>>🚀 Generate Content</button>
</div>

<div class="form-group">
    <label>Additional Instructions</label>
    <textarea id="customPrompt" name="custom_prompt" rows="2" placeholder="Any specific requirements..."></textarea>
</div>
</form>
</div>
</div>

<!-- Output Card -->
<div class="card" style="margin-top:20px">
<div class="card-head">
    <span class="card-title">📄 Generated Content</span>
    <div id="outputActions" style="display:none">
        <button class="btn btn-sm btn-secondary" onclick="copyContent()">📋 Copy</button>
        <button class="btn btn-sm btn-success" onclick="showSaveModal()">💾 Save</button>
        <button class="btn btn-sm btn-primary" onclick="showPublishModal()">📤 Publish</button>
    </div>
</div>
<div class="card-body">
    <div class="output-area empty" id="outputArea">
        <div>
            <div style="font-size:36px;margin-bottom:10px">✨</div>
            <div>Generated content will appear here</div>
        </div>
    </div>
    <div class="word-stats" id="outputStats" style="display:none">
        <span class="stat">📊 <strong id="wordCount">0</strong> words</span>
        <span class="stat" id="targetStat"></span>
        <span class="stat">⏱️ <strong id="genTime">0</strong>s</span>
    </div>
    
    <!-- Quick Quality Check -->
    <div class="word-stats" id="quickCheck" style="display:none;margin-top:8px;background:var(--bg2);border:1px solid var(--border)">
        <span class="stat">🤖 AI: <strong id="qcAi">--</strong></span>
        <span class="stat">📝 Orig: <strong id="qcOrig">--</strong></span>
        <span class="stat">📖 Read: <strong id="qcRead">--</strong></span>
        <a href="#" id="fullAnalysisBtn" class="btn btn-sm btn-secondary" style="margin-left:auto">🔍 Full Analysis</a>
    </div>
</div>
</div>
</div>

<!-- Sidebar -->
<div>
<div class="card">
<div class="card-head">
    <span class="card-title">📁 Saved Drafts</span>
    <button class="btn btn-sm btn-secondary" onclick="loadDrafts()">🔄</button>
</div>
<div class="card-body" style="padding:0">
    <div class="draft-list" id="draftList"><div class="draft-item" style="justify-content:center;color:var(--muted)">Loading...</div></div>
</div>
</div>

<div class="card" style="margin-top:20px">
<div class="card-head"><span class="card-title">💡 Tips</span></div>
<div class="card-body" style="font-size:13px;color:var(--text2)">
    <p style="margin-bottom:10px"><strong>For best results:</strong></p>
    <ul style="padding-left:18px;display:flex;flex-direction:column;gap:6px">
        <li>Use SEO Research first for keyword data</li>
        <li>Include must-use phrases for SEO</li>
        <li>Review and edit H2 suggestions</li>
        <li>Generate multiple versions</li>
    </ul>
</div>
</div>
</div>
</div>
</div>

<!-- Save Modal -->
<div class="modal-overlay" id="saveModal">
<div class="modal">
    <div class="modal-head"><span class="card-title">💾 Save Draft</span><button class="modal-close" onclick="closeSaveModal()">&times;</button></div>
    <div class="modal-body">
        <form id="saveDraftForm">
            <div class="form-group"><label>Title</label><input type="text" id="draftTitle" required></div>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="closeSaveModal()">Cancel</button>
                <button type="submit" class="btn btn-success">💾 Save</button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Publish Modal -->
<div class="modal-overlay" id="publishModal">
<div class="modal">
    <div class="modal-head"><span class="card-title">📤 Create Article</span><button class="modal-close" onclick="closePublishModal()">&times;</button></div>
    <div class="modal-body">
        <form id="publishForm">
            <div class="form-group"><label>Title</label><input type="text" id="articleTitle" required></div>
            <div class="form-group"><label>Status</label><select id="articleStatus"><option value="draft">Draft</option><option value="published">Published</option></select></div>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="closePublishModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">📤 Create</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
const csrf = '<?= esc(csrf_token()) ?>';
const seoRecommendedWords = <?= $prefill['wordcount'] ?: 0 ?>;
let targetWords = seoRecommendedWords;
let generatedContent = '';
let genStartTime = 0;

// Length options mapping
const lengthTargets = {
    'recommended': seoRecommendedWords,
    'short': 300,
    'medium': 600,
    'long': 1000,
    'very_long': 1500
};

// Update target words when length changes
document.getElementById('length')?.addEventListener('change', (e) => {
    const selected = e.target.value;
    targetWords = lengthTargets[selected] || 600;
    document.getElementById('targetWords').value = targetWords;
});

// Content type selection
document.querySelectorAll('.content-type').forEach(el => {
    el.addEventListener('click', () => {
        document.querySelectorAll('.content-type').forEach(e => e.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('contentType').value = el.dataset.type;
    });
});

// Title generator
document.getElementById('genTitlesBtn').addEventListener('click', async () => {
    const keyword = document.getElementById('mainKeyword').value || document.getElementById('topic').value;
    if (!keyword) { alert('Enter a keyword or topic first'); return; }
    
    const btn = document.getElementById('genTitlesBtn');
    const box = document.getElementById('titleSuggestions');
    btn.disabled = true;
    btn.textContent = '⏳...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'generate_titles');
    fd.append('csrf_token', csrf);
    fd.append('keyword', keyword);
    fd.append('provider', document.querySelector('[name="ai_provider"]')?.value || 'openai');
    fd.append('model', document.querySelector('[name="ai_model"]')?.value || 'gpt-5.2-mini');

    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok && data.titles?.length) {
            box.innerHTML = data.titles.map(t => `<div class="title-suggestion">${escHtml(t)}</div>`).join('');
            box.classList.add('show');
            box.querySelectorAll('.title-suggestion').forEach(el => {
                el.addEventListener('click', () => {
                    document.getElementById('topic').value = el.textContent;
                    box.classList.remove('show');
                });
            });
        } else {
            alert(data.error || 'Failed to generate titles');
        }
    } catch (e) { alert('Error: ' + e.message); }
    
    btn.disabled = false;
    btn.textContent = '🎯 Generate';
});

// Generate content
document.getElementById('generateForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('generateBtn');
    const output = document.getElementById('outputArea');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Generating...';
    output.classList.remove('empty');
    output.innerHTML = '<div style="text-align:center;padding:40px"><span class="loading" style="width:28px;height:28px"></span><div style="margin-top:12px;color:var(--muted)">Generating with AI...</div></div>';
    
    genStartTime = Date.now();
    const fd = new FormData(e.target);
    fd.append('ajax', '1');
    fd.append('action', 'generate');
    fd.append('csrf_token', csrf);
    
    try {
        const res = await fetch('', { method: 'POST', body: fd });
        const data = await res.json();
        const genTime = ((Date.now() - genStartTime) / 1000).toFixed(1);
        
        if (data.ok) {
            generatedContent = data.content;
            output.innerHTML = formatContent(data.content);
            document.getElementById('outputActions').style.display = 'flex';
            document.getElementById('outputStats').style.display = 'flex';
            
            const words = data.content.trim().split(/\s+/).length;
            document.getElementById('wordCount').textContent = words;
            document.getElementById('genTime').textContent = genTime;
            
            // Word count vs target
            const targetStat = document.getElementById('targetStat');
            if (targetWords > 0) {
                const pct = Math.round((words / targetWords) * 100);
                let cls = 'good';
                if (pct < 70) cls = 'bad';
                else if (pct < 90) cls = 'warn';
                targetStat.innerHTML = `🎯 <span class="${cls}">${pct}% of target (${targetWords})</span>`;
            } else {
                targetStat.innerHTML = '';
            }
            
            document.getElementById('draftTitle').value = document.getElementById('topic').value;
            document.getElementById('articleTitle').value = document.getElementById('topic').value;
            
            // Run quick quality check
            runQuickCheck(data.content);
        } else {
            output.innerHTML = `<div class="alert alert-warning">❌ ${data.error || 'Generation failed'}</div>`;
        }
    } catch (err) {
        output.innerHTML = `<div class="alert alert-warning">❌ Error: ${err.message}</div>`;
    }
    
    btn.disabled = false;
    btn.innerHTML = '🚀 Generate Content';
});

function formatContent(text) {
    return text
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^- (.+)$/gm, '• $1<br>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/^(.+)$/gm, '<p>$1</p>')
        .replace(/<p><h/g, '<h')
        .replace(/<\/h(\d)><\/p>/g, '</h$1>')
        .replace(/<p><\/p>/g, '');
}

function copyContent() {
    navigator.clipboard.writeText(generatedContent).then(() => alert('Copied!'));
}

function showSaveModal() { document.getElementById('saveModal').classList.add('show'); }
function closeSaveModal() { document.getElementById('saveModal').classList.remove('show'); }
function showPublishModal() { document.getElementById('publishModal').classList.add('show'); }
function closePublishModal() { document.getElementById('publishModal').classList.remove('show'); }

// Save draft
document.getElementById('saveDraftForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'save_draft');
    fd.append('csrf_token', csrf);
    fd.append('title', document.getElementById('draftTitle').value);
    fd.append('content', generatedContent);
    fd.append('content_type', document.getElementById('contentType').value);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.ok) { closeSaveModal(); loadDrafts(); alert('Saved!'); }
    else alert('Error: ' + (data.error || 'Failed'));
});

// Publish article
document.getElementById('publishForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'create_article');
    fd.append('csrf_token', csrf);
    fd.append('title', document.getElementById('articleTitle').value);
    fd.append('content', generatedContent);
    fd.append('status', document.getElementById('articleStatus').value);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.ok) {
        closePublishModal();
        alert('Article created!');
        window.location.href = '/admin/article-edit.php?id=' + data.id;
    } else alert('Error: ' + (data.error || 'Failed'));
});

// Load drafts
async function loadDrafts() {
    const list = document.getElementById('draftList');
    list.innerHTML = '<div class="draft-item" style="justify-content:center;color:var(--muted)">Loading...</div>';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'list_drafts');
    fd.append('csrf_token', csrf);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.ok && data.drafts?.length) {
        list.innerHTML = data.drafts.map(d => `
            <div class="draft-item">
                <div class="draft-info">
                    <div class="draft-title">${escHtml(d.title)}</div>
                    <div class="draft-meta">${d.content_type} • ${d.created_at ? new Date(d.created_at).toLocaleDateString() : ''}</div>
                </div>
                <div class="draft-actions">
                    <button class="btn btn-sm btn-secondary" onclick="loadDraft('${escHtml(d.file)}')">📂</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDraft('${escHtml(d.file)}')">🗑️</button>
                </div>
            </div>
        `).join('');
    } else {
        list.innerHTML = '<div class="draft-item" style="justify-content:center;color:var(--muted)">No drafts yet</div>';
    }
}

async function loadDraft(file) {
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'load_draft');
    fd.append('csrf_token', csrf);
    fd.append('file', file);
    
    const res = await fetch('', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.ok && data.draft) {
        generatedContent = data.draft.content;
        document.getElementById('outputArea').classList.remove('empty');
        document.getElementById('outputArea').innerHTML = formatContent(data.draft.content);
        document.getElementById('outputActions').style.display = 'flex';
        document.getElementById('topic').value = data.draft.title || '';
        document.getElementById('draftTitle').value = data.draft.title || '';
        document.getElementById('articleTitle').value = data.draft.title || '';
    }
}

async function deleteDraft(file) {
    if (!confirm('Delete this draft?')) return;
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'delete_draft');
    fd.append('csrf_token', csrf);
    fd.append('file', file);
    await fetch('', { method: 'POST', body: fd });
    loadDrafts();
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Quick Quality Check
async function runQuickCheck(content) {
    const panel = document.getElementById('quickCheck');
    const qcAi = document.getElementById('qcAi');
    const qcOrig = document.getElementById('qcOrig');
    const qcRead = document.getElementById('qcRead');
    
    panel.style.display = 'flex';
    qcAi.textContent = '...';
    qcOrig.textContent = '...';
    qcRead.textContent = '...';
    
    const fd = new FormData();
    fd.append('ajax', '1');
    fd.append('action', 'quick_analyze');
    fd.append('csrf_token', csrf);
    fd.append('content', content);
    
    try {
        const res = await fetch('/admin/content-quality.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.ok) {
            const q = data.quick;
            qcAi.textContent = q.ai_score + '%';
            qcAi.className = q.ai_score >= 60 ? 'bad' : q.ai_score >= 35 ? 'warn' : 'good';
            
            qcOrig.textContent = q.originality + '%';
            qcOrig.className = q.originality >= 90 ? 'good' : q.originality >= 70 ? 'warn' : 'bad';
            
            qcRead.textContent = q.readability_grade;
            qcRead.className = ['A', 'B+', 'B'].includes(q.readability_grade) ? 'good' : 'warn';
        }
    } catch (e) {
        console.error('Quick check failed:', e);
    }
}

// Full Analysis button
document.getElementById('fullAnalysisBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (generatedContent) {
        // Pass content and keywords to Quality Check
        sessionStorage.setItem('quality_check_content', generatedContent);
        
        // Collect all keywords
        const mainKw = document.getElementById('mainKeyword').value;
        const keywords = document.getElementById('keywords').value;
        const phrases = document.getElementById('phrases').value;
        const allKeywords = [mainKw, keywords, phrases].filter(k => k.trim()).join(', ');
        sessionStorage.setItem('quality_check_keywords', allKeywords);
        
        window.open('/admin/content-quality', '_blank');
    }
});

loadDrafts();
</script>
</body>
</html>
