<?php
/**
 * Automation Platforms Settings
 * Configure n8n, Zapier, Make.com integrations
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

define('AUTOMATION_SETTINGS_FILE', CMS_ROOT . '/config/automation_platforms.json');

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Load automation settings
 */
function load_automation_settings(): array {
    if (!file_exists(AUTOMATION_SETTINGS_FILE)) {
        return get_default_automation_settings();
    }
    $content = file_get_contents(AUTOMATION_SETTINGS_FILE);
    $settings = json_decode($content, true);
    return is_array($settings) ? $settings : get_default_automation_settings();
}

/**
 * Save automation settings
 */
function save_automation_settings(array $settings): bool {
    $settings['updated_at'] = date('Y-m-d H:i:s');
    $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return file_put_contents(AUTOMATION_SETTINGS_FILE, $json, LOCK_EX) !== false;
}

/**
 * Get default settings
 */
function get_default_automation_settings(): array {
    return [
        'n8n' => [
            'enabled' => false,
            'base_url' => '',
            'auth_type' => 'apikey',
            'api_key' => '',
            'webhook_url' => '',
        ],
        'zapier' => [
            'enabled' => false,
            'webhook_url' => '',
            'api_key' => '',
        ],
        'make' => [
            'enabled' => false,
            'api_key' => '',
            'team_id' => '',
            'region' => 'eu1',
            'webhook_url' => '',
        ],
    ];
}

/**
 * Test platform connection
 */
function test_platform_connection(string $platform, array $settings): array {
    $platformSettings = $settings[$platform] ?? [];
    
    switch ($platform) {
        case 'n8n':
            $baseUrl = $platformSettings['base_url'] ?? '';
            if (empty($baseUrl)) {
                return ['success' => false, 'message' => 'Base URL not configured'];
            }
            
            $url = rtrim($baseUrl, '/') . '/healthz';
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            
            // Add auth header if API key set
            $apiKey = $platformSettings['api_key'] ?? '';
            if (!empty($apiKey)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-N8N-API-KEY: ' . $apiKey]);
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if (!empty($error)) {
                return ['success' => false, 'message' => 'Connection error: ' . $error];
            }
            
            if ($httpCode === 200) {
                return ['success' => true, 'message' => 'n8n connection successful!'];
            }
            return ['success' => false, 'message' => 'n8n returned HTTP ' . $httpCode];
            
        case 'zapier':
            $webhookUrl = $platformSettings['webhook_url'] ?? '';
            if (empty($webhookUrl)) {
                return ['success' => false, 'message' => 'Webhook URL not configured'];
            }
            
            // Test webhook with a ping
            $ch = curl_init($webhookUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode(['test' => true, 'source' => 'cms_connection_test']),
                CURLOPT_TIMEOUT => 10,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if (!empty($error)) {
                return ['success' => false, 'message' => 'Connection error: ' . $error];
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return ['success' => true, 'message' => 'Zapier webhook reachable!'];
            }
            return ['success' => false, 'message' => 'Zapier returned HTTP ' . $httpCode];
            
        case 'make':
            $apiKey = $platformSettings['api_key'] ?? '';
            if (empty($apiKey)) {
                return ['success' => false, 'message' => 'API key not configured'];
            }
            
            $region = $platformSettings['region'] ?? 'eu1';
            $url = "https://{$region}.make.com/api/v2/users/me";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Token ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 10,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if (!empty($error)) {
                return ['success' => false, 'message' => 'Connection error: ' . $error];
            }
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $name = $data['name'] ?? 'Unknown';
                return ['success' => true, 'message' => 'Make.com connected as: ' . $name];
            }
            return ['success' => false, 'message' => 'Make.com returned HTTP ' . $httpCode];
            
        default:
            return ['success' => false, 'message' => 'Unknown platform'];
    }
}

// Load settings
$settings = load_automation_settings();
$message = '';
$messageType = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_settings':
            // n8n
            $settings['n8n']['enabled'] = isset($_POST['n8n_enabled']);
            $settings['n8n']['base_url'] = trim($_POST['n8n_base_url'] ?? '');
            $settings['n8n']['auth_type'] = $_POST['n8n_auth_type'] ?? 'apikey';
            if (!empty($_POST['n8n_api_key'])) {
                $settings['n8n']['api_key'] = trim($_POST['n8n_api_key']);
            }
            $settings['n8n']['webhook_url'] = trim($_POST['n8n_webhook_url'] ?? '');
            
            // Zapier
            $settings['zapier']['enabled'] = isset($_POST['zapier_enabled']);
            $settings['zapier']['webhook_url'] = trim($_POST['zapier_webhook_url'] ?? '');
            if (!empty($_POST['zapier_api_key'])) {
                $settings['zapier']['api_key'] = trim($_POST['zapier_api_key']);
            }
            
            // Make
            $settings['make']['enabled'] = isset($_POST['make_enabled']);
            if (!empty($_POST['make_api_key'])) {
                $settings['make']['api_key'] = trim($_POST['make_api_key']);
            }
            $settings['make']['team_id'] = trim($_POST['make_team_id'] ?? '');
            $settings['make']['region'] = $_POST['make_region'] ?? 'eu1';
            $settings['make']['webhook_url'] = trim($_POST['make_webhook_url'] ?? '');
            
            if (save_automation_settings($settings)) {
                $message = 'Settings saved successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to save settings. Check file permissions.';
                $messageType = 'error';
            }
            break;
            
        case 'test_connection':
            $platform = $_POST['platform'] ?? '';
            if ($platform) {
                // Update settings from form before testing
                $testSettings = $settings;
                
                if ($platform === 'n8n') {
                    $testSettings['n8n']['base_url'] = trim($_POST['n8n_base_url'] ?? '');
                    $testSettings['n8n']['api_key'] = trim($_POST['n8n_api_key'] ?? '') ?: ($settings['n8n']['api_key'] ?? '');
                } elseif ($platform === 'zapier') {
                    $testSettings['zapier']['webhook_url'] = trim($_POST['zapier_webhook_url'] ?? '');
                } elseif ($platform === 'make') {
                    $testSettings['make']['api_key'] = trim($_POST['make_api_key'] ?? '') ?: ($settings['make']['api_key'] ?? '');
                    $testSettings['make']['region'] = $_POST['make_region'] ?? 'eu1';
                }
                
                $result = test_platform_connection($platform, $testSettings);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            break;
    }
}

$pageTitle = 'Automation Settings';
require_once CMS_ROOT . '/admin/includes/header.php';
?>

<style>
.platforms-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
.platform-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.platform-header { padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid var(--border); }
.platform-icon { font-size: 2rem; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 10px; }
.platform-icon.n8n { background: #ff6d5a20; }
.platform-icon.zapier { background: #ff4a0020; }
.platform-icon.make { background: #6d28d920; }
.platform-info { flex: 1; }
.platform-name { font-weight: 600; font-size: 1.125rem; }
.platform-desc { font-size: 0.8125rem; color: var(--text-muted); }
.platform-toggle { position: relative; width: 50px; height: 28px; }
.platform-toggle input { opacity: 0; width: 0; height: 0; }
.platform-toggle .slider { position: absolute; cursor: pointer; inset: 0; background-color: #ccc; transition: .3s; border-radius: 28px; }
.platform-toggle .slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
.platform-toggle input:checked + .slider { background-color: var(--success); }
.platform-toggle input:checked + .slider:before { transform: translateX(22px); }
.platform-body { padding: 1.25rem; }
.platform-body.disabled { opacity: 0.5; pointer-events: none; }
.field-row { margin-bottom: 1rem; }
.field-row label { display: block; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.375rem; color: var(--text-muted); }
.field-row input, .field-row select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; }
.field-row input:focus, .field-row select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
.field-row small { display: block; margin-top: 0.25rem; font-size: 0.75rem; color: var(--text-muted); }
.api-key-field { position: relative; }
.api-key-field input { padding-right: 60px; font-family: monospace; }
.api-key-field .toggle-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.75rem; }
.test-btn { width: 100%; padding: 0.625rem; background: var(--primary); color: white; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; margin-top: 0.5rem; }
.test-btn:hover { background: var(--primary-dark); }

.info-box { background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; }
.info-box h3 { font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.5rem; color: #0369a1; }
.info-box p { font-size: 0.8125rem; color: #0c4a6e; margin: 0; }
.info-box ul { margin: 0.5rem 0 0 1.25rem; font-size: 0.8125rem; color: #0c4a6e; }

.save-bar { background: var(--sidebar-bg); color: white; padding: 1rem 1.5rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; }
.save-bar .info { font-size: 0.875rem; opacity: 0.8; }
.save-bar button { background: var(--success); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9375rem; }
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <?= $messageType === 'success' ? '✅' : '❌' ?> <?= esc($message) ?>
</div>
<?php endif; ?>

<div class="info-box">
    <h3>🔗 Automation Platform Integration</h3>
    <p>Connect your CMS to workflow automation platforms to trigger actions based on CMS events.</p>
    <ul>
        <li><strong>n8n</strong> - Self-hosted, open source workflow automation</li>
        <li><strong>Zapier</strong> - Connect 5000+ apps with no-code automations</li>
        <li><strong>Make.com</strong> - Visual automation platform (formerly Integromat)</li>
    </ul>
</div>

<form method="POST" id="automation-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_settings">
    
    <div class="platforms-grid">
        <!-- n8n -->
        <div class="platform-card">
            <div class="platform-header">
                <div class="platform-icon n8n">⚡</div>
                <div class="platform-info">
                    <div class="platform-name">n8n</div>
                    <div class="platform-desc">Self-hosted workflow automation</div>
                </div>
                <label class="platform-toggle">
                    <input type="checkbox" name="n8n_enabled" <?= !empty($settings['n8n']['enabled']) ? 'checked' : '' ?>
                           onchange="togglePlatform('n8n', this.checked)">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="platform-body <?= empty($settings['n8n']['enabled']) ? 'disabled' : '' ?>" id="n8n-body">
                <div class="field-row">
                    <label>Base URL *</label>
                    <input type="url" name="n8n_base_url" 
                           value="<?= esc($settings['n8n']['base_url'] ?? '') ?>"
                           placeholder="https://your-n8n-instance.com">
                    <small>Your n8n server URL (without trailing slash)</small>
                </div>
                
                <div class="field-row">
                    <label>Authentication Type</label>
                    <select name="n8n_auth_type">
                        <option value="none" <?= ($settings['n8n']['auth_type'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                        <option value="apikey" <?= ($settings['n8n']['auth_type'] ?? '') === 'apikey' ? 'selected' : '' ?>>API Key</option>
                        <option value="basic" <?= ($settings['n8n']['auth_type'] ?? '') === 'basic' ? 'selected' : '' ?>>Basic Auth</option>
                    </select>
                </div>
                
                <div class="field-row">
                    <label>API Key</label>
                    <div class="api-key-field">
                        <input type="password" name="n8n_api_key" 
                               placeholder="<?= !empty($settings['n8n']['api_key']) ? '••••••••••••' : 'Enter API key' ?>"
                               id="n8n-key">
                        <button type="button" class="toggle-btn" onclick="toggleVisibility('n8n-key')">Show</button>
                    </div>
                    <small>Leave blank to keep existing key</small>
                </div>
                
                <div class="field-row">
                    <label>Webhook URL (optional)</label>
                    <input type="url" name="n8n_webhook_url" 
                           value="<?= esc($settings['n8n']['webhook_url'] ?? '') ?>"
                           placeholder="https://your-n8n.com/webhook/xxx">
                    <small>Default webhook endpoint for CMS events</small>
                </div>
                
                <button type="button" class="test-btn" onclick="testConnection('n8n')">🔌 Test Connection</button>
            </div>
        </div>
        
        <!-- Zapier -->
        <div class="platform-card">
            <div class="platform-header">
                <div class="platform-icon zapier">🔶</div>
                <div class="platform-info">
                    <div class="platform-name">Zapier</div>
                    <div class="platform-desc">Connect 5000+ apps</div>
                </div>
                <label class="platform-toggle">
                    <input type="checkbox" name="zapier_enabled" <?= !empty($settings['zapier']['enabled']) ? 'checked' : '' ?>
                           onchange="togglePlatform('zapier', this.checked)">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="platform-body <?= empty($settings['zapier']['enabled']) ? 'disabled' : '' ?>" id="zapier-body">
                <div class="field-row">
                    <label>Webhook URL *</label>
                    <input type="url" name="zapier_webhook_url" 
                           value="<?= esc($settings['zapier']['webhook_url'] ?? '') ?>"
                           placeholder="https://hooks.zapier.com/hooks/catch/xxx/yyy">
                    <small>Create a Zap with "Webhooks by Zapier" trigger, then paste the URL here</small>
                </div>
                
                <div class="field-row">
                    <label>API Key (optional)</label>
                    <div class="api-key-field">
                        <input type="password" name="zapier_api_key" 
                               placeholder="<?= !empty($settings['zapier']['api_key']) ? '••••••••••••' : 'For advanced integrations' ?>"
                               id="zapier-key">
                        <button type="button" class="toggle-btn" onclick="toggleVisibility('zapier-key')">Show</button>
                    </div>
                    <small>Only needed for Zapier NLA or custom integrations</small>
                </div>
                
                <button type="button" class="test-btn" onclick="testConnection('zapier')">🔌 Test Webhook</button>
            </div>
        </div>
        
        <!-- Make.com -->
        <div class="platform-card">
            <div class="platform-header">
                <div class="platform-icon make">🟣</div>
                <div class="platform-info">
                    <div class="platform-name">Make.com</div>
                    <div class="platform-desc">Visual automation platform</div>
                </div>
                <label class="platform-toggle">
                    <input type="checkbox" name="make_enabled" <?= !empty($settings['make']['enabled']) ? 'checked' : '' ?>
                           onchange="togglePlatform('make', this.checked)">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="platform-body <?= empty($settings['make']['enabled']) ? 'disabled' : '' ?>" id="make-body">
                <div class="field-row">
                    <label>API Key *</label>
                    <div class="api-key-field">
                        <input type="password" name="make_api_key" 
                               placeholder="<?= !empty($settings['make']['api_key']) ? '••••••••••••' : 'Enter API key' ?>"
                               id="make-key">
                        <button type="button" class="toggle-btn" onclick="toggleVisibility('make-key')">Show</button>
                    </div>
                    <small>Get from Make.com → Profile → API</small>
                </div>
                
                <div class="field-row">
                    <label>Region</label>
                    <select name="make_region">
                        <option value="eu1" <?= ($settings['make']['region'] ?? '') === 'eu1' ? 'selected' : '' ?>>EU (eu1.make.com)</option>
                        <option value="eu2" <?= ($settings['make']['region'] ?? '') === 'eu2' ? 'selected' : '' ?>>EU 2 (eu2.make.com)</option>
                        <option value="us1" <?= ($settings['make']['region'] ?? '') === 'us1' ? 'selected' : '' ?>>US (us1.make.com)</option>
                        <option value="us2" <?= ($settings['make']['region'] ?? '') === 'us2' ? 'selected' : '' ?>>US 2 (us2.make.com)</option>
                    </select>
                </div>
                
                <div class="field-row">
                    <label>Team ID (optional)</label>
                    <input type="text" name="make_team_id" 
                           value="<?= esc($settings['make']['team_id'] ?? '') ?>"
                           placeholder="e.g., 12345">
                </div>
                
                <div class="field-row">
                    <label>Webhook URL (optional)</label>
                    <input type="url" name="make_webhook_url" 
                           value="<?= esc($settings['make']['webhook_url'] ?? '') ?>"
                           placeholder="https://hook.eu1.make.com/xxx">
                    <small>For scenarios triggered by CMS events</small>
                </div>
                
                <button type="button" class="test-btn" onclick="testConnection('make')">🔌 Test Connection</button>
            </div>
        </div>
    </div>
    
    <div class="save-bar">
        <div class="info">
            <?php if (!empty($settings['updated_at'])): ?>
            Last saved: <?= esc($settings['updated_at']) ?>
            <?php else: ?>
            Settings not yet configured
            <?php endif; ?>
        </div>
        <button type="submit">💾 Save All Settings</button>
    </div>
</form>

<script>
function togglePlatform(platform, enabled) {
    const body = document.getElementById(platform + '-body');
    if (body) {
        body.classList.toggle('disabled', !enabled);
    }
}

function toggleVisibility(inputId) {
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

function testConnection(platform) {
    const mainForm = document.getElementById('automation-form');
    const formData = new FormData(mainForm);
    
    const testForm = document.createElement('form');
    testForm.method = 'POST';
    testForm.style.display = 'none';
    
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        testForm.appendChild(input);
    }
    
    // Override action
    const actionInput = testForm.querySelector('[name="action"]');
    if (actionInput) actionInput.value = 'test_connection';
    
    // Add platform
    const platformInput = document.createElement('input');
    platformInput.type = 'hidden';
    platformInput.name = 'platform';
    platformInput.value = platform;
    testForm.appendChild(platformInput);
    
    document.body.appendChild(testForm);
    testForm.submit();
}
</script>

<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
