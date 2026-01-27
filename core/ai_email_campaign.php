<?php
/**
 * AI Email Campaign Generator
 * Pure function library for generating email campaigns/drip sequences
 * Supports multiple AI providers (HuggingFace default, OpenAI, Anthropic, etc.)
 * NO classes, NO database access, NO sessions
 */

if (!defined('CMS_ROOT')) {
    http_response_code(403);
    exit('Direct access not permitted');
}

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';

/**
 * Generate email campaign sequence from specification using AI text model
 *
 * @param array $spec Specification array with keys:
 *   - name: string - Campaign name (required)
 *   - audience: string - Target audience description
 *   - offer: string - What we're promoting
 *   - goal: string - Main objective (required)
 *   - emails_count: string|int - Number of emails (1-10)
 *   - tone: string - Tone of voice (e.g. "friendly, professional")
 *   - language: string - Language code/label (e.g. "en", "pl")
 *   - notes: string - Additional constraints
 * @param string $provider AI provider (default: 'huggingface')
 * @param string $model Model ID (provider-specific)
 * @return array Result with keys: ok (bool), campaign (array on success), json (string on success), prompt (string on success), error (string on failure)
 */
function ai_email_campaign_generate(array $spec, string $provider = 'huggingface', string $model = ''): array
{
    // Normalize all string fields
    $name = isset($spec['name']) ? trim((string)$spec['name']) : '';
    $audience = isset($spec['audience']) ? trim((string)$spec['audience']) : '';
    $offer = isset($spec['offer']) ? trim((string)$spec['offer']) : '';
    $goal = isset($spec['goal']) ? trim((string)$spec['goal']) : '';
    $tone = isset($spec['tone']) ? trim((string)$spec['tone']) : 'friendly, professional';
    $language = isset($spec['language']) ? trim((string)$spec['language']) : 'en';
    $notes = isset($spec['notes']) ? trim((string)$spec['notes']) : '';

    // Normalize and clamp emails_count to 1-10
    $emailsCount = isset($spec['emails_count']) ? (int)$spec['emails_count'] : 5;
    if ($emailsCount < 1) {
        $emailsCount = 1;
    } elseif ($emailsCount > 10) {
        $emailsCount = 10;
    }

    // Default language to "en" if empty
    if ($language === '') {
        $language = 'en';
    }

    // Basic validation
    if ($name === '' || $goal === '') {
        return [
            'ok' => false,
            'error' => 'Campaign name and goal are required.'
        ];
    }

    // Build prompt for HuggingFace model
    $prompt = "You are an expert email marketer.\n";
    $prompt .= "Design an email campaign in $language for the following specification:\n\n";
    $prompt .= "Campaign name: $name\n";

    if ($audience !== '') {
        $prompt .= "Target audience: $audience\n";
    }

    if ($offer !== '') {
        $prompt .= "Offer: $offer\n";
    }

    $prompt .= "Main goal: $goal\n";
    $prompt .= "Tone of voice: $tone\n";
    $prompt .= "Number of emails: $emailsCount\n";

    if ($notes !== '') {
        $prompt .= "Additional notes: $notes\n";
    }

    $prompt .= "\nRequirements:\n";
    $prompt .= "- Output a single JSON object describing the campaign.\n";
    $prompt .= "- Do NOT wrap in markdown or backticks.\n";
    $prompt .= "- JSON structure:\n\n";
    $prompt .= "{\n";
    $prompt .= "  \"campaign_name\": \"...\",\n";
    $prompt .= "  \"language\": \"...\",\n";
    $prompt .= "  \"goal\": \"...\",\n";
    $prompt .= "  \"emails\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"index\": 1,\n";
    $prompt .= "      \"subject\": \"...\",\n";
    $prompt .= "      \"preview_text\": \"...\",\n";
    $prompt .= "      \"html_body\": \"...\",\n";
    $prompt .= "      \"text_body\": \"...\"\n";
    $prompt .= "    }\n";
    $prompt .= "  ]\n";
    $prompt .= "}\n\n";
    $prompt .= "- Provide exactly $emailsCount emails, indexed from 1.\n";
    $prompt .= "- Keep subjects concise and compelling.\n";
    $prompt .= "- html_body must be valid HTML fragments (no <html>/<body> tags).\n";
    $prompt .= "- text_body should be a plain-text version of the same email.\n";
    $prompt .= "- All text (subjects, preview_text, bodies) must be written in $language.\n";
    $prompt .= "- Do NOT include API keys, secrets, or tracking codes.";

    // Call AI provider
    try {
        if ($provider === 'huggingface') {
            // Use HuggingFace
            $params = [
                'temperature' => 0.4,
                'max_new_tokens' => 2500
            ];
            $hfOptions = ['params' => $params];
            if ($model !== '') {
                $hfOptions['model'] = $model;
            }
            $result = ai_hf_generate_text($prompt, $hfOptions);

            if (!$result['ok']) {
                error_log('ai_email_campaign_generate: HF generation failed: ' . ($result['error'] ?? 'Unknown'));
                return [
                    'ok' => false,
                    'error' => $result['error'] ?? 'Failed to generate email campaign.'
                ];
            }
            $text = trim($result['text']);
        } else {
            // Use universal provider (OpenAI, Anthropic, Google, DeepSeek, Ollama)
            $result = ai_universal_generate($provider, $model, '', $prompt, [
                'max_tokens' => 2500,
                'temperature' => 0.4,
            ]);

            if (!$result['ok']) {
                error_log('ai_email_campaign_generate: Universal generation failed: ' . ($result['error'] ?? 'Unknown'));
                return [
                    'ok' => false,
                    'error' => $result['error'] ?? 'Failed to generate email campaign.'
                ];
            }
            $text = trim($result['content'] ?? $result['text'] ?? '');
        }

        // Clean and validate JSON response

        // Remove markdown code blocks (similar to ai_n8n_clean_json_response)
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // Remove leading "JSON:" or "json:" prefix if present
        $text = preg_replace('/^JSON:\s*/i', '', $text);
        $text = trim($text);

        // Attempt to decode JSON
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ai_email_campaign_generate: JSON decode failed: ' . json_last_error_msg());
            return [
                'ok' => false,
                'error' => 'The AI did not return valid JSON. Please try again.'
            ];
        }

        // Validate minimal structure
        if (!isset($data['emails']) || !is_array($data['emails']) || count($data['emails']) === 0) {
            error_log('ai_email_campaign_generate: Invalid campaign structure - missing emails array');
            return [
                'ok' => false,
                'error' => 'The AI response is missing the emails array. Please try again.'
            ];
        }

        // Validate each email has required fields
        foreach ($data['emails'] as $idx => $email) {
            if (!isset($email['subject']) || !isset($email['html_body'])) {
                error_log('ai_email_campaign_generate: Email #' . $idx . ' missing required fields');
                return [
                    'ok' => false,
                    'error' => 'One or more emails are missing required fields (subject, html_body). Please try again.'
                ];
            }
        }

        // Success - return campaign data
        return [
            'ok' => true,
            'campaign' => $data,
            'json' => $text,
            'prompt' => $prompt
        ];

    } catch (Exception $e) {
        error_log('ai_email_campaign_generate: Exception: ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'An error occurred while generating the campaign. Please try again.'
        ];
    }
}
