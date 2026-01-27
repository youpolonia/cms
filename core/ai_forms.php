<?php
/**
 * AI Forms Generator — Phase 1
 * Generates structured form schemas (fields + actions) via AI text models.
 * Supports multiple AI providers (HuggingFace default, OpenAI, Anthropic, etc.)
 *
 * This module does NOT store schemas in DB or create live forms.
 * It provides:
 *   - ai_forms_generate_schema() — produce JSON schema from natural language spec
 */

require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';

/**
 * Generate a form schema using AI text model.
 *
 * @param array $spec Form specification with keys:
 *   - name          (string, required) Form name
 *   - purpose       (string, required) What the form is for
 *   - audience      (string, optional) Target audience
 *   - form_type     (string, optional) Type: contact|registration|newsletter|lead_magnet|custom (default: contact)
 *   - fields_hint   (string, optional) Free-text description of desired fields
 *   - integrations  (string, optional) Systems/actions (e.g. "save to DB, send email")
 *   - language      (string, optional) Language code (default: en)
 *   - notes         (string, optional) Additional constraints
 * @param string $provider AI provider (default: 'huggingface')
 * @param string $model Model ID (provider-specific)
 *
 * @return array
 *   On success: ['ok' => true, 'schema' => array, 'json' => string, 'prompt' => string]
 *   On failure: ['ok' => false, 'error' => string]
 */
function ai_forms_generate_schema(array $spec, string $provider = 'huggingface', string $model = ''): array
{
    // 1) Normalize input
    $name         = trim((string)($spec['name'] ?? ''));
    $purpose      = trim((string)($spec['purpose'] ?? ''));
    $audience     = trim((string)($spec['audience'] ?? ''));
    $form_type    = trim((string)($spec['form_type'] ?? ''));
    $fields_hint  = trim((string)($spec['fields_hint'] ?? ''));
    $integrations = trim((string)($spec['integrations'] ?? ''));
    $language     = trim((string)($spec['language'] ?? ''));
    $notes        = trim((string)($spec['notes'] ?? ''));

    if ($form_type === '') {
        $form_type = 'contact';
    }
    if ($language === '') {
        $language = 'en';
    }

    // 2) Basic validation
    if ($name === '' || $purpose === '') {
        return ['ok' => false, 'error' => 'Form name and purpose are required.'];
    }

    // 3) Build HF prompt
    $prompt = "You are an expert web form designer.
Design a web form in {$language} for the following specification:

Form name: {$name}
Purpose: {$purpose}
Target audience: {$audience}
Form type: {$form_type}
Desired fields (hint):
{$fields_hint}
Integrations / actions: {$integrations}
Additional notes: {$notes}

Requirements:
- Output a single JSON object describing the form schema.
- Do NOT wrap in markdown or backticks.
- JSON structure:

  {
    \"form_name\": \"...\",
    \"language\": \"...\",
    \"description\": \"...\",
    \"type\": \"...\",
    \"fields\": [
      {
        \"name\": \"email\",
        \"label\": \"Email address\",
        \"type\": \"email\",
        \"required\": true,
        \"placeholder\": \"you@example.com\",
        \"options\": [],
        \"validation\": {
          \"min_length\": 3,
          \"max_length\": 200,
          \"pattern\": \"...\"
        }
      }
    ],
    \"actions\": [
      {
        \"type\": \"store_db\",
        \"enabled\": true
      },
      {
        \"type\": \"send_email\",
        \"enabled\": true
      },
      {
        \"type\": \"webhook_n8n\",
        \"enabled\": false
      }
    ]
  }

- Create 3–15 fields based on the hint and form type.
- Use only simple field types: text, textarea, email, tel, select, checkbox, radio, number, date.
- Name keys in English snake_case (e.g. full_name, phone_number).
- Labels and descriptions MUST be written in {$language}.
- Required fields must have required: true; optional fields required: false.
- Validation constraints must be reasonable and simple.
- Do NOT include any API keys, secrets or tracking codes.";

    // 4) Call AI provider
    try {
        if ($provider === 'huggingface') {
            // Use HuggingFace
            $params = [
                'temperature'    => 0.4,
                'max_new_tokens' => 2000
            ];
            $hfOptions = ['params' => $params];
            if ($model !== '') {
                $hfOptions['model'] = $model;
            }
            $result = ai_hf_generate_text($prompt, $hfOptions);

            if (!$result['ok']) {
                return ['ok' => false, 'error' => $result['error']];
            }
            $text = trim($result['text']);
        } else {
            // Use universal provider (OpenAI, Anthropic, Google, DeepSeek, Ollama)
            $result = ai_universal_generate($provider, $model, '', $prompt, [
                'max_tokens' => 2000,
                'temperature' => 0.4,
            ]);

            if (!$result['ok']) {
                return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
            }
            $text = trim($result['content'] ?? $result['text'] ?? '');
        }
    } catch (Exception $e) {
        error_log('ai_forms_generate_schema: Exception during AI generation: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Unexpected error while generating the form schema. Please try again.'];
    }

    // 5) Clean and validate JSON
    try {

        // Remove common wrappers
        // Strip leading/trailing ```json / ``` or ```
        if (preg_match('/^```(?:json)?\s*\n?(.*?)\n?```$/s', $text, $matches)) {
            $text = trim($matches[1]);
        }

        // Remove leading "JSON:" or "Json:" prefixes
        $text = preg_replace('/^(?:JSON|Json):\s*/i', '', $text);

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['ok' => false, 'error' => 'The AI did not return valid JSON. Please try again.'];
        }

        // Validate minimal structure
        if (!is_array($data) || !isset($data['fields']) || !is_array($data['fields']) || empty($data['fields'])) {
            return ['ok' => false, 'error' => 'The generated form schema is incomplete. Please refine your description and try again.'];
        }

        // Validate each field has required keys
        foreach ($data['fields'] as $field) {
            if (!is_array($field)) {
                return ['ok' => false, 'error' => 'The generated form schema is incomplete. Please refine your description and try again.'];
            }
            $fieldName  = trim((string)($field['name'] ?? ''));
            $fieldLabel = trim((string)($field['label'] ?? ''));
            $fieldType  = trim((string)($field['type'] ?? ''));

            if ($fieldName === '' || $fieldLabel === '' || $fieldType === '') {
                return ['ok' => false, 'error' => 'The generated form schema is incomplete. Please refine your description and try again.'];
            }
        }

        // 6) On success
        return [
            'ok'     => true,
            'schema' => $data,
            'json'   => $text,
            'prompt' => $prompt
        ];
    } catch (Exception $e) {
        error_log('ai_forms_generate_schema: Exception during JSON processing: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Unexpected error while generating the form schema. Please try again.'];
    }
}
