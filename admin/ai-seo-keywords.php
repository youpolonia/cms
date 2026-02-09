<?php
/**
 * AI SEO Keywords - Modern Dark UI
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


require_once CMS_ROOT . '/core/ai_seo_assistant.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$reports = ai_seo_assistant_list_reports();

// Extract clusters
$allClusters = [];
foreach ($reports as $r) {
    $full = ai_seo_assistant_load_report($r['id']);
    if (!$full) continue;
    $data = $full['data'] ?? [];
    $clusters = $data['keyword_clusters'] ?? [];
    foreach ($clusters as $c) {
        if (!is_array($c)) continue;
        $label = trim($c['label'] ?? '');
        if (!$label) continue;
        $key = mb_strtolower($label);
        if (!isset($allClusters[$key])) {
            $allClusters[$key] = ['label' => $label, 'keywords' => [], 'count' => 0];
        }
        foreach ($c['keywords'] ?? [] as $kw) {
            $kw = trim($kw);
            if ($kw && !in_array(mb_strtolower($kw), array_map('mb_strtolower', $allClusters[$key]['keywords']))) {
                $allClusters[$key]['keywords'][] = $kw;
            }
        }
        $allClusters[$key]['count']++;
    }
}

// Group by keyword
$keywordGroups = [];
$noKeyword = [];
foreach ($reports as $r) {
    $kw = trim($r['keyword'] ?? '');
    if (!$kw) { $noKeyword[] = $r; continue; }
    $key = mb_strtolower($kw);
    if (!isset($keywordGroups[$key])) {
        $keywordGroups[$key] = ['keyword' => $kw, 'reports' => [], 'urls' => [], 'scores' => []];
    }
    $keywordGroups[$key]['reports'][] = $r;
    if ($r['url']) $keywordGroups[$key]['urls'][] = $r['url'];
    if ($r['health_score'] !== null) $keywordGroups[$key]['scores'][] = (int)$r['health_score'];
}

// Compute metrics
$metrics = [];
foreach ($keywordGroups as $g) {
    $urls = array_unique($g['urls']);
    $scores = $g['scores'];
    $cannib = count($urls) > 1;
    $sev = 'none';
    if (count($urls) >= 4) $sev = 'critical';
    elseif (count($urls) === 3) $sev = 'high';
    elseif (count($urls) === 2) $sev = 'medium';
    
    $metrics[] = [
        'keyword' => $g['keyword'],
        'count' => count($g['reports']),
        'urls' => count($urls),
        'cannib' => $cannib,
        'severity' => $sev,
        'avg' => !empty($scores) ? (int)round(array_sum($scores)/count($scores)) : null,
        'min' => !empty($scores) ? min($scores) : null,
        'max' => !empty($scores) ? max($scores) : null,
    ];
}

usort($metrics, fn($a,$b) => $b['count'] - $a['count']);

$showCannib = ($_GET['cannib'] ?? '') === '1';
$sortBy = $_GET['sort'] ?? '';

if ($showCannib) $metrics = array_filter($metrics, fn($m) => $m['cannib']);
if ($sortBy === 'health_asc') usort($metrics, fn($a,$b) => ($a['avg'] ?? 999) - ($b['avg'] ?? 999));
elseif ($sortBy === 'health_desc') usort($metrics, fn($a,$b) => ($b['avg'] ?? -1) - ($a['avg'] ?? -1));
elseif ($sortBy === 'cannib') usort($metrics, fn($a,$b) => ['critical'=>0,'high'=>1,'medium'=>2,'none'=>3][$a['severity']] - ['critical'=>0,'high'=>1,'medium'=>2,'none'=>3][$b['severity']]);

$totalKw = count($metrics);
$totalReports = count($reports);
$cannibCount = count(array_filter($metrics, fn($m) => $m['cannib']));
$clusterCount = count($allClusters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI SEO Keywords - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.warning{background:rgba(249,226,175,.1)}
.card-head.info{background:rgba(137,220,235,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:800px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:12px;padding:16px;text-align:center}
.stat-val{font-size:28px;font-weight:700;margin-bottom:4px}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
.filters{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:20px}
.form-select,.form-check{padding:8px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px}
.data-table{width:100%;border-collapse:collapse;font-size:13px}
.data-table th,.data-table td{padding:12px 14px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:11px;text-transform:uppercase;background:var(--bg)}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.cluster-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.cluster-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px}
.cluster-card h4{font-size:14px;margin-bottom:8px;display:flex;justify-content:space-between}
.cluster-kw{display:flex;flex-wrap:wrap;gap:6px}
.cluster-kw span{background:var(--bg3);padding:3px 8px;border-radius:4px;font-size:11px}
.empty{text-align:center;padding:40px;color:var(--muted)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🎯',
    'title' => 'SEO Keywords',
    'description' => 'Keyword analysis & cannibalization',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--warning-color), #fab387',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-assistant', 'text' => '🔍 Analyze', 'class' => 'primary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="card">
<div class="card-head"><span class="card-title"><span>📈</span> Statistics</span></div>
<div class="card-body">
<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $totalKw ?></div><div class="stat-lbl">Keywords</div></div>
<div class="stat-box"><div class="stat-val"><?= $totalReports ?></div><div class="stat-lbl">Reports</div></div>
<div class="stat-box"><div class="stat-val" style="color:<?= $cannibCount > 0 ? 'var(--warning)' : 'var(--success)' ?>"><?= $cannibCount ?></div><div class="stat-lbl">Cannibalized</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $clusterCount ?></div><div class="stat-lbl">Clusters</div></div>
</div>
</div>
</div>

<?php if ($totalKw === 0): ?>
<div class="empty"><p>No keywords found. Run analysis in <a href="/admin/ai-seo-assistant.php" style="color:var(--accent)">AI SEO Assistant</a>.</p></div>
<?php else: ?>

<div class="card">
<div class="card-head">
<span class="card-title"><span>📋</span> Keywords Overview</span>
<div class="filters" style="margin:0">
<form method="get" style="display:flex;gap:8px;align-items:center">
<label style="font-size:12px;display:flex;align-items:center;gap:6px">
<input type="checkbox" name="cannib" value="1" <?= $showCannib ? 'checked' : '' ?> onchange="this.form.submit()"> Cannibalized only
</label>
<select name="sort" class="form-select" onchange="this.form.submit()">
<option value="">Sort: Default</option>
<option value="health_desc" <?= $sortBy === 'health_desc' ? 'selected' : '' ?>>Best Score</option>
<option value="health_asc" <?= $sortBy === 'health_asc' ? 'selected' : '' ?>>Worst Score</option>
<option value="cannib" <?= $sortBy === 'cannib' ? 'selected' : '' ?>>Cannib Severity</option>
</select>
</form>
</div>
</div>
<div class="card-body">
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>Keyword</th><th>Reports</th><th>URLs</th><th>Health Score</th><th>Cannibalization</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($metrics as $m): 
$avgClass = $m['avg'] === null ? 'muted' : ($m['avg'] >= 80 ? 'success' : ($m['avg'] >= 60 ? 'warning' : 'danger'));
$sevClass = match($m['severity']) { 'critical'=>'danger', 'high'=>'warning', 'medium'=>'info', default=>'success' };
?>
<tr>
<td><strong><?= esc($m['keyword']) ?></strong></td>
<td><span class="tag muted"><?= $m['count'] ?></span></td>
<td><?= $m['urls'] ?></td>
<td>
<?php if ($m['avg'] !== null): ?>
<span style="color:var(--muted)"><?= $m['min'] ?></span> / <span class="tag <?= $avgClass ?>"><?= $m['avg'] ?></span> / <span style="color:var(--muted)"><?= $m['max'] ?></span>
<?php else: ?>—<?php endif; ?>
</td>
<td>
<?php if ($m['cannib']): ?>
<span class="tag <?= $sevClass ?>"><?= ucfirst($m['severity']) ?> (<?= $m['urls'] ?> pages)</span>
<?php else: ?>
<span class="tag success">None</span>
<?php endif; ?>
</td>
<td><a href="/admin/ai-seo-reports.php?keyword=<?= urlencode($m['keyword']) ?>" class="btn btn-secondary">📄 Reports</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>

<?php if ($clusterCount > 0): ?>
<div class="card">
<div class="card-head info"><span class="card-title"><span>🧩</span> Keyword Clusters</span><span class="tag info"><?= $clusterCount ?></span></div>
<div class="card-body">
<div class="cluster-grid">
<?php foreach ($allClusters as $c): ?>
<div class="cluster-card">
<h4><?= esc($c['label']) ?><span class="tag muted"><?= $c['count'] ?></span></h4>
<div class="cluster-kw">
<?php foreach (array_slice($c['keywords'], 0, 8) as $kw): ?>
<span><?= esc($kw) ?></span>
<?php endforeach; ?>
<?php if (count($c['keywords']) > 8): ?><span style="color:var(--muted)">+<?= count($c['keywords']) - 8 ?></span><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
<?php endif; ?>

<?php 
$cannibMetrics = array_filter($metrics, fn($m) => $m['cannib']);
if (!empty($cannibMetrics)): 
?>
<div class="card">
<div class="card-head warning"><span class="card-title"><span>⚠️</span> Cannibalization Matrix</span></div>
<div class="card-body">
<p style="color:var(--text2);margin-bottom:16px">Keywords with multiple pages competing for the same search intent. Review and consolidate.</p>
<?php foreach ($cannibMetrics as $cm): 
$sevClass = match($cm['severity']) { 'critical'=>'danger', 'high'=>'warning', default=>'info' };
?>
<div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<strong style="font-size:15px"><?= esc($cm['keyword']) ?></strong>
<span class="tag <?= $sevClass ?>"><?= ucfirst($cm['severity']) ?> - <?= $cm['urls'] ?> pages</span>
</div>
<p style="font-size:12px;color:var(--warning);margin-bottom:8px">
<?php if ($cm['severity'] === 'critical'): ?>⚠️ Critical: Too many pages. Merge into 1-2 authority pages.
<?php elseif ($cm['severity'] === 'high'): ?>⚠️ High: Consider merging or differentiating content angles.
<?php else: ?>💡 Medium: Review if pages serve different intents. Consider canonical.
<?php endif; ?>
</p>
<a href="/admin/ai-seo-reports.php?keyword=<?= urlencode($cm['keyword']) ?>" class="btn btn-secondary">View all reports →</a>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if (!empty($noKeyword)): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>❓</span> Reports Without Keyword</span><span class="tag muted"><?= count($noKeyword) ?></span></div>
<div class="card-body">
<p style="color:var(--text2);margin-bottom:12px">Consider re-running analysis with a focus keyword.</p>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>Date</th><th>Title</th><th>Health</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach (array_slice($noKeyword, 0, 10) as $r): ?>
<tr>
<td><?= esc(substr($r['created_at'], 0, 10)) ?></td>
<td><?= esc($r['title'] ?: '—') ?></td>
<td><?= $r['health_score'] ?? '—' ?></td>
<td><a href="/admin/ai-seo-reports.php?id=<?= urlencode($r['id']) ?>" class="btn btn-secondary">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php endif; ?>

<div style="display:flex;gap:12px;margin-top:20px">
<a href="/admin/ai-seo-reports.php" class="btn btn-secondary">📊 All Reports</a>
<a href="/admin/ai-seo-pages.php" class="btn btn-secondary">📄 Pages Overview</a>
<a href="/admin/ai-seo-assistant.php" class="btn btn-primary">🔍 New Analysis</a>
</div>
</div>
</body>
</html>
