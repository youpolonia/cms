<?php
/**
 * Sitemap.xml Preview — Catppuccin Dark UI
 */
define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/init.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
cms_session_start('admin');
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$settings = file_exists($seoConfigPath) ? (json_decode(@file_get_contents($seoConfigPath), true) ?? []) : [];
$base = rtrim($settings['canonical_base_url'] ?? '', '/');

// Build preview XML
$urls = [];
if ($base !== '') {
    $urls[] = $base . '/';
    $urls[] = $base . '/articles';
    $urls[] = $base . '/pages';
    // Try to get real page slugs
    try {
        require_once CMS_ROOT . '/core/database.php';
        $db = \core\Database::connection();
        $pages = $db->query("SELECT slug FROM pages WHERE status='published' ORDER BY updated_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($pages as $slug) {
            $urls[] = $base . '/' . $slug;
        }
        $articles = $db->query("SELECT slug FROM articles WHERE status='published' ORDER BY updated_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($articles as $slug) {
            $urls[] = $base . '/article/' . $slug;
        }
    } catch (\Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<title>Sitemap - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px}
.card-body{padding:20px}
pre{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:16px;font-family:'JetBrains Mono','Fira Code',monospace;font-size:12px;line-height:1.7;overflow-x:auto;color:var(--text2)}
pre .tag-name{color:var(--accent)}
pre .tag-val{color:var(--success)}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px}
.alert-warning{background:rgba(249,226,175,.1);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.stats{display:flex;gap:12px;margin-bottom:20px}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:14px 20px;text-align:center;flex:1}
.stat-val{font-size:24px;font-weight:700;color:var(--accent)}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;margin-top:2px}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '🗺️',
    'title' => 'Sitemap Preview',
    'description' => 'Dynamic sitemap generator preview',
    'back_url' => '/admin/seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--success-color), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<?php if (empty($base)): ?>
<div class="alert alert-warning">⚠️ <strong>Canonical base URL not configured.</strong> Set it in <a href="/admin/seo.php">SEO Settings</a> to generate a sitemap.</div>
<?php else: ?>

<div class="stats">
<div class="stat"><div class="stat-val"><?= count($urls) ?></div><div class="stat-lbl">URLs</div></div>
<div class="stat"><div class="stat-val"><?= esc($base) ?></div><div class="stat-lbl">Base URL</div></div>
</div>

<div class="card">
<div class="card-head">
<span class="card-title">📄 XML Preview</span>
<div>
<a href="/public/sitemap.xml.php" target="_blank" class="btn btn-primary" style="font-size:12px;padding:6px 14px">View Live →</a>
</div>
</div>
<div class="card-body">
<pre>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"&gt;
<?php foreach ($urls as $url): ?>
  &lt;url&gt;
    &lt;loc&gt;<?= esc($url) ?>&lt;/loc&gt;
  &lt;/url&gt;
<?php endforeach; ?>
&lt;/urlset&gt;</pre>
</div>
</div>
<?php endif; ?>

</div>
</body>
</html>
