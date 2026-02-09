<?php
/**
 * Sitemap Manager — Catppuccin Dark UI
 * Shows live sitemap data + management
 */
define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/core/database.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$seoConfigPath = CMS_ROOT . '/config/seo_settings.json';
$settings = file_exists($seoConfigPath) ? (json_decode(@file_get_contents($seoConfigPath), true) ?? []) : [];
$base = rtrim($settings['canonical_base_url'] ?? '', '/');

if ($base === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $scheme . '://' . $host;
}

// Get real data
$pages = $articles = [];
$totalPages = $totalArticles = 0;
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SELECT id, title, slug, status, updated_at FROM pages WHERE status = 'published' ORDER BY updated_at DESC");
    $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $totalPages = count($pages);

    $stmt = $pdo->query("SELECT id, title, slug, status, updated_at, published_at, views FROM articles WHERE status = 'published' ORDER BY published_at DESC");
    $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $totalArticles = count($articles);
} catch (\Exception $e) {}

$totalUrls = 1 + $totalPages + $totalArticles; // +1 for homepage
$sitemapUrl = $base . '/sitemap.xml';

// Check if robots.txt mentions sitemap
$robotsPath = CMS_ROOT . '/public/robots.txt';
$robotsHasSitemap = false;
if (file_exists($robotsPath)) {
    $robotsContent = file_get_contents($robotsPath);
    $robotsHasSitemap = stripos($robotsContent, 'sitemap:') !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sitemap Manager - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px}
.card-body{padding:20px}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
@media(max-width:700px){.stats{grid-template-columns:repeat(2,1fr)}}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:16px 20px;text-align:center}
.stat-val{font-size:28px;font-weight:700;color:var(--accent)}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.url-table{width:100%;border-collapse:collapse;font-size:13px}
.url-table th,.url-table td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border)}
.url-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg)}
.url-table tr:hover td{background:rgba(137,180,250,.05)}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.tag.page{background:rgba(137,180,250,.15);color:var(--accent)}
.tag.article{background:rgba(203,166,247,.15);color:var(--purple)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-warning{background:rgba(249,226,175,.1);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
code{background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:12px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '🗺️',
    'title' => 'Sitemap Manager',
    'description' => 'Dynamic XML sitemap for search engines',
    'back_url' => '/admin/seo',
    'back_text' => 'SEO Settings',
    'gradient' => 'var(--success-color), var(--accent-color)',
    'actions' => [
        ['type' => 'link', 'url' => $sitemapUrl, 'text' => '🔗 View Live Sitemap', 'class' => 'primary', 'target' => '_blank'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<!-- Status alerts -->
<div class="alert alert-success">✅ Dynamic sitemap active at <a href="<?= esc($sitemapUrl) ?>" target="_blank"><code><?= esc($sitemapUrl) ?></code></a></div>

<?php if (!$robotsHasSitemap): ?>
<div class="alert alert-warning">⚠️ Your <code>robots.txt</code> doesn't reference the sitemap. Add <code>Sitemap: <?= esc($sitemapUrl) ?></code> to help search engines find it. <a href="/admin/seo-robots">Edit robots.txt →</a></div>
<?php endif; ?>

<?php if (empty($settings['canonical_base_url'])): ?>
<div class="alert alert-info">💡 No canonical base URL set — sitemap uses auto-detected <code><?= esc($base) ?></code>. <a href="/admin/seo">Set it in SEO Settings →</a></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats">
<div class="stat"><div class="stat-val"><?= $totalUrls ?></div><div class="stat-lbl">Total URLs</div></div>
<div class="stat"><div class="stat-val"><?= $totalPages ?></div><div class="stat-lbl">Pages</div></div>
<div class="stat"><div class="stat-val"><?= $totalArticles ?></div><div class="stat-lbl">Articles</div></div>
<div class="stat"><div class="stat-val" style="color:var(--success)">✓</div><div class="stat-lbl">Auto-Updated</div></div>
</div>

<!-- URL List -->
<div class="card">
<div class="card-head">
<span class="card-title">🔗 Sitemap URLs</span>
<span style="font-size:12px;color:var(--muted)"><?= $totalUrls ?> URLs</span>
</div>
<div class="card-body" style="padding:0">
<table class="url-table">
<thead><tr><th>Type</th><th>URL</th><th>Last Modified</th><th>Priority</th></tr></thead>
<tbody>
<!-- Homepage -->
<tr>
<td><span class="tag success">🏠 Home</span></td>
<td><a href="<?= esc($base) ?>/" target="_blank"><?= esc($base) ?>/</a></td>
<td><?= date('Y-m-d') ?></td>
<td>1.0</td>
</tr>
<!-- Pages -->
<?php foreach ($pages as $p):
    $slug = trim($p['slug'] ?? '');
    if ($slug === '' || $slug === 'home') continue;
?>
<tr>
<td><span class="tag page">📄 Page</span></td>
<td><a href="<?= esc($base . '/' . $slug) ?>" target="_blank"><?= esc($base . '/' . $slug) ?></a></td>
<td><?= substr($p['updated_at'] ?? '', 0, 10) ?></td>
<td>0.8</td>
</tr>
<?php endforeach; ?>
<!-- Articles -->
<?php foreach ($articles as $a):
    $slug = trim($a['slug'] ?? '');
    if ($slug === '') continue;
?>
<tr>
<td><span class="tag article">📝 Article</span></td>
<td><a href="<?= esc($base . '/article/' . $slug) ?>" target="_blank"><?= esc($base . '/article/' . $slug) ?></a></td>
<td><?= substr($a['updated_at'] ?? $a['published_at'] ?? '', 0, 10) ?></td>
<td>0.6</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- Info card -->
<div class="card">
<div class="card-head"><span class="card-title">ℹ️ How It Works</span></div>
<div class="card-body" style="color:var(--text2);font-size:13px;line-height:1.8">
<p>The sitemap is <strong>generated dynamically</strong> — no manual regeneration needed. Every time a search engine crawls <code>/sitemap.xml</code>, it gets the latest published pages and articles.</p>
<ul style="margin:12px 0 0 20px">
<li>📄 <strong>Pages</strong> — all published pages (priority 0.8, weekly)</li>
<li>📝 <strong>Articles</strong> — all published articles (priority 0.6, monthly)</li>
<li>🏠 <strong>Homepage</strong> — always included (priority 1.0, daily)</li>
</ul>
<p style="margin-top:12px">To submit your sitemap to Google: <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> → Sitemaps → Add <code><?= esc($sitemapUrl) ?></code></p>
</div>
</div>

</div>
</body>
</html>
