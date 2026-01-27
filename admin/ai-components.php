<?php
/**
 * AI Component Builder - Admin Tool
 * Generate reusable HTML/CSS components using Hugging Face
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_hf.php';
require_once CMS_ROOT . '/core/ai_models.php';
require_once CMS_ROOT . '/core/ai_content.php';
require_once CMS_ROOT . '/core/ai_components.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$form = [
    'name' => '', 'purpose' => '', 'target_page' => '', 'layout_context' => '',
    'style' => '', 'brand_voice' => '', 'language' => 'en', 'cta' => '', 'notes' => '',
];

$component = null;
$generatedJson = '';
$generatorError = null;

// Multi-provider support: default to huggingface
$selectedProvider = $_POST['ai_provider'] ?? 'huggingface';
$selectedModel = $_POST['ai_model'] ?? '';

// Check if at least one provider is available
$hfConfigured = function_exists('ai_hf_is_configured') ? ai_hf_is_configured(ai_hf_config_load()) : false;
$anyProviderAvailable = $hfConfigured || !empty(ai_get_all_providers());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate_component') {
    csrf_validate_or_403();
    foreach (['name','purpose','target_page','layout_context','style','brand_voice','language','cta','notes'] as $k) {
        $form[$k] = trim((string)($_POST[$k] ?? ''));
    }

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

    $result = ai_components_generate($form, $provider, $model);
    if ($result['ok']) {
        $component = $result['component'];
        $generatedJson = $result['json'];
    } else {
        $generatorError = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Component Builder - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px}
.alert-info{background:rgba(137,180,250,.15);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.alert-warning{background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
.form-group small{display:block;margin-top:4px;font-size:11px;color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 20px;font-size:14px;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:300px;overflow:auto}
textarea.code{font-family:monospace;font-size:12px;background:var(--bg);resize:vertical}
h2{font-size:16px;margin:24px 0 12px;color:var(--text)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🧩',
    'title' => 'AI Component Builder',
    'description' => 'Generate reusable HTML/CSS components',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'AI Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<div class="card">
<div class="card-head">ℹ️ About this tool</div>
<div class="card-body">
<p>This tool generates standalone sections (hero, feature grid, CTA, pricing table, etc.). All text comes in the selected language. You paste the HTML into your page builder and the CSS into your theme stylesheet.</p>
</div>
</div>

<?php if (!$anyProviderAvailable): ?>
<div class="alert alert-warning">⚠️ <strong>Warning:</strong> No AI providers configured. Please configure at least one provider in AI Settings.</div>
<?php endif; ?>

<?php if ($generatorError !== null): ?>
<div class="alert alert-danger">❌ <strong>Error:</strong> <?= esc($generatorError) ?></div>
<?php endif; ?>

<div class="card">
<div class="card-head">📝 Component Specification</div>
<div class="card-body">
<form method="post">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate_component">

<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('ai_provider', 'ai_model', $selectedProvider, $selectedModel) ?>
<small>Select AI provider. HuggingFace is default for component generation.</small>
</div>

<div class="form-group">
<label>Component Name <span style="color:var(--danger)">*</span></label>
<input type="text" name="name" value="<?= esc($form['name']) ?>" placeholder="e.g. Hero section for CMS landing" required>
</div>

<div class="form-group">
<label>Purpose <span style="color:var(--danger)">*</span></label>
<input type="text" name="purpose" value="<?= esc($form['purpose']) ?>" placeholder="e.g. grab attention and drive demo clicks" required>
<small>What should this component achieve?</small>
</div>

<div class="form-group">
<label>Target Page</label>
<input type="text" name="target_page" value="<?= esc($form['target_page']) ?>" placeholder="e.g. home page, pricing page">
</div>

<div class="form-group">
<label>Layout Context</label>
<input type="text" name="layout_context" value="<?= esc($form['layout_context']) ?>" placeholder="e.g. full-width section, inside 2-column layout">
</div>

<div class="form-group">
<label>Visual Style</label>
<textarea name="style" rows="2" placeholder="e.g. minimal, clean, lots of white space"><?= esc($form['style']) ?></textarea>
</div>

<div class="form-group">
<label>Brand Voice</label>
<input type="text" name="brand_voice" value="<?= esc($form['brand_voice']) ?>" placeholder="e.g. friendly, expert, trustworthy">
</div>

<div class="form-group">
<label>Language</label>
<select name="language">
<option value="en" <?= $form['language']==='en'?'selected':'' ?>>English</option>
<option value="pl" <?= $form['language']==='pl'?'selected':'' ?>>Polski</option>
<option value="de" <?= $form['language']==='de'?'selected':'' ?>>Deutsch</option>
<option value="es" <?= $form['language']==='es'?'selected':'' ?>>Español</option>
<option value="fr" <?= $form['language']==='fr'?'selected':'' ?>>Français</option>
</select>
</div>

<div class="form-group">
<label>Main Call to Action</label>
<input type="text" name="cta" value="<?= esc($form['cta']) ?>" placeholder="e.g. Request a demo">
</div>

<div class="form-group">
<label>Additional Notes</label>
<textarea name="notes" rows="2" placeholder="e.g. no JS, works on dark background"><?= esc($form['notes']) ?></textarea>
</div>

<button type="submit" class="btn btn-primary" <?= !$anyProviderAvailable ? 'disabled' : '' ?>>🚀 Generate Component</button>
</form>
</div>
</div>


<?php if (is_array($component) && $generatedJson !== ''): ?>
<div class="card">
<div class="card-head">✅ Generated Component</div>
<div class="card-body">
<div class="alert alert-success">
<strong>Component:</strong> <?= esc($component['name'] ?? '') ?><br>
<strong>Preview:</strong> <?= esc($component['preview_text'] ?? '') ?><br>
<strong>Language:</strong> <?= esc($form['language']) ?>
</div>

<?php if (!empty($component['description'])): ?>
<h2>📖 Description</h2>
<p><?= nl2br(esc($component['description'])) ?></p>
<?php endif; ?>

<h2>📄 Component HTML</h2>
<p style="color:var(--muted);margin-bottom:8px">Paste this into your page/builder HTML.</p>
<textarea class="code" rows="10" onclick="this.select()" readonly style="width:100%"><?= esc($component['html'] ?? '') ?></textarea>

<h2>🎨 Component CSS</h2>
<p style="color:var(--muted);margin-bottom:8px">Paste this into your theme stylesheet.</p>
<textarea class="code" rows="10" onclick="this.select()" readonly style="width:100%"><?= esc($component['css'] ?? '') ?></textarea>

<h2>📦 Raw JSON</h2>
<p style="color:var(--muted);margin-bottom:8px">For automation (component library, n8n workflows, codegen).</p>
<textarea class="code" rows="8" onclick="this.select()" readonly style="width:100%"><?= esc($generatedJson) ?></textarea>

<?php if (!empty($component['accessibility_notes'])): ?>
<h2>♿ Accessibility Notes</h2>
<p><?= nl2br(esc($component['accessibility_notes'])) ?></p>
<?php endif; ?>

<?php if (!empty($component['usage_notes'])): ?>
<h2>💡 Usage Notes</h2>
<p><?= nl2br(esc($component['usage_notes'])) ?></p>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

</div>
</body>
</html>
