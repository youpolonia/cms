<?php
/**
 * AI Component Generator Library
 * Pure function library for generating reusable HTML/CSS components
 * Supports multiple AI providers (HuggingFace default, OpenAI, Anthropic, etc.)
 * NO classes, NO database access, NO sessions
 */

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';

/**
 * Generate a reusable UI component using AI text generation
 *
 * @param array $spec Component specification with keys:
 *   - name: Component name (required)
 *   - purpose: Main purpose (required)
 *   - target_page: Where it will be used
 *   - layout_context: Layout information
 *   - style: Visual style
 *   - brand_voice: Tone and voice
 *   - language: Language code (default: 'en')
 *   - cta: Call to action text
 *   - notes: Additional constraints
 * @param string $provider AI provider (default: 'huggingface')
 * @param string $model Model ID (provider-specific)
 * @return array Result with keys: ok (bool), component (array on success), json (string), prompt (string), error (string on failure)
 */
function ai_components_generate(array $spec, string $provider = 'huggingface', string $model = ''): array
{
    try {
        // 1) Normalize inputs
        $name = trim((string)($spec['name'] ?? ''));
        $purpose = trim((string)($spec['purpose'] ?? ''));
        $targetPage = trim((string)($spec['target_page'] ?? ''));
        $layoutContext = trim((string)($spec['layout_context'] ?? ''));
        $style = trim((string)($spec['style'] ?? ''));
        $brandVoice = trim((string)($spec['brand_voice'] ?? ''));
        $language = trim((string)($spec['language'] ?? ''));
        $cta = trim((string)($spec['cta'] ?? ''));
        $notes = trim((string)($spec['notes'] ?? ''));

        // Default language to 'en'
        if ($language === '') {
            $language = 'en';
        }

        // 2) Basic validation
        if ($name === '' || $purpose === '') {
            return [
                'ok' => false,
                'error' => 'Component name and purpose are required.'
            ];
        }

        // 3) Build Hugging Face prompt
        $prompt = "You are an expert UI component designer and frontend developer.

Design a single reusable website component based on the following specification.

Component name: {$name}
Purpose: {$purpose}
Target page: {$targetPage}
Layout context: {$layoutContext}
Visual style: {$style}
Brand voice: {$brandVoice}
Main call to action: {$cta}
Additional notes: {$notes}

Language for all visible text: {$language} (use this language for all headings, labels and CTA text).

Requirements:
- The component must be built with semantic HTML5 and simple CSS only (no frameworks, no Tailwind, no Bootstrap, no JavaScript).
- It must be responsive and look good on mobile, tablet and desktop.
- It should be accessible:
  - Use proper headings, ARIA attributes only if necessary.
  - Ensure good color contrast and readable text.
- Assume it will be pasted into an existing page inside a <main> element.
- Do NOT include <html>, <head> or <body> tags.
- Do NOT include <style> tags in the HTML; put all CSS in a separate string.

Return ONLY a single JSON object (no markdown, no backticks) with this exact structure:

{
  \"name\": \"Short internal component name in {$language}.\",
  \"description\": \"1-3 sentences describing what this component does and where to use it.\",
  \"html\": \"<section>...</section>\",
  \"css\": \"/* CSS for this component only */\",
  \"preview_text\": \"One-line explanation suitable for UI listing.\",
  \"accessibility_notes\": \"Any important a11y considerations (e.g. contrast, ARIA usage).\",
  \"usage_notes\": \"Tips for using this component inside a CMS page builder.\"
}

Details:
- html: self-contained section or div structure for the component (no external dependencies).
- css: minimal, scoped styles that can be pasted into a theme stylesheet; use class selectors prefixed with a neutral prefix like .ai-component-... to avoid conflicts.
- Do NOT invent JavaScript behavior; the component must work with HTML+CSS only.";

        // 4) Call AI provider
        if ($provider === 'huggingface') {
            // Use HuggingFace
            $params = [
                'temperature' => 0.4,
                'max_new_tokens' => 2000,
            ];
            $hfOptions = ['params' => $params];
            if ($model !== '') {
                $hfOptions['model'] = $model;
            }
            $result = ai_hf_generate_text($prompt, $hfOptions);

            if (!$result['ok']) {
                return [
                    'ok' => false,
                    'error' => $result['error']
                ];
            }
            $text = trim($result['text']);
        } else {
            // Use universal provider (OpenAI, Anthropic, Google, DeepSeek, Ollama)
            $result = ai_universal_generate($provider, $model, '', $prompt, [
                'max_tokens' => 2000,
                'temperature' => 0.4,
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

        // Remove wrappers: strip leading "JSON:" or "Json:" (case-insensitive)
        $text = preg_replace('/^json:\s*/i', '', $text);

        // Strip leading/trailing ```json, ``` or ```
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ai_components_generate: invalid JSON from HF: ' . json_last_error_msg());
            return [
                'ok' => false,
                'error' => 'The AI did not return valid JSON. Please try again.'
            ];
        }

        // 6) Validate minimal structure
        $requiredKeys = ['name', 'description', 'html', 'css'];
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key]) || !is_string($data[$key]) || trim($data[$key]) === '') {
                return [
                    'ok' => false,
                    'error' => 'The generated component is incomplete. Please refine inputs and try again.'
                ];
            }
        }

        // 7) Success result
        return [
            'ok' => true,
            'component' => $data,
            'json' => $text,
            'prompt' => $prompt,
        ];
    } catch (Exception $e) {
        error_log('ai_components_generate exception: ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'Unexpected error while generating component. Please try again.'
        ];
    }
}
