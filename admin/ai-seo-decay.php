<?php
/**
 * AI SEO Content Decay - Modern Dark UI
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
require_once CMS_ROOT . '/core/ai_content_decay.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();


function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

$analysis = ai_decay_analyze_all();
$hasData = $analysis['ok'] && !empty($analysis['pages']);

$filterLevel = $_GET['level'] ?? '';
$sortBy = $_GET['sort'] ?? 'risk';
if (!in_array($filterLevel, ['','critical','high','medium','low'])) $filterLevel = '';
if (!in_array($sortBy, ['risk','age','score','title'])) $sortBy = 'risk';

$filtered = $analysis['pages'] ?? [];
if ($filterLevel !== '') $filtered = array_filter($filtered, fn($p) => $p['decay_level'] === $filterLevel);

usort($filtered, function($a, $b) use ($sortBy) {
    return match($sortBy) {
        'age' => $b['age_days'] - $a['age_days'],
        'score' => ($a['current_score'] ?? 999) - ($b['current_score'] ?? 999),
        'title' => strcasecmp($a['title'], $b['title']),
        default => $b['decay_risk'] - $a['decay_risk']
    };
});

$stats = $analysis['statistics'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content Decay - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.stat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px}
@media(max-width:1000px){.stat-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:500px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center}
.stat-val{font-size:24px;font-weight:700}
.stat-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.danger{background:rgba(243,139,168,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-warning{background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.filters{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:20px;padding:16px;background:var(--bg2);border:1px solid var(--border);border-radius:12px}
.form-select{padding:8px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th,.data-table td{padding:12px 14px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg)}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.empty{text-align:center;padding:30px;color:var(--muted)}
.rec-row{background:var(--bg)!important}
.rec-row td{padding:8px 14px!important;font-size:11px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '⏰',
    'title' => 'Content Decay',
    'description' => 'Detect aging content',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--warning-color), var(--danger-color)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-assistant', 'text' => '🔍 New Analysis', 'class' => 'primary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if (!$hasData): ?>
<div class="alert alert-warning"><span>⚠️</span><span><?= esc($analysis['error'] ?? 'No data available') ?></span></div>
<?php else: ?>

<div class="stat-grid">
<div class="stat-box" style="border-color:var(--danger)"><div class="stat-val" style="color:var(--danger)"><?= $stats['critical_decay'] ?? 0 ?></div><div class="stat-lbl">Critical</div></div>
<div class="stat-box" style="border-color:var(--warning)"><div class="stat-val" style="color:var(--warning)"><?= $stats['high_decay'] ?? 0 ?></div><div class="stat-lbl">High</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $stats['medium_decay'] ?? 0 ?></div><div class="stat-lbl">Medium</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $stats['low_decay'] ?? 0 ?></div><div class="stat-lbl">Low</div></div>
<div class="stat-box"><div class="stat-val"><?= $stats['pages_over_1_year'] ?? 0 ?></div><div class="stat-lbl">Over 1 Year</div></div>
<div class="stat-box"><div class="stat-val"><?= ai_decay_format_age($stats['avg_age_days'] ?? 0) ?></div><div class="stat-lbl">Avg Age</div></div>
</div>

<?php if (($stats['critical_decay'] ?? 0) > 0 || ($stats['high_decay'] ?? 0) > 0): ?>
<div class="alert alert-danger">
<span>⚠️</span>
<span><strong>Attention:</strong> <?= $stats['critical_decay'] ?> critical and <?= $stats['high_decay'] ?> high-risk pages need refresh. Outdated content hurts SEO.</span>
</div>
<?php endif; ?>

<div class="filters">
<span style="font-weight:600;color:var(--text2);font-size:12px">Risk Level:</span>
<select class="form-select" onchange="location.href='?level='+this.value+'&sort=<?= $sortBy ?>'">
<option value="">All</option>
<option value="critical" <?= $filterLevel === 'critical' ? 'selected' : '' ?>>🔴 Critical</option>
<option value="high" <?= $filterLevel === 'high' ? 'selected' : '' ?>>🟠 High</option>
<option value="medium" <?= $filterLevel === 'medium' ? 'selected' : '' ?>>🟡 Medium</option>
<option value="low" <?= $filterLevel === 'low' ? 'selected' : '' ?>>🟢 Low</option>
</select>
<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Sort:</span>
<select class="form-select" onchange="location.href='?level=<?= $filterLevel ?>&sort='+this.value">
<option value="risk" <?= $sortBy === 'risk' ? 'selected' : '' ?>>Decay Risk ↓</option>
<option value="age" <?= $sortBy === 'age' ? 'selected' : '' ?>>Age ↓</option>
<option value="score" <?= $sortBy === 'score' ? 'selected' : '' ?>>SEO Score ↑</option>
<option value="title" <?= $sortBy === 'title' ? 'selected' : '' ?>>Title A-Z</option>
</select>
<span style="margin-left:auto;font-size:12px;color:var(--muted)"><?= count($filtered) ?> of <?= $analysis['total_pages'] ?> pages</span>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📉</span> Content Decay Analysis</span></div>
<div class="card-body">
<?php if (empty($filtered)): ?>
<div class="empty"><p>No pages match filters.</p></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>Page</th><th style="text-align:center">Age</th><th style="text-align:center">Score</th><th style="text-align:center">Trend</th><th style="text-align:center">Decay</th><th>Issues</th><th></th></tr></thead>
<tbody>
<?php foreach ($filtered as $p): 
$lvlClass = match($p['decay_level']) { 'critical'=>'danger', 'high'=>'warning', 'medium'=>'info', default=>'success' };
$ageClass = $p['age_days'] >= 365 ? 'danger' : ($p['age_days'] >= 180 ? 'warning' : ($p['age_days'] >= 90 ? 'info' : 'success'));
$scoreClass = ($p['current_score'] ?? 0) >= 80 ? 'success' : (($p['current_score'] ?? 0) >= 60 ? 'warning' : 'danger');
$trend = match($p['score_trend']) { 'declining'=>['📉','color:var(--danger)'], 'improving'=>['📈','color:var(--success)'], default=>['➡️','color:var(--muted)'] };
?>
<tr>
<td><strong><?= esc($p['title']) ?></strong><br><small style="color:var(--muted)">/<?= esc($p['slug']) ?></small></td>
<td style="text-align:center"><span class="tag <?= $ageClass ?>"><?= ai_decay_format_age($p['age_days']) ?></span></td>
<td style="text-align:center"><?= $p['current_score'] !== null ? "<span class='tag {$scoreClass}'>{$p['current_score']}</span>" : '—' ?></td>
<td style="text-align:center;<?= $trend[1] ?>"><?= $trend[0] ?><?= $p['score_change'] !== 0 ? '<small> '.($p['score_change'] > 0 ? '+' : '').$p['score_change'].'</small>' : '' ?></td>
<td style="text-align:center"><span class="tag <?= $lvlClass ?>"><?= $p['decay_risk'] ?>%</span><br><small style="color:var(--muted)"><?= ucfirst($p['decay_level']) ?></small></td>
<td><?= count($p['markers']) > 0 ? '<span class="tag muted" title="'.esc(implode(', ', array_column($p['markers'], 'detail'))).'">'.count($p['markers']).' issues</span>' : '—' ?></td>
<td><a href="/admin/ai-seo-assistant.php?page_id=<?= $p['id'] ?>" class="btn btn-secondary">Analyze</a></td>
</tr>
<?php if (!empty($p['recommendations']) && in_array($p['decay_level'], ['critical','high'])): ?>
<tr class="rec-row"><td colspan="7" style="padding-left:24px">
<strong>Recommendations:</strong>
<?php foreach (array_slice($p['recommendations'], 0, 3) as $r): ?>
<span class="tag muted" style="margin-left:6px"><?= $r['priority'] === 'high' ? '🔴' : '🟡' ?> <?= esc($r['action']) ?></span>
<?php endforeach; ?>
</td></tr>
<?php endif; ?>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</div>

<!-- Info -->
<div class="card">
<div class="card-head"><span class="card-title"><span>ℹ️</span> About Content Decay</span></div>
<div class="card-body">
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">
<div>
<h4 style="font-size:13px;margin-bottom:8px">What is Content Decay?</h4>
<p style="font-size:12px;color:var(--text2)">Content decay occurs when pages become outdated, lose relevance, or drop in rankings. Regular audits maintain SEO performance.</p>
</div>
<div>
<h4 style="font-size:13px;margin-bottom:8px">Risk Levels</h4>
<div style="font-size:12px;color:var(--text2)">
<div><span class="tag danger">70-100%</span> Immediate action</div>
<div style="margin-top:4px"><span class="tag warning">50-69%</span> Update soon</div>
<div style="margin-top:4px"><span class="tag info">30-49%</span> Monitor</div>
<div style="margin-top:4px"><span class="tag success">0-29%</span> Fresh</div>
</div>
</div>
<div>
<h4 style="font-size:13px;margin-bottom:8px">Risk Factors</h4>
<ul style="font-size:12px;color:var(--text2);margin-left:16px">
<li>Age 6+ months</li>
<li>Declining scores</li>
<li>Outdated references</li>
<li>Thin content (&lt;300 words)</li>
</ul>
</div>
</div>
</div>
</div>

<?php endif; ?>

<div style="display:flex;gap:12px;margin-top:20px">
<a href="/admin/ai-seo-dashboard.php" class="btn btn-secondary">📊 Dashboard</a>
<a href="/admin/ai-seo-linking.php" class="btn btn-secondary">🔗 Linking</a>
<a href="/admin/ai-seo-assistant.php" class="btn btn-primary">🔍 New Analysis</a>
</div>
</div>
</body>
</html>
