<?php
/**
 * Robots.txt Preview — Catppuccin Dark UI
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
cms_session_start('admin');
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$settings = file_exists($seoConfigPath) ? (json_decode(@file_get_contents($seoConfigPath), true) ?? []) : [];

$robotsIndex = ($settings['robots_index'] ?? 'index');
$robotsFollow = ($settings['robots_follow'] ?? 'follow');
$base = rtrim($settings['canonical_base_url'] ?? '', '/');

$robotsTxt = "User-agent: *\n";
$robotsTxt .= ($robotsIndex === 'noindex') ? "Disallow: /\n" : "Allow: /\n";
if ($robotsFollow === 'nofollow') $robotsTxt .= "Crawl-delay: 999\n";
if ($base !== '') $robotsTxt .= "Sitemap: " . $base . "/sitemap.xml\n";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Robots.txt - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px}
.card h2{font-size:16px;font-weight:600;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.card p{color:var(--text2);font-size:13px;margin-bottom:12px}
pre{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:16px;font-family:'JetBrains Mono','Fira Code',monospace;font-size:13px;color:var(--success);line-height:1.8;overflow-x:auto}
code{background:var(--bg3);padding:2px 8px;border-radius:4px;font-size:12px;font-family:'JetBrains Mono','Fira Code',monospace}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px}
@media(max-width:600px){.info-grid{grid-template-columns:1fr}}
.info-item{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px}
.info-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.info-val{font-size:14px;font-weight:500}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:600}
.tag-success{background:rgba(166,227,161,.15);color:var(--success)}
.tag-warning{background:rgba(249,226,175,.15);color:var(--warning)}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '🤖',
    'title' => 'Robots.txt Preview',
    'description' => 'Generated dynamically from your SEO settings',
    'back_url' => '/admin/seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--success-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<div class="card">
<h2>📄 Preview</h2>
<pre><?= esc($robotsTxt) ?></pre>
</div>

<div class="card">
<h2>ℹ️ Configuration</h2>
<p>These values are derived from your <a href="/admin/seo.php">SEO Settings</a>.</p>
<div class="info-grid">
<div class="info-item">
<div class="info-label">Index</div>
<div class="info-val"><span class="tag <?= $robotsIndex === 'noindex' ? 'tag-warning' : 'tag-success' ?>"><?= esc($robotsIndex) ?></span></div>
</div>
<div class="info-item">
<div class="info-label">Follow</div>
<div class="info-val"><span class="tag <?= $robotsFollow === 'nofollow' ? 'tag-warning' : 'tag-success' ?>"><?= esc($robotsFollow) ?></span></div>
</div>
<div class="info-item">
<div class="info-label">Endpoint</div>
<div class="info-val"><code>/robots.txt</code></div>
</div>
<div class="info-item">
<div class="info-label">Canonical Base</div>
<div class="info-val"><?= $base !== '' ? esc($base) : '<span style="color:var(--warning)">Not configured</span>' ?></div>
</div>
</div>
</div>

</div>
</body>
</html>
