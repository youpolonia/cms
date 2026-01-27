<?php
/**
 * AI SEO Reports - Modern Dark UI
 */
if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) { die('Cannot determine CMS_ROOT'); }
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit('Forbidden'); }

require_once CMS_ROOT . '/core/ai_seo_assistant.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$reports = ai_seo_assistant_list_reports();
$selectedReport = null;
$selectedReportId = null;

if (isset($_GET['id']) && $_GET['id'] !== '') {
    $selectedReportId = (string)$_GET['id'];
    $selectedReport = ai_seo_assistant_load_report($selectedReportId);
}

$statusLabels = ['high'=>'High Priority','medium'=>'Needs Work','ok'=>'Good','unknown'=>'No Data'];

foreach ($reports as $idx => $r) {
    $baseScore = $r['health_score'] ?? $r['content_score'] ?? null;
    if ($baseScore !== null) $baseScore = (int)$baseScore;
    $status = $baseScore === null ? 'unknown' : ($baseScore >= 80 ? 'ok' : ($baseScore >= 60 ? 'medium' : 'high'));
    $reports[$idx]['base_score'] = $baseScore;
    $reports[$idx]['status'] = $status;
    $reports[$idx]['status_label'] = $statusLabels[$status];
}

$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['all','high','medium','ok','unknown']) ? $_GET['status'] : 'all';
$keywordFilter = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$uniqueKeywords = [];
foreach ($reports as $r) {
    $kw = trim((string)$r['keyword']);
    if ($kw !== '' && !in_array($kw, $uniqueKeywords)) $uniqueKeywords[] = $kw;
}
sort($uniqueKeywords, SORT_STRING | SORT_FLAG_CASE);

$filteredReports = $reports;
if ($statusFilter !== 'all') {
    $filteredReports = array_filter($filteredReports, fn($r) => $r['status'] === $statusFilter);
}
if ($keywordFilter !== '') {
    $filteredReports = array_filter($filteredReports, fn($r) => strcasecmp(trim($r['keyword']), $keywordFilter) === 0);
}

$totalReports = count($reports);
$filteredCount = count($filteredReports);

$avgHealth = null;
$avgContent = null;
$healthScores = array_filter(array_column($reports, 'health_score'), fn($v) => $v !== null);
$contentScores = array_filter(array_column($reports, 'content_score'), fn($v) => $v !== null);
if (!empty($healthScores)) $avgHealth = (int)round(array_sum($healthScores) / count($healthScores));
if (!empty($contentScores)) $avgContent = (int)round(array_sum($contentScores) / count($contentScores));

$buildUrl = function($st = null, $kw = null) use ($statusFilter, $keywordFilter) {
    $p = [];
    $s = $st !== null ? $st : $statusFilter;
    $k = $kw !== null ? $kw : $keywordFilter;
    if ($s !== 'all') $p['status'] = $s;
    if ($k !== '') $p['keyword'] = $k;
    return '/admin/ai-seo-reports.php' . (!empty($p) ? '?' . http_build_query($p) : '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI SEO Reports - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:900px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.stat-grid{grid-template-columns:1fr}}
.stat-box{background:var(--bg);border-radius:12px;padding:20px;text-align:center}
.stat-val{font-size:28px;font-weight:700;margin-bottom:4px}
.stat-lbl{font-size:12px;color:var(--muted);text-transform:uppercase}
.filters{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:20px}
.filter-group{display:flex;gap:6px;flex-wrap:wrap}
.filter-btn{padding:8px 16px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text2);font-size:13px;text-decoration:none;transition:.15s}
.filter-btn:hover{background:var(--bg4);color:var(--text)}
.filter-btn.active{background:var(--accent);color:#000;border-color:var(--accent)}
.filter-btn.active.danger{background:var(--danger)}
.filter-btn.active.warning{background:var(--warning);color:#000}
.filter-btn.active.success{background:var(--success);color:#000}
.form-select{padding:8px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-select:focus{outline:none;border-color:var(--accent)}
.data-table{width:100%;border-collapse:collapse;font-size:13px}
.data-table th,.data-table td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:11px;text-transform:uppercase;letter-spacing:.5px;background:var(--bg)}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.tag{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.btn-success{background:var(--success);color:#000}
.empty{text-align:center;padding:60px 20px;color:var(--muted)}
.empty-icon{font-size:48px;margin-bottom:16px;opacity:.5}
.alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;gap:12px}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.alert-warn{background:rgba(249,226,175,.1);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;font-family:monospace;font-size:12px;max-height:500px;overflow:auto;white-space:pre-wrap}
.report-detail{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:700px){.report-detail{grid-template-columns:1fr}}
.detail-item{padding:12px;background:var(--bg);border-radius:8px}
.detail-item label{font-size:11px;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:4px}
.detail-item span{font-weight:500}
.score-circle{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;border:3px solid var(--border)}
.score-circle.success{border-color:var(--success);color:var(--success)}
.score-circle.warning{border-color:var(--warning);color:var(--warning)}
.score-circle.danger{border-color:var(--danger);color:var(--danger)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📊',
    'title' => 'SEO Reports',
    'description' => 'Saved analysis reports',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-assistant', 'text' => '🔍 New Analysis', 'class' => 'primary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<?php if ($selectedReport): ?>
<!-- Report Detail View -->
<div class="card">
<div class="card-head" style="background:rgba(137,180,250,.1)">
<span class="card-title"><span>📄</span> Report Details</span>
<a href="<?= esc($buildUrl()) ?>" class="btn btn-secondary">← Back to List</a>
</div>
<div class="card-body">
<?php
$ctx = $selectedReport['context'] ?? [];
$data = $selectedReport['data'] ?? [];
$hs = $data['health_score'] ?? null;
$hsClass = $hs === null ? '' : ($hs >= 80 ? 'success' : ($hs >= 60 ? 'warning' : 'danger'));
?>
<div style="display:flex;gap:24px;align-items:flex-start;margin-bottom:24px">
<?php if ($hs !== null): ?>
<div class="score-circle <?= $hsClass ?>"><?= (int)$hs ?></div>
<?php endif; ?>
<div style="flex:1">
<h3 style="margin-bottom:8px"><?= esc($ctx['title'] ?? 'Untitled') ?></h3>
<div style="display:flex;gap:12px;flex-wrap:wrap">
<span class="tag">🎯 <?= esc($ctx['keyword'] ?? '—') ?></span>
<span class="tag">🌐 <?= strtoupper(esc($ctx['language'] ?? 'en')) ?></span>
<span class="tag">📅 <?= esc($selectedReport['created_at'] ?? '') ?></span>
</div>
</div>
</div>

<div class="report-detail">
<div class="detail-item"><label>Report ID</label><span><?= esc($selectedReport['id'] ?? $selectedReportId) ?></span></div>
<div class="detail-item"><label>URL</label><span><?= esc($ctx['url'] ?? '—') ?></span></div>
<div class="detail-item"><label>Health Score</label><span><?= $hs !== null ? $hs . '/100' : '—' ?></span></div>
<div class="detail-item"><label>Content Score</label><span><?= isset($data['content_score']) ? $data['content_score'] . '/100' : '—' ?></span></div>
<div class="detail-item"><label>Recommended Words</label><span><?= isset($data['recommended_word_count']) ? number_format($data['recommended_word_count']) : '—' ?></span></div>
<div class="detail-item"><label>Keywords Found</label><span><?= isset($data['keyword_difficulty']) ? count($data['keyword_difficulty']) : '—' ?></span></div>
</div>

<h4 style="margin:24px 0 12px">Full JSON Data</h4>
<div class="output-box"><?= esc(json_encode($selectedReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></div>
</div>
</div>

<?php else: ?>
<!-- Reports List View -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📈</span> Statistics</span></div>
<div class="card-body">
<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $totalReports ?></div><div class="stat-lbl">Total Reports</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $avgHealth !== null ? $avgHealth : '—' ?></div><div class="stat-lbl">Avg Health</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--warning)"><?= $avgContent !== null ? $avgContent : '—' ?></div><div class="stat-lbl">Avg Content</div></div>
<div class="stat-box"><div class="stat-val"><?= $filteredCount ?></div><div class="stat-lbl">Visible</div></div>
</div>
</div>
</div>

<?php if ($totalReports === 0): ?>
<div class="alert alert-info">
<span>💡</span>
<div><strong>No reports yet.</strong> Run an analysis in <a href="/admin/ai-seo-assistant.php" style="color:inherit">AI SEO Assistant</a> to create your first report.</div>
</div>
<?php else: ?>

<div class="card">
<div class="card-head">
<span class="card-title"><span>📋</span> Reports List</span>
<span style="font-size:13px;color:var(--muted)"><?= $filteredCount ?> of <?= $totalReports ?></span>
</div>
<div class="card-body">
<div class="filters">
<span style="font-weight:600;color:var(--text2)">Status:</span>
<div class="filter-group">
<a href="<?= esc($buildUrl('all', null)) ?>" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
<a href="<?= esc($buildUrl('high', null)) ?>" class="filter-btn <?= $statusFilter === 'high' ? 'active danger' : '' ?>">🔴 High</a>
<a href="<?= esc($buildUrl('medium', null)) ?>" class="filter-btn <?= $statusFilter === 'medium' ? 'active warning' : '' ?>">🟡 Medium</a>
<a href="<?= esc($buildUrl('ok', null)) ?>" class="filter-btn <?= $statusFilter === 'ok' ? 'active success' : '' ?>">🟢 Good</a>
<a href="<?= esc($buildUrl('unknown', null)) ?>" class="filter-btn <?= $statusFilter === 'unknown' ? 'active' : '' ?>">⚪ Unknown</a>
</div>

<?php if (!empty($uniqueKeywords)): ?>
<span style="font-weight:600;color:var(--text2);margin-left:16px">Keyword:</span>
<select class="form-select" onchange="if(this.value)location.href=this.value">
<option value="<?= esc($buildUrl(null, '')) ?>">All keywords</option>
<?php foreach ($uniqueKeywords as $kw): ?>
<option value="<?= esc($buildUrl(null, $kw)) ?>" <?= $keywordFilter === $kw ? 'selected' : '' ?>><?= esc($kw) ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>
</div>

<?php if ($filteredCount === 0): ?>
<div class="empty"><div class="empty-icon">📭</div><p>No reports match the selected filters.</p></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>Date</th><th>Title</th><th>Keyword</th><th>Health</th><th>Content</th><th>Words</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($filteredReports as $r): 
$hs = $r['health_score'];
$hsClass = $hs === null ? 'muted' : ($hs >= 80 ? 'success' : ($hs >= 60 ? 'warning' : 'danger'));
$stClass = match($r['status']) { 'high'=>'danger', 'medium'=>'warning', 'ok'=>'success', default=>'muted' };
?>
<tr>
<td style="white-space:nowrap"><?= esc($r['created_at']) ?></td>
<td><strong><?= esc($r['title'] ?: '—') ?></strong></td>
<td><?= esc($r['keyword'] ?: '—') ?></td>
<td><span class="tag <?= $hsClass ?>"><?= $hs !== null ? $hs : '—' ?></span></td>
<td><?= $r['content_score'] !== null ? $r['content_score'] : '—' ?></td>
<td><?= $r['recommended_word_count'] !== null ? number_format($r['recommended_word_count']) : '—' ?></td>
<td><span class="tag <?= $stClass ?>"><?= esc($r['status_label']) ?></span></td>
<td style="white-space:nowrap">
<a href="?id=<?= urlencode($r['id']) ?><?= $statusFilter !== 'all' ? '&status=' . $statusFilter : '' ?><?= $keywordFilter !== '' ? '&keyword=' . urlencode($keywordFilter) : '' ?>" class="btn btn-secondary">👁 View</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
