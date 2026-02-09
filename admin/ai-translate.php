<?php
/**
 * AI Translation - Modern Dark UI
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
require_once CMS_ROOT . '/core/ai_translate.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();


function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$form = [
    'source_language' => 'auto', 'target_language' => 'en', 'content_type' => 'generic',
    'original_title' => '', 'original_body' => '', 'original_excerpt' => '',
    'original_meta_title' => '', 'original_meta_description' => '', 'notes' => ''
];
$translation = null;
$generatedJson = '';
$error = null;

// Multi-provider support: default to huggingface
$selectedProvider = $_POST['ai_provider'] ?? 'huggingface';
$selectedModel = $_POST['ai_model'] ?? '';

// Check if at least one provider is available
$hfConfig = function_exists('ai_hf_config_load') ? ai_hf_config_load() : [];
$hfOk = function_exists('ai_hf_is_configured') ? ai_hf_is_configured($hfConfig) : false;
$anyProviderAvailable = $hfOk || !empty(ai_get_all_providers());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate_translation') {
    csrf_validate_or_403();
    foreach (array_keys($form) as $k) {
        $form[$k] = trim($_POST[$k] ?? '');
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

    $result = ai_translate_generate($form, $provider, $model);
    if ($result['ok']) {
        $translation = $result['translation'];
        $generatedJson = $result['json'];
    } else {
        $error = $result['error'];
    }
}

$langs = ['auto'=>'Auto-detect','en'=>'English','pl'=>'Polski','de'=>'Deutsch','es'=>'Español','fr'=>'Français','it'=>'Italiano','pt'=>'Português'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Translate - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:800px){.grid{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.success{background:rgba(166,227,161,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px}
.alert-warning{background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:8px;color:var(--text2)}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:12px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;transition:.15s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.form-group textarea{font-family:monospace;font-size:13px;resize:vertical}
.form-group small{display:block;margin-top:6px;font-size:12px;color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;font-size:14px;font-weight:600;border:none;border-radius:10px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;font-family:monospace;font-size:12px;white-space:pre-wrap;max-height:300px;overflow:auto}
.result-field{padding:14px;background:var(--bg);border-radius:10px;margin-bottom:12px}
.result-field label{font-size:11px;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:6px}
.row-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🌐',
    'title' => 'AI Translation',
    'description' => 'Translate content with AI',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--cyan), var(--accent-color)',
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

<?php if ($translation === null && !$error): ?>
<div class="alert alert-info"><span>💡</span><span><strong>Phase 1:</strong> Copy-paste workflow. Paste content, get translation, copy results into your pages.</span></div>
<?php endif; ?>

<div class="grid">
<div>
<!-- Input Form -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Original Content</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate_translation">

<div class="form-group">
<label>AI Provider & Model</label>
<?= ai_render_dual_selector('ai_provider', 'ai_model', $selectedProvider, $selectedModel) ?>
<small>Select AI provider. HuggingFace is default.</small>
</div>

<div class="row-2">
<div class="form-group">
<label>Source Language</label>
<select name="source_language">
<?php foreach ($langs as $k => $v): ?>
<option value="<?= $k ?>" <?= $form['source_language'] === $k ? 'selected' : '' ?>><?= $v ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group">
<label>Target Language *</label>
<select name="target_language" required>
<?php foreach ($langs as $k => $v): if ($k === 'auto') continue; ?>
<option value="<?= $k ?>" <?= $form['target_language'] === $k ? 'selected' : '' ?>><?= $v ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<div class="form-group">
<label>Content Type</label>
<select name="content_type">
<option value="generic" <?= $form['content_type'] === 'generic' ? 'selected' : '' ?>>Generic</option>
<option value="page" <?= $form['content_type'] === 'page' ? 'selected' : '' ?>>Page</option>
<option value="blog_post" <?= $form['content_type'] === 'blog_post' ? 'selected' : '' ?>>Blog Post</option>
</select>
</div>

<div class="form-group">
<label>Original Title</label>
<input type="text" name="original_title" value="<?= esc($form['original_title']) ?>" placeholder="Page or post title">
</div>

<div class="form-group">
<label>Original Body</label>
<textarea name="original_body" rows="8" placeholder="Paste HTML, Markdown or plain text..."><?= esc($form['original_body']) ?></textarea>
</div>

<div class="form-group">
<label>Original Excerpt</label>
<textarea name="original_excerpt" rows="2" placeholder="Brief summary..."><?= esc($form['original_excerpt']) ?></textarea>
</div>

<div class="form-group">
<label>SEO Meta Title</label>
<input type="text" name="original_meta_title" value="<?= esc($form['original_meta_title']) ?>" placeholder="Page Title | Site Name">
</div>

<div class="form-group">
<label>SEO Meta Description</label>
<textarea name="original_meta_description" rows="2" placeholder="SEO description..."><?= esc($form['original_meta_description']) ?></textarea>
</div>

<div class="form-group">
<label>Additional Notes</label>
<textarea name="notes" rows="2" placeholder="Keep formal, avoid slang..."><?= esc($form['notes']) ?></textarea>
</div>

<button type="submit" class="btn btn-primary" <?= !$anyProviderAvailable ? 'disabled' : '' ?>>🌐 Translate</button>
</form>
</div>
</div>
</div>

<div>
<!-- Results -->
<?php if ($translation): ?>
<div class="card">
<div class="card-head success"><span class="card-title"><span>✅</span> Translation Result</span></div>
<div class="card-body">

<div class="result-field">
<label>Translated Title</label>
<div style="font-size:16px;font-weight:500"><?= esc($translation['title'] ?? '') ?></div>
</div>

<div class="result-field">
<label>Translated Body</label>
<div class="output-box" onclick="this.select?.()" style="max-height:200px"><?= esc($translation['body_html'] ?? '') ?></div>
</div>

<?php if (!empty($translation['excerpt'])): ?>
<div class="result-field">
<label>Translated Excerpt</label>
<div><?= nl2br(esc($translation['excerpt'])) ?></div>
</div>
<?php endif; ?>

<div class="result-field">
<label>SEO Meta Title</label>
<div><?= esc($translation['meta_title'] ?? '') ?></div>
</div>

<div class="result-field">
<label>SEO Meta Description</label>
<div><?= esc($translation['meta_description'] ?? '') ?></div>
</div>

<?php if (!empty($translation['notes'])): ?>
<div class="result-field" style="background:rgba(249,226,175,.1);border:1px solid rgba(249,226,175,.3)">
<label style="color:var(--warning)">Translator Notes</label>
<div style="color:var(--warning)"><?= nl2br(esc($translation['notes'])) ?></div>
</div>
<?php endif; ?>

</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Raw JSON</span></div>
<div class="card-body">
<div class="output-box" onclick="navigator.clipboard?.writeText(this.textContent)"><?= esc($generatedJson) ?></div>
<small style="color:var(--muted);margin-top:8px;display:block">Click to copy</small>
</div>
</div>
<?php else: ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Translation Output</span></div>
<div class="card-body" style="text-align:center;padding:40px;color:var(--muted)">
<p style="font-size:32px;margin-bottom:12px">🌐</p>
<p>Fill in the form and click Translate</p>
</div>
</div>
<?php endif; ?>
</div>
</div>

<div style="margin-top:20px">
<a href="/admin/hf-settings.php" class="btn btn-secondary">⚙️ HF Settings</a>
<a href="/admin/ai-copywriter.php" class="btn btn-secondary">✍️ Copywriter</a>
</div>
</div>
</body>
</html>
