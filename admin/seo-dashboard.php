<?php
/**
 * SEO Dashboard - Modern Dark UI
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/controllers/seocontroller.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$db = \core\Database::connection();
$controller = new SeoController($db);
$data = $controller->dashboard();

$stats = $data['stats'] ?? [];
$lowScorePages = $data['low_score_pages'] ?? [];
$pagesWithoutSeo = $data['pages_without_seo'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<title>SEO Dashboard - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.stats{display:grid;grid-template-columns:repeat(6,1fr);gap:16px;margin-bottom:24px}
@media(max-width:1100px){.stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:600px){.stats{grid-template-columns:repeat(2,1fr)}}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;transition:.15s}
.stat:hover{border-color:var(--accent)}
.stat-val{font-size:28px;font-weight:700}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.stat.warn{border-color:var(--warning)}
.stat.warn .stat-val{color:var(--warning)}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-sm{padding:6px 12px;font-size:12px}
.btn-group{display:flex;gap:10px;flex-wrap:wrap}
table{width:100%;border-collapse:collapse}
th,td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)}
th{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;background:var(--bg)}
tr:hover td{background:rgba(137,180,250,.03)}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600}
.tag-danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag-warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag-success{background:rgba(166,227,161,.2);color:var(--success)}
code{background:var(--bg3);padding:2px 8px;border-radius:4px;font-size:12px}
.empty{text-align:center;padding:30px;color:var(--muted)}
.quick-links{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:24px}
@media(max-width:800px){.quick-links{grid-template-columns:repeat(2,1fr)}}
.quick-link{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;background:var(--bg2);border:1px solid var(--border);border-radius:12px;text-decoration:none;color:var(--text);transition:.15s}
.quick-link:hover{border-color:var(--accent);background:var(--bg3)}
.quick-link .icon{font-size:28px;margin-bottom:8px}
.quick-link .name{font-weight:500;font-size:13px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📊',
    'title' => 'SEO Dashboard',
    'description' => 'Site SEO health overview',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent-color), var(--success-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<!-- Stats -->
<div class="stats">
<div class="stat"><div class="stat-val"><?= (int)($stats['total_with_seo'] ?? 0) ?></div><div class="stat-lbl">Pages with SEO</div></div>
<div class="stat"><div class="stat-val"><?= esc($stats['avg_seo_score'] ?? '0') ?>%</div><div class="stat-lbl">Avg Score</div></div>
<div class="stat <?= ($stats['needs_attention'] ?? 0) > 0 ? 'warn' : '' ?>"><div class="stat-val"><?= (int)($stats['needs_attention'] ?? 0) ?></div><div class="stat-lbl">Need Attention</div></div>
<div class="stat"><div class="stat-val"><?= (int)($stats['active_redirects'] ?? 0) ?></div><div class="stat-lbl">Redirects</div></div>
<div class="stat"><div class="stat-val"><?= number_format($stats['total_redirect_hits'] ?? 0) ?></div><div class="stat-lbl">Redirect Hits</div></div>
<div class="stat"><div class="stat-val"><?= (int)($stats['tracked_keywords'] ?? 0) ?></div><div class="stat-lbl">Keywords</div></div>
</div>

<!-- Quick Actions -->
<div class="card">
<div class="card-head"><span class="card-title"><span>⚡</span> Quick Actions</span></div>
<div class="card-body">
<div class="btn-group">
<a href="seo-metadata.php" class="btn btn-secondary">📝 Metadata</a>
<a href="seo-redirects.php" class="btn btn-secondary">🔀 Redirects</a>
<a href="seo-sitemap.php" class="btn btn-secondary">🗺️ Sitemap</a>
<a href="seo-robots.php" class="btn btn-secondary">🤖 Robots.txt</a>
<button type="button" class="btn btn-primary" onclick="regenerateSitemap()">🔄 Regenerate Sitemap</button>
</div>
</div>
</div>

<?php if (!empty($lowScorePages)): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>⚠️</span> Pages Needing Attention</span><span style="color:var(--muted);font-size:12px"><?= count($lowScorePages) ?> pages</span></div>
<div class="card-body" style="padding:0">
<table>
<thead><tr><th>Page</th><th>Type</th><th>Score</th><th>Keyword</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($lowScorePages as $p): 
$score = (int)($p['seo_score'] ?? 0);
$cls = $score < 30 ? 'danger' : 'warning';
?>
<tr>
<td><strong><?= esc($p['entity_title']) ?></strong></td>
<td><?= esc(ucfirst($p['entity_type'])) ?></td>
<td><span class="tag tag-<?= $cls ?>"><?= $score ?>%</span></td>
<td><?= esc($p['focus_keyword'] ?? '—') ?></td>
<td>
<a href="seo-edit.php?type=<?= esc($p['entity_type']) ?>&id=<?= (int)$p['entity_id'] ?>" class="btn btn-sm btn-secondary">✏️ Edit</a>
<a href="ai-seo-assistant.php?page_id=<?= (int)$p['entity_id'] ?>" class="btn btn-sm btn-primary">🔍 Analyze</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endif; ?>

<?php if (!empty($pagesWithoutSeo)): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>📄</span> Pages Without SEO</span><span style="color:var(--muted);font-size:12px"><?= count($pagesWithoutSeo) ?> pages</span></div>
<div class="card-body" style="padding:0">
<table>
<thead><tr><th>Title</th><th>Slug</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($pagesWithoutSeo as $p): ?>
<tr>
<td><?= esc($p['title']) ?></td>
<td><code>/<?= esc($p['slug']) ?></code></td>
<td><a href="seo-edit.php?type=page&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary">➕ Add SEO</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endif; ?>

<!-- Quick Links to AI SEO -->
<div class="quick-links">
<a href="/admin/ai-seo-dashboard.php" class="quick-link"><span class="icon">🤖</span><span class="name">AI SEO Dashboard</span></a>
<a href="/admin/ai-seo-assistant.php" class="quick-link"><span class="icon">🔍</span><span class="name">AI SEO Assistant</span></a>
<a href="/admin/ai-seo-keywords.php" class="quick-link"><span class="icon">🔑</span><span class="name">Keyword Analysis</span></a>
<a href="/admin/ai-seo-linking.php" class="quick-link"><span class="icon">🔗</span><span class="name">Internal Linking</span></a>
</div>
</div>

<script>
function regenerateSitemap() {
    if (!confirm('Regenerate XML sitemap?')) return;
    fetch('api/seo-actions.php?action=regenerate_sitemap', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-Token':document.querySelector('meta[name="csrf-token"]')?.content||''}
    }).then(r=>r.json()).then(d=>{
        alert(d.success ? 'Sitemap regenerated! ' + (d.url_count||0) + ' URLs.' : 'Error: ' + (d.errors?.join(', ')||'Unknown'));
    }).catch(e=>alert('Failed: '+e.message));
}
</script>
</body>
</html>
