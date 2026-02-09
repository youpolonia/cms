<?php
/**
 * Bulk SEO Editor — Inline edit meta titles, descriptions, keywords
 * Catppuccin Dark UI
 */
if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) die('Cannot determine CMS_ROOT');
    define('CMS_ROOT', $cmsRoot);
}

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

$pdo = \core\Database::connection();
$message = '';
$messageType = '';

// ── Handle bulk save ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_validate_or_403();

    if ($_POST['action'] === 'save') {
        $updates = $_POST['items'] ?? [];
        $saved = 0;
        foreach ($updates as $key => $data) {
            [$type, $id] = explode('-', $key, 2);
            $table = $type === 'page' ? 'pages' : 'articles';
            $metaTitle = trim($data['meta_title'] ?? '');
            $metaDesc  = trim($data['meta_description'] ?? '');
            $focusKw   = trim($data['focus_keyword'] ?? '');

            $stmt = $pdo->prepare("UPDATE {$table} SET meta_title = ?, meta_description = ?, focus_keyword = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$metaTitle, $metaDesc, $focusKw, (int)$id]);
            $saved++;
        }
        $message = "✅ Saved {$saved} items successfully.";
        $messageType = 'success';
    }

    if ($_POST['action'] === 'template') {
        $template = trim($_POST['title_template'] ?? '');
        $applyTo  = $_POST['apply_to'] ?? 'all';
        if ($template) {
            $applied = 0;
            // Get site name from SEO settings
            $seoConfig = CMS_ROOT . '/config/seo_settings.json';
            $seoSettings = file_exists($seoConfig) ? (json_decode(file_get_contents($seoConfig), true) ?? []) : [];
            $siteName = $seoSettings['site_name'] ?? 'CMS';

            $tables = [];
            if ($applyTo === 'all' || $applyTo === 'pages') $tables[] = 'pages';
            if ($applyTo === 'all' || $applyTo === 'articles') $tables[] = 'articles';

            foreach ($tables as $table) {
                $rows = $pdo->query("SELECT id, title FROM {$table} WHERE status = 'published' AND (meta_title IS NULL OR meta_title = '')")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $metaTitle = str_replace(['{title}', '{site_name}'], [$row['title'], $siteName], $template);
                    $metaTitle = mb_substr($metaTitle, 0, 70);
                    $stmt = $pdo->prepare("UPDATE {$table} SET meta_title = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$metaTitle, $row['id']]);
                    $applied++;
                }
            }
            $message = "✅ Applied template to {$applied} items (only those without meta title).";
            $messageType = 'success';
        }
    }
}

// ── Load data ──
$filter = $_GET['filter'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';

$items = [];

// Pages
if ($typeFilter === 'all' || $typeFilter === 'pages') {
    $rows = $pdo->query("SELECT id, title, slug, status, meta_title, meta_description, focus_keyword FROM pages ORDER BY updated_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $r['item_type'] = 'page';
        $r['key'] = 'page-' . $r['id'];
        $items[] = $r;
    }
}

// Articles
if ($typeFilter === 'all' || $typeFilter === 'articles') {
    $rows = $pdo->query("SELECT id, title, slug, status, meta_title, meta_description, focus_keyword FROM articles ORDER BY updated_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $r['item_type'] = 'article';
        $r['key'] = 'article-' . $r['id'];
        $items[] = $r;
    }
}

// Apply filter
if ($filter === 'missing_title')  $items = array_filter($items, fn($i) => empty(trim($i['meta_title'] ?? '')));
if ($filter === 'missing_desc')   $items = array_filter($items, fn($i) => empty(trim($i['meta_description'] ?? '')));
if ($filter === 'missing_kw')     $items = array_filter($items, fn($i) => empty(trim($i['focus_keyword'] ?? '')));
if ($filter === 'long_title')     $items = array_filter($items, fn($i) => mb_strlen($i['meta_title'] ?? '') > 60);
if ($filter === 'long_desc')      $items = array_filter($items, fn($i) => mb_strlen($i['meta_description'] ?? '') > 160);
if ($filter === 'published')      $items = array_filter($items, fn($i) => $i['status'] === 'published');
$items = array_values($items);

// Stats
$allItems = [];
if ($typeFilter === 'all' || $typeFilter === 'pages') {
    $allItems = array_merge($allItems, $pdo->query("SELECT meta_title, meta_description, focus_keyword FROM pages")->fetchAll(\PDO::FETCH_ASSOC));
}
if ($typeFilter === 'all' || $typeFilter === 'articles') {
    $allItems = array_merge($allItems, $pdo->query("SELECT meta_title, meta_description, focus_keyword FROM articles")->fetchAll(\PDO::FETCH_ASSOC));
}
$statTotal     = count($allItems);
$statNoTitle   = count(array_filter($allItems, fn($i) => empty(trim($i['meta_title'] ?? ''))));
$statNoDesc    = count(array_filter($allItems, fn($i) => empty(trim($i['meta_description'] ?? ''))));
$statNoKw      = count(array_filter($allItems, fn($i) => empty(trim($i['focus_keyword'] ?? ''))));
$statLongTitle = count(array_filter($allItems, fn($i) => mb_strlen($i['meta_title'] ?? '') > 60));
$statLongDesc  = count(array_filter($allItems, fn($i) => mb_strlen($i['meta_description'] ?? '') > 160));
$statComplete  = $statTotal - max($statNoTitle, $statNoDesc, $statNoKw);

$buildUrl = function($f = null, $t = null) use ($filter, $typeFilter) {
    $p = [];
    $fv = $f !== null ? $f : $filter;
    $tv = $t !== null ? $t : $typeFilter;
    if ($fv !== 'all') $p['filter'] = $fv;
    if ($tv !== 'all') $p['type'] = $tv;
    return '/admin/ai-seo-bulk.php' . (!empty($p) ? '?' . http_build_query($p) : '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk SEO Editor - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--peach:#fab387;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1800px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:12px}
@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:600px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:12px;padding:16px;text-align:center;cursor:pointer;transition:.15s;border:2px solid transparent;text-decoration:none;display:block;color:inherit}
.stat-box:hover{border-color:var(--accent)}
.stat-box.active{border-color:var(--accent);background:rgba(137,180,250,.08)}
.stat-val{font-size:24px;font-weight:700;margin-bottom:4px}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
.filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.filter-btn{padding:8px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text2);font-size:12px;text-decoration:none;transition:.15s}
.filter-btn:hover{background:var(--bg4)}
.filter-btn.active{background:var(--accent);color:#000;border-color:var(--accent)}
.bulk-table{width:100%;border-collapse:collapse}
.bulk-table th,.bulk-table td{padding:8px 10px;text-align:left;border-bottom:1px solid var(--border);vertical-align:top}
.bulk-table th{font-size:10px;text-transform:uppercase;color:var(--text2);font-weight:600;background:var(--bg);white-space:nowrap;position:sticky;top:0;z-index:1}
.bulk-table tr:hover td{background:rgba(137,180,250,.03)}
.bulk-input{width:100%;padding:6px 10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:12px;font-family:'Inter',sans-serif;transition:.15s}
.bulk-input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 2px rgba(137,180,250,.15)}
.bulk-input.warn{border-color:var(--warning)}
.bulk-input.danger{border-color:var(--danger)}
textarea.bulk-input{resize:vertical;min-height:36px}
.char-count{font-size:10px;margin-top:2px;text-align:right}
.char-ok{color:var(--success)}
.char-warn{color:var(--warning)}
.char-danger{color:var(--danger)}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:500}
.tag.page{background:rgba(137,180,250,.15);color:var(--accent)}
.tag.article{background:rgba(203,166,247,.15);color:var(--purple)}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none;font-family:'Inter',sans-serif}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-success{background:var(--success);color:#000}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:13px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.template-row{display:flex;gap:12px;align-items:end;margin-top:12px}
.template-row .form-group{flex:1}
.template-row .form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.template-row select,.template-row input{width:100%;padding:8px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.template-row select:focus,.template-row input:focus{outline:none;border-color:var(--accent)}
.empty{text-align:center;padding:40px;color:var(--muted)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '✏️',
    'title' => 'Bulk SEO Editor',
    'description' => 'Edit meta titles, descriptions, and keywords in bulk',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--peach), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= esc($message) ?></div>
<?php endif; ?>

<!-- Stats (clickable filters) -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Overview</span></div>
<div class="card-body">
<div class="stat-grid">
<a href="<?= esc($buildUrl('all', null)) ?>" class="stat-box <?= $filter === 'all' ? 'active' : '' ?>"><div class="stat-val" style="color:var(--accent)"><?= $statTotal ?></div><div class="stat-lbl">Total</div></a>
<a href="<?= esc($buildUrl('missing_title', null)) ?>" class="stat-box <?= $filter === 'missing_title' ? 'active' : '' ?>"><div class="stat-val" style="color:<?= $statNoTitle ? 'var(--danger)' : 'var(--success)' ?>"><?= $statNoTitle ?></div><div class="stat-lbl">No Meta Title</div></a>
<a href="<?= esc($buildUrl('missing_desc', null)) ?>" class="stat-box <?= $filter === 'missing_desc' ? 'active' : '' ?>"><div class="stat-val" style="color:<?= $statNoDesc ? 'var(--danger)' : 'var(--success)' ?>"><?= $statNoDesc ?></div><div class="stat-lbl">No Description</div></a>
<a href="<?= esc($buildUrl('missing_kw', null)) ?>" class="stat-box <?= $filter === 'missing_kw' ? 'active' : '' ?>"><div class="stat-val" style="color:<?= $statNoKw ? 'var(--warning)' : 'var(--success)' ?>"><?= $statNoKw ?></div><div class="stat-lbl">No Keyword</div></a>
<a href="<?= esc($buildUrl('long_title', null)) ?>" class="stat-box <?= $filter === 'long_title' ? 'active' : '' ?>"><div class="stat-val" style="color:<?= $statLongTitle ? 'var(--warning)' : 'var(--success)' ?>"><?= $statLongTitle ?></div><div class="stat-lbl">Title &gt;60ch</div></a>
<a href="<?= esc($buildUrl('long_desc', null)) ?>" class="stat-box <?= $filter === 'long_desc' ? 'active' : '' ?>"><div class="stat-val" style="color:<?= $statLongDesc ? 'var(--warning)' : 'var(--success)' ?>"><?= $statLongDesc ?></div><div class="stat-lbl">Desc &gt;160ch</div></a>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= max(0, $statComplete) ?></div><div class="stat-lbl">Complete</div></div>
</div>
</div>
</div>

<!-- Batch Template -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Title Template</span></div>
<div class="card-body">
<p style="font-size:12px;color:var(--text2);margin-bottom:8px">Apply a meta title template to items that have NO meta title set. Variables: <code style="background:var(--bg3);padding:2px 6px;border-radius:4px">{title}</code> <code style="background:var(--bg3);padding:2px 6px;border-radius:4px">{site_name}</code></p>
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="template">
<div class="template-row">
<div class="form-group"><label>Template</label><input type="text" name="title_template" placeholder="{title} | {site_name}" value="{title} | {site_name}"></div>
<div class="form-group" style="max-width:200px"><label>Apply to</label><select name="apply_to"><option value="all">All</option><option value="pages">Pages only</option><option value="articles">Articles only</option></select></div>
<button type="submit" class="btn btn-secondary">📋 Apply Template</button>
</div>
</form>
</div>
</div>

<!-- Type filter -->
<div class="filters">
<a href="<?= esc($buildUrl(null, 'all')) ?>" class="filter-btn <?= $typeFilter === 'all' ? 'active' : '' ?>">All</a>
<a href="<?= esc($buildUrl(null, 'pages')) ?>" class="filter-btn <?= $typeFilter === 'pages' ? 'active' : '' ?>">📄 Pages</a>
<a href="<?= esc($buildUrl(null, 'articles')) ?>" class="filter-btn <?= $typeFilter === 'articles' ? 'active' : '' ?>">📝 Articles</a>
</div>

<!-- Bulk edit form -->
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="save">

<div class="card">
<div class="card-head">
<span class="card-title"><span>✏️</span> Edit SEO Data</span>
<div style="display:flex;gap:8px;align-items:center">
<span style="font-size:12px;color:var(--muted)"><?= count($items) ?> items</span>
<button type="submit" class="btn btn-success">💾 Save All Changes</button>
</div>
</div>
<div class="card-body" style="padding:0;overflow-x:auto">
<?php if (empty($items)): ?>
<div class="empty"><p>No items match the current filter.</p></div>
<?php else: ?>
<table class="bulk-table">
<thead><tr>
<th style="width:40px">Type</th>
<th style="width:180px">Title</th>
<th style="width:250px">Meta Title <span style="color:var(--muted)">(≤60)</span></th>
<th style="min-width:300px">Meta Description <span style="color:var(--muted)">(≤160)</span></th>
<th style="width:180px">Focus Keyword</th>
</tr></thead>
<tbody>
<?php foreach ($items as $item):
$mtLen = mb_strlen($item['meta_title'] ?? '');
$mdLen = mb_strlen($item['meta_description'] ?? '');
$mtClass = $mtLen === 0 ? 'danger' : ($mtLen > 60 ? 'warn' : '');
$mdClass = $mdLen === 0 ? 'danger' : ($mdLen > 160 ? 'warn' : '');
$mtCountClass = $mtLen === 0 ? 'char-danger' : ($mtLen > 60 ? 'char-warn' : 'char-ok');
$mdCountClass = $mdLen === 0 ? 'char-danger' : ($mdLen > 160 ? 'char-warn' : 'char-ok');
?>
<tr>
<td><span class="tag <?= $item['item_type'] ?>"><?= $item['item_type'] === 'page' ? '📄' : '📝' ?></span></td>
<td>
<strong style="font-size:12px"><?= esc(mb_substr($item['title'], 0, 40)) ?><?= mb_strlen($item['title']) > 40 ? '…' : '' ?></strong>
<div style="font-size:10px;color:var(--muted);font-family:monospace">/<?= esc($item['slug']) ?></div>
</td>
<td>
<input type="text" class="bulk-input <?= $mtClass ?>" name="items[<?= esc($item['key']) ?>][meta_title]" value="<?= esc($item['meta_title'] ?? '') ?>" placeholder="Meta title…" maxlength="70" oninput="updateCount(this, 60, 'mt-<?= esc($item['key']) ?>')">
<div id="mt-<?= esc($item['key']) ?>" class="char-count <?= $mtCountClass ?>"><?= $mtLen ?>/60</div>
</td>
<td>
<textarea class="bulk-input <?= $mdClass ?>" name="items[<?= esc($item['key']) ?>][meta_description]" placeholder="Meta description…" rows="2" maxlength="200" oninput="updateCount(this, 160, 'md-<?= esc($item['key']) ?>')"><?= esc($item['meta_description'] ?? '') ?></textarea>
<div id="md-<?= esc($item['key']) ?>" class="char-count <?= $mdCountClass ?>"><?= $mdLen ?>/160</div>
</td>
<td>
<input type="text" class="bulk-input" name="items[<?= esc($item['key']) ?>][focus_keyword]" value="<?= esc($item['focus_keyword'] ?? '') ?>" placeholder="Keyword…">
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
<?php if (!empty($items)): ?>
<div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
<span style="font-size:12px;color:var(--muted)"><?= count($items) ?> items</span>
<button type="submit" class="btn btn-success">💾 Save All Changes</button>
</div>
<?php endif; ?>
</div>
</form>

</div>

<script>
function updateCount(el, limit, targetId) {
    const len = el.value.length;
    const target = document.getElementById(targetId);
    if (!target) return;
    target.textContent = len + '/' + limit;
    target.className = 'char-count ' + (len === 0 ? 'char-danger' : (len > limit ? 'char-warn' : 'char-ok'));
    el.className = 'bulk-input' + (len === 0 ? ' danger' : (len > limit ? ' warn' : ''));
}
</script>
</body>
</html>
