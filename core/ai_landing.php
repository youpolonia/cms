<?php
/**
 * AI Landing Page Generator - Core Library
 * Generates structured landing page specifications using HuggingFace
 * NO classes, NO database access, NO sessions
 */

// Guard against multiple includes
if (defined('AI_LANDING_LOADED')) {
    return;
}
define('AI_LANDING_LOADED', true);

// Detect CMS_ROOT if needed
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

// Load required dependencies
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_landing_import.php';

/**
 * Generate landing page specification using HuggingFace AI
 *
 * @param array $params Input parameters:
 *   - 'goal': string - Landing page goal (e.g., "lead generation for CMS")
 *   - 'audience': string - Target audience
 *   - 'offer': string - Main offer/product
 *   - 'language': string - Language/locale (e.g., "pl" or "en")
 *   - 'primary_keyword': string - Optional primary keyword
 *   - 'tone': string - Optional tone (e.g., "professional", "friendly")
 * @return array Result with keys:
 *   - 'meta': array - Meta information (title, slug, meta_title, meta_description)
 *   - 'hero': array - Hero section (headline, subheadline, hero_image_prompt)
 *   - 'sections': array - Content sections (type, heading, body, cta_label, cta_url_placeholder)
 *   - 'faq': array - FAQ entries (question, answer)
 */
function ai_landing_generate_spec(array $params): array
{
    // Normalize and validate input parameters
    $goal = isset($params['goal']) ? trim((string)$params['goal']) : '';
    $audience = isset($params['audience']) ? trim((string)$params['audience']) : '';
    $offer = isset($params['offer']) ? trim((string)$params['offer']) : '';
    $language = isset($params['language']) ? trim((string)$params['language']) : 'en';
    $primaryKeyword = isset($params['primary_keyword']) ? trim((string)$params['primary_keyword']) : '';
    $tone = isset($params['tone']) ? trim((string)$params['tone']) : 'professional';

    // Build comprehensive prompt for HuggingFace
    $prompt = ai_landing_build_prompt($goal, $audience, $offer, $language, $primaryKeyword, $tone);

    // Attempt HuggingFace generation
    $config = ai_hf_config_load();
    $aiEnabled = ai_hf_is_configured($config);

    $rawResponse = null;
    if ($aiEnabled) {
        try {
            $result = ai_hf_generate_text($prompt, [
                'params' => [
                    'max_new_tokens' => 2000,
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                ]
            ]);

            if ($result['ok'] === true && !empty($result['text'])) {
                $rawResponse = $result['text'];
            } else {
                error_log('[AI_LANDING] HuggingFace generation failed: ' . ($result['error'] ?? 'unknown'));
            }
        } catch (\Throwable $e) {
            error_log('[AI_LANDING] Exception during HF call: ' . $e->getMessage());
        }
    }

    // Parse response or build fallback
    if ($rawResponse !== null) {
        $spec = ai_landing_parse_response($rawResponse, $goal, $offer, $language);
    } else {
        $spec = ai_landing_fallback_spec($goal, $audience, $offer, $language, $primaryKeyword);
    }

    return $spec;
}

/**
 * Build HuggingFace prompt for landing page generation
 *
 * @param string $goal Landing page goal
 * @param string $audience Target audience
 * @param string $offer Main offer/product
 * @param string $language Language code
 * @param string $primaryKeyword Primary keyword
 * @param string $tone Tone of voice
 * @return string Formatted prompt
 */
function ai_landing_build_prompt(string $goal, string $audience, string $offer, string $language, string $primaryKeyword, string $tone): string
{
    $prompt = "You are a professional landing page copywriter. Generate a complete landing page specification in STRICT JSON format. Do NOT include any natural language explanation, markdown code blocks, or comments. Return ONLY valid JSON.\n\n";
    $prompt .= "Requirements:\n";
    if ($goal !== '') {
        $prompt .= "- Goal: {$goal}\n";
    }
    if ($audience !== '') {
        $prompt .= "- Target Audience: {$audience}\n";
    }
    if ($offer !== '') {
        $prompt .= "- Main Offer/Product: {$offer}\n";
    }
    $prompt .= "- Language: {$language}\n";
    if ($primaryKeyword !== '') {
        $prompt .= "- Primary Keyword: {$primaryKeyword}\n";
    }
    $prompt .= "- Tone: {$tone}\n\n";

    $prompt .= "JSON Structure (return EXACTLY this format):\n";
    $prompt .= "{\n";
    $prompt .= "  \"meta\": {\n";
    $prompt .= "    \"title\": \"Page title\",\n";
    $prompt .= "    \"slug\": \"kebab-case-slug\",\n";
    $prompt .= "    \"meta_title\": \"SEO title (max 60 chars)\",\n";
    $prompt .= "    \"meta_description\": \"SEO description (max 160 chars)\"\n";
    $prompt .= "  },\n";
    $prompt .= "  \"hero\": {\n";
    $prompt .= "    \"headline\": \"Compelling headline\",\n";
    $prompt .= "    \"subheadline\": \"Supporting subheadline\",\n";
    $prompt .= "    \"hero_image_prompt\": \"Image description prompt\"\n";
    $prompt .= "  },\n";
    $prompt .= "  \"sections\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"type\": \"text|features|testimonials|cta\",\n";
    $prompt .= "      \"heading\": \"Section heading\",\n";
    $prompt .= "      \"body\": \"Section content (HTML-safe, no scripts)\",\n";
    $prompt .= "      \"cta_label\": \"Button label or null\",\n";
    $prompt .= "      \"cta_url_placeholder\": \"URL placeholder or null\"\n";
    $prompt .= "    }\n";
    $prompt .= "  ],\n";
    $prompt .= "  \"faq\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"question\": \"Question text\",\n";
    $prompt .= "      \"answer\": \"Answer text\"\n";
    $prompt .= "    }\n";
    $prompt .= "  ]\n";
    $prompt .= "}\n\n";
    $prompt .= "Generate 3-7 sections covering: introduction, features/benefits, social proof, and call-to-action.\n";
    $prompt .= "Generate 0-5 FAQ entries.\n";
    $prompt .= "Return ONLY the JSON object, no other text.\n";

    return $prompt;
}

/**
 * Parse HuggingFace response into structured spec
 *
 * @param string $rawResponse Raw text response from HF
 * @param string $goal Landing page goal (for fallback)
 * @param string $offer Main offer (for fallback)
 * @param string $language Language code (for fallback)
 * @return array Structured spec
 */
function ai_landing_parse_response(string $rawResponse, string $goal, string $offer, string $language): array
{
    // Try to extract JSON from response
    // Some models wrap JSON in markdown code blocks
    $cleaned = trim($rawResponse);

    // Remove markdown code blocks if present
    $cleaned = preg_replace('/^```json\s*/i', '', $cleaned);
    $cleaned = preg_replace('/\s*```$/i', '', $cleaned);
    $cleaned = trim($cleaned);

    // Try to decode
    $data = @json_decode($cleaned, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        error_log('[AI_LANDING] Failed to parse JSON response: ' . json_last_error_msg());
        return ai_landing_fallback_spec($goal, '', $offer, $language, '');
    }

    // Validate and normalize structure
    $spec = [];

    // Meta section
    $spec['meta'] = ai_landing_normalize_meta($data['meta'] ?? [], $goal, $offer);

    // Hero section
    $spec['hero'] = ai_landing_normalize_hero($data['hero'] ?? [], $goal, $offer);

    // Sections
    $spec['sections'] = ai_landing_normalize_sections($data['sections'] ?? []);

    // FAQ
    $spec['faq'] = ai_landing_normalize_faq($data['faq'] ?? []);

    return $spec;
}

/**
 * Normalize meta section
 *
 * @param array $meta Raw meta data
 * @param string $goal Fallback goal
 * @param string $offer Fallback offer
 * @return array Normalized meta
 */
function ai_landing_normalize_meta(array $meta, string $goal, string $offer): array
{
    $title = isset($meta['title']) ? trim((string)$meta['title']) : '';
    if ($title === '') {
        $title = $offer !== '' ? $offer : ($goal !== '' ? $goal : 'Landing Page');
    }

    $slug = isset($meta['slug']) ? trim((string)$meta['slug']) : '';
    if ($slug === '') {
        $slug = ai_landing_sanitize_slug($title);
    } else {
        $slug = ai_landing_sanitize_slug($slug);
    }

    $metaTitle = isset($meta['meta_title']) ? trim((string)$meta['meta_title']) : '';
    if ($metaTitle === '') {
        $metaTitle = mb_substr($title, 0, 60);
    }

    $metaDescription = isset($meta['meta_description']) ? trim((string)$meta['meta_description']) : '';
    if ($metaDescription === '') {
        $metaDescription = mb_substr($title . ' - ' . $goal, 0, 160);
    }

    return [
        'title' => $title,
        'slug' => $slug,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
    ];
}

/**
 * Normalize hero section
 *
 * @param array $hero Raw hero data
 * @param string $goal Fallback goal
 * @param string $offer Fallback offer
 * @return array Normalized hero
 */
function ai_landing_normalize_hero(array $hero, string $goal, string $offer): array
{
    $headline = isset($hero['headline']) ? trim((string)$hero['headline']) : '';
    if ($headline === '') {
        $headline = $offer !== '' ? $offer : 'Welcome';
    }

    $subheadline = isset($hero['subheadline']) ? trim((string)$hero['subheadline']) : '';
    if ($subheadline === '') {
        $subheadline = $goal !== '' ? $goal : 'Discover our solution';
    }

    $heroImagePrompt = isset($hero['hero_image_prompt']) ? trim((string)$hero['hero_image_prompt']) : '';
    if ($heroImagePrompt === '') {
        $heroImagePrompt = 'Professional hero image for ' . $headline;
    }

    return [
        'headline' => $headline,
        'subheadline' => $subheadline,
        'hero_image_prompt' => $heroImagePrompt,
    ];
}

/**
 * Normalize sections array
 *
 * @param array $sections Raw sections data
 * @return array Normalized sections
 */
function ai_landing_normalize_sections(array $sections): array
{
    if (!is_array($sections)) {
        return [];
    }

    $normalized = [];
    foreach ($sections as $section) {
        if (!is_array($section)) {
            continue;
        }

        $type = isset($section['type']) ? trim((string)$section['type']) : 'text';
        $validTypes = ['text', 'features', 'testimonials', 'cta', 'faq'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'text';
        }

        $heading = isset($section['heading']) ? trim((string)$section['heading']) : '';
        $body = isset($section['body']) ? trim((string)$section['body']) : '';
        $ctaLabel = isset($section['cta_label']) ? trim((string)$section['cta_label']) : null;
        $ctaUrlPlaceholder = isset($section['cta_url_placeholder']) ? trim((string)$section['cta_url_placeholder']) : null;

        // Skip empty sections
        if ($heading === '' && $body === '') {
            continue;
        }

        $normalized[] = [
            'type' => $type,
            'heading' => $heading,
            'body' => $body,
            'cta_label' => $ctaLabel !== '' ? $ctaLabel : null,
            'cta_url_placeholder' => $ctaUrlPlaceholder !== '' ? $ctaUrlPlaceholder : null,
        ];
    }

    return $normalized;
}

/**
 * Normalize FAQ array
 *
 * @param array $faq Raw FAQ data
 * @return array Normalized FAQ
 */
function ai_landing_normalize_faq(array $faq): array
{
    if (!is_array($faq)) {
        return [];
    }

    $normalized = [];
    foreach ($faq as $item) {
        if (!is_array($item)) {
            continue;
        }

        $question = isset($item['question']) ? trim((string)$item['question']) : '';
        $answer = isset($item['answer']) ? trim((string)$item['answer']) : '';

        // Skip empty items
        if ($question === '' || $answer === '') {
            continue;
        }

        $normalized[] = [
            'question' => $question,
            'answer' => $answer,
        ];
    }

    return $normalized;
}

/**
 * Generate fallback spec when AI is unavailable
 *
 * @param string $goal Landing page goal
 * @param string $audience Target audience
 * @param string $offer Main offer/product
 * @param string $language Language code
 * @param string $primaryKeyword Primary keyword
 * @return array Fallback spec
 */
function ai_landing_fallback_spec(string $goal, string $audience, string $offer, string $language, string $primaryKeyword): array
{
    $title = $offer !== '' ? $offer : ($goal !== '' ? $goal : 'Landing Page');
    $slug = ai_landing_sanitize_slug($title);

    return [
        'meta' => [
            'title' => $title,
            'slug' => $slug,
            'meta_title' => mb_substr($title, 0, 60),
            'meta_description' => mb_substr($goal . ' - ' . $offer, 0, 160),
        ],
        'hero' => [
            'headline' => $offer !== '' ? $offer : 'Welcome',
            'subheadline' => $goal !== '' ? $goal : 'Discover our solution',
            'hero_image_prompt' => 'Professional hero image',
        ],
        'sections' => [
            [
                'type' => 'text',
                'heading' => 'About',
                'body' => $goal !== '' ? $goal : 'Learn more about our offering.',
                'cta_label' => null,
                'cta_url_placeholder' => null,
            ],
            [
                'type' => 'features',
                'heading' => 'Features',
                'body' => 'Key features and benefits will be listed here.',
                'cta_label' => null,
                'cta_url_placeholder' => null,
            ],
            [
                'type' => 'cta',
                'heading' => 'Get Started',
                'body' => 'Ready to begin? Take action now.',
                'cta_label' => 'Learn More',
                'cta_url_placeholder' => '#contact',
            ],
        ],
        'faq' => [],
    ];
}

/**
 * Sanitize string to kebab-case slug
 *
 * @param string $str Input string
 * @return string Kebab-case slug
 */
function ai_landing_sanitize_slug(string $str): string
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/[^a-z0-9\s\-]+/', '', $str);
    $str = preg_replace('/[\s\-]+/', '-', $str);
    $str = trim($str, '-');

    if ($str === '') {
        $str = 'landing-' . date('YmdHis');
    }

    return $str;
}

/**
 * Save landing page spec as JSON draft
 *
 * @param array $spec Landing page specification
 * @param string $storageDir Directory path for storage
 * @return string|null Relative path to saved file, or null on failure
 */
function ai_landing_save_spec(array $spec, string $storageDir): ?string
{
    try {
        // Ensure storage directory exists
        if (!is_dir($storageDir)) {
            if (!@mkdir($storageDir, 0775, true)) {
                error_log('[AI_LANDING] Failed to create storage directory: ' . $storageDir);
                return null;
            }
        }

        // Build filename
        $date = date('Y-m-d');
        $slug = isset($spec['meta']['slug']) ? $spec['meta']['slug'] : 'landing';
        $slug = ai_landing_sanitize_slug($slug);

        // Add unique suffix to avoid collisions
        $baseName = $date . '-' . $slug;
        $fileName = $baseName . '.json';
        $counter = 1;

        while (file_exists($storageDir . '/' . $fileName)) {
            $fileName = $baseName . '-' . $counter . '.json';
            $counter++;
        }

        $filePath = $storageDir . '/' . $fileName;

        // Encode JSON with pretty print
        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            error_log('[AI_LANDING] Failed to encode spec to JSON');
            return null;
        }

        // Write to file
        if (@file_put_contents($filePath, $json . "\n", LOCK_EX) === false) {
            error_log('[AI_LANDING] Failed to write spec file: ' . $filePath);
            return null;
        }

        // Return relative path from CMS_ROOT
        $relativePath = str_replace(CMS_ROOT . '/', '', $filePath);
        return $relativePath;
    } catch (\Throwable $e) {
        error_log('[AI_LANDING] Exception in save_spec: ' . $e->getMessage());
        return null;
    }
}

/**
 * Generate landing page spec and import as CMS page (convenience wrapper)
 *
 * @param array $params Input parameters (same as ai_landing_generate_spec)
 * @return array Result with keys:
 *   - 'spec': array - Generated landing page specification
 *   - 'import': array - Import result from ai_landing_import_create_page()
 */
function ai_landing_generate_and_import(array $params): array
{
    $spec = ai_landing_generate_spec($params);
    $importResult = ai_landing_import_create_page($spec);

    return [
        'spec' => $spec,
        'import' => $importResult,
    ];
}

/**
 * Generate landing page HTML using AI
 *
 * @param array $form Form input with keys:
 *   - 'project': string - Project/product name
 *   - 'audience': string - Target audience
 *   - 'goal': string - Main goal
 *   - 'tone': string - Tone of voice
 *   - 'features': string - Key features/benefits
 *   - 'language': string - Language code (default: en)
 *   - 'length': string - short|medium|long (default: medium)
 * @param string $provider AI provider (default: 'huggingface')
 * @param string $model Model ID (provider-specific)
 *
 * @return array Result with keys:
 *   - 'ok': bool - Success status
 *   - 'html': string - Generated HTML on success
 *   - 'error': string - Error message on failure
 */
function ai_landing_generate(array $form, string $provider = 'huggingface', string $model = ''): array
{
    // Normalize input
    $project  = trim((string)($form['project'] ?? ''));
    $audience = trim((string)($form['audience'] ?? ''));
    $goal     = trim((string)($form['goal'] ?? ''));
    $tone     = trim((string)($form['tone'] ?? ''));
    $features = trim((string)($form['features'] ?? ''));
    $language = trim((string)($form['language'] ?? ''));
    $length   = trim((string)($form['length'] ?? ''));

    if ($language === '') {
        $language = 'en';
    }
    if ($length === '') {
        $length = 'medium';
    }

    // Basic validation
    if ($project === '') {
        return ['ok' => false, 'error' => 'Project/product name is required.'];
    }

    // Determine section count based on length
    $sectionCount = match($length) {
        'short' => '3-4',
        'long' => '7-10',
        default => '5-6',
    };

    // Build the prompt
    $prompt = "You are an expert landing page designer. Generate a complete, modern HTML landing page.\n\n";
    $prompt .= "Project/Product: {$project}\n";
    if ($audience !== '') {
        $prompt .= "Target Audience: {$audience}\n";
    }
    if ($goal !== '') {
        $prompt .= "Main Goal: {$goal}\n";
    }
    if ($tone !== '') {
        $prompt .= "Tone: {$tone}\n";
    }
    if ($features !== '') {
        $prompt .= "Key Features/Benefits:\n{$features}\n";
    }
    $prompt .= "Language: {$language}\n";
    $prompt .= "Length: {$length} ({$sectionCount} sections)\n\n";

    $prompt .= "Requirements:\n";
    $prompt .= "- Output ONLY valid HTML (no markdown, no code blocks, no explanations)\n";
    $prompt .= "- Include inline CSS styles in a <style> tag\n";
    $prompt .= "- Use modern, clean design with good typography\n";
    $prompt .= "- Include: hero section, features, benefits, call-to-action\n";
    $prompt .= "- Make it responsive (mobile-friendly)\n";
    $prompt .= "- Use professional color scheme\n";
    $prompt .= "- All text must be in {$language}\n";
    $prompt .= "- Do NOT include <html>, <head>, or <body> tags - just the content\n";
    $prompt .= "- Start directly with <style> or <div>\n";

    // Determine max tokens based on length
    $maxTokens = match($length) {
        'short' => 1500,
        'long' => 3000,
        default => 2000,
    };

    try {
        if ($provider === 'huggingface') {
            // Use HuggingFace
            $params = [
                'temperature'    => 0.7,
                'max_new_tokens' => $maxTokens,
            ];
            $hfOptions = ['params' => $params];
            if ($model !== '') {
                $hfOptions['model'] = $model;
            }
            $result = ai_hf_generate_text($prompt, $hfOptions);

            if (!$result['ok']) {
                return ['ok' => false, 'error' => $result['error'] ?? 'HuggingFace generation failed'];
            }
            $html = trim($result['text']);
        } else {
            // Use universal provider (OpenAI, Anthropic, Google, DeepSeek, Ollama)
            $result = ai_universal_generate($provider, $model, '', $prompt, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

            if (!$result['ok']) {
                return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
            }
            $html = trim($result['content'] ?? $result['text'] ?? '');
        }

        // Clean up common issues
        // Remove markdown code blocks if present
        $html = preg_replace('/^```html?\s*/i', '', $html);
        $html = preg_replace('/\s*```$/i', '', $html);
        $html = trim($html);

        // Basic validation - should contain some HTML
        if ($html === '' || (strpos($html, '<') === false && strpos($html, '>') === false)) {
            return ['ok' => false, 'error' => 'AI did not return valid HTML. Please try again.'];
        }

        return ['ok' => true, 'html' => $html];

    } catch (\Throwable $e) {
        error_log('[AI_LANDING] Exception in ai_landing_generate: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Unexpected error during generation. Please try again.'];
    }
}
