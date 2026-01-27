<?php
/**
 * AI-Powered n8n Workflow Generator
 * Generates n8n workflow JSON blueprints using OpenAI
 * Pure function library - NO classes, NO database, NO sessions
 */

require_once __DIR__ . '/ai_content.php';

/**
 * Generate n8n workflow JSON from natural language description using AI
 * Uses OpenAI GPT-4o-mini for reliable JSON generation
 *
 * @param array $spec Workflow specification with keys:
 *   - description: Natural language description of automation (required)
 *   - name: Workflow name (optional, AI will generate if not provided)
 *   - trigger_type: Preferred trigger type (optional: webhook, schedule, manual)
 *   - integrations: Comma-separated services (optional: Gmail, Slack, etc.)
 *   - language: Language for comments/labels (default: "en")
 *
 * @return array Result array with keys:
 *   - ok: bool (true on success, false on failure)
 *   - json: string (workflow JSON on success)
 *   - name: string (workflow name)
 *   - error: string (error message on failure)
 */
function ai_n8n_generate_workflow(array $spec): array
{
    // Normalize input
    $description = isset($spec['description']) ? trim((string)$spec['description']) : '';
    $name = isset($spec['name']) ? trim((string)$spec['name']) : '';
    $triggerType = isset($spec['trigger_type']) ? trim((string)$spec['trigger_type']) : '';
    $integrations = isset($spec['integrations']) ? trim((string)$spec['integrations']) : '';
    $language = isset($spec['language']) ? trim((string)$spec['language']) : 'en';

    if ($language === '') {
        $language = 'en';
    }

    // Validate required fields
    if ($description === '') {
        return [
            'ok' => false,
            'json' => null,
            'name' => null,
            'error' => 'Workflow description is required.'
        ];
    }

    // Load AI configuration
    $aiConfig = ai_config_load();

    if (empty($aiConfig['api_key'])) {
        return [
            'ok' => false,
            'json' => null,
            'name' => null,
            'error' => 'AI provider not configured. Please configure OpenAI API key in AI Settings.'
        ];
    }

    // Build system prompt
    $systemPrompt = ai_n8n_build_system_prompt($language);

    // Build user prompt
    $userPrompt = ai_n8n_build_user_prompt($description, $name, $triggerType, $integrations);

    // Call OpenAI API
    $result = ai_n8n_call_openai($aiConfig, $systemPrompt, $userPrompt);

    if (!$result['ok']) {
        return [
            'ok' => false,
            'json' => null,
            'name' => null,
            'error' => $result['error'] ?? 'Failed to generate workflow.'
        ];
    }

    // Parse and validate JSON response
    $text = trim($result['content']);
    $text = ai_n8n_clean_json_response($text);

    $decoded = @json_decode($text, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('[AI_N8N] Invalid JSON returned: ' . json_last_error_msg() . ' - Raw: ' . substr($text, 0, 500));
        return [
            'ok' => false,
            'json' => null,
            'name' => null,
            'error' => 'AI returned invalid JSON. Please try again with a clearer description.'
        ];
    }

    // Extract workflow name
    $workflowName = $decoded['name'] ?? ($name !== '' ? $name : 'AI Generated Workflow');

    // Ensure required n8n structure
    if (!isset($decoded['nodes']) || !is_array($decoded['nodes'])) {
        $decoded['nodes'] = [];
    }
    if (!isset($decoded['connections'])) {
        $decoded['connections'] = new \stdClass();
    }
    if (!isset($decoded['settings'])) {
        $decoded['settings'] = new \stdClass();
    }

    // Re-encode to ensure clean JSON
    $cleanJson = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return [
        'ok' => true,
        'json' => $cleanJson,
        'name' => $workflowName,
        'error' => null
    ];
}

/**
 * Build the system prompt for n8n workflow generation
 *
 * @param string $language Language code
 * @return string System prompt
 */
function ai_n8n_build_system_prompt(string $language): string
{
    $languageName = ai_n8n_get_language_name($language);

    return <<<PROMPT
You are an expert n8n workflow automation designer. Your task is to generate valid n8n workflow JSON based on user descriptions.

CRITICAL RULES:
1. Output ONLY valid JSON - no markdown, no code blocks, no explanations
2. The JSON must be compatible with n8n workflow import format
3. Use realistic node types that exist in n8n (Webhook, HTTP Request, Set, IF, Code, etc.)
4. Include proper node positions for visual layout (increment x by 250 for each node)
5. Set "active": false for safety
6. Use descriptive node names in {$languageName}
7. Never include actual API keys, passwords, or secrets - use placeholders like "YOUR_API_KEY"

n8n workflow JSON structure:
{
  "name": "Workflow Name",
  "nodes": [
    {
      "parameters": {},
      "id": "unique-uuid",
      "name": "Node Name",
      "type": "n8n-nodes-base.nodetype",
      "typeVersion": 1,
      "position": [x, y]
    }
  ],
  "connections": {
    "Node Name": {
      "main": [[{"node": "Next Node", "type": "main", "index": 0}]]
    }
  },
  "active": false,
  "settings": {}
}

Common n8n node types:
- n8n-nodes-base.webhook (HTTP Webhook trigger)
- n8n-nodes-base.scheduleTrigger (Cron/Schedule trigger)
- n8n-nodes-base.manualTrigger (Manual trigger)
- n8n-nodes-base.httpRequest (HTTP Request)
- n8n-nodes-base.set (Set/Transform data)
- n8n-nodes-base.if (Conditional)
- n8n-nodes-base.code (JavaScript code)
- n8n-nodes-base.gmail (Gmail)
- n8n-nodes-base.slack (Slack)
- n8n-nodes-base.googleSheets (Google Sheets)
- n8n-nodes-base.postgres (PostgreSQL)
- n8n-nodes-base.mysql (MySQL)
- n8n-nodes-base.noOp (No Operation - for organization)

Generate UUID format for node IDs like: "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
PROMPT;
}

/**
 * Build the user prompt for workflow generation
 *
 * @param string $description Automation description
 * @param string $name Optional workflow name
 * @param string $triggerType Optional trigger type preference
 * @param string $integrations Optional integrations list
 * @return string User prompt
 */
function ai_n8n_build_user_prompt(string $description, string $name, string $triggerType, string $integrations): string
{
    $prompt = "Create an n8n workflow for the following automation:\n\n";
    $prompt .= "Description: {$description}\n";

    if ($name !== '') {
        $prompt .= "Workflow Name: {$name}\n";
    }

    if ($triggerType !== '') {
        $prompt .= "Preferred Trigger: {$triggerType}\n";
    }

    if ($integrations !== '') {
        $prompt .= "Services to integrate: {$integrations}\n";
    }

    $prompt .= "\nGenerate the complete n8n workflow JSON:";

    return $prompt;
}

/**
 * Call OpenAI API for workflow generation
 *
 * @param array $config AI configuration
 * @param string $systemPrompt System prompt
 * @param string $userPrompt User prompt
 * @return array Result with ok, content, error
 */
function ai_n8n_call_openai(array $config, string $systemPrompt, string $userPrompt): array
{
    $apiKey = $config['api_key'] ?? '';
    $model = $config['model'] ?? 'gpt-4o-mini';
    $baseUrl = $config['base_url'] ?? 'https://api.openai.com/v1';

    // Use gpt-4o-mini for better JSON generation
    if (strpos($model, 'gpt') === false) {
        $model = 'gpt-4o-mini';
    }

    $endpoint = rtrim($baseUrl, '/') . '/chat/completions';

    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ],
        'temperature' => 0.3,
        'max_tokens' => 4000,
        'response_format' => ['type' => 'json_object']
    ];

    $ch = curl_init($endpoint);
    if ($ch === false) {
        return [
            'ok' => false,
            'content' => null,
            'error' => 'Failed to initialize cURL'
        ];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('[AI_N8N] cURL error: ' . $curlError);
        return [
            'ok' => false,
            'content' => null,
            'error' => 'Connection error: ' . $curlError
        ];
    }

    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['error']['message'] ?? ('HTTP ' . $httpCode);
        error_log('[AI_N8N] OpenAI API error: ' . $errorMsg);
        return [
            'ok' => false,
            'content' => null,
            'error' => 'OpenAI API error: ' . $errorMsg
        ];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return [
            'ok' => false,
            'content' => null,
            'error' => 'Invalid JSON response from OpenAI'
        ];
    }

    $content = $data['choices'][0]['message']['content'] ?? null;
    if ($content === null || trim($content) === '') {
        return [
            'ok' => false,
            'content' => null,
            'error' => 'Empty response from OpenAI'
        ];
    }

    return [
        'ok' => true,
        'content' => trim($content),
        'error' => null
    ];
}

/**
 * Get human-readable language name from code
 *
 * @param string $code Language code (e.g., "en", "pl")
 * @return string Language name
 */
function ai_n8n_get_language_name(string $code): string
{
    $languages = [
        'en' => 'English',
        'pl' => 'Polish',
        'de' => 'German',
        'es' => 'Spanish',
        'fr' => 'French',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
        'zh' => 'Chinese',
        'ja' => 'Japanese'
    ];

    return $languages[$code] ?? 'English';
}

/**
 * Clean AI-generated JSON response
 * Removes common formatting issues like markdown code blocks
 *
 * @param string $text Raw AI response
 * @return string Cleaned JSON string
 */
function ai_n8n_clean_json_response(string $text): string
{
    // Remove markdown code blocks
    $text = preg_replace('/^```json\s*/i', '', $text);
    $text = preg_replace('/^```\s*/i', '', $text);
    $text = preg_replace('/\s*```$/i', '', $text);

    // Remove leading/trailing whitespace
    $text = trim($text);

    // If text starts with "JSON:" or similar prefix, remove it
    $text = preg_replace('/^JSON:\s*/i', '', $text);

    // Remove any leading text before the JSON object
    if (preg_match('/(\{[\s\S]*\})/', $text, $matches)) {
        $text = $matches[1];
    }

    return $text;
}

/**
 * Get list of common trigger types for UI dropdown
 *
 * @return array Array of trigger options with value and label
 */
function ai_n8n_get_trigger_types(): array
{
    return [
        ['value' => '', 'label' => 'Let AI decide'],
        ['value' => 'webhook', 'label' => 'HTTP Webhook'],
        ['value' => 'schedule', 'label' => 'Schedule/Cron'],
        ['value' => 'manual', 'label' => 'Manual Trigger'],
        ['value' => 'email', 'label' => 'Email Trigger'],
        ['value' => 'database', 'label' => 'Database Trigger']
    ];
}

/**
 * Get list of common integrations for UI suggestions
 *
 * @return array Array of integration names
 */
function ai_n8n_get_common_integrations(): array
{
    return [
        'Gmail',
        'Slack',
        'Discord',
        'Google Sheets',
        'Notion',
        'Airtable',
        'Trello',
        'Asana',
        'GitHub',
        'GitLab',
        'Jira',
        'Salesforce',
        'HubSpot',
        'Mailchimp',
        'Stripe',
        'Twilio',
        'OpenAI',
        'PostgreSQL',
        'MySQL',
        'MongoDB',
        'Redis',
        'AWS S3',
        'Dropbox',
        'Google Drive'
    ];
}
