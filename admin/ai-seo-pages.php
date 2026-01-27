<?php
/**
 * AI SEO Pages - Modern Dark UI
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
require_once CMS_ROOT . '/core/database.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Load pages
$pages = [];
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SELECT id, title, slug, status, updated_at, content FROM pages ORDER BY updated_at DESC LIMIT 500");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $pages[] = $row;
    }
} catch (\Exception $e) { $pages = []; }

// Load reports
$reports = ai_seo_assistant_list_reports();
$reportsByPageId = [];
foreach ($reports as $r) {
    $pid = $r['page_id'] ?? null;
    if ($pid) {
        if (!isset($reportsByPageId[$pid])) $reportsByPageId[$pid] = [];
        $reportsByPageId[$pid][] = $r;
    }
}

// Process pages with SEO data
$pagesWithSeo = [];
foreach ($pages as $page) {
    $pid = (string)$page['id'];
    $latest = $reportsByPageId[$pid][0] ?? null;
    
    $score = $latest['health_score'] ?? $latest['content_score'] ?? null;
    if ($score !== null) $score = (int)$score;
    
    $status = $score === null ? 'unknown' : ($score >= 80 ? 'ok' : ($score >= 60 ? 'medium' : 'high'));
    
    $contentText = strip_tags($page['content'] ?? '');
    $wordCount = $contentText ? count(preg_split('/\s+/u', trim($contentText), -1, PREG_SPLIT_NO_EMPTY)) : 0;
    $recWords = $latest['recommended_word_count'] ?? 0;
    
    $lengthStatus = 'unknown';
    if ($recWords > 0 && $wordCount > 0) {
        $ratio = $wordCount / $recWords;
        $lengthStatus = $ratio < 0.8 ? 'short' : ($ratio <= 1.2 ? 'optimal' : 'long');
    }
    
    $keyword = $latest['keyword'] ?? null;
    $kd = isset($latest['keyword_difficulty']) && is_numeric($latest['keyword_difficulty']) ? (int)$latest['keyword_difficulty'] : null;
    
    $opp = 'no_data';
    if ($score !== null && $kd !== null) {
        if ($score < 60 && $kd < 40) $opp = 'quick_win';
        elseif ($score < 60 && $kd <= 70) $opp = 'improve';
        elseif ($score < 60) $opp = 'competitive';
        elseif ($kd < 40) $opp = 'maintain';
        else $opp = 'strong';
    }
    
    $fresh = 'no_report';
    if ($latest) {
        $repTime = strtotime($latest['created_at'] ?? '');
        $pageTime = strtotime($page['updated_at'] ?? '');
        if ($repTime && $pageTime) {
            if ($pageTime > $repTime) $fresh = 'outdated';
            elseif ((time() - $repTime) / 86400 > 30) $fresh = 'stale';
            else $fresh = 'fresh';
        }
    }
    
    $pagesWithSeo[] = [
        'id' => $page['id'],
        'title' => $page['title'],
        'slug' => $page['slug'],
        'page_status' => $page['status'],
        'updated_at' => $page['updated_at'],
        'score' => $score,
        'seo_status' => $status,
        'word_count' => $wordCount,
        'rec_words' => $recWords > 0 ? $recWords : null,
        'length_status' => $lengthStatus,
        'keyword' => $keyword,
        'kd' => $kd,
        'opportunity' => $opp,
        'freshness' => $fresh,
        'report_count' => count($reportsByPageId[$pid] ?? []),
        'latest_id' => $latest['id'] ?? null,
    ];
}

// Filters
$statusFilter = $_GET['status'] ?? 'all';
$oppFilter = $_GET['opp'] ?? '';
$sortBy = $_GET['sort'] ?? '';

$filtered = $pagesWithSeo;
if ($statusFilter !== 'all') {
    $filtered = array_filter($filtered, fn($p) => $p['seo_status'] === $statusFilter);
}
if ($oppFilter !== '') {
    $filtered = array_filter($filtered, fn($p) => $p['opportunity'] === $oppFilter);
}

if ($sortBy === 'score_desc') usort($filtered, fn($a,$b) => ($b['score'] ?? -1) - ($a['score'] ?? -1));
elseif ($sortBy === 'score_asc') usort($filtered, fn($a,$b) => ($a['score'] ?? 999) - ($b['score'] ?? 999));
elseif ($sortBy === 'opp') usort($filtered, fn($a,$b) => ['quick_win'=>5,'improve'=>4,'competitive'=>3,'strong'=>2,'maintain'=>1,'no_data'=>0][$b['opportunity']] - ['quick_win'=>5,'improve'=>4,'competitive'=>3,'strong'=>2,'maintain'=>1,'no_data'=>0][$a['opportunity']]);

$total = count($pagesWithSeo);
$shown = count($filtered);

// Stats
$statsOk = count(array_filter($pagesWithSeo, fn($p) => $p['seo_status'] === 'ok'));
$statsMed = count(array_filter($pagesWithSeo, fn($p) => $p['seo_status'] === 'medium'));
$statsHigh = count(array_filter($pagesWithSeo, fn($p) => $p['seo_status'] === 'high'));
$statsUnk = count(array_filter($pagesWithSeo, fn($p) => $p['seo_status'] === 'unknown'));
$statsQW = count(array_filter($pagesWithSeo, fn($p) => $p['opportunity'] === 'quick_win'));
$scores = array_filter(array_column($pagesWithSeo, 'score'), fn($v) => $v !== null);
$avgScore = !empty($scores) ? (int)round(array_sum($scores) / count($scores)) : null;

$buildUrl = function($st = null, $op = null, $so = null) use ($statusFilter, $oppFilter, $sortBy) {
    $p = [];
    $s = $st !== null ? $st : $statusFilter;
    $o = $op !== null ? $op : $oppFilter;
    $r = $so !== null ? $so : $sortBy;
    if ($s !== 'all') $p['status'] = $s;
    if ($o !== '') $p['opp'] = $o;
    if ($r !== '') $p['sort'] = $r;
    return '/admin/ai-seo-pages.php' . (!empty($p) ? '?' . http_build_query($p) : '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI SEO Pages - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1600px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px}
@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:600px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:12px;padding:16px;text-align:center}
.stat-val{font-size:24px;font-weight:700;margin-bottom:4px}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
.filters{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:20px}
.filter-group{display:flex;gap:6px;flex-wrap:wrap}
.filter-btn{padding:8px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text2);font-size:12px;text-decoration:none;transition:.15s}
.filter-btn:hover{background:var(--bg4);color:var(--text)}
.filter-btn.active{background:var(--accent);color:#000;border-color:var(--accent)}
.filter-btn.active.danger{background:var(--danger)}
.filter-btn.active.warning{background:var(--warning);color:#000}
.filter-btn.active.success{background:var(--success);color:#000}
.form-select{padding:8px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th,.data-table td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg);white-space:nowrap}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.tag{display:inline-flex;align-items:center;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.tag.purple{background:rgba(203,166,247,.2);color:var(--purple)}
.btn{display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:11px;font-weight:500;border:none;border-radius:6px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-success{background:var(--success);color:#000}
.empty{text-align:center;padding:40px;color:var(--muted)}
.alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;gap:12px}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📄',
    'title' => 'SEO Pages Overview',
    'description' => 'SEO status for all CMS pages',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--success-color)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-assistant', 'text' => '🔍 Analyze', 'class' => 'primary'],
        ['type' => 'link', 'url' => '/admin/ai-seo-reports', 'text' => '📊 Reports', 'class' => 'secondary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="card">
<div class="card-head"><span class="card-title"><span>📈</span> Statistics</span></div>
<div class="card-body">
<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $total ?></div><div class="stat-lbl">Total Pages</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $avgScore ?? '—' ?></div><div class="stat-lbl">Avg Score</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $statsOk ?></div><div class="stat-lbl">Good</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--warning)"><?= $statsMed ?></div><div class="stat-lbl">Medium</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--danger)"><?= $statsHigh ?></div><div class="stat-lbl">High Priority</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $statsQW ?></div><div class="stat-lbl">Quick Wins</div></div>
</div>
</div>
</div>

<?php if ($total === 0): ?>
<div class="alert alert-info"><span>💡</span><div>No pages found. Create pages in <a href="/admin/pages.php" style="color:inherit">Pages</a> section.</div></div>
<?php else: ?>

<div class="card">
<div class="card-head">
<span class="card-title"><span>📋</span> Pages</span>
<span style="font-size:12px;color:var(--muted)"><?= $shown ?> of <?= $total ?></span>
</div>
<div class="card-body">
<div class="filters">
<span style="font-weight:600;color:var(--text2);font-size:12px">Status:</span>
<div class="filter-group">
<a href="<?= esc($buildUrl('all', null, null)) ?>" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
<a href="<?= esc($buildUrl('high', null, null)) ?>" class="filter-btn <?= $statusFilter === 'high' ? 'active danger' : '' ?>">🔴 High</a>
<a href="<?= esc($buildUrl('medium', null, null)) ?>" class="filter-btn <?= $statusFilter === 'medium' ? 'active warning' : '' ?>">🟡 Medium</a>
<a href="<?= esc($buildUrl('ok', null, null)) ?>" class="filter-btn <?= $statusFilter === 'ok' ? 'active success' : '' ?>">🟢 Good</a>
<a href="<?= esc($buildUrl('unknown', null, null)) ?>" class="filter-btn <?= $statusFilter === 'unknown' ? 'active' : '' ?>">⚪ No Data</a>
</div>

<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Opportunity:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, '', null)) ?>">All</option>
<option value="<?= esc($buildUrl(null, 'quick_win', null)) ?>" <?= $oppFilter === 'quick_win' ? 'selected' : '' ?>>⚡ Quick Win</option>
<option value="<?= esc($buildUrl(null, 'improve', null)) ?>" <?= $oppFilter === 'improve' ? 'selected' : '' ?>>📈 Improve</option>
<option value="<?= esc($buildUrl(null, 'competitive', null)) ?>" <?= $oppFilter === 'competitive' ? 'selected' : '' ?>>🏆 Competitive</option>
<option value="<?= esc($buildUrl(null, 'strong', null)) ?>" <?= $oppFilter === 'strong' ? 'selected' : '' ?>>💪 Strong</option>
</select>

<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Sort:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, null, '')) ?>">Default</option>
<option value="<?= esc($buildUrl(null, null, 'score_desc')) ?>" <?= $sortBy === 'score_desc' ? 'selected' : '' ?>>Score ↓</option>
<option value="<?= esc($buildUrl(null, null, 'score_asc')) ?>" <?= $sortBy === 'score_asc' ? 'selected' : '' ?>>Score ↑</option>
<option value="<?= esc($buildUrl(null, null, 'opp')) ?>" <?= $sortBy === 'opp' ? 'selected' : '' ?>>Opportunity</option>
</select>
</div>

<?php if ($shown === 0): ?>
<div class="empty"><p>No pages match filters.</p></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Score</th><th>SEO Status</th><th>Words</th><th>Keyword</th><th>KD</th><th>Opportunity</th><th>Freshness</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($filtered as $p): 
$scClass = $p['score'] === null ? 'muted' : ($p['score'] >= 80 ? 'success' : ($p['score'] >= 60 ? 'warning' : 'danger'));
$stClass = match($p['seo_status']) { 'ok'=>'success', 'medium'=>'warning', 'high'=>'danger', default=>'muted' };
$stLabel = match($p['seo_status']) { 'ok'=>'Good', 'medium'=>'Medium', 'high'=>'High Priority', default=>'No Data' };
$lenClass = match($p['length_status']) { 'optimal'=>'success', 'short'=>'danger', 'long'=>'warning', default=>'muted' };
$kdClass = $p['kd'] === null ? 'muted' : ($p['kd'] < 30 ? 'success' : ($p['kd'] < 60 ? 'warning' : 'danger'));
$oppClass = match($p['opportunity']) { 'quick_win'=>'success', 'improve'=>'info', 'competitive'=>'warning', 'strong'=>'purple', default=>'muted' };
$oppLabel = match($p['opportunity']) { 'quick_win'=>'⚡ Quick Win', 'improve'=>'📈 Improve', 'competitive'=>'🏆 Competitive', 'strong'=>'💪 Strong', 'maintain'=>'🔒 Maintain', default=>'—' };
$freshClass = match($p['freshness']) { 'fresh'=>'success', 'stale'=>'warning', 'outdated'=>'danger', default=>'muted' };
$freshLabel = match($p['freshness']) { 'fresh'=>'Fresh', 'stale'=>'Stale', 'outdated'=>'Outdated', default=>'No Report' };
?>
<tr>
<td><?= $p['id'] ?></td>
<td><strong><?= esc($p['title'] ?: '—') ?></strong></td>
<td style="font-family:monospace;font-size:11px;color:var(--muted)"><?= esc($p['slug'] ?: '—') ?></td>
<td><span class="tag <?= $p['page_status'] === 'published' ? 'success' : 'warning' ?>"><?= ucfirst($p['page_status']) ?></span></td>
<td><span class="tag <?= $scClass ?>"><?= $p['score'] ?? '—' ?></span></td>
<td><span class="tag <?= $stClass ?>"><?= $stLabel ?></span></td>
<td>
<?= number_format($p['word_count']) ?>
<?php if ($p['rec_words']): ?><br><span style="font-size:10px;color:var(--muted)">/ <?= number_format($p['rec_words']) ?></span><?php endif; ?>
</td>
<td style="max-width:120px;overflow:hidden;text-overflow:ellipsis"><?= esc($p['keyword'] ?? '—') ?></td>
<td><span class="tag <?= $kdClass ?>"><?= $p['kd'] ?? '—' ?></span></td>
<td><span class="tag <?= $oppClass ?>"><?= $oppLabel ?></span></td>
<td><span class="tag <?= $freshClass ?>"><?= $freshLabel ?></span></td>
<td style="white-space:nowrap">
<?php if ($p['latest_id']): ?><a href="/admin/ai-seo-reports.php?id=<?= urlencode($p['latest_id']) ?>" class="btn btn-secondary">📄</a><?php endif; ?>
<a href="/admin/ai-seo-assistant.php?page_id=<?= $p['id'] ?>" class="btn btn-success">🔍</a>
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
</div>
</body>
</html>
