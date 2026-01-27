<?php
/**
 * AI Content Rewrite Suite
 * Paraphrasing, summarizing, expanding, tone-shifting
 *
 * Features:
 * - Single content rewrite
 * - Batch processing
 * - Multiple rewrite modes
 * - Tone shifting
 * - SEO optimization
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';

/**
 * Generate text using OpenAI API
 * Supports GPT-4o, GPT-4.1, GPT-5, O-series models
 */
function ai_openai_generate_text(string $prompt, array $options = []): array
{
    // Load settings
    $settingsFile = CMS_ROOT . '/config/ai_settings.json';
    if (!file_exists($settingsFile)) {
        return ['ok' => false, 'error' => 'AI settings not found'];
    }

    $settings = json_decode(file_get_contents($settingsFile), true);
    $openaiConfig = $settings['providers']['openai'] ?? [];

    if (empty($openaiConfig['api_key'])) {
        return ['ok' => false, 'error' => 'OpenAI API key not configured'];
    }

    // Model selection: options > config > default
    $model = $options['model'] ?? $openaiConfig['default_model'] ?? 'gpt-5.2';

    // Validate model if ai_models.php is loaded
    if (function_exists('ai_is_valid_model') && !ai_is_valid_model($model)) {
        $model = function_exists('ai_get_default_model') ? ai_get_default_model() : 'gpt-5.2';
    }

    $maxTokens = $options['params']['max_new_tokens'] ?? $options['params']['max_tokens'] ?? 1000;
    $temperature = $options['params']['temperature'] ?? 0.7;
    
    $requestBody = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'You are a professional SEO content writer. Output clean HTML formatted content for a CMS. Use proper HTML tags: <h2>, <h3> for headings, <p> for paragraphs, <ul>/<ol> with <li> for lists, <strong> for emphasis. Never use markdown. Output only the HTML content without any preamble or explanation.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => (int)$maxTokens,
        'temperature' => (float)$temperature
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $openaiConfig['api_key'],
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return ['ok' => false, 'error' => 'OpenAI API error (HTTP ' . $httpCode . ')'];
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        return ['ok' => false, 'error' => $data['error']['message'] ?? 'OpenAI error'];
    }
    
    $text = $data['choices'][0]['message']['content'] ?? null;
    
    if (empty($text)) {
        return ['ok' => false, 'error' => 'No content generated'];
    }
    
    return ['ok' => true, 'text' => trim($text)];
}

/**
 * Available rewrite modes
 */
define('REWRITE_MODES', [
    'paraphrase' => [
        'label' => 'Paraphrase',
        'description' => 'Rewrite keeping the same meaning',
        'icon' => '🔄',
    ],
    'summarize' => [
        'label' => 'Summarize',
        'description' => 'Shorten while keeping key points',
        'icon' => '📝',
    ],
    'expand' => [
        'label' => 'Expand',
        'description' => 'Add more detail and examples',
        'icon' => '📖',
    ],
    'simplify' => [
        'label' => 'Simplify',
        'description' => 'Make easier to understand',
        'icon' => '🎯',
    ],
    'formalize' => [
        'label' => 'Formalize',
        'description' => 'Make more professional/formal',
        'icon' => '👔',
    ],
    'casual' => [
        'label' => 'Casual',
        'description' => 'Make more conversational',
        'icon' => '💬',
    ],
    'seo' => [
        'label' => 'SEO Optimize',
        'description' => 'Optimize for search engines',
        'icon' => '🔍',
    ],
    'kids' => [
        'label' => 'Kid-Friendly',
        'description' => 'Rewrite for children',
        'icon' => '🧒',
    ],
]);

/**
 * Available tone options
 */
define('TONE_OPTIONS', [
    'neutral' => 'Neutral',
    'formal' => 'Formal',
    'casual' => 'Casual',
    'friendly' => 'Friendly',
    'professional' => 'Professional',
    'enthusiastic' => 'Enthusiastic',
    'educational' => 'Educational',
    'persuasive' => 'Persuasive',
    'empathetic' => 'Empathetic',
    'authoritative' => 'Authoritative',
]);

/**
 * Build the AI prompt for rewriting
 *
 * @param string $content Original content
 * @param string $mode Rewrite mode
 * @param array $options Additional options
 * @return string Constructed prompt
 */
function ai_rewrite_build_prompt(string $content, string $mode, array $options = []): string
{
    $tone = $options['tone'] ?? 'neutral';
    $keyword = $options['keyword'] ?? '';
    $targetLength = $options['target_length'] ?? null;
    $preserveStructure = $options['preserve_structure'] ?? true;

    $basePrompt = "Rewrite the following content.\n\n";
    $basePrompt .= "ORIGINAL CONTENT:\n{$content}\n\n";
    $basePrompt .= "INSTRUCTIONS:\n";

    switch ($mode) {
        case 'paraphrase':
            $basePrompt .= "- Rewrite this text using different words while keeping the exact same meaning\n";
            $basePrompt .= "- Maintain the same length approximately\n";
            $basePrompt .= "- Keep all facts and information intact\n";
            break;

        case 'summarize':
            $targetWords = $targetLength ?? 100;
            $basePrompt .= "- Summarize this content to approximately {$targetWords} words\n";
            $basePrompt .= "- Keep only the most important points\n";
            $basePrompt .= "- Remove redundant information\n";
            break;

        case 'expand':
            $basePrompt .= "- Expand this content with more detail\n";
            $basePrompt .= "- Add examples, explanations, and context\n";
            $basePrompt .= "- Make it approximately 50% longer\n";
            $basePrompt .= "- Keep the original message and facts\n";
            break;

        case 'simplify':
            $basePrompt .= "- Simplify this content for easy understanding\n";
            $basePrompt .= "- Use shorter sentences\n";
            $basePrompt .= "- Replace complex words with simpler alternatives\n";
            $basePrompt .= "- Target reading level: 8th grade\n";
            break;

        case 'formalize':
            $basePrompt .= "- Rewrite in a formal, professional tone\n";
            $basePrompt .= "- Use proper business language\n";
            $basePrompt .= "- Remove casual expressions and slang\n";
            $basePrompt .= "- Suitable for official documents\n";
            break;

        case 'casual':
            $basePrompt .= "- Rewrite in a casual, friendly tone\n";
            $basePrompt .= "- Use conversational language\n";
            $basePrompt .= "- Make it feel like talking to a friend\n";
            $basePrompt .= "- Keep it engaging and approachable\n";
            break;

        case 'seo':
            $basePrompt .= "- Optimize this content for search engines\n";
            $basePrompt .= "- Improve readability and structure\n";
            $basePrompt .= "- Use clear headings if appropriate\n";
            if (!empty($keyword)) {
                $basePrompt .= "- Naturally include the keyword '{$keyword}' 2-3 times\n";
            }
            $basePrompt .= "- Make it scannable with short paragraphs\n";
            break;

        case 'kids':
            $basePrompt .= "- Rewrite for children aged 8-12\n";
            $basePrompt .= "- Use simple, easy words\n";
            $basePrompt .= "- Make it fun and engaging\n";
            $basePrompt .= "- Explain any difficult concepts\n";
            $basePrompt .= "- Keep sentences short\n";
            break;

        default:
            $basePrompt .= "- Rewrite this content improving clarity\n";
    }

    // Add tone instruction
    if ($tone !== 'neutral') {
        $toneLabel = TONE_OPTIONS[$tone] ?? $tone;
        $basePrompt .= "- Use a {$toneLabel} tone throughout\n";
    }

    // Structure preservation
    if ($preserveStructure) {
        $basePrompt .= "- Preserve the original structure (paragraphs, lists)\n";
    }

    $basePrompt .= "\nFORMATTING REQUIREMENTS:\n";
    $basePrompt .= "- Output as clean HTML for a CMS editor\n";
    $basePrompt .= "- Use <h2> and <h3> tags for headings/sections\n";
    $basePrompt .= "- Wrap paragraphs in <p> tags\n";
    $basePrompt .= "- Use <ul>/<ol> with <li> for lists\n";
    $basePrompt .= "- Use <strong> for emphasis where appropriate\n";
    $basePrompt .= "- NO markdown syntax (no #, **, etc.)\n";
    $basePrompt .= "- Return ONLY the HTML content, no explanations or preamble\n";

    return $basePrompt;
}

/**
 * Rewrite content using AI
 *
 * @param string $content Original content
 * @param string $mode Rewrite mode
 * @param array $options Additional options
 * @return array Result with ok, rewritten, or error
 */
function ai_rewrite_content(string $content, string $mode = 'paraphrase', array $options = []): array
{
    if (empty(trim($content))) {
        return ['ok' => false, 'error' => 'Content is empty'];
    }

    $wordCount = str_word_count($content);

    // Estimate max tokens needed
    $maxTokens = match($mode) {
        'summarize' => min(500, (int)($wordCount * 0.5)),
        'expand' => (int)($wordCount * 2),
        default => (int)($wordCount * 1.5),
    };
    $maxTokens = max(100, min(2000, $maxTokens));

    $prompt = ai_rewrite_build_prompt($content, $mode, $options);

    // Use multi-provider ai_universal_generate if provider specified
    $provider = $options['provider'] ?? 'openai';
    $model = $options['model'] ?? 'gpt-5.2';

    // Validate provider and model
    if (function_exists('ai_is_valid_provider') && !ai_is_valid_provider($provider)) {
        $provider = 'openai';
    }
    if (function_exists('ai_is_valid_provider_model') && !ai_is_valid_provider_model($provider, $model)) {
        $model = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2';
    }

    $systemPrompt = 'You are a professional content rewriter. Output clean, high-quality text. Do not add any preamble or explanation.';

    // Use universal generate for multi-provider support
    if (function_exists('ai_universal_generate')) {
        $result = ai_universal_generate($provider, $model, $systemPrompt, $prompt, [
            'max_tokens' => $maxTokens,
            'temperature' => 0.7
        ]);
    } else {
        // Fallback to legacy OpenAI-only function
        $genOptions = [
            'params' => [
                'max_new_tokens' => $maxTokens,
                'temperature' => 0.7,
            ],
            'model' => $model
        ];
        $result = ai_openai_generate_text($prompt, $genOptions);
    }

    if (!$result['ok']) {
        return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
    }

    $rewritten = trim($result['text']);

    // Clean up common AI artifacts
    $rewritten = preg_replace('/^(Here\'s|Here is|The rewritten|Rewritten version:?)\s*/i', '', $rewritten);
    $rewritten = trim($rewritten, '"\'');

    $newWordCount = str_word_count($rewritten);

    return [
        'ok' => true,
        'original' => $content,
        'rewritten' => $rewritten,
        'mode' => $mode,
        'original_words' => $wordCount,
        'new_words' => $newWordCount,
        'change_percent' => $wordCount > 0 ? round((($newWordCount - $wordCount) / $wordCount) * 100) : 0,
    ];
}

/**
 * Batch rewrite multiple content pieces
 *
 * @param array $items Array of ['id' => ..., 'content' => ...]
 * @param string $mode Rewrite mode
 * @param array $options Options
 * @return array Batch results
 */
function ai_rewrite_batch(array $items, string $mode = 'paraphrase', array $options = []): array
{
    $results = [];
    $success = 0;
    $failed = 0;

    foreach ($items as $item) {
        $id = $item['id'] ?? null;
        $content = $item['content'] ?? '';

        if (empty($content)) {
            $results[] = [
                'id' => $id,
                'ok' => false,
                'error' => 'Empty content',
            ];
            $failed++;
            continue;
        }

        $result = ai_rewrite_content($content, $mode, $options);
        $result['id'] = $id;
        $results[] = $result;

        if ($result['ok']) {
            $success++;
        } else {
            $failed++;
        }

        // Rate limiting
        usleep(500000); // 0.5 second delay
    }

    return [
        'ok' => true,
        'total' => count($items),
        'success' => $success,
        'failed' => $failed,
        'results' => $results,
    ];
}

/**
 * Rewrite page content from database
 *
 * @param int $pageId Page ID
 * @param string $mode Rewrite mode
 * @param array $options Options
 * @param bool $save Whether to save to database
 * @return array Result
 */
function ai_rewrite_page(int $pageId, string $mode = 'paraphrase', array $options = [], bool $save = false): array
{
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT id, title, content FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$page) {
            return ['ok' => false, 'error' => 'Page not found'];
        }

        // Strip HTML for rewriting, but we'll need smarter handling for real use
        $textContent = strip_tags($page['content']);

        $result = ai_rewrite_content($textContent, $mode, $options);

        if (!$result['ok']) {
            return $result;
        }

        $result['page_id'] = $pageId;
        $result['page_title'] = $page['title'];

        if ($save) {
            // Wrap in paragraph tags if original had HTML
            $newContent = '<p>' . nl2br(htmlspecialchars($result['rewritten'])) . '</p>';

            $updateStmt = $pdo->prepare("UPDATE pages SET content = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newContent, $pageId]);

            $result['saved'] = true;
        }

        return $result;

    } catch (Exception $e) {
        error_log('[AI_REWRITE] Page rewrite error: ' . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Compare original and rewritten content
 *
 * @param string $original Original content
 * @param string $rewritten Rewritten content
 * @return array Comparison stats
 */
function ai_rewrite_compare(string $original, string $rewritten): array
{
    $origWords = str_word_count($original);
    $newWords = str_word_count($rewritten);

    $origChars = mb_strlen($original);
    $newChars = mb_strlen($rewritten);

    $origSentences = preg_match_all('/[.!?]+/', $original, $m);
    $newSentences = preg_match_all('/[.!?]+/', $rewritten, $m);

    // Calculate similarity (simple approach)
    similar_text(strtolower($original), strtolower($rewritten), $similarity);

    return [
        'original' => [
            'words' => $origWords,
            'characters' => $origChars,
            'sentences' => $origSentences,
        ],
        'rewritten' => [
            'words' => $newWords,
            'characters' => $newChars,
            'sentences' => $newSentences,
        ],
        'changes' => [
            'word_diff' => $newWords - $origWords,
            'word_percent' => $origWords > 0 ? round((($newWords - $origWords) / $origWords) * 100) : 0,
            'char_diff' => $newChars - $origChars,
            'similarity_percent' => round($similarity),
        ],
    ];
}

/**
 * Get rewrite history for a page
 *
 * @param int $pageId Page ID
 * @return array History entries
 */
function ai_rewrite_get_history(int $pageId): array
{
    $historyFile = CMS_ROOT . '/cms_storage/rewrite_history/' . $pageId . '.json';

    if (!file_exists($historyFile)) {
        return [];
    }

    $data = json_decode(file_get_contents($historyFile), true);
    return is_array($data) ? $data : [];
}

/**
 * Save rewrite to history
 *
 * @param int $pageId Page ID
 * @param array $rewriteData Rewrite result data
 * @return bool Success
 */
function ai_rewrite_save_history(int $pageId, array $rewriteData): bool
{
    $dir = CMS_ROOT . '/cms_storage/rewrite_history';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $historyFile = $dir . '/' . $pageId . '.json';
    $history = ai_rewrite_get_history($pageId);

    // Add new entry
    $history[] = [
        'timestamp' => gmdate('Y-m-d H:i:s'),
        'mode' => $rewriteData['mode'] ?? 'unknown',
        'original_words' => $rewriteData['original_words'] ?? 0,
        'new_words' => $rewriteData['new_words'] ?? 0,
        'original_excerpt' => mb_substr($rewriteData['original'] ?? '', 0, 200),
        'rewritten_excerpt' => mb_substr($rewriteData['rewritten'] ?? '', 0, 200),
    ];

    // Keep only last 20 entries
    if (count($history) > 20) {
        $history = array_slice($history, -20);
    }

    return file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Quick rewrite presets
 *
 * @param string $content Content to rewrite
 * @param string $preset Preset name
 * @return array Result
 */
function ai_rewrite_preset(string $content, string $preset): array
{
    $presets = [
        'blog_to_social' => [
            'mode' => 'summarize',
            'options' => ['target_length' => 50, 'tone' => 'enthusiastic'],
        ],
        'formal_email' => [
            'mode' => 'formalize',
            'options' => ['tone' => 'formal'],
        ],
        'kid_friendly' => [
            'mode' => 'kids',
            'options' => ['tone' => 'educational'],
        ],
        'executive_summary' => [
            'mode' => 'summarize',
            'options' => ['target_length' => 100, 'tone' => 'formal'],
        ],
        'seo_boost' => [
            'mode' => 'seo',
            'options' => ['tone' => 'neutral'],
        ],
        'casual_rewrite' => [
            'mode' => 'casual',
            'options' => ['tone' => 'friendly'],
        ],
        'expand_detailed' => [
            'mode' => 'expand',
            'options' => ['tone' => 'educational'],
        ],
    ];

    if (!isset($presets[$preset])) {
        return ['ok' => false, 'error' => 'Unknown preset: ' . $preset];
    }

    $config = $presets[$preset];
    return ai_rewrite_content($content, $config['mode'], $config['options']);
}

/**
 * Get available rewrite modes
 *
 * @return array Modes with metadata
 */
function ai_rewrite_get_modes(): array
{
    return REWRITE_MODES;
}

/**
 * Get available tone options
 *
 * @return array Tone options
 */
function ai_rewrite_get_tones(): array
{
    return TONE_OPTIONS;
}

/**
 * Get available presets
 *
 * @return array Preset names and descriptions
 */
function ai_rewrite_get_presets(): array
{
    return [
        'blog_to_social' => 'Convert blog post to social media snippet',
        'formal_email' => 'Convert to formal business email',
        'kid_friendly' => 'Make content suitable for children',
        'executive_summary' => 'Create executive summary',
        'seo_boost' => 'Optimize for search engines',
        'casual_rewrite' => 'Make content more casual and friendly',
        'expand_detailed' => 'Expand with more detail and examples',
    ];
}

/**
 * Validate rewrite mode
 *
 * @param string $mode Mode to validate
 * @return bool Valid or not
 */
function ai_rewrite_validate_mode(string $mode): bool
{
    return isset(REWRITE_MODES[$mode]);
}

/**
 * Validate tone option
 *
 * @param string $tone Tone to validate
 * @return bool Valid or not
 */
function ai_rewrite_validate_tone(string $tone): bool
{
    return isset(TONE_OPTIONS[$tone]);
}
