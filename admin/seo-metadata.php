<?php
/**
 * SEO Metadata - Modern Dark UI
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
$data = $controller->index();
$type = $_GET['type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEO Metadata - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.filters{display:flex;gap:8px;margin-bottom:20px}
.filter{padding:8px 16px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;color:var(--text2);text-decoration:none;font-size:13px;transition:.15s}
.filter:hover{border-color:var(--accent);color:var(--text)}
.filter.active{background:var(--accent);color:#000;border-color:var(--accent)}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)}
th{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;background:var(--bg)}
tr:hover td{background:rgba(137,180,250,.03)}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag-success{background:rgba(166,227,161,.2);color:var(--success)}
.tag-warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag-danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag-muted{background:var(--bg3);color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:6px;cursor:pointer;transition:.15s;text-decoration:none;background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn:hover{border-color:var(--accent)}
.empty{text-align:center;padding:40px;color:var(--muted)}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:20px;padding:16px}
.pagination a{padding:8px 14px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;color:var(--text);text-decoration:none;font-size:13px}
.pagination a:hover{border-color:var(--accent)}
.pagination a.active{background:var(--accent);color:#000;border-color:var(--accent)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📝',
    'title' => 'SEO Metadata',
    'description' => 'Manage page SEO settings',
    'back_url' => '/admin/seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--success-color), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="filters">
<a href="?type=" class="filter <?= empty($type) ? 'active' : '' ?>">All</a>
<a href="?type=page" class="filter <?= $type === 'page' ? 'active' : '' ?>">Pages</a>
<a href="?type=article" class="filter <?= $type === 'article' ? 'active' : '' ?>">Articles</a>
<a href="?type=category" class="filter <?= $type === 'category' ? 'active' : '' ?>">Categories</a>
</div>

<div class="card">
<table>
<thead><tr><th>Title</th><th>Type</th><th>Meta Title</th><th>Robots</th><th>Score</th><th>Updated</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($data['items'])): ?>
<tr><td colspan="7" class="empty">No SEO metadata found.</td></tr>
<?php else: ?>
<?php foreach ($data['items'] as $item): 
$score = $item['seo_score'] ?? null;
$scoreClass = $score !== null ? ($score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger')) : 'muted';
$robotsClass = ($item['robots_index'] ?? 'index') === 'noindex' ? 'danger' : 'success';
?>
<tr>
<td><strong><?= esc($item['entity_title']) ?></strong></td>
<td><span class="tag tag-muted"><?= esc(ucfirst($item['entity_type'])) ?></span></td>
<td><?= !empty($item['meta_title']) ? esc(mb_substr($item['meta_title'], 0, 40)) . '...' : '<span style="color:var(--muted)">—</span>' ?></td>
<td><span class="tag tag-<?= $robotsClass ?>"><?= esc($item['robots_index'] ?? 'index') ?></span></td>
<td><?php if ($score !== null): ?><span class="tag tag-<?= $scoreClass ?>"><?= (int)$score ?>%</span><?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?></td>
<td style="font-size:12px;color:var(--muted)"><?= esc(date('M j, Y', strtotime($item['updated_at']))) ?></td>
<td><a href="seo-edit.php?type=<?= esc($item['entity_type']) ?>&id=<?= (int)$item['entity_id'] ?>" class="btn">✏️ Edit</a></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

<?php if ($data['total_pages'] > 1): ?>
<div class="pagination">
<?php for ($p = 1; $p <= $data['total_pages']; $p++): ?>
<a href="?page=<?= $p ?>&type=<?= esc($type) ?>" class="<?= $p === $data['page'] ? 'active' : '' ?>"><?= $p ?></a>
<?php endfor; ?>
</div>
<?php endif; ?>
</div>
</div>
</body>
</html>
