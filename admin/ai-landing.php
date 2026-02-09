<?php
/**
 * AI Landing Page Generator - Modern Dark UI
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_landing.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();


function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$form = ['project'=>'','audience'=>'','goal'=>'','tone'=>'','features'=>'','language'=>'en','length'=>'medium'];
$generatedHtml = '';
$error = null;

// Multi-provider support: default to huggingface
$selectedProvider = $_POST['ai_provider'] ?? 'huggingface';
$selectedModel = $_POST['ai_model'] ?? '';

// Check if at least one provider is available
$hfConfig = function_exists('ai_hf_config_load') ? ai_hf_config_load() : [];
$hfOk = function_exists('ai_hf_is_configured') ? ai_hf_is_configured($hfConfig) : false;
$anyProviderAvailable = $hfOk || !empty(ai_get_all_providers());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate') {
    csrf_validate_or_403();
    foreach (array_keys($form) as $k) $form[$k] = trim($_POST[$k] ?? '');

    // Get provider/model from form
    $provider = trim($_POST['ai_provider'] ?? 'huggingface');
    $model = trim($_POST['ai_model'] ?? '');

    // Validate provider, fallback to huggingface
    if (!ai_is_valid_provider($provider)) {
        $provider = 'huggingface';
    }

    // Get default model if not specified
    if ($model === '' || !ai_is_valid_provider_model($provider, $model)) {
        $model = ai_get_provider_default_model($provider);
    }

    $selectedProvider = $provider;
    $selectedModel = $model;

    $result = ai_landing_generate($form, $provider, $model);
    if ($result['ok']) {
        $generatedHtml = $result['html'];
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Landing Page - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;transition:.15s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
.form-group textarea{resize:vertical;min-height:120px;font-family:monospace}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;font-size:14px;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;font-family:monospace;font-size:12px;max-height:400px;overflow:auto;white-space:pre-wrap}
.preview-frame{background:var(--bg2);border-radius:10px;padding:20px;min-height:300px;overflow:auto}
.tabs{display:flex;gap:8px;margin-bottom:16px}
.tab{padding:8px 16px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text2);cursor:pointer;font-size:12px}
.tab.active{background:var(--accent);color:#000;border-color:var(--accent)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🚀',
    'title' => 'Landing Page Generator',
    'description' => 'Create landing pages with AI',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if (!$anyProviderAvailable): ?>
<div class="alert" style="background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)"><span>⚠️</span><span>No AI providers configured. <a href="/admin/ai-settings.php" style="color:inherit">Configure one</a> first.</span></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><span>❌</span><span><?= esc($error) ?></span></div>
<?php endif; ?>

<?php if ($generatedHtml): ?>
<div class="alert alert-success"><span>✅</span><span>Landing page generated successfully!</span></div>
<?php endif; ?>

<div class="grid">
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Specification</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate">

<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('ai_provider', 'ai_model', $selectedProvider, $selectedModel) ?>
<small style="color:var(--muted);margin-top:4px;display:block">Select AI provider. HuggingFace is default.</small>
</div>

<div class="form-group">
<label>Project / Product Name *</label>
<input type="text" name="project" value="<?= esc($form['project']) ?>" placeholder="e.g. Polish Saturday School CMS" required>
</div>

<div class="form-group">
<label>Target Audience</label>
<input type="text" name="audience" value="<?= esc($form['audience']) ?>" placeholder="e.g. small business owners">
</div>

<div class="form-group">
<label>Main Goal</label>
<input type="text" name="goal" value="<?= esc($form['goal']) ?>" placeholder="e.g. get demo requests">
</div>

<div class="form-group">
<label>Tone of Voice</label>
<input type="text" name="tone" value="<?= esc($form['tone']) ?>" placeholder="e.g. friendly, professional">
</div>

<div class="form-group">
<label>Key Features / Benefits</label>
<textarea name="features" placeholder="- Easy content management
- Multi-language support
- SEO optimized"><?= esc($form['features']) ?></textarea>
</div>

<div class="form-row">
<div class="form-group">
<label>Language</label>
<select name="language">
<option value="en" <?= $form['language'] === 'en' ? 'selected' : '' ?>>English</option>
<option value="pl" <?= $form['language'] === 'pl' ? 'selected' : '' ?>>Polish</option>
<option value="de" <?= $form['language'] === 'de' ? 'selected' : '' ?>>German</option>
<option value="es" <?= $form['language'] === 'es' ? 'selected' : '' ?>>Spanish</option>
<option value="fr" <?= $form['language'] === 'fr' ? 'selected' : '' ?>>French</option>
</select>
</div>
<div class="form-group">
<label>Length</label>
<select name="length">
<option value="short" <?= $form['length'] === 'short' ? 'selected' : '' ?>>Short</option>
<option value="medium" <?= $form['length'] === 'medium' ? 'selected' : '' ?>>Medium</option>
<option value="long" <?= $form['length'] === 'long' ? 'selected' : '' ?>>Long</option>
</select>
</div>
</div>

<button type="submit" class="btn btn-primary" <?= !$anyProviderAvailable ? 'disabled' : '' ?>>🚀 Generate Landing Page</button>
</form>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Output</span></div>
<div class="card-body">
<?php if ($generatedHtml): ?>
<div class="tabs">
<button class="tab active" onclick="showTab('code')">📝 HTML Code</button>
<button class="tab" onclick="showTab('preview')">👁️ Preview</button>
</div>
<div id="tab-code">
<div class="output-box" onclick="navigator.clipboard?.writeText(this.textContent)"><?= esc($generatedHtml) ?></div>
<small style="color:var(--muted);margin-top:8px;display:block">Click to copy</small>
</div>
<div id="tab-preview" style="display:none">
<div class="preview-frame"><?= $generatedHtml ?></div>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px;color:var(--muted)">
<p style="font-size:48px;margin-bottom:12px">🚀</p>
<p>Fill in the form and generate your landing page</p>
</div>
<?php endif; ?>
</div>
</div>
</div>

<div style="margin-top:20px;display:flex;gap:12px">
<a href="/admin/hf-settings.php" class="btn" style="background:var(--bg3);color:var(--text);border:1px solid var(--border)">⚙️ HF Settings</a>
<a href="/admin/ai-copywriter.php" class="btn" style="background:var(--bg3);color:var(--text);border:1px solid var(--border)">✍️ Copywriter</a>
</div>
</div>

<script>
function showTab(t) {
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
    document.querySelector(`[onclick="showTab('${t}')"]`).classList.add('active');
    document.getElementById('tab-code').style.display = t === 'code' ? 'block' : 'none';
    document.getElementById('tab-preview').style.display = t === 'preview' ? 'block' : 'none';
}
</script>
</body>
</html>
