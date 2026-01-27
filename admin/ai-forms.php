<?php
/**
 * AI Forms Generator - Modern Dark UI
 */
require_once realpath(__DIR__ . '/../config.php');
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_forms.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit('Forbidden'); }

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$form = ['name'=>'','purpose'=>'','audience'=>'','form_type'=>'contact','fields_hint'=>'','integrations'=>'','language'=>'en','notes'=>''];
$schema = null;
$json = '';
$error = null;

// Multi-provider support: default to huggingface
$selectedProvider = $_POST['ai_provider'] ?? 'huggingface';
$selectedModel = $_POST['ai_model'] ?? '';

// Check if at least one provider is available
$hfConfig = function_exists('ai_hf_config_load') ? ai_hf_config_load() : [];
$hfOk = function_exists('ai_hf_is_configured') ? ai_hf_is_configured($hfConfig) : false;
$anyProviderAvailable = $hfOk || !empty(ai_get_all_providers());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate_form_schema') {
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

    $result = ai_forms_generate_schema($form, $provider, $model);
    if ($result['ok']) { $schema = $result['schema']; $json = $result['json']; }
    else { $error = $result['error']; }
}

$types = ['contact'=>'Contact Form','feedback'=>'Feedback','survey'=>'Survey','registration'=>'Registration','order'=>'Order Form','booking'=>'Booking','newsletter'=>'Newsletter','application'=>'Application'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Forms Generator - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.success{background:rgba(166,227,161,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px}
.alert-warning{background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
.form-group textarea{resize:vertical;min-height:80px}
.form-group small{display:block;margin-top:4px;font-size:11px;color:var(--muted)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:12px 20px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:400px;overflow:auto}
.field-item{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:8px}
.field-item .name{font-weight:600;color:var(--accent)}
.field-item .type{font-size:11px;color:var(--muted);margin-left:8px}
.field-item .label{font-size:13px;margin-top:4px}
.field-item .meta{font-size:11px;color:var(--text2);margin-top:4px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📋',
    'title' => 'AI Forms Generator',
    'description' => 'Generate form schemas with AI',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--success-color), var(--cyan)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if (!$anyProviderAvailable): ?>
<div class="alert alert-warning"><span>⚠️</span><span>No AI providers configured. <a href="/admin/ai-settings.php" style="color:inherit">Configure one</a> first.</span></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><span>❌</span><span><?= esc($error) ?></span></div>
<?php endif; ?>

<?php if (!$schema): ?>
<div class="alert alert-info"><span>💡</span><span><strong>Phase 1:</strong> Generate form schemas. Preview and copy JSON for integration.</span></div>
<?php endif; ?>

<div class="grid">
<div>
<div class="card">
<div class="card-head"><span class="card-title"><span>⚙️</span> Form Settings</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate_form_schema">

<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('ai_provider', 'ai_model', $selectedProvider, $selectedModel) ?>
<small>Select AI provider. HuggingFace is default.</small>
</div>

<div class="form-row">
<div class="form-group"><label>Form Name *</label><input type="text" name="name" value="<?= esc($form['name']) ?>" required placeholder="e.g., Contact Form"></div>
<div class="form-group"><label>Form Type</label><select name="form_type"><?php foreach ($types as $k => $v): ?><option value="<?= $k ?>" <?= $form['form_type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
</div>

<div class="form-group"><label>Purpose / Description</label><textarea name="purpose" rows="2" placeholder="What is this form for?"><?= esc($form['purpose']) ?></textarea></div>

<div class="form-row">
<div class="form-group"><label>Target Audience</label><input type="text" name="audience" value="<?= esc($form['audience']) ?>" placeholder="e.g., customers, students"></div>
<div class="form-group"><label>Language</label><select name="language"><option value="en" <?= $form['language'] === 'en' ? 'selected' : '' ?>>English</option><option value="pl" <?= $form['language'] === 'pl' ? 'selected' : '' ?>>Polski</option><option value="de" <?= $form['language'] === 'de' ? 'selected' : '' ?>>Deutsch</option><option value="es" <?= $form['language'] === 'es' ? 'selected' : '' ?>>Español</option></select></div>
</div>

<div class="form-group"><label>Fields Hint</label><textarea name="fields_hint" rows="2" placeholder="e.g., name, email, phone, message"><?= esc($form['fields_hint']) ?></textarea><small>Suggest fields to include</small></div>

<div class="form-group"><label>Integrations</label><input type="text" name="integrations" value="<?= esc($form['integrations']) ?>" placeholder="e.g., email notification, CRM"><small>Optional: where should data go?</small></div>

<div class="form-group"><label>Additional Notes</label><textarea name="notes" rows="2" placeholder="Any special requirements..."><?= esc($form['notes']) ?></textarea></div>

<button type="submit" class="btn btn-primary" <?= !$anyProviderAvailable ? 'disabled' : '' ?>>✨ Generate Form Schema</button>
</form>
</div>
</div>
</div>

<div>
<?php if ($schema): ?>
<div class="card">
<div class="card-head success"><span class="card-title"><span>✅</span> Generated Schema</span></div>
<div class="card-body">
<h4 style="font-size:14px;margin-bottom:12px"><?= esc($schema['name'] ?? 'Form') ?></h4>
<?php if (!empty($schema['description'])): ?><p style="font-size:12px;color:var(--text2);margin-bottom:16px"><?= esc($schema['description']) ?></p><?php endif; ?>

<h5 style="font-size:12px;color:var(--muted);margin-bottom:8px">FIELDS (<?= count($schema['fields'] ?? []) ?>)</h5>
<?php foreach (($schema['fields'] ?? []) as $f): ?>
<div class="field-item">
<span class="name"><?= esc($f['name'] ?? '') ?></span>
<span class="type"><?= esc($f['type'] ?? 'text') ?></span>
<?php if (!empty($f['required'])): ?><span style="color:var(--danger);font-size:10px;margin-left:4px">*</span><?php endif; ?>
<div class="label"><?= esc($f['label'] ?? '') ?></div>
<?php if (!empty($f['placeholder'])): ?><div class="meta">Placeholder: <?= esc($f['placeholder']) ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> JSON Schema</span></div>
<div class="card-body">
<div class="output-box" onclick="navigator.clipboard?.writeText(this.textContent)"><?= esc($json) ?></div>
<small style="color:var(--muted);display:block;margin-top:8px">Click to copy</small>
</div>
</div>
<?php else: ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Preview</span></div>
<div class="card-body" style="text-align:center;padding:60px;color:var(--muted)">
<p style="font-size:32px;margin-bottom:12px">📋</p>
<p>Generated form schema will appear here</p>
</div>
</div>
<?php endif; ?>
</div>
</div>

<div style="margin-top:20px">
<a href="/admin/ai-landing-generator.php" class="btn btn-secondary">🚀 Landing Generator</a>
<a href="/admin/hf-settings.php" class="btn btn-secondary">⚙️ HF Settings</a>
</div>
</div>
</body>
</html>
