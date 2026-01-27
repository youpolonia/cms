<?php
/**
 * AI Workflow Generator
 * Generate automation workflows for n8n, Zapier, Make.com using AI
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Forbidden - DEV_MODE required');
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';

// Load AI settings
$aiSettingsFile = CMS_ROOT . '/config/ai_settings.json';
$aiSettings = file_exists($aiSettingsFile) ? json_decode(file_get_contents($aiSettingsFile), true) : [];

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate workflow using AI
 */
function generate_workflow_with_ai(array $spec, array $aiSettings): array {
    // Get provider and model from spec (form data) or fall back to settings
    $provider = $spec['ai_provider'] ?? $aiSettings['default_provider'] ?? 'openai';
    $selectedModel = $spec['ai_model'] ?? '';

    // Validate provider and model using central functions
    if (!function_exists('ai_is_valid_provider') || !ai_is_valid_provider($provider)) {
        $provider = 'openai';
    }
    if (!function_exists('ai_is_valid_provider_model') || !ai_is_valid_provider_model($provider, $selectedModel)) {
        $selectedModel = function_exists('ai_get_provider_default_model') ? ai_get_provider_default_model($provider) : 'gpt-5.2';
    }

    $platform = $spec['platform'] ?? 'n8n';
    $name = $spec['name'] ?? 'My Workflow';
    $description = $spec['description'] ?? '';
    $trigger = $spec['trigger'] ?? '';
    $steps = $spec['steps'] ?? '';
    $integrations = $spec['integrations'] ?? '';

    // Build prompt based on platform
    $prompt = build_workflow_prompt($platform, $name, $description, $trigger, $steps, $integrations);

    // Call AI API using universal generate
    try {
        $systemPrompt = 'You are an expert automation workflow designer. Always return valid JSON only.';

        // Use ai_universal_generate for multi-provider support
        $response = ai_universal_generate($provider, $selectedModel, $systemPrompt, $prompt, [
            'max_tokens' => 4000,
            'temperature' => 0.3
        ]);

        if (!$response['ok']) {
            return $response;
        }

        // Extract JSON from response
        $json = extract_json_from_response($response['text'] ?? '');

        return [
            'ok' => true,
            'json' => $json,
            'raw' => $response['text'] ?? '',
            'provider' => $provider,
            'model' => $selectedModel
        ];

    } catch (Exception $e) {
        return ['ok' => false, 'error' => 'AI Error: ' . $e->getMessage()];
    }
}

/**
 * Build prompt for workflow generation
 */
function build_workflow_prompt(string $platform, string $name, string $description, string $trigger, string $steps, string $integrations): string {
    $platformInstructions = [
        'n8n' => 'Generate a valid n8n workflow JSON. Use correct n8n node types like "n8n-nodes-base.httpRequest", "n8n-nodes-base.set", "n8n-nodes-base.if", etc. Include proper connections array.',
        'zapier' => 'Generate a Zapier-compatible workflow description in JSON format with triggers, actions, and filters.',
        'make' => 'Generate a Make.com (Integromat) scenario JSON with modules, connections, and data mappings.',
    ];
    
    $instruction = $platformInstructions[$platform] ?? $platformInstructions['n8n'];
    
    return <<<PROMPT
You are an expert automation workflow designer. Create a complete, production-ready workflow.

PLATFORM: {$platform}
WORKFLOW NAME: {$name}
DESCRIPTION: {$description}
TRIGGER: {$trigger}
STEPS/ACTIONS: {$steps}
INTEGRATIONS/APPS: {$integrations}

INSTRUCTIONS:
{$instruction}

REQUIREMENTS:
1. Return ONLY valid JSON - no markdown, no explanation before or after
2. Include all necessary nodes/modules for the described workflow
3. Add proper error handling where appropriate
4. Use realistic placeholder values for API endpoints, credentials, etc.
5. Make the workflow complete and functional

OUTPUT FORMAT:
Return only the JSON workflow definition, nothing else.
PROMPT;
}

/**
 * Call OpenAI API
 */
function call_openai_api(string $apiKey, string $model, string $prompt): array {
    // Newer models (o-series, GPT-5.x, GPT-4.1.x) use max_completion_tokens
    $useNewTokenParam = preg_match('/^(o[1-4]|gpt-[45]\.|gpt-5$)/', $model);
    
    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'You are an expert automation workflow designer. Always return valid JSON only.'],
            ['role' => 'user', 'content' => $prompt]
        ]
    ];
    
    if ($useNewTokenParam) {
        $payload['max_completion_tokens'] = 4000;
    } else {
        $payload['max_tokens'] = 4000;
        $payload['temperature'] = 0.3;
    }
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 60,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        return ['ok' => false, 'error' => 'OpenAI API error: ' . ($error['error']['message'] ?? 'HTTP ' . $httpCode)];
    }
    
    $data = json_decode($response, true);

    // Extract content from various response formats (GPT-4o, GPT-5.x, etc.)
    $text = null;
    if (isset($data['choices'][0]['message']['content'])) {
        $text = $data['choices'][0]['message']['content'];
    } elseif (isset($data['output_text'])) {
        $text = $data['output_text'];
    } elseif (isset($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (isset($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $c) {
                    if (isset($c['text'])) { $text = $c['text']; break 2; }
                }
            }
        }
    } elseif (isset($data['choices'][0]['text'])) {
        $text = $data['choices'][0]['text'];
    }

    return ['ok' => true, 'text' => $text ?? ''];
}

/**
 * Call Anthropic API
 */
function call_anthropic_api(string $apiKey, string $model, string $prompt): array {
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'max_tokens' => 4000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
        ]),
        CURLOPT_TIMEOUT => 60,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $error = json_decode($response, true);
        return ['ok' => false, 'error' => 'Anthropic API error: ' . ($error['error']['message'] ?? 'HTTP ' . $httpCode)];
    }
    
    $data = json_decode($response, true);
    $text = $data['content'][0]['text'] ?? '';
    
    return ['ok' => true, 'text' => $text];
}

/**
 * Extract JSON from AI response
 */
function extract_json_from_response(string $text): string {
    // Remove markdown code blocks
    $text = preg_replace('/```json\s*/i', '', $text);
    $text = preg_replace('/```\s*/', '', $text);
    $text = trim($text);
    
    // Try to find JSON object
    if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
        return $matches[0];
    }
    
    return $text;
}

// Handle form submission
$result = null;
$formData = [
    'platform' => 'n8n',
    'name' => '',
    'description' => '',
    'trigger' => '',
    'steps' => '',
    'integrations' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    $formData = [
        'platform' => $_POST['platform'] ?? 'n8n',
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'trigger' => trim($_POST['trigger'] ?? ''),
        'steps' => trim($_POST['steps'] ?? ''),
        'integrations' => trim($_POST['integrations'] ?? ''),
        'ai_provider' => $_POST['ai_provider'] ?? 'openai',
        'ai_model' => $_POST['ai_model'] ?? 'gpt-5.2',
    ];
    
    if (empty($formData['name']) || empty($formData['description'])) {
        $result = ['ok' => false, 'error' => 'Name and description are required'];
    } else {
        $result = generate_workflow_with_ai($formData, $aiSettings);
    }
}

// Workflow templates
$templates = [
    [
        'name' => 'Lead Capture to CRM',
        'description' => 'Capture form submissions and add leads to CRM',
        'trigger' => 'Webhook receives form data',
        'steps' => '1. Validate email format\n2. Check if lead exists\n3. Create or update lead in CRM\n4. Send welcome email\n5. Notify sales team on Slack',
        'integrations' => 'Webhook, HubSpot/Salesforce, Gmail, Slack',
    ],
    [
        'name' => 'Content Publishing Pipeline',
        'description' => 'Automate content publishing across platforms',
        'trigger' => 'New blog post published (webhook)',
        'steps' => '1. Extract title, excerpt, image\n2. Post to Twitter\n3. Post to LinkedIn\n4. Post to Facebook\n5. Send newsletter digest',
        'integrations' => 'CMS Webhook, Twitter API, LinkedIn, Facebook, Mailchimp',
    ],
    [
        'name' => 'Invoice Processing',
        'description' => 'Process incoming invoices automatically',
        'trigger' => 'Email received with PDF attachment',
        'steps' => '1. Extract PDF from email\n2. OCR to extract invoice data\n3. Validate amounts\n4. Create entry in accounting software\n5. Notify finance team',
        'integrations' => 'Gmail/IMAP, OCR API, QuickBooks/Xero, Slack',
    ],
    [
        'name' => 'Customer Feedback Loop',
        'description' => 'Collect and analyze customer feedback',
        'trigger' => 'Survey response submitted',
        'steps' => '1. Parse survey data\n2. Analyze sentiment (AI)\n3. Route negative feedback to support\n4. Update customer profile\n5. Generate weekly report',
        'integrations' => 'Typeform/Google Forms, OpenAI, Zendesk, Google Sheets',
    ],
];

$pageTitle = 'AI Workflow Generator';
require_once CMS_ROOT . '/admin/includes/header.php';
?>

<style>
.workflow-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 1200px) { .workflow-grid { grid-template-columns: 1fr; } }

.form-section { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; }
.form-section h3 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }

.result-section { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.result-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.result-body { padding: 1.25rem; }

.platform-select { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
.platform-btn { flex: 1; padding: 0.75rem; border: 2px solid var(--border); border-radius: 8px; background: var(--card-bg); cursor: pointer; text-align: center; transition: all 0.2s; }
.platform-btn:hover { border-color: var(--primary); }
.platform-btn.active { border-color: var(--primary); background: rgba(99,102,241,0.1); }
.platform-btn .icon { font-size: 1.5rem; display: block; margin-bottom: 0.25rem; }
.platform-btn .name { font-size: 0.8125rem; font-weight: 500; }

.field-row { margin-bottom: 1rem; }
.field-row label { display: block; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.375rem; color: var(--text-muted); }
.field-row input, .field-row textarea, .field-row select { width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; }
.field-row textarea { min-height: 100px; font-family: inherit; resize: vertical; }

.templates-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1.5rem; }
.template-card { padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.template-card:hover { border-color: var(--primary); background: rgba(99,102,241,0.05); }
.template-card .title { font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.25rem; }
.template-card .desc { font-size: 0.75rem; color: var(--text-muted); }

.generate-btn { width: 100%; padding: 0.875rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
.generate-btn:hover { background: var(--primary-dark); }
.generate-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.json-output { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-family: 'Monaco', 'Consolas', monospace; font-size: 0.75rem; overflow-x: auto; max-height: 500px; white-space: pre-wrap; word-break: break-all; }

.action-btns { display: flex; gap: 0.5rem; }
.action-btn { padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.8125rem; font-weight: 500; cursor: pointer; border: none; display: flex; align-items: center; gap: 0.375rem; }
.action-btn.copy { background: var(--primary); color: white; }
.action-btn.download { background: var(--success); color: white; }

.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.status-badge.success { background: #d1fae5; color: #065f46; }
.status-badge.error { background: #fee2e2; color: #991b1b; }

.provider-info { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; }
</style>

<?php if ($result && !$result['ok']): ?>
<div class="alert alert-error">
    ❌ <?= esc($result['error']) ?>
</div>
<?php endif; ?>

<div class="workflow-grid">
    <!-- Left: Form -->
    <div>
        <form method="POST" id="workflow-form">
            <?= csrf_field() ?>
            
            <div class="form-section">
                <h3>🎯 Target Platform</h3>
                <div class="platform-select">
                    <label class="platform-btn <?= $formData['platform'] === 'n8n' ? 'active' : '' ?>">
                        <input type="radio" name="platform" value="n8n" <?= $formData['platform'] === 'n8n' ? 'checked' : '' ?> style="display:none;">
                        <span class="icon">⚡</span>
                        <span class="name">n8n</span>
                    </label>
                    <label class="platform-btn <?= $formData['platform'] === 'zapier' ? 'active' : '' ?>">
                        <input type="radio" name="platform" value="zapier" <?= $formData['platform'] === 'zapier' ? 'checked' : '' ?> style="display:none;">
                        <span class="icon">🔶</span>
                        <span class="name">Zapier</span>
                    </label>
                    <label class="platform-btn <?= $formData['platform'] === 'make' ? 'active' : '' ?>">
                        <input type="radio" name="platform" value="make" <?= $formData['platform'] === 'make' ? 'checked' : '' ?> style="display:none;">
                        <span class="icon">🟣</span>
                        <span class="name">Make.com</span>
                    </label>
                </div>
            </div>
            
            <div class="form-section" style="margin-top: 1rem;">
                <h3>📋 Quick Templates</h3>
                <div class="templates-grid">
                    <?php foreach ($templates as $i => $tpl): ?>
                    <div class="template-card" onclick="loadTemplate(<?= $i ?>)">
                        <div class="title"><?= esc($tpl['name']) ?></div>
                        <div class="desc"><?= esc($tpl['description']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-section" style="margin-top: 1rem;">
                <h3>✏️ Workflow Details</h3>
                
                <div class="field-row">
                    <label>Workflow Name *</label>
                    <input type="text" name="name" value="<?= esc($formData['name']) ?>" placeholder="e.g., Lead Capture Automation" required>
                </div>
                
                <div class="field-row">
                    <label>Description / Goal *</label>
                    <textarea name="description" placeholder="Describe what this workflow should accomplish..." required><?= esc($formData['description']) ?></textarea>
                </div>
                
                <div class="field-row">
                    <label>Trigger Event</label>
                    <input type="text" name="trigger" value="<?= esc($formData['trigger']) ?>" placeholder="e.g., Webhook, Schedule (every hour), New email received">
                </div>
                
                <div class="field-row">
                    <label>Steps / Actions</label>
                    <textarea name="steps" placeholder="Describe the workflow steps:&#10;1. Receive data&#10;2. Process/transform&#10;3. Send to destination&#10;4. Notify team"><?= esc($formData['steps']) ?></textarea>
                </div>
                
                <div class="field-row">
                    <label>Integrations / Apps</label>
                    <input type="text" name="integrations" value="<?= esc($formData['integrations']) ?>" placeholder="e.g., Gmail, Slack, HubSpot, Google Sheets">
                </div>
                
                <div class="field-row">
                    <label>AI Provider & Model</label>
                    <?= ai_render_dual_selector('ai_provider', 'ai_model', 'openai', 'gpt-5.2') ?>
                </div>

                <button type="submit" class="generate-btn" id="generate-btn">
                    🤖 Generate Workflow with AI
                </button>
            </div>
        </form>
    </div>
    
    <!-- Right: Result -->
    <div>
        <div class="result-section">
            <div class="result-header">
                <h3 style="margin:0;">📄 Generated Workflow</h3>
                <?php if ($result && $result['ok']): ?>
                <div class="action-btns">
                    <button class="action-btn copy" onclick="copyToClipboard()">📋 Copy JSON</button>
                    <button class="action-btn download" onclick="downloadJson()">⬇️ Download</button>
                </div>
                <?php endif; ?>
            </div>
            <div class="result-body">
                <?php if ($result && $result['ok']): ?>
                <div style="margin-bottom: 0.75rem;">
                    <span class="status-badge success">✓ Generated successfully</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 0.5rem;">
                        via <?= esc($result['provider']) ?> / <?= esc($result['model']) ?>
                    </span>
                </div>
                <pre class="json-output" id="json-output"><?= esc($result['json']) ?></pre>
                <?php elseif ($result && !$result['ok']): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
                    <div>Generation failed. Please try again.</div>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🤖</div>
                    <div>Fill in the workflow details and click<br>"Generate Workflow with AI"</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($result && $result['ok']): ?>
        <div class="form-section" style="margin-top: 1rem;">
            <h3>🚀 Next Steps</h3>
            <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                <li style="margin-bottom: 0.5rem;">Copy the JSON or download the file</li>
                <li style="margin-bottom: 0.5rem;">Open your automation platform (<?= esc($formData['platform']) ?>)</li>
                <li style="margin-bottom: 0.5rem;">Import the workflow JSON</li>
                <li style="margin-bottom: 0.5rem;">Configure credentials and connections</li>
                <li>Test and activate the workflow</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const templates = <?= json_encode($templates) ?>;

document.querySelectorAll('.platform-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.platform-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

function loadTemplate(index) {
    const tpl = templates[index];
    document.querySelector('[name="name"]').value = tpl.name;
    document.querySelector('[name="description"]').value = tpl.description;
    document.querySelector('[name="trigger"]').value = tpl.trigger;
    document.querySelector('[name="steps"]').value = tpl.steps;
    document.querySelector('[name="integrations"]').value = tpl.integrations;
}

function copyToClipboard() {
    const json = document.getElementById('json-output').textContent;
    navigator.clipboard.writeText(json).then(() => {
        alert('JSON copied to clipboard!');
    });
}

function downloadJson() {
    const json = document.getElementById('json-output').textContent;
    const platform = document.querySelector('[name="platform"]:checked').value;
    const name = document.querySelector('[name="name"]').value || 'workflow';
    const filename = name.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-' + platform + '.json';
    
    const blob = new Blob([json], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

document.getElementById('workflow-form').addEventListener('submit', function() {
    document.getElementById('generate-btn').disabled = true;
    document.getElementById('generate-btn').innerHTML = '⏳ Generating...';
});
</script>

<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
