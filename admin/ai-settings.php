<?php
/**
 * AI Settings - Complete Configuration Panel
 * Manage API keys, models, and settings for all AI providers
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();


// Settings file path
define('AI_SETTINGS_FILE', CMS_ROOT . '/config/ai_settings.json');

/**
 * Load AI settings from JSON file
 */
function load_ai_settings(): array {
    if (!file_exists(AI_SETTINGS_FILE)) {
        return get_default_ai_settings();
    }
    $content = file_get_contents(AI_SETTINGS_FILE);
    $settings = json_decode($content, true);
    return is_array($settings) ? $settings : get_default_ai_settings();
}

/**
 * Save AI settings to JSON file
 */
function save_ai_settings(array $settings): bool {
    $settings['updated_at'] = date('Y-m-d H:i:s');
    $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if ($json === false) {
        error_log('AI Settings: JSON encode failed - ' . json_last_error_msg());
        return false;
    }
    
    $result = @file_put_contents(AI_SETTINGS_FILE, $json, LOCK_EX);
    
    if ($result === false) {
        error_log('AI Settings: Failed to write to ' . AI_SETTINGS_FILE . ' - ' . error_get_last()['message'] ?? 'Unknown error');
        return false;
    }
    
    return true;
}

/**
 * Get default settings structure
 */
function get_default_ai_settings(): array {
    return [
        'default_provider' => 'openai',
        'providers' => [
            'openai' => ['enabled' => false, 'api_key' => '', 'default_model' => 'gpt-4o-mini'],
            'anthropic' => ['enabled' => false, 'api_key' => '', 'default_model' => 'claude-3-5-sonnet-20241022'],
            'google' => ['enabled' => false, 'api_key' => '', 'default_model' => 'gemini-1.5-flash'],
            'deepseek' => ['enabled' => false, 'api_key' => '', 'base_url' => 'https://api.deepseek.com/v1', 'default_model' => 'deepseek-chat'],
            'huggingface' => ['enabled' => false, 'api_key' => '', 'default_model' => 'mistralai/Mistral-7B-Instruct-v0.3'],
            'ollama' => ['enabled' => false, 'base_url' => 'http://localhost:11434', 'default_model' => 'llama2'],
        ],
        'generation_defaults' => [
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'top_p' => 1.0,
        ],
        'rate_limits' => [
            'requests_per_minute' => 60,
            'tokens_per_day' => 100000,
            'cost_limit_daily_usd' => 10.00,
        ],
    ];
}

/**
 * Test API connection
 */
function test_ai_connection(string $provider, array $settings): array {
    $providerSettings = $settings['providers'][$provider] ?? [];
    $apiKey = $providerSettings['api_key'] ?? '';
    
    if (empty($apiKey) && $provider !== 'ollama') {
        return ['success' => false, 'message' => 'API key not configured'];
    }
    
    try {
        switch ($provider) {
            case 'openai':
                $ch = curl_init('https://api.openai.com/v1/models');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $apiKey,
                    ],
                    CURLOPT_TIMEOUT => 10,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    return ['success' => true, 'message' => 'OpenAI connection successful'];
                }
                return ['success' => false, 'message' => 'OpenAI returned HTTP ' . $httpCode];
                
            case 'anthropic':
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
                        'model' => 'claude-3-haiku-20240307',
                        'max_tokens' => 10,
                        'messages' => [['role' => 'user', 'content' => 'Hi']],
                    ]),
                    CURLOPT_TIMEOUT => 15,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    return ['success' => true, 'message' => 'Anthropic connection successful'];
                }
                $data = json_decode($response, true);
                $error = $data['error']['message'] ?? 'HTTP ' . $httpCode;
                return ['success' => false, 'message' => 'Anthropic: ' . $error];
                
            case 'google':
                $url = 'https://generativelanguage.googleapis.com/v1/models?key=' . $apiKey;
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    return ['success' => true, 'message' => 'Google AI connection successful'];
                }
                return ['success' => false, 'message' => 'Google AI returned HTTP ' . $httpCode];
                
            case 'huggingface':
                $ch = curl_init('https://huggingface.co/api/whoami-v2');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $apiKey,
                    ],
                    CURLOPT_TIMEOUT => 10,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $data = json_decode($response, true);
                    $name = $data['name'] ?? 'Unknown';
                    return ['success' => true, 'message' => 'HuggingFace connected as: ' . $name];
                }
                return ['success' => false, 'message' => 'HuggingFace returned HTTP ' . $httpCode];
                
            case 'ollama':
                $baseUrl = $providerSettings['base_url'] ?? 'http://localhost:11434';
                $ch = curl_init($baseUrl . '/api/tags');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    $data = json_decode($response, true);
                    $models = $data['models'] ?? [];
                    $count = count($models);
                    return ['success' => true, 'message' => "Ollama connected. {$count} models available."];
                }
                return ['success' => false, 'message' => 'Cannot connect to Ollama at ' . $baseUrl];

            case 'deepseek':
                $baseUrl = $providerSettings['base_url'] ?? 'https://api.deepseek.com/v1';
                $ch = curl_init($baseUrl . '/models');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $apiKey,
                    ],
                    CURLOPT_TIMEOUT => 10,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    return ['success' => true, 'message' => 'DeepSeek connection successful'];
                }
                $data = json_decode($response, true);
                $error = $data['error']['message'] ?? 'HTTP ' . $httpCode;
                return ['success' => false, 'message' => 'DeepSeek: ' . $error];

            default:
                return ['success' => false, 'message' => 'Unknown provider'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Load current settings
$settings = load_ai_settings();
$message = '';
$messageType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_settings':
            // Update default provider
            $settings['default_provider'] = $_POST['default_provider'] ?? 'openai';
            
            // Update OpenAI
            $settings['providers']['openai']['enabled'] = isset($_POST['openai_enabled']);
            $settings['providers']['openai']['api_key'] = trim($_POST['openai_api_key'] ?? '');
            $settings['providers']['openai']['organization'] = trim($_POST['openai_organization'] ?? '');
            $settings['providers']['openai']['default_model'] = $_POST['openai_model'] ?? 'gpt-4o-mini';
            
            // Update Anthropic
            $settings['providers']['anthropic']['enabled'] = isset($_POST['anthropic_enabled']);
            $settings['providers']['anthropic']['api_key'] = trim($_POST['anthropic_api_key'] ?? '');
            $settings['providers']['anthropic']['default_model'] = $_POST['anthropic_model'] ?? 'claude-3-5-sonnet-20241022';
            
            // Update Google
            $settings['providers']['google']['enabled'] = isset($_POST['google_enabled']);
            $settings['providers']['google']['api_key'] = trim($_POST['google_api_key'] ?? '');
            $settings['providers']['google']['default_model'] = $_POST['google_model'] ?? 'gemini-1.5-flash';

            // Update DeepSeek
            $settings['providers']['deepseek']['enabled'] = isset($_POST['deepseek_enabled']);
            $settings['providers']['deepseek']['api_key'] = trim($_POST['deepseek_api_key'] ?? '');
            $settings['providers']['deepseek']['base_url'] = trim($_POST['deepseek_base_url'] ?? 'https://api.deepseek.com/v1');
            $settings['providers']['deepseek']['default_model'] = $_POST['deepseek_model'] ?? 'deepseek-chat';

            // Update HuggingFace
            $settings['providers']['huggingface']['enabled'] = isset($_POST['huggingface_enabled']);
            $settings['providers']['huggingface']['api_key'] = trim($_POST['huggingface_api_key'] ?? '');
            $settings['providers']['huggingface']['models'] = [
                'text' => trim($_POST['huggingface_model_text'] ?? ''),
                'image' => trim($_POST['huggingface_model_image'] ?? ''),
                'vision' => trim($_POST['huggingface_model_vision'] ?? ''),
            ];
            // Remove legacy field if exists
            unset($settings['providers']['huggingface']['default_model']);
            
            // Update Ollama
            $settings['providers']['ollama']['enabled'] = isset($_POST['ollama_enabled']);
            $settings['providers']['ollama']['base_url'] = trim($_POST['ollama_base_url'] ?? 'http://localhost:11434');
            $settings['providers']['ollama']['default_model'] = $_POST['ollama_model'] ?? 'llama2';
            
            // Update generation defaults
            $settings['generation_defaults']['temperature'] = (float)($_POST['temperature'] ?? 0.7);
            $settings['generation_defaults']['max_tokens'] = (int)($_POST['max_tokens'] ?? 2000);
            $settings['generation_defaults']['top_p'] = (float)($_POST['top_p'] ?? 1.0);
            
            // Update rate limits
            $settings['rate_limits']['requests_per_minute'] = (int)($_POST['requests_per_minute'] ?? 60);
            $settings['rate_limits']['tokens_per_day'] = (int)($_POST['tokens_per_day'] ?? 100000);
            $settings['rate_limits']['cost_limit_daily_usd'] = (float)($_POST['cost_limit_daily_usd'] ?? 10.00);
            
            if (save_ai_settings($settings)) {
                $message = 'Settings saved successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to save settings';
                $messageType = 'error';
            }
            break;
            
        case 'test_connection':
            $provider = $_POST['provider'] ?? '';
            if ($provider) {
                // Use API key from POST (form) instead of saved settings
                $testSettings = $settings;
                
                // Override with form values for testing
                if ($provider === 'openai') {
                    $testSettings['providers']['openai']['api_key'] = trim($_POST['openai_api_key'] ?? '');
                } elseif ($provider === 'anthropic') {
                    $testSettings['providers']['anthropic']['api_key'] = trim($_POST['anthropic_api_key'] ?? '');
                } elseif ($provider === 'google') {
                    $testSettings['providers']['google']['api_key'] = trim($_POST['google_api_key'] ?? '');
                } elseif ($provider === 'huggingface') {
                    $testSettings['providers']['huggingface']['api_key'] = trim($_POST['huggingface_api_key'] ?? '');
                } elseif ($provider === 'ollama') {
                    $testSettings['providers']['ollama']['base_url'] = trim($_POST['ollama_base_url'] ?? 'http://localhost:11434');
                } elseif ($provider === 'deepseek') {
                    $testSettings['providers']['deepseek']['api_key'] = trim($_POST['deepseek_api_key'] ?? '');
                    $testSettings['providers']['deepseek']['base_url'] = trim($_POST['deepseek_base_url'] ?? 'https://api.deepseek.com/v1');
                }

                $result = test_ai_connection($provider, $testSettings);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            break;
    }
}

// Provider configurations for display - dynamically load models from settings
$loadedSettings = load_ai_settings();

// Helper function to format model list from JSON
function format_models_from_json($models) {
    $formatted = [];
    foreach ($models as $modelId => $modelData) {
        if (is_array($modelData)) {
            $name = $modelData['name'] ?? $modelId;
            // Add badges for special features
            if (!empty($modelData['recommended'])) $name .= ' ⭐';
            if (!empty($modelData['legacy'])) $name .= ' (Legacy)';
            if (!empty($modelData['free_tier'])) $name .= ' 🆓';
            if (!empty($modelData['preview'])) $name .= ' 🔬';
            if (!empty($modelData['reasoning']) || !empty($modelData['extended_thinking'])) $name .= ' 🧠';
            $formatted[$modelId] = $name;
        } else {
            $formatted[$modelId] = $modelData;
        }
    }
    return $formatted;
}

$providerConfigs = [
    'openai' => [
        'name' => 'OpenAI',
        'icon' => '🤖',
        'color' => '#10a37f',
        'models' => !empty($loadedSettings['providers']['openai']['models'])
            ? format_models_from_json($loadedSettings['providers']['openai']['models'])
            : [
                'gpt-4o' => 'GPT-4o (Latest)',
                'gpt-4o-mini' => 'GPT-4o Mini',
            ],
    ],
    'anthropic' => [
        'name' => 'Anthropic Claude',
        'icon' => '🧠',
        'color' => '#d4a27f',
        'models' => !empty($loadedSettings['providers']['anthropic']['models'])
            ? format_models_from_json($loadedSettings['providers']['anthropic']['models'])
            : [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
            ],
    ],
    'google' => [
        'name' => 'Google Gemini',
        'icon' => '💎',
        'color' => '#4285f4',
        'models' => !empty($loadedSettings['providers']['google']['models'])
            ? format_models_from_json($loadedSettings['providers']['google']['models'])
            : [
                'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            ],
    ],
    'deepseek' => [
        'name' => 'DeepSeek',
        'icon' => '🔮',
        'color' => '#7c3aed',
        'models' => !empty($loadedSettings['providers']['deepseek']['models'])
            ? format_models_from_json($loadedSettings['providers']['deepseek']['models'])
            : [
                'deepseek-chat' => 'DeepSeek Chat',
                'deepseek-coder' => 'DeepSeek Coder',
            ],
        'has_base_url' => true,
    ],
    'huggingface' => [
        'name' => 'HuggingFace',
        'icon' => '🤗',
        'color' => '#ff9d00',
        'models' => [
            'HuggingFaceTB/SmolLM3-3B' => 'SmolLM3 3B',
            'mistralai/Mistral-7B-Instruct-v0.3' => 'Mistral 7B Instruct v0.3',
            'meta-llama/Llama-3.1-70B-Instruct' => 'Llama 3.1 70B',
        ],
    ],
    'ollama' => [
        'name' => 'Ollama (Local)',
        'icon' => '🦙',
        'color' => '#1a1a1a',
        'models' => [
            'llama3.2' => 'Llama 3.2',
            'llama3.1' => 'Llama 3.1',
            'mistral' => 'Mistral',
            'codellama' => 'Code Llama',
            'qwen2.5' => 'Qwen 2.5',
            'deepseek-r1' => 'DeepSeek R1',
        ],
    ],
];
$pageTitle = 'AI Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Settings - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--card-bg:#1e1e2e;--primary:#89b4fa;--primary-dark:#7aa2f7;--text-muted:#6c7086;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '⚙️',
    'title' => 'AI Settings',
    'description' => 'Configure AI providers and models',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'AI Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<style>
.provider-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.provider-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.provider-header { padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid var(--border); }
.provider-icon { font-size: 1.5rem; }
.provider-name { font-weight: 600; flex: 1; }
.provider-toggle { position: relative; width: 48px; height: 26px; }
.provider-toggle input { opacity: 0; width: 0; height: 0; }
.provider-toggle .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 26px; }
.provider-toggle .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
.provider-toggle input:checked + .slider { background-color: var(--success); }
.provider-toggle input:checked + .slider:before { transform: translateX(22px); }
.provider-body { padding: 1.25rem; }
.provider-body.disabled { opacity: 0.5; pointer-events: none; }
.field-row { margin-bottom: 1rem; }
.field-row label { display: block; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.375rem; color: var(--text-muted); }
.field-row input, .field-row select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; }
.field-row input:focus, .field-row select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.api-key-field { position: relative; }
.api-key-field input { padding-right: 80px; font-family: monospace; }
.api-key-field .toggle-visibility { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.75rem; }
.test-btn { background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; width: 100%; margin-top: 0.5rem; }
.test-btn:hover { background: var(--primary-dark); }
.settings-section { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
.settings-section h3 { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
.settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.save-bar { background: var(--sidebar-bg); color: white; padding: 1rem 1.5rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; }
.save-bar .info { font-size: 0.875rem; opacity: 0.8; }
.save-bar button { background: var(--success); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9375rem; }
.save-bar button:hover { filter: brightness(1.1); }
.range-field { display: flex; align-items: center; gap: 1rem; }
.range-field input[type="range"] { flex: 1; }
.range-field .value { min-width: 50px; text-align: right; font-family: monospace; font-weight: 600; }
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="POST" id="ai-settings-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_settings">

    <!-- Default Provider -->
    <div class="settings-section">
        <h3>🎯 Default AI Provider</h3>
        <div class="field-row" style="max-width: 300px;">
            <label>Primary Provider for AI Features</label>
            <select name="default_provider">
                <?php foreach ($providerConfigs as $key => $config): ?>
                <option value="<?= $key ?>" <?= ($settings['default_provider'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= $config['icon'] ?> <?= $config['name'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Provider Cards -->
    <div class="provider-grid">
        <?php foreach ($providerConfigs as $providerKey => $config): 
            $providerSettings = $settings['providers'][$providerKey] ?? [];
            $isEnabled = !empty($providerSettings['enabled']);
        ?>
        <div class="provider-card">
            <div class="provider-header" style="background: <?= $config['color'] ?>15;">
                <span class="provider-icon"><?= $config['icon'] ?></span>
                <span class="provider-name"><?= $config['name'] ?></span>
                <label class="provider-toggle">
                    <input type="checkbox" name="<?= $providerKey ?>_enabled" <?= $isEnabled ? 'checked' : '' ?> 
                           onchange="toggleProvider('<?= $providerKey ?>', this.checked)">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="provider-body <?= $isEnabled ? '' : 'disabled' ?>" id="<?= $providerKey ?>-body">
                <?php if ($providerKey === 'ollama'): ?>
                <div class="field-row">
                    <label>Base URL</label>
                    <input type="text" name="ollama_base_url"
                           value="<?= htmlspecialchars($providerSettings['base_url'] ?? 'http://localhost:11434') ?>"
                           placeholder="http://localhost:11434">
                </div>
                <?php elseif ($providerKey === 'deepseek'): ?>
                <div class="field-row">
                    <label>Base URL</label>
                    <input type="text" name="deepseek_base_url"
                           value="<?= htmlspecialchars($providerSettings['base_url'] ?? 'https://api.deepseek.com/v1') ?>"
                           placeholder="https://api.deepseek.com/v1">
                </div>
                <div class="field-row">
                    <label>API Key</label>
                    <div class="api-key-field">
                        <input type="password" name="deepseek_api_key"
                               value="<?= htmlspecialchars($providerSettings['api_key'] ?? '') ?>"
                               placeholder="Enter your API key"
                               id="deepseek-key">
                        <button type="button" class="toggle-visibility" onclick="toggleKeyVisibility('deepseek-key')">Show</button>
                    </div>
                </div>
                <?php else: ?>
                <div class="field-row">
                    <label>API Key</label>
                    <div class="api-key-field">
                        <input type="password" name="<?= $providerKey ?>_api_key"
                               value="<?= htmlspecialchars($providerSettings['api_key'] ?? '') ?>"
                               placeholder="Enter your API key"
                               id="<?= $providerKey ?>-key">
                        <button type="button" class="toggle-visibility" onclick="toggleKeyVisibility('<?= $providerKey ?>-key')">Show</button>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($providerKey === 'openai'): ?>
                <div class="field-row">
                    <label>Organization ID (optional)</label>
                    <input type="text" name="openai_organization" 
                           value="<?= htmlspecialchars($providerSettings['organization'] ?? '') ?>" 
                           placeholder="org-xxxxx">
                </div>
                <?php endif; ?>
                
                <?php if ($providerKey === 'huggingface'): ?>
                <div class="field-row">
                    <label>Text Model (SEO, Copywriting)</label>
                    <input type="text" name="huggingface_model_text"
                           value="<?= htmlspecialchars($providerSettings['models']['text'] ?? $providerSettings['default_model'] ?? '') ?>"
                           placeholder="mistralai/Mistral-7B-Instruct-v0.2">
                </div>
                <div class="field-row">
                    <label>Image Model (AI Image Generator)</label>
                    <input type="text" name="huggingface_model_image"
                           value="<?= htmlspecialchars($providerSettings['models']['image'] ?? '') ?>"
                           placeholder="stabilityai/stable-diffusion-xl-base-1.0">
                </div>
                <div class="field-row">
                    <label>Vision Model (ALT Text Generator)</label>
                    <input type="text" name="huggingface_model_vision"
                           value="<?= htmlspecialchars($providerSettings['models']['vision'] ?? '') ?>"
                           placeholder="Salesforce/blip-image-captioning-large">
                </div>
                <?php else: ?>
                <div class="field-row">
                    <label>Default Model</label>
                    <select name="<?= $providerKey ?>_model">
                        <?php foreach ($config['models'] as $modelKey => $modelName): ?>
                        <option value="<?= $modelKey ?>" <?= ($providerSettings['default_model'] ?? '') === $modelKey ? 'selected' : '' ?>>
                            <?= $modelName ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <button type="button" class="test-btn" onclick="testConnection('<?= $providerKey ?>')">
                    🔌 Test Connection
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Generation Defaults -->
    <div class="settings-section">
        <h3>⚙️ Generation Defaults</h3>
        <div class="settings-grid">
            <div class="field-row">
                <label>Temperature (Creativity: 0 = Precise, 2 = Creative)</label>
                <div class="range-field">
                    <input type="range" name="temperature" min="0" max="2" step="0.1" 
                           value="<?= $settings['generation_defaults']['temperature'] ?? 0.7 ?>"
                           oninput="this.nextElementSibling.textContent = this.value">
                    <span class="value"><?= $settings['generation_defaults']['temperature'] ?? 0.7 ?></span>
                </div>
            </div>
            <div class="field-row">
                <label>Max Tokens (Response Length)</label>
                <input type="number" name="max_tokens" min="100" max="128000" 
                       value="<?= $settings['generation_defaults']['max_tokens'] ?? 2000 ?>">
            </div>
            <div class="field-row">
                <label>Top P (Nucleus Sampling)</label>
                <div class="range-field">
                    <input type="range" name="top_p" min="0" max="1" step="0.05" 
                           value="<?= $settings['generation_defaults']['top_p'] ?? 1.0 ?>"
                           oninput="this.nextElementSibling.textContent = this.value">
                    <span class="value"><?= $settings['generation_defaults']['top_p'] ?? 1.0 ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rate Limits -->
    <div class="settings-section">
        <h3>🚦 Rate Limits & Budget</h3>
        <div class="settings-grid">
            <div class="field-row">
                <label>Requests per Minute</label>
                <input type="number" name="requests_per_minute" min="1" max="1000" 
                       value="<?= $settings['rate_limits']['requests_per_minute'] ?? 60 ?>">
            </div>
            <div class="field-row">
                <label>Tokens per Day</label>
                <input type="number" name="tokens_per_day" min="1000" max="10000000" 
                       value="<?= $settings['rate_limits']['tokens_per_day'] ?? 100000 ?>">
            </div>
            <div class="field-row">
                <label>Daily Cost Limit (USD)</label>
                <input type="number" name="cost_limit_daily_usd" min="0" max="1000" step="0.01" 
                       value="<?= $settings['rate_limits']['cost_limit_daily_usd'] ?? 10.00 ?>">
            </div>
        </div>
    </div>

    <!-- Save Bar -->
    <div class="save-bar">
        <div class="info">
            <?php if (!empty($settings['updated_at'])): ?>
            Last saved: <?= htmlspecialchars($settings['updated_at']) ?>
            <?php else: ?>
            Settings not yet configured
            <?php endif; ?>
        </div>
        <button type="submit">💾 Save All Settings</button>
    </div>
</form>

<script>
function toggleProvider(provider, enabled) {
    const body = document.getElementById(provider + '-body');
    if (body) {
        body.classList.toggle('disabled', !enabled);
    }
}

function toggleKeyVisibility(inputId) {
    const input = document.getElementById(inputId);
    const btn = input.nextElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}

function testConnection(provider) {
    // Create a hidden form with all current values + test action
    const mainForm = document.getElementById('ai-settings-form');
    const formData = new FormData(mainForm);
    
    // Create temporary form
    const testForm = document.createElement('form');
    testForm.method = 'POST';
    testForm.style.display = 'none';
    
    // Copy all form fields
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        testForm.appendChild(input);
    }
    
    // Override action to test_connection
    const actionInput = testForm.querySelector('[name="action"]');
    if (actionInput) {
        actionInput.value = 'test_connection';
    } else {
        const newAction = document.createElement('input');
        newAction.type = 'hidden';
        newAction.name = 'action';
        newAction.value = 'test_connection';
        testForm.appendChild(newAction);
    }
    
    // Add provider
    const providerInput = document.createElement('input');
    providerInput.type = 'hidden';
    providerInput.name = 'provider';
    providerInput.value = provider;
    testForm.appendChild(providerInput);
    
    // Submit
    document.body.appendChild(testForm);
    testForm.submit();
}
</script>
</div>
</body>
</html>
