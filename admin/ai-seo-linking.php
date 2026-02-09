<?php
/**
 * AI SEO Internal Linking - Modern Dark UI
 * Supports both Pages and Articles
 */
if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) die('Cannot determine CMS_ROOT');
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_internal_linking.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();


function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    $ajaxAction = $_POST['ajax_action'] ?? '';

    if ($ajaxAction === 'apply_link') {
        $fromId = (int)($_POST['from_page_id'] ?? 0);
        $toId = (int)($_POST['to_page_id'] ?? 0);
        $anchor = $_POST['anchor'] ?? '';
        $fromType = $_POST['from_type'] ?? 'page';
        $toType = $_POST['to_type'] ?? 'page';

        // Validate types
        $fromType = in_array($fromType, ['page', 'article'], true) ? $fromType : 'page';
        $toType = in_array($toType, ['page', 'article'], true) ? $toType : 'page';

        if ($fromId <= 0 || $toId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid content IDs']);
            exit;
        }

        $result = ai_linking_apply_link($fromId, $toId, $anchor, $fromType, $toType);
        echo json_encode($result);
        exit;
    }

    if ($ajaxAction === 'remove_link') {
        $fromId = (int)($_POST['from_id'] ?? 0);
        $targetUrl = $_POST['target_url'] ?? '';
        $fromType = $_POST['from_type'] ?? 'page';

        // Validate type
        $fromType = in_array($fromType, ['page', 'article'], true) ? $fromType : 'page';

        if ($fromId <= 0 || empty($targetUrl)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid parameters']);
            exit;
        }

        $result = ai_linking_remove_link($fromId, $targetUrl, $fromType);
        echo json_encode($result);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
    exit;
}

$action = $_GET['action'] ?? '';
$pageId = (int)($_GET['page_id'] ?? $_GET['id'] ?? 0);
$contentType = $_GET['type'] ?? 'page';
$filter = $_GET['filter'] ?? 'all';
$msg = '';
$msgType = '';

// Validate content type
$contentType = in_array($contentType, ['page', 'article'], true) ? $contentType : 'page';

if ($action === 'analyze' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $start = microtime(true);
    $result = ai_linking_analyze_all();
    $dur = round(microtime(true) - $start, 2);
    if ($result['ok']) {
        ai_linking_save_analysis($result);
        $msg = "Analysis done in {$dur}s. Found " . count($result['opportunities']) . " opportunities across " . ($result['statistics']['total_pages'] ?? 0) . " pages and " . ($result['statistics']['total_articles'] ?? 0) . " articles.";
        $msgType = 'success';
    } else {
        $msg = 'Analysis failed: ' . ($result['error'] ?? 'Unknown');
        $msgType = 'danger';
    }
}

$analysis = ai_linking_load_analysis();
$hasData = $analysis && ($analysis['ok'] ?? false);

$pageSugg = null;
if ($pageId > 0) {
    $pageSugg = ai_linking_get_suggestions($pageId, 10, $contentType);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Internal Linking - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.grid{display:grid;gap:20px}
.grid-2{grid-template-columns:2fr 1fr}
@media(max-width:900px){.grid-2{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.success{background:rgba(166,227,161,.1)}
.card-head.warning{background:rgba(249,226,175,.1)}
.card-head.info{background:rgba(137,180,250,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px}
@media(max-width:1000px){.stat-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:500px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:10px;padding:16px;text-align:center}
.stat-val{font-size:24px;font-weight:700}
.stat-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th,.data-table td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg)}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.tag.purple{background:rgba(203,166,247,.2);color:var(--purple)}
.tag.info{background:rgba(137,180,250,.2);color:var(--accent)}
.filter-tabs{display:flex;gap:8px;margin-bottom:16px}
.filter-tabs .btn{padding:6px 12px;font-size:11px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-success{background:var(--success);color:#000}
.btn-danger{background:var(--danger);color:#000}
.empty{text-align:center;padding:30px;color:var(--muted)}
.page-list{max-height:400px;overflow-y:auto}
.sugg-item{padding:12px;background:var(--bg);border-radius:8px;margin-bottom:8px}
.sugg-item:last-child{margin-bottom:0}
code{background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:11px}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🔗',
    'title' => 'Internal Linking',
    'description' => 'Analyze and optimize your internal link structure',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--cyan)',
    'actions' => [
        ['type' => 'button', 'text' => '🔄 Run Analysis', 'class' => 'primary', 'form_action' => '?action=analyze'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><span><?= $msgType === 'success' ? '✅' : '❌' ?></span><span><?= esc($msg) ?></span></div>
<?php endif; ?>

<?php if (!$hasData): ?>
<div class="alert alert-info"><span>💡</span><span>No analysis data. Click "Run Analysis" to scan your internal linking structure.</span></div>
<?php else: ?>

<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $analysis['statistics']['total_pages'] ?? 0 ?></div><div class="stat-lbl">Pages</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--purple)"><?= $analysis['statistics']['total_articles'] ?? 0 ?></div><div class="stat-lbl">Articles</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $analysis['statistics']['total_internal_links'] ?? 0 ?></div><div class="stat-lbl">Total Links</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--warning)"><?= count($analysis['orphan_pages'] ?? []) ?></div><div class="stat-lbl">Orphans</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= count($analysis['opportunities'] ?? []) ?></div><div class="stat-lbl">Opportunities</div></div>
<div class="stat-box"><div class="stat-val"><?= $analysis['statistics']['avg_incoming_links'] ?? 0 ?></div><div class="stat-lbl">Avg In</div></div>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
<a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All Content</a>
<a href="?filter=pages" class="btn <?= $filter === 'pages' ? 'btn-primary' : 'btn-secondary' ?>">Pages Only</a>
<a href="?filter=articles" class="btn <?= $filter === 'articles' ? 'btn-primary' : 'btn-secondary' ?>">Articles Only</a>
</div>

<div class="grid grid-2">
<div>
<!-- Opportunities -->
<div class="card">
<div class="card-head success"><span class="card-title"><span>✨</span> Link Opportunities</span><span class="tag success"><?= count($analysis['opportunities'] ?? []) ?></span></div>
<div class="card-body">
<?php
// Filter opportunities based on filter selection
$filteredOpps = $analysis['opportunities'] ?? [];
if ($filter === 'pages') {
    $filteredOpps = array_filter($filteredOpps, fn($o) => ($o['from_type'] ?? 'page') === 'page' && ($o['to_type'] ?? 'page') === 'page');
} elseif ($filter === 'articles') {
    $filteredOpps = array_filter($filteredOpps, fn($o) => ($o['from_type'] ?? 'page') === 'article' || ($o['to_type'] ?? 'page') === 'article');
}
$filteredOpps = array_values($filteredOpps);
?>
<?php if (empty($filteredOpps)): ?>
<div class="empty"><p>No opportunities found<?= $filter !== 'all' ? ' for this filter' : '' ?>. Your linking looks good!</p></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>From</th><th>Type</th><th></th><th>To</th><th>Type</th><th>Score</th><th>Anchor</th><th>Action</th></tr></thead>
<tbody>
<?php foreach (array_slice($filteredOpps, 0, 15) as $idx => $o):
$sClass = $o['relevance_score'] >= 70 ? 'success' : ($o['relevance_score'] >= 50 ? 'warning' : 'muted');
$anchor = $o['suggested_anchors'][0]['text'] ?? $o['to_page_title'];
$fromType = $o['from_type'] ?? 'page';
$toType = $o['to_type'] ?? 'page';
$fromTypeClass = $fromType === 'article' ? 'purple' : 'muted';
$toTypeClass = $toType === 'article' ? 'purple' : 'muted';
?>
<tr id="opp-row-<?= $idx ?>">
<td><a href="?page_id=<?= $o['from_page_id'] ?>&type=<?= $fromType ?>"><?= esc(mb_substr($o['from_page_title'], 0, 22)) ?></a></td>
<td><span class="tag <?= $fromTypeClass ?>"><?= ucfirst($fromType) ?></span></td>
<td style="color:var(--muted)">→</td>
<td><a href="?page_id=<?= $o['to_page_id'] ?>&type=<?= $toType ?>"><?= esc(mb_substr($o['to_page_title'], 0, 22)) ?></a></td>
<td><span class="tag <?= $toTypeClass ?>"><?= ucfirst($toType) ?></span></td>
<td><span class="tag <?= $sClass ?>"><?= $o['relevance_score'] ?></span></td>
<td><code><?= esc(mb_substr($anchor, 0, 18)) ?></code></td>
<td>
<button type="button" class="btn btn-success btn-sm apply-link-btn"
    data-from="<?= $o['from_page_id'] ?>"
    data-to="<?= $o['to_page_id'] ?>"
    data-from-type="<?= $fromType ?>"
    data-to-type="<?= $toType ?>"
    data-anchor="<?= esc($anchor) ?>"
    data-row="<?= $idx ?>">✓ Apply</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</div>

<!-- Orphans -->
<?php
// Filter orphans based on filter selection
$filteredOrphans = $analysis['orphan_pages'] ?? [];
if ($filter === 'pages') {
    $filteredOrphans = array_filter($filteredOrphans, fn($o) => ($o['type'] ?? 'page') === 'page');
} elseif ($filter === 'articles') {
    $filteredOrphans = array_filter($filteredOrphans, fn($o) => ($o['type'] ?? 'page') === 'article');
}
$filteredOrphans = array_values($filteredOrphans);
?>
<?php if (!empty($filteredOrphans)): ?>
<div class="card">
<div class="card-head warning"><span class="card-title"><span>⚠️</span> Orphan Content</span><span class="tag warning"><?= count($filteredOrphans) ?></span></div>
<div class="card-body">
<p style="color:var(--text2);font-size:12px;margin-bottom:12px">Content with no incoming links - hard for search engines to find.</p>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>Title</th><th>Type</th><th>Outgoing</th><th></th></tr></thead>
<tbody>
<?php foreach ($filteredOrphans as $op):
$opType = $op['type'] ?? 'page';
$opTypeClass = $opType === 'article' ? 'purple' : 'muted';
$opUrl = $op['url'] ?? '/' . $op['slug'];
?>
<tr>
<td><strong><?= esc(mb_substr($op['title'], 0, 28)) ?></strong><br><small style="color:var(--muted)"><?= esc($opUrl) ?></small></td>
<td><span class="tag <?= $opTypeClass ?>"><?= ucfirst($opType) ?></span></td>
<td><span class="tag muted"><?= $op['outgoing_links'] ?></span></td>
<td><a href="?page_id=<?= $op['id'] ?>&type=<?= $opType ?>" class="btn btn-secondary">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php endif; ?>
</div>

<div>
<?php if ($pageSugg && $pageSugg['ok']): ?>
<!-- Content Suggestions -->
<?php
$suggType = $pageSugg['page']['type'] ?? 'page';
$suggTypeClass = $suggType === 'article' ? 'purple' : 'info';
$suggIcon = $suggType === 'article' ? '📝' : '📄';
?>
<div class="card">
<div class="card-head <?= $suggTypeClass ?>"><span class="card-title"><span><?= $suggIcon ?></span> <?= esc(mb_substr($pageSugg['page']['title'], 0, 25)) ?></span><span class="tag <?= $suggTypeClass ?>"><?= ucfirst($suggType) ?></span></div>
<div class="card-body">
<p style="font-size:12px;color:var(--muted);margin-bottom:16px"><?= esc($pageSugg['page']['url'] ?? '/' . $pageSugg['page']['slug']) ?></p>

<h4 style="font-size:13px;margin-bottom:10px;color:var(--success)">Add Links TO:</h4>
<?php if (empty($pageSugg['link_to'])): ?>
<p style="font-size:12px;color:var(--muted)">No suggestions</p>
<?php else: ?>
<?php foreach (array_slice($pageSugg['link_to'], 0, 5) as $s):
$sType = $s['type'] ?? 'page';
$sTypeClass = $sType === 'article' ? 'purple' : 'muted';
?>
<div class="sugg-item">
<div style="display:flex;justify-content:space-between;align-items:center"><strong><?= esc(mb_substr($s['title'], 0, 22)) ?></strong><div><span class="tag <?= $sTypeClass ?>" style="margin-right:4px"><?= ucfirst($sType) ?></span><span class="tag success"><?= $s['relevance_score'] ?></span></div></div>
<small style="color:var(--muted)">URL: <code><?= esc($s['url']) ?></code></small>
<?php if (!empty($s['suggested_anchors'])): ?><br><small>Anchor: <em><?= esc($s['suggested_anchors'][0]) ?></em></small><?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<h4 style="font-size:13px;margin:16px 0 10px;color:var(--cyan)">Get Links FROM:</h4>
<?php if (empty($pageSugg['link_from'])): ?>
<p style="font-size:12px;color:var(--muted)">No suggestions</p>
<?php else: ?>
<?php foreach (array_slice($pageSugg['link_from'], 0, 5) as $s):
$sType = $s['type'] ?? 'page';
$sTypeClass = $sType === 'article' ? 'purple' : 'muted';
?>
<div class="sugg-item">
<div style="display:flex;justify-content:space-between;align-items:center"><a href="?page_id=<?= $s['page_id'] ?>&type=<?= $sType ?>"><?= esc(mb_substr($s['title'], 0, 20)) ?></a><div><span class="tag <?= $sTypeClass ?>" style="margin-right:4px"><?= ucfirst($sType) ?></span><span class="tag info"><?= $s['relevance_score'] ?></span></div></div>
<?php if (!empty($s['matching_keywords'])): ?><small style="color:var(--muted)">Keywords: <?= esc(implode(', ', array_slice($s['matching_keywords'], 0, 3))) ?></small><?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<h4 style="font-size:13px;margin:16px 0 10px;color:var(--warning)">🔗 Existing Internal Links:</h4>
<?php if (empty($pageSugg['existing_links'])): ?>
<p style="font-size:12px;color:var(--muted)">No internal links found</p>
<?php else: ?>
<?php foreach ($pageSugg['existing_links'] as $idx => $el):
$elType = $el['type'] ?? 'page';
$elTypeClass = $elType === 'article' ? 'purple' : 'muted';
?>
<div class="sugg-item existing-link-item" id="existing-link-<?= $idx ?>">
<div style="display:flex;justify-content:space-between;align-items:center">
<div style="flex:1">
<code style="font-size:11px"><?= esc($el['href']) ?></code><br>
<small style="color:var(--text2)">Anchor: "<?= esc(mb_substr($el['anchor_text'], 0, 30)) ?>"</small>
</div>
<div style="display:flex;gap:6px;align-items:center">
<span class="tag <?= $elTypeClass ?>"><?= ucfirst($elType) ?></span>
<button type="button" class="btn btn-danger btn-sm remove-link-btn" 
    data-from-id="<?= $pageSugg['page']['id'] ?>" 
    data-from-type="<?= $suggType ?>"
    data-target-url="<?= esc($el['href']) ?>"
    data-idx="<?= $idx ?>"
    title="Remove this link">✕</button>
</div>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<a href="/admin/ai-seo-linking.php" class="btn btn-secondary" style="margin-top:16px">← Back to All</a>
</div>
</div>

<?php else: ?>
<!-- All Content -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> All Content</span></div>
<div class="card-body page-list">
<?php
// Filter content based on filter selection
$filteredContent = $analysis['pages'] ?? [];
if ($filter === 'pages') {
    $filteredContent = array_filter($filteredContent, fn($p) => ($p['type'] ?? 'page') === 'page');
} elseif ($filter === 'articles') {
    $filteredContent = array_filter($filteredContent, fn($p) => ($p['type'] ?? 'page') === 'article');
}
$filteredContent = array_values($filteredContent);
?>
<?php if (empty($filteredContent)): ?>
<div class="empty"><p>No content<?= $filter !== 'all' ? ' for this filter' : '' ?></p></div>
<?php else: ?>
<table class="data-table">
<thead><tr><th>Title</th><th>Type</th><th style="text-align:center">In</th><th style="text-align:center">Out</th></tr></thead>
<tbody>
<?php
usort($filteredContent, fn($a,$b) => $a['incoming_links'] - $b['incoming_links']);
foreach (array_slice($filteredContent, 0, 20) as $pg):
$pgType = $pg['type'] ?? 'page';
$pgTypeClass = $pgType === 'article' ? 'purple' : 'muted';
$rowStyle = $pg['incoming_links'] === 0 ? 'background:rgba(243,139,168,.1)' : '';
?>
<tr style="<?= $rowStyle ?>">
<td><a href="?page_id=<?= $pg['id'] ?>&type=<?= $pgType ?>"><?= esc(mb_substr($pg['title'], 0, 18)) ?></a></td>
<td><span class="tag <?= $pgTypeClass ?>"><?= ucfirst($pgType) ?></span></td>
<td style="text-align:center"><?= $pg['incoming_links'] === 0 ? '<span class="tag danger">0</span>' : $pg['incoming_links'] ?></td>
<td style="text-align:center"><?= $pg['outgoing_links'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

<!-- Analysis Info -->
<div class="card">
<div class="card-body">
<h4 style="font-size:13px;margin-bottom:10px">Analysis Info</h4>
<p style="font-size:12px;color:var(--muted);margin:0">
Last run: <strong><?= esc($analysis['analyzed_at'] ?? 'Never') ?></strong><br>
No outgoing: <strong><?= $analysis['statistics']['pages_with_no_outgoing'] ?></strong><br>
No incoming: <strong><?= $analysis['statistics']['pages_with_no_incoming'] ?></strong>
</p>
</div>
</div>
</div>
</div>

<?php endif; ?>

<div style="display:flex;gap:12px;margin-top:20px">
<a href="/admin/ai-seo-dashboard.php" class="btn btn-secondary">📊 Dashboard</a>
<a href="/admin/ai-seo-content.php?type=pages" class="btn btn-secondary">📄 Pages</a>
<a href="/admin/ai-seo-assistant.php" class="btn btn-primary">🔍 New Analysis</a>
</div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';

document.querySelectorAll('.apply-link-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const fromId = this.dataset.from;
        const toId = this.dataset.to;
        const fromType = this.dataset.fromType || 'page';
        const toType = this.dataset.toType || 'page';
        const anchor = this.dataset.anchor;
        const rowIdx = this.dataset.row;
        const row = document.getElementById('opp-row-' + rowIdx);

        this.disabled = true;
        this.textContent = '⏳...';

        try {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('ajax_action', 'apply_link');
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('from_page_id', fromId);
            formData.append('to_page_id', toId);
            formData.append('from_type', fromType);
            formData.append('to_type', toType);
            formData.append('anchor', anchor);

            const resp = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const data = await resp.json();

            if (data.ok) {
                this.textContent = '✓ Done';
                this.classList.remove('btn-success');
                this.classList.add('btn-secondary');
                row.style.opacity = '0.5';
                row.style.background = 'rgba(166,227,161,0.1)';
            } else {
                if (data.link_html) {
                    // Show copyable link
                    const copyLink = confirm(data.error + '\n\nCopy link to clipboard?\n' + data.link_html);
                    if (copyLink) {
                        navigator.clipboard.writeText(data.link_html).then(() => {
                            alert('Link copied to clipboard! Paste it in the content editor.');
                        }).catch(() => {
                            prompt('Copy this link:', data.link_html);
                        });
                    }
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
                this.textContent = '✓ Apply';
                this.disabled = false;
            }
        } catch (e) {
            alert('Network error: ' + e.message);
            this.textContent = '✓ Apply';
            this.disabled = false;
        }
    });
});

// Remove link buttons
document.querySelectorAll('.remove-link-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const fromId = this.dataset.fromId;
        const fromType = this.dataset.fromType || 'page';
        const targetUrl = this.dataset.targetUrl;
        const idx = this.dataset.idx;
        const item = document.getElementById('existing-link-' + idx);

        if (!confirm('Remove link to "' + targetUrl + '"?\n\nThe anchor text will remain but will no longer be a link.')) {
            return;
        }

        this.disabled = true;
        this.textContent = '⏳';

        try {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('ajax_action', 'remove_link');
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('from_id', fromId);
            formData.append('from_type', fromType);
            formData.append('target_url', targetUrl);

            const resp = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const data = await resp.json();

            if (data.ok) {
                item.style.opacity = '0.3';
                item.style.background = 'rgba(243,139,168,0.1)';
                this.textContent = '✓';
                this.classList.remove('btn-danger');
                this.classList.add('btn-secondary');
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
                this.textContent = '✕';
                this.disabled = false;
            }
        } catch (e) {
            alert('Network error: ' + e.message);
            this.textContent = '✕';
            this.disabled = false;
        }
    });
});
</script>
</body>
</html>
