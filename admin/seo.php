<?php
/**
 * SEO Settings — Catppuccin Dark UI
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/core/seo.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$successMessage = '';
$errors = [];
$settings = seo_get_settings();

// Compute helper values
$canonicalBase = trim((string)($settings['canonical_base_url'] ?? ''));
if ($canonicalBase === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $canonicalBase = $scheme . '://' . $host;
}
$robotsIndex  = (string)($settings['robots_index'] ?? 'index');
$robotsFollow = (string)($settings['robots_follow'] ?? 'follow');
$isNoIndex  = ($robotsIndex === 'noindex');
$isNoFollow = ($robotsFollow === 'nofollow');
$isIndexable = (!$isNoIndex && !$isNoFollow);
$sitemapUrl   = rtrim($canonicalBase, '/') . '/sitemap.php';
$robotsTxtUrl = rtrim($canonicalBase, '/') . '/robots.txt';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $site_name = trim($_POST['site_name'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $robots_index = trim($_POST['robots_index'] ?? 'index');
    $robots_follow = trim($_POST['robots_follow'] ?? 'follow');
    $canonical_base_url = trim($_POST['canonical_base_url'] ?? '');
    $og_image_url = trim($_POST['og_image_url'] ?? '');

    if (!in_array($robots_index, ['index', 'noindex'], true)) $robots_index = 'index';
    if (!in_array($robots_follow, ['follow', 'nofollow'], true)) $robots_follow = 'follow';
    if ($canonical_base_url !== '' && filter_var($canonical_base_url, FILTER_VALIDATE_URL) === false) $errors[] = 'Canonical Base URL must be a valid URL or left empty.';
    if ($og_image_url !== '' && filter_var($og_image_url, FILTER_VALIDATE_URL) === false) $errors[] = 'Default Open Graph Image URL must be a valid URL or left empty.';

    if (empty($errors)) {
        $settings = compact('site_name', 'meta_description', 'meta_keywords', 'robots_index', 'robots_follow', 'canonical_base_url', 'og_image_url');
        $result = @file_put_contents($seoConfigPath, json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
        if ($result === false) $errors[] = 'Failed to save SEO settings.';
        else $successMessage = 'SEO settings saved successfully.';
    } else {
        $settings = compact('site_name', 'meta_description', 'meta_keywords', 'robots_index', 'robots_follow', 'canonical_base_url', 'og_image_url');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEO Settings - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px}
.card h2{font-size:16px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.card p.sub{color:var(--text2);font-size:13px;margin:-8px 0 16px}
.form-row{margin-bottom:18px}
.form-row label{display:block;font-size:13px;font-weight:500;margin-bottom:6px;color:var(--text)}
.form-row small{display:block;font-size:12px;color:var(--muted);margin-top:4px}
input[type="text"],input[type="url"],textarea,select{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;font-family:'Inter',sans-serif;transition:.15s}
input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
textarea{resize:vertical}
select{cursor:pointer;appearance:auto}
.form-row-inline{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row-inline{grid-template-columns:1fr}}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;font-size:13px;font-weight:600;border:none;border-radius:8px;cursor:pointer;transition:.15s;font-family:'Inter',sans-serif}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.form-actions{padding-top:8px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px;display:flex;align-items:flex-start;gap:10px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-error{background:rgba(243,139,168,.1);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert ul{margin:0;padding-left:16px}
.overview-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:16px}
.ov-item{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px}
.ov-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.ov-val{font-size:14px;font-weight:500}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:600}
.tag-success{background:rgba(166,227,161,.15);color:var(--success)}
.tag-warning{background:rgba(249,226,175,.15);color:var(--warning)}
.tag-danger{background:rgba(243,139,168,.15);color:var(--danger)}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '⚙️',
    'title' => 'SEO Settings',
    'description' => 'Configure default SEO settings for your site',
    'back_url' => '/admin/seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<?php if ($successMessage): ?>
<div class="alert alert-success">✅ <?= esc($successMessage) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-error">❌ <ul><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<!-- SEO Overview -->
<div class="card">
<h2>📊 SEO Overview</h2>
<div class="overview-grid">
<div class="ov-item">
<div class="ov-label">Indexable</div>
<div class="ov-val"><span class="tag <?= $isIndexable ? 'tag-success' : 'tag-danger' ?>"><?= $isIndexable ? 'Yes' : 'No' ?></span> <small style="color:var(--muted)"><?= esc($robotsIndex) ?>, <?= esc($robotsFollow) ?></small></div>
</div>
<div class="ov-item">
<div class="ov-label">Robots.txt Rule</div>
<div class="ov-val"><?= $isNoIndex ? 'Disallow: /' : 'Allow: /' ?></div>
</div>
<div class="ov-item">
<div class="ov-label">Sitemap</div>
<div class="ov-val"><a href="<?= esc($sitemapUrl) ?>" target="_blank" style="font-size:12px;word-break:break-all"><?= esc($sitemapUrl) ?></a></div>
</div>
<div class="ov-item">
<div class="ov-label">Canonical Base</div>
<div class="ov-val"><?php $cb = trim((string)($settings['canonical_base_url'] ?? '')); if ($cb === ''): ?><span style="color:var(--warning)">⚠️ Auto-detected: <?= esc($canonicalBase) ?></span><?php else: ?><?= esc($cb) ?><?php endif; ?></div>
</div>
</div>
</div>

<form method="post" action="">
<?php csrf_field(); ?>

<!-- Site Identity -->
<div class="card">
<h2>🏷️ Site Identity</h2>
<div class="form-row">
<label for="site_name">Site Name</label>
<input type="text" id="site_name" name="site_name" placeholder="Your Site Name" value="<?= esc($settings['site_name'] ?? '') ?>">
<small>Used in page titles and social sharing cards.</small>
</div>
<div class="form-row">
<label for="meta_description">Default Meta Description</label>
<textarea id="meta_description" name="meta_description" rows="3" placeholder="Default description for pages without custom meta"><?= esc($settings['meta_description'] ?? '') ?></textarea>
<small>Fallback &lt;meta name="description"&gt; when no page-specific description is set.</small>
</div>
<div class="form-row">
<label for="meta_keywords">Default Meta Keywords</label>
<input type="text" id="meta_keywords" name="meta_keywords" placeholder="keyword1, keyword2, keyword3" value="<?= esc($settings['meta_keywords'] ?? '') ?>">
</div>
</div>

<!-- Search Indexing -->
<div class="card">
<h2>🔎 Search Indexing</h2>
<div class="form-row-inline">
<div class="form-row">
<label for="robots_index">Robots: Index</label>
<select id="robots_index" name="robots_index">
<option value="index" <?= ($settings['robots_index'] ?? 'index') === 'index' ? 'selected' : '' ?>>Index</option>
<option value="noindex" <?= ($settings['robots_index'] ?? '') === 'noindex' ? 'selected' : '' ?>>No index</option>
</select>
</div>
<div class="form-row">
<label for="robots_follow">Robots: Follow</label>
<select id="robots_follow" name="robots_follow">
<option value="follow" <?= ($settings['robots_follow'] ?? 'follow') === 'follow' ? 'selected' : '' ?>>Follow</option>
<option value="nofollow" <?= ($settings['robots_follow'] ?? '') === 'nofollow' ? 'selected' : '' ?>>No follow</option>
</select>
</div>
</div>
</div>

<!-- Canonical & Social -->
<div class="card">
<h2>🔗 Canonical &amp; Social</h2>
<div class="form-row">
<label for="canonical_base_url">Canonical Base URL</label>
<input type="url" id="canonical_base_url" name="canonical_base_url" placeholder="https://example.com" value="<?= esc($settings['canonical_base_url'] ?? '') ?>">
</div>
<div class="form-row">
<label for="og_image_url">Default Open Graph Image URL</label>
<input type="url" id="og_image_url" name="og_image_url" placeholder="https://example.com/images/og-default.jpg" value="<?= esc($settings['og_image_url'] ?? '') ?>">
</div>
</div>

<div class="form-actions">
<button type="submit" class="btn btn-primary">💾 Save SEO Settings</button>
</div>
</form>

</div>
</body>
</html>
