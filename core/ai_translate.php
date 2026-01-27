<?php
/**
 * AI Translation Module - Core Library
 * Pure function library for translating content and SEO meta tags using AI
 * Supports multiple providers (HuggingFace, OpenAI, Anthropic, etc.)
 * NO classes, NO database access, NO sessions
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';

/**
 * Generate translation for content and SEO meta tags using AI
 *
 * @param array $spec Input specification with keys:
 *   - source_language: e.g. "auto", "en", "pl", "de", "es", "fr"
 *   - target_language: e.g. "en", "pl", "de", "es", "fr" (required)
 *   - content_type: e.g. "page", "blog_post", "generic"
 *   - original_title: original page/post title
 *   - original_body: main content (HTML, Markdown or plain text)
 *   - original_excerpt: short summary/lead
 *   - original_meta_title: existing SEO title
 *   - original_meta_description: existing SEO description
 *   - notes: extra instructions for translator
 * @param string $provider AI provider (default: 'huggingface')
 * @param string $model Model ID (provider-specific)
 * @return array Result array with keys:
 *   - ok (bool): true on success, false on failure
 *   - translation (array): translated content with keys (language, title, body_html, body_text, excerpt, meta_title, meta_description, notes)
 *   - json (string): raw JSON response from model
 *   - prompt (string): prompt sent to model
 *   - error (string): error message on failure
 */
function ai_translate_generate(array $spec, string $provider = 'huggingface', string $model = ''): array
{
    try {
        // 1) Normalize inputs
        $sourceLanguage = isset($spec['source_language']) ? trim((string)$spec['source_language']) : '';
        $targetLanguage = isset($spec['target_language']) ? trim((string)$spec['target_language']) : '';
        $contentType = isset($spec['content_type']) ? trim((string)$spec['content_type']) : '';
        $originalTitle = isset($spec['original_title']) ? trim((string)$spec['original_title']) : '';
        $originalBody = isset($spec['original_body']) ? trim((string)$spec['original_body']) : '';
        $originalExcerpt = isset($spec['original_excerpt']) ? trim((string)$spec['original_excerpt']) : '';
        $originalMetaTitle = isset($spec['original_meta_title']) ? trim((string)$spec['original_meta_title']) : '';
        $originalMetaDescription = isset($spec['original_meta_description']) ? trim((string)$spec['original_meta_description']) : '';
        $notes = isset($spec['notes']) ? trim((string)$spec['notes']) : '';

        // Apply defaults
        if ($sourceLanguage === '') {
            $sourceLanguage = 'auto';
        }
        if ($targetLanguage === '') {
            $targetLanguage = 'en';
        }
        if ($contentType === '') {
            $contentType = 'generic';
        }

        // 2) Basic validation
        if ($targetLanguage === '') {
            return [
                'ok' => false,
                'error' => 'Target language is required.'
            ];
        }

        // Check if there's any content to translate
        if ($originalTitle === '' && $originalBody === '' && $originalMetaTitle === '' && $originalMetaDescription === '') {
            return [
                'ok' => false,
                'error' => 'There is no content to translate. Please provide at least one field.'
            ];
        }

        // 3) Build Hugging Face prompt
        $sourceLanguageDisplay = ($sourceLanguage === 'auto') ? 'auto' : $sourceLanguage;

        $prompt = "You are a professional website translator.\n\n";
        $prompt .= "Translate the following website content from {$sourceLanguageDisplay} to {$targetLanguage}.\n\n";

        if ($sourceLanguage === 'auto') {
            $prompt .= "If SOURCE_LANGUAGE is 'auto', first infer the language, then translate.\n\n";
        }

        $prompt .= "Context:\n";
        $prompt .= "- Content type: {$contentType}\n";
        if ($notes !== '') {
            $prompt .= "- Additional notes from admin: {$notes}\n";
        }
        $prompt .= "\n";

        if ($originalTitle !== '') {
            $prompt .= "Original title:\n{$originalTitle}\n\n";
        }

        if ($originalBody !== '') {
            $prompt .= "Original body (can be HTML, Markdown or plain text):\n{$originalBody}\n\n";
        }

        if ($originalExcerpt !== '') {
            $prompt .= "Original excerpt / lead:\n{$originalExcerpt}\n\n";
        }

        if ($originalMetaTitle !== '') {
            $prompt .= "Original SEO meta title:\n{$originalMetaTitle}\n\n";
        }

        if ($originalMetaDescription !== '') {
            $prompt .= "Original SEO meta description:\n{$originalMetaDescription}\n\n";
        }

        $prompt .= "Requirements:\n";
        $prompt .= "- Respond ONLY with a single JSON object (no markdown, no backticks).\n";
        $prompt .= "- All fields must be in {$targetLanguage}.\n";
        $prompt .= "- Preserve meaning, tone and intent; adapt idioms naturally.\n";
        $prompt .= "- For HTML input, keep basic structure but may simplify classes/attributes.\n";
        $prompt .= "- When a source field is empty, still fill the translated field if it can be inferred from context (e.g. meta_description from body/excerpt).\n\n";

        $prompt .= "JSON structure:\n\n";
        $prompt .= "{\n";
        $prompt .= "  \"language\": \"{$targetLanguage}\",\n";
        $prompt .= "  \"title\": \"...\",\n";
        $prompt .= "  \"body_html\": \"...\",\n";
        $prompt .= "  \"body_text\": \"...\",\n";
        $prompt .= "  \"excerpt\": \"...\",\n";
        $prompt .= "  \"meta_title\": \"...\",\n";
        $prompt .= "  \"meta_description\": \"...\",\n";
        $prompt .= "  \"notes\": \"Optional translator notes about choices, tone and potential issues.\"\n";
        $prompt .= "}\n\n";

        $prompt .= "Details:\n";
        $prompt .= "- body_html: HTML-ready version of the translated content (no <html> or <body> tags).\n";
        $prompt .= "- body_text: plain-text version (no HTML tags).\n";
        $prompt .= "- excerpt: 1–3 short sentences summarising the content.\n";
        $prompt .= "- meta_title: 50–60 characters if possible.\n";
        $prompt .= "- meta_description: 140–160 characters if possible, written to improve CTR.\n";
        $prompt .= "- Do NOT include tracking codes, UTM parameters or email addresses that were not in the original.\n";
        $prompt .= "- Do NOT add promotional claims that are not implied by the original.\n";

        // 4) Call AI provider
        if ($provider === 'huggingface') {
            // Use HuggingFace
            $params = [
                'temperature'    => 0.3,
                'max_new_tokens' => 2200,
            ];
            $hfOptions = ['params' => $params];
            if ($model !== '') {
                $hfOptions['model'] = $model;
            }
            $result = ai_hf_generate_text($prompt, $hfOptions);

            if (!$result['ok']) {
                return [
                    'ok' => false,
                    'error' => $result['error'] ?? 'HuggingFace generation failed'
                ];
            }
            $text = trim($result['text']);
        } else {
            // Use universal provider (OpenAI, Anthropic, Google, DeepSeek, Ollama)
            $result = ai_universal_generate($provider, $model, '', $prompt, [
                'max_tokens' => 2200,
                'temperature' => 0.3,
            ]);

            if (!$result['ok']) {
                return [
                    'ok' => false,
                    'error' => $result['error'] ?? 'AI generation failed'
                ];
            }
            $text = trim($result['content'] ?? $result['text'] ?? '');
        }

        // 5) Clean + decode JSON

        // Remove wrappers similar to other AI modules
        // Strip leading "JSON:" or "Json:" (case-insensitive)
        $text = preg_replace('/^json:\s*/i', '', $text);

        // Strip leading/trailing ```json, ``` or ``` if present
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ai_translate_generate: invalid JSON from HF: ' . json_last_error_msg());
            return [
                'ok' => false,
                'error' => 'The AI did not return valid JSON. Please try again.'
            ];
        }

        // 6) Validate minimal structure
        if (!isset($data['title']) || !is_string($data['title'])) {
            return [
                'ok' => false,
                'error' => 'The translated payload is incomplete. Please refine inputs and try again.'
            ];
        }
        if (!isset($data['body_html']) || !is_string($data['body_html'])) {
            return [
                'ok' => false,
                'error' => 'The translated payload is incomplete. Please refine inputs and try again.'
            ];
        }
        if (!isset($data['meta_title']) || !is_string($data['meta_title'])) {
            return [
                'ok' => false,
                'error' => 'The translated payload is incomplete. Please refine inputs and try again.'
            ];
        }
        if (!isset($data['meta_description']) || !is_string($data['meta_description'])) {
            return [
                'ok' => false,
                'error' => 'The translated payload is incomplete. Please refine inputs and try again.'
            ];
        }

        // 7) Success result
        return [
            'ok' => true,
            'translation' => $data,
            'json' => $text,
            'prompt' => $prompt,
        ];

    } catch (Exception $e) {
        error_log('ai_translate_generate exception: ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'Unexpected error while generating translation. Please try again.'
        ];
    }
}
