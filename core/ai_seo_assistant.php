<?php

require_once __DIR__ . '/ai_content.php';
if (file_exists(__DIR__ . '/ai_readability.php')) { require_once __DIR__ . '/ai_readability.php'; }
if (file_exists(__DIR__ . '/ai_image_seo.php')) { require_once __DIR__ . '/ai_image_seo.php'; }
if (file_exists(__DIR__ . '/ai_eeat_scorer.php')) { require_once __DIR__ . '/ai_eeat_scorer.php'; }
if (file_exists(__DIR__ . '/ai_featured_snippets.php')) { require_once __DIR__ . '/ai_featured_snippets.php'; }

/**
 * AI SEO Log file path
 */
if (!defined('AI_SEO_LOG_FILE')) {
    define('AI_SEO_LOG_FILE', CMS_ROOT . '/logs/ai-seo.log');
}

/**
 * Log AI SEO event
 *
 * @param string $level Log level: INFO, WARNING, ERROR
 * @param string $message Log message
 * @param array $context Optional context data
 * @return void
 */
function ai_seo_log(string $level, string $message, array $context = []): void
{
    $logDir = dirname(AI_SEO_LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $timestamp = gmdate('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $line = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";

    @file_put_contents(AI_SEO_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

/**
 * Log AI SEO analysis start
 *
 * @param string $keyword Focus keyword
 * @param string $url URL being analyzed
 * @return float Start time for duration calculation
 */
function ai_seo_log_start(string $keyword, string $url = ''): float
{
    $startTime = microtime(true);
    ai_seo_log('INFO', 'Analysis started', [
        'keyword' => $keyword,
        'url' => $url,
    ]);
    return $startTime;
}

/**
 * Log AI SEO analysis completion
 *
 * @param float $startTime Start time from ai_seo_log_start()
 * @param bool $success Whether analysis succeeded
 * @param int|null $score Health score if successful
 * @param bool $cached Whether result was from cache
 * @return void
 */
function ai_seo_log_complete(float $startTime, bool $success, ?int $score = null, bool $cached = false): void
{
    $duration = round(microtime(true) - $startTime, 2);
    $level = $success ? 'INFO' : 'ERROR';
    $message = $success ? 'Analysis completed' : 'Analysis failed';

    ai_seo_log($level, $message, [
        'duration_sec' => $duration,
        'score' => $score,
        'cached' => $cached,
    ]);
}

/**
 * Log AI SEO error
 *
 * @param string $message Error message
 * @param array $context Optional context
 * @return void
 */
function ai_seo_log_error(string $message, array $context = []): void
{
    ai_seo_log('ERROR', $message, $context);
}

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

function ai_seo_assistant_analyze(array $spec): array
{
    try {
        $logStartTime = ai_seo_log_start(
            $spec['focus_keyword'] ?? '',
            $spec['url'] ?? ''
        );
        $title = trim((string)($spec['title'] ?? ''));
        $url = trim((string)($spec['url'] ?? ''));
        $focusKeyword = trim((string)($spec['focus_keyword'] ?? ''));
        $secondaryKeywords = trim((string)($spec['secondary_keywords'] ?? ''));
        $contentHtml = trim((string)($spec['content_html'] ?? ''));
        // Clean builder markup for AI analysis
        if (class_exists('ContentRenderer')) {
            $contentHtml = ContentRenderer::render($contentHtml);
        }
        $contentType = trim((string)($spec['content_type'] ?? ''));
        $language = trim((string)($spec['language'] ?? ''));
        $notes = trim((string)($spec['notes'] ?? ''));

        if ($language === '') {
            $language = 'en';
        }
        $allowedLanguages = ['en', 'pl', 'de', 'es', 'fr'];
        if (!in_array($language, $allowedLanguages, true)) {
            $language = 'en';
        }

        if ($focusKeyword === '' || $contentHtml === '') {
            ai_seo_log_error('Missing focus keyword or content');
            return [
                'ok' => false,
                'error' => 'Focus keyword and content are required.'
            ];
        }

        $prompt = "You are an expert SEO consultant analyzing a single page or post for on-page SEO optimization.\n\n";
        $prompt .= "Analyze the following content and provide structured SEO recommendations in {$language}.\n\n";

        if ($url !== '') {
            $prompt .= "URL: {$url}\n";
        }
        if ($title !== '') {
            $prompt .= "Title: {$title}\n";
        }
        $prompt .= "Focus keyword: {$focusKeyword}\n";
        if ($secondaryKeywords !== '') {
            $prompt .= "Secondary keywords: {$secondaryKeywords}\n";
        }
        if ($contentType !== '') {
            $prompt .= "Content type: {$contentType}\n";
        }
        $prompt .= "\nMain content:\n{$contentHtml}\n\n";
        if ($notes !== '') {
            $prompt .= "Additional instructions:\n{$notes}\n\n";
        }

        $prompt .= "Requirements:\n";
        $prompt .= "- Output ONLY a single JSON object.\n";
        $prompt .= "- Do NOT use markdown, backticks, or any wrapper.\n";
        $prompt .= "- All text in {$language}.\n";
        $prompt .= "- JSON structure (STRICT):\n\n";
        $prompt .= "{\n";
        $prompt .= "  \"language\": \"{$language}\",\n";
        $prompt .= "  \"focus_keyword\": \"{$focusKeyword}\",\n";
        $prompt .= "  \"health_score\": 0-100,\n";
        $prompt .= "  \"summary\": \"short overview of SEO health\",\n";
        $prompt .= "  \"on_page_checks\": {\n";
        $prompt .= "    \"title_usage\": \"analysis of keyword in title\",\n";
        $prompt .= "    \"meta_suggestions\": {\n";
        $prompt .= "      \"recommended_title\": \"optimal title tag\",\n";
        $prompt .= "      \"recommended_meta_description\": \"optimal meta description\"\n";
        $prompt .= "    },\n";
        $prompt .= "    \"headings\": {\n";
        $prompt .= "      \"current_issues\": [\"issue 1\", \"issue 2\"],\n";
        $prompt .= "      \"suggested_improvements\": [\"suggestion 1\", \"suggestion 2\"]\n";
        $prompt .= "    },\n";
        $prompt .= "    \"keyword_usage\": {\n";
        $prompt .= "      \"density_comment\": \"analysis of keyword density\",\n";
        $prompt .= "      \"missing_variants\": [\"variant 1\", \"variant 2\"]\n";
        $prompt .= "    },\n";
        $prompt .= "    \"internal_links\": {\n";
        $prompt .= "      \"status\": \"good | needs improvement\",\n";
        $prompt .= "      \"suggestions\": [\"suggestion 1\", \"suggestion 2\"]\n";
        $prompt .= "    },\n";
        $prompt .= "    \"readability\": {\n";
        $prompt .= "      \"score_comment\": \"readability assessment\",\n";
        $prompt .= "      \"suggestions\": [\"suggestion 1\", \"suggestion 2\"]\n";
        $prompt .= "    }\n";
        $prompt .= "  },\n";
        $prompt .= "  \"quick_wins\": [\"quick win 1\", \"quick win 2\", \"quick win 3\"],\n";
        $prompt .= "  \"content_ideas\": [\"idea 1\", \"idea 2\", \"idea 3\"],\n";
        $prompt .= "  \"technical_flags\": [\"flag 1\", \"flag 2\"],\n";
        $prompt .= "  \"keyword_difficulty\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"keyword\": \"main topic phrase\",\n";
        $prompt .= "      \"difficulty\": 0-100,\n";
        $prompt .= "      \"level\": \"easy|medium|hard\",\n";
        $prompt .= "      \"note\": \"short explanation (1-2 sentences)\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"keyword_clusters\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"label\": \"Cluster name\",\n";
        $prompt .= "      \"summary\": \"1-2 sentence summary of this cluster\",\n";
        $prompt .= "      \"keywords\": [\"kw1\", \"kw2\", \"kw3\"]\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"recommended_word_count\": 1500,\n";
        $prompt .= "  \"section_outline\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"id\": \"intro\",\n";
        $prompt .= "      \"heading\": \"H2 or H3 heading text\",\n";
        $prompt .= "      \"type\": \"intro|body|faq|cta|conclusion|other\",\n";
        $prompt .= "      \"purpose\": \"short explanation of this section\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"content_score_breakdown\": {\n";
        $prompt .= "    \"word_count\": { \"score\": 0-100, \"note\": \"explanation\" },\n";
        $prompt .= "    \"headings\": { \"score\": 0-100, \"note\": \"explanation\" },\n";
        $prompt .= "    \"keywords\": { \"score\": 0-100, \"note\": \"explanation\" },\n";
        $prompt .= "    \"structure\": { \"score\": 0-100, \"note\": \"explanation\" },\n";
        $prompt .= "    \"media\": { \"score\": 0-100, \"note\": \"explanation\" },\n";
        $prompt .= "    \"links\": { \"score\": 0-100, \"note\": \"explanation\" }\n";
        $prompt .= "  },\n";
        $prompt .= "  \"serp_profile\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"position\": 1,\n";
        $prompt .= "      \"title\": \"Competitor page title\",\n";
        $prompt .= "      \"url\": \"https://example.com/page\",\n";
        $prompt .= "      \"word_count\": 2500,\n";
        $prompt .= "      \"headings_count\": 12,\n";
        $prompt .= "      \"images_count\": 5,\n";
        $prompt .= "      \"key_topics\": [\"topic1\", \"topic2\", \"topic3\"],\n";
        $prompt .= "      \"strengths\": [\"strength1\", \"strength2\"],\n";
        $prompt .= "      \"gaps\": [\"gap you can exploit\"]\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"nlp_terms\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"term\": \"semantic phrase\",\n";
        $prompt .= "      \"importance\": \"high|medium|low\",\n";
        $prompt .= "      \"found_in_content\": true,\n";
        $prompt .= "      \"recommended_count\": 3,\n";
        $prompt .= "      \"current_count\": 1,\n";
        $prompt .= "      \"context\": \"where/how to use this term\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"actionable_tasks\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"id\": \"task_1\",\n";
        $prompt .= "      \"priority\": \"critical|high|medium|low\",\n";
        $prompt .= "      \"category\": \"content|technical|on-page|structure\",\n";
        $prompt .= "      \"task\": \"Short task description\",\n";
        $prompt .= "      \"details\": \"Detailed explanation of what to do and why\",\n";
        $prompt .= "      \"impact\": \"Expected SEO impact (1-2 sentences)\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        $prompt .= "- health_score: 0-100 integer based on overall SEO quality\n";
        $prompt .= "- Provide 3-8 quick wins (immediate actions)\n";
        $prompt .= "- Provide 3-8 content ideas (related topics/keywords)\n";
        $prompt .= "- technical_flags: any technical SEO issues found\n";
        $prompt .= "- keyword_difficulty: analyze difficulty (0-100) for focus keyword and important secondary keywords\n";
        $prompt .= "- keyword_clusters: group related keywords into 2-5 thematic clusters with labels and summaries\n";
        $prompt .= "- recommended_word_count: integer (typical range 500-5000) for optimal content length\n";
        $prompt .= "- section_outline: array of recommended sections with headings, types, and purposes\n";
        $prompt .= "- content_score_breakdown: object with scores (0-100) and notes for each SEO factor\n";
        $prompt .= "- serp_profile: simulate top 10 competing pages for the focus keyword. Include position (1-10), estimated word count, headings count, images count, key topics covered, strengths and content gaps you can exploit\n";
        $prompt .= "- nlp_terms: provide 15-25 semantically related NLP terms/phrases. For each term indicate importance (high/medium/low), whether it was found in the content, recommended usage count, current count, and context for how to use it\n";
        $prompt .= "- actionable_tasks: provide 8-15 specific TODO tasks sorted by priority. Each task has id, priority (critical/high/medium/low), category (content/technical/on-page/structure), short task description, detailed explanation, and expected SEO impact\n";
        $prompt .= "- Return STRICT JSON with these keys if possible; if you are unsure, still fill them with best-effort estimates\n";
        $prompt .= "- Be specific, actionable and practical\n";

        // Use multi-provider AI via ai_universal_generate()
        $aiSettings = ai_config_load_full();
        $provider = '';
        $model = '';
        if (!empty($aiSettings['providers'])) {
            foreach ($aiSettings['providers'] as $pName => $pConfig) {
                if (!empty($pConfig['enabled']) && !empty($pConfig['api_key'])) {
                    $provider = $pName;
                    $model = $pConfig['default_model'] ?? '';
                    break;
                }
            }
        }
        if (empty($provider)) {
            ai_seo_log_error('No AI provider configured');
            ai_seo_log_complete($logStartTime, false);
            return [
                'ok' => false,
                'error' => 'No AI provider configured. Please configure AI settings.'
            ];
        }

        $systemPrompt = 'You are an expert SEO consultant. Always respond with valid JSON only, no markdown formatting.';
        $aiResult = ai_universal_generate($provider, $model, $systemPrompt, $prompt, [
            'max_tokens' => 4000,
            'temperature' => 0.35
        ]);

        if (!$aiResult['ok']) {
            ai_seo_log_error('AI generation failed', ['error' => $aiResult['error'] ?? 'Unknown']);
            ai_seo_log_complete($logStartTime, false);
            return [
                'ok' => false,
                'error' => $aiResult['error'] ?? 'AI generation failed. Please try again.'
            ];
        }

        $wasCached = false;
        $text = trim($aiResult['content'] ?? '');

        $text = preg_replace('/^json:\s*/i', '', $text);
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        $data = @json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            error_log('AI SEO Assistant: JSON decode failed - ' . $jsonError);
            ai_seo_log_error('JSON decode failed', ['error' => $jsonError]);
            ai_seo_log_complete($logStartTime, false);
            return [
                'ok' => false,
                'error' => 'The AI did not return valid JSON.'
            ];
        }

        $requiredKeys = ['language', 'focus_keyword', 'health_score', 'summary', 'on_page_checks', 'quick_wins', 'content_ideas'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                error_log('AI SEO Assistant: Missing required key: ' . $key);
                ai_seo_log_error('Missing required key in response', ['key' => $key]);
                ai_seo_log_complete($logStartTime, false);
                return [
                    'ok' => false,
                    'error' => 'The generated report is incomplete. Please try again.'
                ];
            }
        }

        if (isset($data['health_score'])) {
            $score = is_numeric($data['health_score']) ? (int)$data['health_score'] : 0;
            $data['health_score'] = max(0, min(100, $score));
        } else {
            $data['health_score'] = 0;
        }

        if (!isset($data['keyword_difficulty']) || !is_array($data['keyword_difficulty'])) {
            $data['keyword_difficulty'] = [];
        } else {
            $validated = [];
            foreach ($data['keyword_difficulty'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $keyword = isset($item['keyword']) ? trim((string)$item['keyword']) : '';
                if ($keyword === '') {
                    continue;
                }
                $difficulty = isset($item['difficulty']) && is_numeric($item['difficulty']) ? (int)$item['difficulty'] : 50;
                $difficulty = max(0, min(100, $difficulty));
                $level = isset($item['level']) ? strtolower(trim((string)$item['level'])) : 'medium';
                if (!in_array($level, ['easy', 'medium', 'hard'], true)) {
                    $level = 'medium';
                }
                $note = isset($item['note']) ? trim((string)$item['note']) : '';
                $validated[] = [
                    'keyword' => $keyword,
                    'difficulty' => $difficulty,
                    'level' => $level,
                    'note' => $note
                ];
            }
            $data['keyword_difficulty'] = $validated;
        }

        if (!isset($data['keyword_clusters']) || !is_array($data['keyword_clusters'])) {
            $data['keyword_clusters'] = [];
        } else {
            $validated = [];
            foreach ($data['keyword_clusters'] as $cluster) {
                if (!is_array($cluster)) {
                    continue;
                }
                $label = isset($cluster['label']) ? trim((string)$cluster['label']) : '';
                if ($label === '') {
                    continue;
                }
                $summary = isset($cluster['summary']) ? trim((string)$cluster['summary']) : '';
                $keywords = isset($cluster['keywords']) && is_array($cluster['keywords']) ? $cluster['keywords'] : [];
                $keywordsValidated = [];
                foreach ($keywords as $kw) {
                    $kwStr = trim((string)$kw);
                    if ($kwStr !== '') {
                        $keywordsValidated[] = $kwStr;
                    }
                }
                $validated[] = [
                    'label' => $label,
                    'summary' => $summary,
                    'keywords' => $keywordsValidated
                ];
            }
            $data['keyword_clusters'] = $validated;
        }

        if (isset($data['recommended_word_count']) && is_numeric($data['recommended_word_count'])) {
            $wc = (int)$data['recommended_word_count'];
            if ($wc >= 300 && $wc <= 10000) {
                $data['recommended_word_count'] = $wc;
            } else {
                $data['recommended_word_count'] = null;
            }
        } else {
            $data['recommended_word_count'] = null;
        }

        if (!isset($data['section_outline']) || !is_array($data['section_outline'])) {
            $data['section_outline'] = [];
        } else {
            $validated = [];
            $allowedTypes = ['intro', 'body', 'faq', 'cta', 'conclusion', 'other'];
            foreach ($data['section_outline'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = isset($item['id']) && is_string($item['id']) ? trim($item['id']) : '';
                $heading = isset($item['heading']) && is_string($item['heading']) ? trim($item['heading']) : '';
                $type = isset($item['type']) && is_string($item['type']) ? strtolower(trim($item['type'])) : 'other';
                if (!in_array($type, $allowedTypes, true)) {
                    $type = 'other';
                }
                $purpose = isset($item['purpose']) && is_string($item['purpose']) ? trim($item['purpose']) : '';
                if ($heading === '' && $purpose === '') {
                    continue;
                }
                $validated[] = [
                    'id' => $id,
                    'heading' => $heading,
                    'type' => $type,
                    'purpose' => $purpose
                ];
            }
            $data['section_outline'] = $validated;
        }

        if (!isset($data['content_score_breakdown']) || !is_array($data['content_score_breakdown'])) {
            $data['content_score_breakdown'] = [];
        } else {
            $allowedMetrics = ['word_count', 'headings', 'keywords', 'structure', 'media', 'links'];
            $validated = [];
            foreach ($allowedMetrics as $metric) {
                if (isset($data['content_score_breakdown'][$metric]) && is_array($data['content_score_breakdown'][$metric])) {
                    $scoreData = $data['content_score_breakdown'][$metric];
                    $score = isset($scoreData['score']) && is_numeric($scoreData['score']) ? (int)$scoreData['score'] : 0;
                    $score = max(0, min(100, $score));
                    $note = isset($scoreData['note']) && is_string($scoreData['note']) ? trim($scoreData['note']) : '';
                    $validated[$metric] = [
                        'score' => $score,
                        'note' => $note
                    ];
                }
            }
            $data['content_score_breakdown'] = $validated;
        }

        // Validate serp_profile (SERP competitor analysis)
        if (!isset($data['serp_profile']) || !is_array($data['serp_profile'])) {
            $data['serp_profile'] = [];
        } else {
            $validated = [];
            foreach ($data['serp_profile'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $position = isset($item['position']) && is_numeric($item['position']) ? (int)$item['position'] : 0;
                $position = max(1, min(10, $position));
                $title = isset($item['title']) && is_string($item['title']) ? trim($item['title']) : '';
                $url = isset($item['url']) && is_string($item['url']) ? trim($item['url']) : '';
                $wordCount = isset($item['word_count']) && is_numeric($item['word_count']) ? (int)$item['word_count'] : 0;
                $headingsCount = isset($item['headings_count']) && is_numeric($item['headings_count']) ? (int)$item['headings_count'] : 0;
                $imagesCount = isset($item['images_count']) && is_numeric($item['images_count']) ? (int)$item['images_count'] : 0;
                $keyTopics = isset($item['key_topics']) && is_array($item['key_topics']) ? array_filter(array_map('trim', array_map('strval', $item['key_topics']))) : [];
                $strengths = isset($item['strengths']) && is_array($item['strengths']) ? array_filter(array_map('trim', array_map('strval', $item['strengths']))) : [];
                $gaps = isset($item['gaps']) && is_array($item['gaps']) ? array_filter(array_map('trim', array_map('strval', $item['gaps']))) : [];

                if ($title === '' && $url === '') {
                    continue;
                }

                $validated[] = [
                    'position' => $position,
                    'title' => $title,
                    'url' => $url,
                    'word_count' => max(0, $wordCount),
                    'headings_count' => max(0, $headingsCount),
                    'images_count' => max(0, $imagesCount),
                    'key_topics' => array_values($keyTopics),
                    'strengths' => array_values($strengths),
                    'gaps' => array_values($gaps)
                ];
            }
            // Sort by position
            usort($validated, function($a, $b) {
                return $a['position'] - $b['position'];
            });
            $data['serp_profile'] = $validated;
        }

        // Validate nlp_terms (semantic NLP phrases)
        if (!isset($data['nlp_terms']) || !is_array($data['nlp_terms'])) {
            $data['nlp_terms'] = [];
        } else {
            $validated = [];
            $allowedImportance = ['high', 'medium', 'low'];
            foreach ($data['nlp_terms'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $term = isset($item['term']) && is_string($item['term']) ? trim($item['term']) : '';
                if ($term === '') {
                    continue;
                }
                $importance = isset($item['importance']) && is_string($item['importance']) ? strtolower(trim($item['importance'])) : 'medium';
                if (!in_array($importance, $allowedImportance, true)) {
                    $importance = 'medium';
                }
                $foundInContent = isset($item['found_in_content']) ? (bool)$item['found_in_content'] : false;
                $recommendedCount = isset($item['recommended_count']) && is_numeric($item['recommended_count']) ? max(0, (int)$item['recommended_count']) : 1;
                $currentCount = isset($item['current_count']) && is_numeric($item['current_count']) ? max(0, (int)$item['current_count']) : 0;
                $context = isset($item['context']) && is_string($item['context']) ? trim($item['context']) : '';

                $validated[] = [
                    'term' => $term,
                    'importance' => $importance,
                    'found_in_content' => $foundInContent,
                    'recommended_count' => $recommendedCount,
                    'current_count' => $currentCount,
                    'context' => $context
                ];
            }
            // Sort by importance (high first, then medium, then low)
            $importanceOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            usort($validated, function($a, $b) use ($importanceOrder) {
                return $importanceOrder[$a['importance']] - $importanceOrder[$b['importance']];
            });
            $data['nlp_terms'] = $validated;
        }

        // Validate actionable_tasks (SEO TODO items)
        if (!isset($data['actionable_tasks']) || !is_array($data['actionable_tasks'])) {
            $data['actionable_tasks'] = [];
        } else {
            $validated = [];
            $allowedPriorities = ['critical', 'high', 'medium', 'low'];
            $allowedCategories = ['content', 'technical', 'on-page', 'structure'];
            $taskIndex = 1;
            foreach ($data['actionable_tasks'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $id = isset($item['id']) && is_string($item['id']) ? trim($item['id']) : '';
                if ($id === '') {
                    $id = 'task_' . $taskIndex;
                }
                $priority = isset($item['priority']) && is_string($item['priority']) ? strtolower(trim($item['priority'])) : 'medium';
                if (!in_array($priority, $allowedPriorities, true)) {
                    $priority = 'medium';
                }
                $category = isset($item['category']) && is_string($item['category']) ? strtolower(trim($item['category'])) : 'content';
                if (!in_array($category, $allowedCategories, true)) {
                    $category = 'content';
                }
                $task = isset($item['task']) && is_string($item['task']) ? trim($item['task']) : '';
                $details = isset($item['details']) && is_string($item['details']) ? trim($item['details']) : '';
                $impact = isset($item['impact']) && is_string($item['impact']) ? trim($item['impact']) : '';

                if ($task === '') {
                    continue;
                }

                $validated[] = [
                    'id' => $id,
                    'priority' => $priority,
                    'category' => $category,
                    'task' => $task,
                    'details' => $details,
                    'impact' => $impact
                ];
                $taskIndex++;
            }
            // Sort by priority (critical first, then high, medium, low)
            $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            usort($validated, function($a, $b) use ($priorityOrder) {
                return $priorityOrder[$a['priority']] - $priorityOrder[$b['priority']];
            });
            $data['actionable_tasks'] = $validated;
        }

        $healthScore = isset($data['health_score']) ? (int)$data['health_score'] : null;
        ai_seo_log_complete($logStartTime, true, $healthScore, $wasCached);

        // Add readability analysis
        $readabilityData = function_exists("ai_readability_analyze") ? ai_readability_analyze($contentHtml) : ["ok" => false];
        if ($readabilityData['ok']) {
            $data['readability'] = $readabilityData;
        }

        // Add image SEO analysis
        $imageSeoData = function_exists("ai_image_analyze_content") ? ai_image_analyze_content($contentHtml, $focusKeyword) : ["ok" => false];
        if ($imageSeoData['ok']) {
            $data['image_seo'] = $imageSeoData;
        }

        // Add E-E-A-T analysis
        $eeatData = function_exists("ai_eeat_score") ? ai_eeat_score($contentHtml, [
            'author' => $author ?? '',
            'updated_at' => $updatedAt ?? '',
        ]
        ) : [];
        if (!empty($eeatData)) {
            $data['eeat'] = $eeatData;
        }

        // Add Featured Snippets analysis
        $snippetsData = function_exists("ai_snippets_analyze") ? ai_snippets_analyze($contentHtml, $focusKeyword) : ["ok" => false];
        if ($snippetsData['ok']) {
            $data['featured_snippets'] = $snippetsData;
        }

        return [
            'ok' => true,
            'report' => $data,
            'json' => $text,
            'prompt' => $prompt,
            'cached' => $wasCached
        ];

    } catch (Exception $e) {
        error_log('AI SEO Assistant exception: ' . $e->getMessage());
        ai_seo_log_error('Exception', ['message' => $e->getMessage()]);
        return [
            'ok' => false,
            'error' => 'Unexpected error while generating SEO analysis. Please try again.'
        ];
    }
}

/**
 * Returns the directory path for SEO reports and ensures it exists.
 *
 * @return string The reports directory path
 */
function ai_seo_assistant_reports_dir(): string
{
    $dir = CMS_ROOT . '/cms_storage/ai-seo-reports';

    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true)) {
            error_log('ai_seo_assistant: failed to create reports dir: ' . $dir);
        }
    }

    return $dir;
}

/**
 * Saves an SEO analysis report to disk as a JSON file.
 *
 * @param array $payload The analysis data to save
 * @param array $context Optional context (source, page_id, slug, url, title, language, keyword)
 * @return string|null The filename on success, null on failure
 */
function ai_seo_assistant_save_report(array $payload, array $context = []): ?string
{
    try {
        $record = [
            'id' => 'seo_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(4)),
            'created_at' => gmdate('c'),
            'context' => [
                'source' => $context['source'] ?? null,
                'page_id' => $context['page_id'] ?? null,
                'article_id' => $context['article_id'] ?? null,
                'slug' => $context['slug'] ?? null,
                'url' => $context['url'] ?? null,
                'title' => $context['title'] ?? null,
                'language' => $context['language'] ?? null,
                'keyword' => $context['keyword'] ?? null,
            ],
            'data' => $payload,
        ];

        $dir = ai_seo_assistant_reports_dir();
        $filename = $record['id'] . '.json';
        $path = $dir . '/' . $filename;

        $json = json_encode(
            $record,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            error_log('ai_seo_assistant: json_encode failed: ' . json_last_error_msg());
            return null;
        }

        $written = @file_put_contents($path, $json);

        if ($written === false) {
            error_log('ai_seo_assistant: failed to write report: ' . $path);
            return null;
        }

        return $filename;

    } catch (\Throwable $e) {
        error_log('ai_seo_assistant: save_report exception: ' . $e->getMessage());
        return null;
    }
}

/**
 * Lists all saved SEO reports from the reports directory.
 *
 * @return array Array of report summaries sorted by created_at descending
 */
function ai_seo_assistant_list_reports(): array
{
    $dir = ai_seo_assistant_reports_dir();

    if (!is_dir($dir)) {
        return [];
    }

    $files = glob($dir . '/*.json');
    if ($files === false || empty($files)) {
        return [];
    }

    $summary = [];

    foreach ($files as $file) {
        $contents = @file_get_contents($file);
        if ($contents === false) {
            continue;
        }

        $record = @json_decode($contents, true);
        if (!is_array($record)) {
            continue;
        }

        $data = is_array($record['data'] ?? null) ? $record['data'] : [];
        $context = is_array($record['context'] ?? null) ? $record['context'] : [];

        $healthScore = isset($data['health_score']) ? (int) $data['health_score'] : null;
        $recommendedWordCount = isset($data['recommended_word_count']) ? (int) $data['recommended_word_count'] : null;
        $keywordDifficulty = is_array($data['keyword_difficulty'] ?? null) ? $data['keyword_difficulty'] : [];
        $keywordCount = count($keywordDifficulty);

        $contentScore = null;
        if (isset($data['content_score_breakdown']) && is_array($data['content_score_breakdown'])) {
            $scores = [];
            foreach ($data['content_score_breakdown'] as $metric) {
                if (isset($metric['score']) && is_numeric($metric['score'])) {
                    $scores[] = (float) $metric['score'];
                }
            }
            if (!empty($scores)) {
                $avg = array_sum($scores) / count($scores);
                $contentScore = (int) round($avg);
            }
        }

        // Keyword field fix: prefer focus_keyword from context, then keyword, then data
        $ctxKeyword = '';
        if (!empty($context['focus_keyword'])) {
            $ctxKeyword = (string) $context['focus_keyword'];
        } elseif (!empty($context['keyword'])) {
            $ctxKeyword = (string) $context['keyword'];
        } elseif (!empty($data['focus_keyword'])) {
            $ctxKeyword = (string) $data['focus_keyword'];
        }
        $ctxKeyword = trim($ctxKeyword);

        $summary[] = [
            'id'                     => (string) ($record['id'] ?? ''),
            'filename'               => basename($file),
            'created_at'             => (string) ($record['created_at'] ?? ''),
            'url'                    => isset($context['url']) ? (string) $context['url'] : '',
            'title'                  => isset($context['title']) ? (string) $context['title'] : '',
            'keyword'                => $ctxKeyword,
            'language'               => isset($context['language']) ? (string) $context['language'] : '',
            'source'                 => isset($context['source']) ? (string) $context['source'] : '',
            'page_id'                => isset($context['page_id']) ? (string) $context['page_id'] : '',
            'article_id'             => isset($context['article_id']) ? (string) $context['article_id'] : '',
            'slug'                   => isset($context['slug']) ? (string) $context['slug'] : '',
            'content_type'           => isset($context['content_type']) ? (string) $context['content_type'] : '',
            'health_score'           => $healthScore,
            'content_score'          => $contentScore,
            'recommended_word_count' => $recommendedWordCount,
            'keyword_count'          => $keywordCount,
        ];
    }

    usort($summary, function ($a, $b) {
        $cmp = strcmp($b['created_at'], $a['created_at']);
        if ($cmp !== 0) {
            return $cmp;
        }
        return strcmp($b['id'], $a['id']);
    });

    return $summary;
}

/**
 * Loads a single SEO report by its ID.
 *
 * @param string $id The report ID (e.g. seo_20251202_143052_a1b2c3d4)
 * @return array|null The full report record or null if not found/invalid
 */
function ai_seo_assistant_load_report(string $id): ?array
{
    $dir = ai_seo_assistant_reports_dir();

    $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $id);
    if ($safeId === '') {
        return null;
    }

    $path = $dir . '/' . $safeId . '.json';

    if (!is_file($path)) {
        return null;
    }

    $contents = @file_get_contents($path);
    if ($contents === false) {
        return null;
    }

    $record = @json_decode($contents, true);
    if (!is_array($record)) {
        return null;
    }

    return $record;
}

/**
 * Generates an article draft based on SEO analysis data.
 *
 * Uses the section outline, keywords, and recommended word count from the analysis
 * to build a detailed prompt for HuggingFace text generation.
 *
 * @param array $analysis The SEO analysis data (from ai_seo_assistant_analyze or loaded report)
 * @param array $context Optional context with keys: language, content_type, title, focus_keyword
 * @return array ['ok' => bool, 'content' => string, 'error' => string]
 */
function ai_seo_assistant_generate_content(array $analysis, array $context = []): array
{
    try {
        $language = trim((string)($context['language'] ?? ''));
        if ($language === '') {
            $language = isset($analysis['language']) ? trim((string)$analysis['language']) : 'en';
        }
        $allowedLanguages = ['en', 'pl', 'de', 'es', 'fr'];
        if (!in_array($language, $allowedLanguages, true)) {
            $language = 'en';
        }

        $contentType = trim((string)($context['content_type'] ?? ''));
        $title = trim((string)($context['title'] ?? ''));
        $focusKeyword = trim((string)($context['focus_keyword'] ?? ''));

        if ($focusKeyword === '' && isset($analysis['focus_keyword'])) {
            $focusKeyword = trim((string)$analysis['focus_keyword']);
        }

        $recommendedWordCount = null;
        if (isset($analysis['recommended_word_count']) && is_numeric($analysis['recommended_word_count'])) {
            $wc = (int)$analysis['recommended_word_count'];
            if ($wc >= 300 && $wc <= 10000) {
                $recommendedWordCount = $wc;
            }
        }

        $sectionOutline = [];
        if (isset($analysis['section_outline']) && is_array($analysis['section_outline'])) {
            $sectionOutline = $analysis['section_outline'];
        }

        $keywordDifficulty = [];
        if (isset($analysis['keyword_difficulty']) && is_array($analysis['keyword_difficulty'])) {
            $keywordDifficulty = $analysis['keyword_difficulty'];
        }

        $languageNames = [
            'en' => 'English',
            'pl' => 'Polish',
            'de' => 'German',
            'es' => 'Spanish',
            'fr' => 'French',
        ];
        $languageName = $languageNames[$language] ?? 'English';

        $prompt = "You are an expert content writer creating SEO-optimized long-form content.\n\n";
        $prompt .= "Write a comprehensive article in {$languageName}.\n\n";

        if ($title !== '') {
            $prompt .= "Article title: {$title}\n";
        }

        if ($focusKeyword !== '') {
            $prompt .= "Focus keyword: {$focusKeyword}\n";
        }

        if ($contentType !== '') {
            $contentTypeLabels = [
                'blog_post' => 'blog post',
                'landing_page' => 'landing page',
                'product' => 'product description',
                'other' => 'article',
            ];
            $typeLabel = $contentTypeLabels[$contentType] ?? 'article';
            $prompt .= "Content type: {$typeLabel}\n";
        }

        if ($recommendedWordCount !== null) {
            $prompt .= "Target length: approximately {$recommendedWordCount} words\n";
        } else {
            $prompt .= "Target length: comprehensive, in-depth article (1500-2500 words)\n";
        }

        $prompt .= "\n";

        if (!empty($sectionOutline)) {
            $prompt .= "## Article Structure (follow this outline):\n\n";
            foreach ($sectionOutline as $idx => $section) {
                if (!is_array($section)) {
                    continue;
                }
                $heading = isset($section['heading']) ? trim((string)$section['heading']) : '';
                $type = isset($section['type']) ? trim((string)$section['type']) : '';
                $purpose = isset($section['purpose']) ? trim((string)$section['purpose']) : '';

                if ($heading === '' && $purpose === '') {
                    continue;
                }

                $sectionNum = $idx + 1;
                $prompt .= "{$sectionNum}. ";
                if ($heading !== '') {
                    $prompt .= "**{$heading}**";
                    if ($type !== '' && $type !== 'other') {
                        $prompt .= " ({$type})";
                    }
                }
                if ($purpose !== '') {
                    $prompt .= " - {$purpose}";
                }
                $prompt .= "\n";
            }
            $prompt .= "\n";
        }

        if (!empty($keywordDifficulty)) {
            $prompt .= "## Keywords to incorporate naturally:\n";
            foreach ($keywordDifficulty as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $keyword = isset($item['keyword']) ? trim((string)$item['keyword']) : '';
                if ($keyword === '') {
                    continue;
                }
                $level = isset($item['level']) ? strtolower(trim((string)$item['level'])) : '';
                $difficulty = isset($item['difficulty']) ? (int)$item['difficulty'] : null;

                $prompt .= "- {$keyword}";
                if ($level !== '' && $difficulty !== null) {
                    $prompt .= " (difficulty: {$difficulty}, {$level})";
                }
                $prompt .= "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "## Writing requirements:\n";
        $prompt .= "- Write in {$languageName} only\n";
        $prompt .= "- Use proper heading hierarchy (H2, H3 as needed)\n";
        $prompt .= "- Include the focus keyword naturally in headings and body text\n";
        $prompt .= "- Write engaging, informative content that provides real value\n";
        $prompt .= "- Use short paragraphs for readability\n";
        $prompt .= "- Include a brief introduction and conclusion\n";
        $prompt .= "- Do NOT include meta descriptions or SEO metadata in the output\n";
        $prompt .= "- Output the article text only, in plain text or light markdown formatting\n\n";
        $prompt .= "Begin writing the article now:\n\n";

        // Load AI configuration
        // Use multi-provider AI via ai_universal_generate()
        $aiSettings = ai_config_load_full();
        $provider = '';
        $model = '';
        if (!empty($aiSettings['providers'])) {
            foreach ($aiSettings['providers'] as $pName => $pConfig) {
                if (!empty($pConfig['enabled']) && !empty($pConfig['api_key'])) {
                    $provider = $pName;
                    $model = $pConfig['default_model'] ?? '';
                    break;
                }
            }
        }
        if (empty($provider)) {
            return [
                'ok' => false,
                'content' => '',
                'error' => 'No AI provider configured.'
            ];
        }

        $systemPrompt = 'You are an expert SEO content writer. Write high-quality, engaging content.';
        $aiResult = ai_universal_generate($provider, $model, $systemPrompt, $prompt, [
            'max_tokens' => 4000,
            'temperature' => 0.7
        ]);

        if (!$aiResult['ok']) {
            return [
                'ok' => false,
                'content' => '',
                'error' => $aiResult['error'] ?? 'AI generation failed.'
            ];
        }

        $content = trim($aiResult['content'] ?? '');

        if ($content === '') {
            return [
                'ok' => false,
                'content' => '',
                'error' => 'AI returned an empty response. Please try again.'
            ];
        }

        return [
            'ok' => true,
            'content' => $content,
            'error' => ''
        ];

    } catch (\Throwable $e) {
        error_log('AI SEO Assistant content generation exception: ' . $e->getMessage());
        return [
            'ok' => false,
            'content' => '',
            'error' => 'An unexpected error occurred while generating the article draft.'
        ];
    }
}
