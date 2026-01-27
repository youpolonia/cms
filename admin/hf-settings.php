<?php
/**
 * Hugging Face Settings - Modern Dark UI
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit('Forbidden'); }

require_once CMS_ROOT . '/core/ai_hf.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$settings = ai_hf_load_settings();
$saveMsg = null;
$saveOk = null;
$healthResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_settings') {
        $new = [
            'enabled' => !empty($_POST['enabled']),
            'base_url' => trim($_POST['base_url'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'timeout' => (int)($_POST['timeout'] ?? 15),
        ];
        if (trim($_POST['api_token'] ?? '') !== '') {
            $new['api_token'] = trim($_POST['api_token']);
        }
        if (ai_hf_save_settings($new)) {
            $saveOk = true;
            $saveMsg = 'Settings saved successfully.';
            $settings = ai_hf_load_settings();
        } else {
            $saveOk = false;
            $saveMsg = 'Failed to save settings.';
        }
    } elseif ($action === 'health_check') {
        $healthResult = ai_hf_health_check();
    }
}

$testRun = isset($_GET['test']) && $_GET['test'] === '1';
$testResult = null;
if ($testRun) {
    $config = ai_hf_config_load();
    if (!ai_hf_is_configured($config)) {
        $testResult = ['ok' => false, 'error' => 'HF not configured'];
    } else {
        $testResult = ai_hf_infer($config, 'Test: respond with confirmation.', ['max_new_tokens' => 32]);
    }
}

$keyConfigured = !empty($settings['api_token']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hugging Face Settings - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:900px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-warning{background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.status-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
@media(max-width:600px){.status-grid{grid-template-columns:1fr}}
.status-item{background:var(--bg);border-radius:10px;padding:14px;display:flex;justify-content:space-between;align-items:center}
.status-item label{font-size:13px;color:var(--text2)}
.status-item span{font-weight:500}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:8px;color:var(--text2)}
.form-group input[type="text"],.form-group input[type="password"],.form-group input[type="number"]{width:100%;padding:12px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;transition:.15s}
.form-group input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.form-group small{display:block;margin-top:6px;font-size:12px;color:var(--muted)}
.form-check{display:flex;align-items:center;gap:10px;padding:14px;background:var(--bg);border-radius:10px;cursor:pointer}
.form-check input{width:18px;height:18px;accent-color:var(--accent)}
.form-check span{font-weight:500}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 20px;font-size:14px;font-weight:500;border:none;border-radius:10px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-success{background:var(--success);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.actions{display:flex;gap:12px;flex-wrap:wrap}
pre{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;font-size:12px;overflow-x:auto;margin-top:10px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🤗',
    'title' => 'Hugging Face Settings',
    'description' => 'AI API Configuration',
    'back_url' => '/admin/ai-seo-assistant',
    'back_text' => 'SEO Assistant',
    'gradient' => '#fab387, var(--warning-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if ($saveMsg): ?>
<div class="alert <?= $saveOk ? 'alert-success' : 'alert-danger' ?>">
<span><?= $saveOk ? '✅' : '❌' ?></span>
<span><?= esc($saveMsg) ?></span>
</div>
<?php endif; ?>

<?php if ($healthResult): ?>
<div class="alert <?= $healthResult['ok'] ? 'alert-success' : 'alert-danger' ?>">
<span><?= $healthResult['ok'] ? '✅' : '❌' ?></span>
<div>
<strong><?= $healthResult['ok'] ? 'Connection OK' : 'Connection Failed' ?></strong>
<?php if (!empty($healthResult['statusCode'])): ?> (HTTP <?= $healthResult['statusCode'] ?>)<?php endif; ?>
<?php if (!empty($healthResult['error'])): ?><br><small><?= esc($healthResult['error']) ?></small><?php endif; ?>
</div>
</div>
<?php endif; ?>

<?php if ($testRun && $testResult): ?>
<div class="alert <?= $testResult['ok'] ? 'alert-success' : 'alert-danger' ?>">
<span><?= $testResult['ok'] ? '✅' : '❌' ?></span>
<div>
<strong><?= $testResult['ok'] ? 'Test Inference Succeeded' : 'Test Inference Failed' ?></strong>
<?php if (!empty($testResult['error'])): ?><br><small><?= esc($testResult['error']) ?></small><?php endif; ?>
<?php if ($testResult['ok'] && !empty($testResult['json'])): ?>
<pre><?= esc(substr(json_encode($testResult['json'], JSON_PRETTY_PRINT), 0, 300)) ?></pre>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Current Status</span></div>
<div class="card-body">
<div class="status-grid">
<div class="status-item"><label>Status</label><span class="tag <?= $settings['enabled'] ? 'success' : 'muted' ?>"><?= $settings['enabled'] ? 'Enabled' : 'Disabled' ?></span></div>
<div class="status-item"><label>API Key</label><span class="tag <?= $keyConfigured ? 'success' : 'danger' ?>"><?= $keyConfigured ? 'Configured' : 'Not Set' ?></span></div>
<div class="status-item"><label>Base URL</label><span style="font-size:12px"><?= esc($settings['base_url'] ?: '—') ?></span></div>
<div class="status-item"><label>Model</label><span style="font-size:12px"><?= esc($settings['model'] ?: '—') ?></span></div>
</div>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>🔍</span> Connection Tests</span></div>
<div class="card-body">
<p style="color:var(--text2);margin-bottom:16px">Test your Hugging Face API connection before using AI features.</p>
<div class="actions">
<form method="POST" style="display:inline">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="health_check">
<button type="submit" class="btn btn-success">🩺 Health Check</button>
</form>
<a href="?test=1" class="btn btn-secondary">🧪 Test Inference</a>
</div>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>⚙️</span> Configuration</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="save_settings">

<div class="form-group">
<label class="form-check">
<input type="checkbox" name="enabled" value="1" <?= $settings['enabled'] ? 'checked' : '' ?>>
<span>Enable Hugging Face Integration</span>
</label>
</div>

<div class="form-group">
<label>Base URL</label>
<input type="text" name="base_url" value="<?= esc($settings['base_url']) ?>" placeholder="https://api-inference.huggingface.co">
<small>The Hugging Face Inference API endpoint</small>
</div>

<div class="form-group">
<label>Model</label>
<input type="text" name="model" value="<?= esc($settings['model']) ?>" placeholder="e.g., meta-llama/Llama-2-7b-chat-hf">
<small>Model identifier from Hugging Face Hub</small>
</div>

<div class="form-group">
<label>API Token</label>
<input type="password" name="api_token" placeholder="<?= $keyConfigured ? '••••••••••••••••' : 'Enter API token' ?>">
<small>Leave blank to keep existing token</small>
</div>

<div class="form-group">
<label>Timeout (seconds)</label>
<input type="number" name="timeout" value="<?= (int)$settings['timeout'] ?>" min="1" max="120">
</div>

<button type="submit" class="btn btn-primary">💾 Save Settings</button>
</form>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📖</span> Help</span></div>
<div class="card-body">
<p style="color:var(--text2);margin-bottom:12px">To use Hugging Face AI features:</p>
<ol style="margin-left:20px;color:var(--text2);line-height:2">
<li>Get an API token from <a href="https://huggingface.co/settings/tokens" target="_blank" style="color:var(--accent)">huggingface.co/settings/tokens</a></li>
<li>Choose a model from <a href="https://huggingface.co/models" target="_blank" style="color:var(--accent)">Hugging Face Hub</a></li>
<li>Enter your credentials above and save</li>
<li>Run a test inference to verify</li>
</ol>
</div>
</div>

</div>
</body>
</html>
