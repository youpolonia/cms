<?php
/**
 * AI SEO Content Overview — Pages & Articles
 * Merged from ai-seo-pages.php + ai-seo-articles.php
 * Catppuccin Dark UI
 */
if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) { die('Cannot determine CMS_ROOT'); }
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

require_once CMS_ROOT . '/core/ai_seo_assistant.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/content_renderer.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// ── Load all reports ──
$reports = ai_seo_assistant_list_reports();
$reportsByPageId = [];
$reportsByArticleId = [];
foreach ($reports as $r) {
    if (!empty($r['page_id'])) {
        $pid = (string)$r['page_id'];
        if (!isset($reportsByPageId[$pid])) $reportsByPageId[$pid] = [];
        $reportsByPageId[$pid][] = $r;
    }
    if (!empty($r['article_id'])) {
        $aid = (string)$r['article_id'];
        if (!isset($reportsByArticleId[$aid])) $reportsByArticleId[$aid] = [];
        $reportsByArticleId[$aid][] = $r;
    }
}

// ── Shared SEO enrichment ──
function enrichWithSeo(array $item, ?array $latest): array {
    $score = $latest['health_score'] ?? $latest['content_score'] ?? null;
    if ($score !== null) $score = (int)$score;

    $status = $score === null ? 'unknown' : ($score >= 80 ? 'ok' : ($score >= 60 ? 'medium' : 'high'));

    $contentText = ContentRenderer::toText($item['content'] ?? '');
    $wordCount = $contentText ? count(preg_split('/\s+/u', trim($contentText), -1, PREG_SPLIT_NO_EMPTY)) : 0;
    $recWords = $latest['recommended_word_count'] ?? 0;

    $lengthStatus = 'unknown';
    if ($recWords > 0 && $wordCount > 0) {
        $ratio = $wordCount / $recWords;
        $lengthStatus = $ratio < 0.8 ? 'short' : ($ratio <= 1.2 ? 'optimal' : 'long');
    }

    $keyword = $item['focus_keyword'] ?? ($latest['keyword'] ?? null);
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
        $itemTime = strtotime($item['updated_at'] ?? '');
        if ($repTime && $itemTime) {
            if ($itemTime > $repTime) $fresh = 'outdated';
            elseif ((time() - $repTime) / 86400 > 30) $fresh = 'stale';
            else $fresh = 'fresh';
        }
    }

    return [
        'score' => $score,
        'seo_status' => $status,
        'word_count' => $wordCount,
        'rec_words' => $recWords > 0 ? $recWords : null,
        'length_status' => $lengthStatus,
        'keyword' => $keyword,
        'kd' => $kd,
        'opportunity' => $opp,
        'freshness' => $fresh,
        'latest_id' => $latest['id'] ?? null,
    ];
}

// ── Load pages ──
$allItems = [];
$categories = [];
try {
    $pdo = \core\Database::connection();

    // Pages
    $stmt = $pdo->query("SELECT id, title, slug, status, updated_at, content FROM pages ORDER BY updated_at DESC LIMIT 500");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $pid = (string)$row['id'];
        $latest = $reportsByPageId[$pid][0] ?? null;
        $seo = enrichWithSeo($row, $latest);
        $allItems[] = array_merge($seo, [
            'id'            => $row['id'],
            'type'          => 'page',
            'title'         => $row['title'],
            'slug'          => $row['slug'],
            'item_status'   => $row['status'],
            'updated_at'    => $row['updated_at'],
            'category_name' => null,
            'category_id'   => null,
            'views'         => null,
            'report_count'  => count($reportsByPageId[$pid] ?? []),
        ]);
    }

    // Categories for filter
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    while ($row = $catStmt->fetch(\PDO::FETCH_ASSOC)) {
        $categories[$row['id']] = $row['name'];
    }

    // Articles
    $stmt = $pdo->query("
        SELECT a.id, a.title, a.slug, a.status, a.content, a.focus_keyword,
               a.published_at, a.views, a.updated_at, a.category_id, c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.updated_at DESC LIMIT 500
    ");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $aid = (string)$row['id'];
        $latest = $reportsByArticleId[$aid][0] ?? null;
        $seo = enrichWithSeo($row, $latest);
        $allItems[] = array_merge($seo, [
            'id'            => $row['id'],
            'type'          => 'article',
            'title'         => $row['title'],
            'slug'          => $row['slug'],
            'item_status'   => $row['status'],
            'updated_at'    => $row['updated_at'],
            'category_name' => $row['category_name'] ?: '—',
            'category_id'   => $row['category_id'],
            'views'         => (int)($row['views'] ?? 0),
            'report_count'  => count($reportsByArticleId[$aid] ?? []),
        ]);
    }
} catch (\Exception $e) { $allItems = []; }

// ── Filters ──
$typeFilter   = $_GET['type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$oppFilter    = $_GET['opp'] ?? '';
$catFilter    = $_GET['cat'] ?? '';
$sortBy       = $_GET['sort'] ?? '';

$filtered = $allItems;

if ($typeFilter === 'pages')    $filtered = array_filter($filtered, fn($i) => $i['type'] === 'page');
elseif ($typeFilter === 'articles') $filtered = array_filter($filtered, fn($i) => $i['type'] === 'article');

if ($statusFilter !== 'all') $filtered = array_filter($filtered, fn($i) => $i['seo_status'] === $statusFilter);
if ($oppFilter !== '')       $filtered = array_filter($filtered, fn($i) => $i['opportunity'] === $oppFilter);
if ($catFilter !== '')       $filtered = array_filter($filtered, fn($i) => (string)$i['category_id'] === $catFilter);

$oppPriority = ['quick_win'=>5,'improve'=>4,'competitive'=>3,'strong'=>2,'maintain'=>1,'no_data'=>0];
if ($sortBy === 'score_desc')  usort($filtered, fn($a,$b) => ($b['score'] ?? -1) - ($a['score'] ?? -1));
elseif ($sortBy === 'score_asc')   usort($filtered, fn($a,$b) => ($a['score'] ?? 999) - ($b['score'] ?? 999));
elseif ($sortBy === 'views_desc')  usort($filtered, fn($a,$b) => ($b['views'] ?? 0) - ($a['views'] ?? 0));
elseif ($sortBy === 'views_asc')   usort($filtered, fn($a,$b) => ($a['views'] ?? 0) - ($b['views'] ?? 0));
elseif ($sortBy === 'opp')         usort($filtered, fn($a,$b) => $oppPriority[$b['opportunity']] - $oppPriority[$a['opportunity']]);

$filtered = array_values($filtered);
$total = count($allItems);
$totalPages = count(array_filter($allItems, fn($i) => $i['type'] === 'page'));
$totalArticles = count(array_filter($allItems, fn($i) => $i['type'] === 'article'));
$shown = count($filtered);

// Stats (from full set, not filtered)
$statsOk   = count(array_filter($allItems, fn($i) => $i['seo_status'] === 'ok'));
$statsMed  = count(array_filter($allItems, fn($i) => $i['seo_status'] === 'medium'));
$statsHigh = count(array_filter($allItems, fn($i) => $i['seo_status'] === 'high'));
$statsUnk  = count(array_filter($allItems, fn($i) => $i['seo_status'] === 'unknown'));
$statsQW   = count(array_filter($allItems, fn($i) => $i['opportunity'] === 'quick_win'));
$scores    = array_filter(array_column($allItems, 'score'), fn($v) => $v !== null);
$avgScore  = !empty($scores) ? (int)round(array_sum($scores) / count($scores)) : null;

$buildUrl = function($ty = null, $st = null, $op = null, $so = null, $ct = null) use ($typeFilter, $statusFilter, $oppFilter, $sortBy, $catFilter) {
    $p = [];
    $t = $ty !== null ? $ty : $typeFilter;
    $s = $st !== null ? $st : $statusFilter;
    $o = $op !== null ? $op : $oppFilter;
    $r = $so !== null ? $so : $sortBy;
    $c = $ct !== null ? $ct : $catFilter;
    if ($t !== 'all')  $p['type'] = $t;
    if ($s !== 'all')  $p['status'] = $s;
    if ($o !== '')     $p['opp'] = $o;
    if ($r !== '')     $p['sort'] = $r;
    if ($c !== '')     $p['cat'] = $c;
    return '/admin/ai-seo-content.php' . (!empty($p) ? '?' . http_build_query($p) : '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEO Content Overview - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--peach:#fab387;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1700px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
/* Tabs */
.tabs{display:flex;gap:4px;margin-bottom:20px;background:var(--bg3);border-radius:12px;padding:4px;width:fit-content}
.tab{padding:10px 20px;border-radius:10px;font-size:13px;font-weight:500;color:var(--text2);text-decoration:none;transition:.15s;display:flex;align-items:center;gap:8px}
.tab:hover{color:var(--text);background:var(--bg4)}
.tab.active{background:var(--accent);color:#000;font-weight:600}
.tab .count{font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(0,0,0,.15)}
.tab.active .count{background:rgba(0,0,0,.2)}
/* Stats */
.stat-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:12px}
@media(max-width:1400px){.stat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:600px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:12px;padding:16px;text-align:center}
.stat-val{font-size:24px;font-weight:700;margin-bottom:4px}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
/* Filters */
.filters{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:20px}
.filter-group{display:flex;gap:6px;flex-wrap:wrap}
.filter-btn{padding:8px 14px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text2);font-size:12px;text-decoration:none;transition:.15s}
.filter-btn:hover{background:var(--bg4);color:var(--text)}
.filter-btn.active{background:var(--accent);color:#000;border-color:var(--accent)}
.filter-btn.active.danger{background:var(--danger)}
.filter-btn.active.warning{background:var(--warning);color:#000}
.filter-btn.active.success{background:var(--success);color:#000}
.form-select{padding:8px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px}
.form-select option{background:var(--bg2);color:var(--text)}
/* Table */
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
.tag.peach{background:rgba(250,179,135,.2);color:var(--peach)}
.tag.page{background:rgba(137,180,250,.15);color:var(--accent)}
.tag.article{background:rgba(203,166,247,.15);color:var(--purple)}
.btn{display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:11px;font-weight:500;border:none;border-radius:6px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-success{background:var(--success);color:#000}
.empty{text-align:center;padding:40px;color:var(--muted)}
.alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;gap:12px}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.truncate{max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$tabLabel = match($typeFilter) { 'pages'=>'Pages', 'articles'=>'Articles', default=>'All Content' };
$pageHeader = [
    'icon' => '📋',
    'title' => "SEO: {$tabLabel}",
    'description' => 'SEO status for all pages and articles',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-assistant', 'text' => '🔍 Analyze', 'class' => 'primary'],
        ['type' => 'link', 'url' => '/admin/ai-seo-reports', 'text' => '📊 Reports', 'class' => 'secondary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<!-- Tabs -->
<div class="tabs">
<a href="<?= esc($buildUrl('all', 'all', '', '', '')) ?>" class="tab <?= $typeFilter === 'all' ? 'active' : '' ?>">📋 All <span class="count"><?= $total ?></span></a>
<a href="<?= esc($buildUrl('pages', 'all', '', '', '')) ?>" class="tab <?= $typeFilter === 'pages' ? 'active' : '' ?>">📄 Pages <span class="count"><?= $totalPages ?></span></a>
<a href="<?= esc($buildUrl('articles', 'all', '', '', '')) ?>" class="tab <?= $typeFilter === 'articles' ? 'active' : '' ?>">📝 Articles <span class="count"><?= $totalArticles ?></span></a>
</div>

<!-- Stats -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📈</span> Statistics</span></div>
<div class="card-body">
<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $total ?></div><div class="stat-lbl">Total Content</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $avgScore ?? '—' ?></div><div class="stat-lbl">Avg Score</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $statsOk ?></div><div class="stat-lbl">Good (80+)</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--warning)"><?= $statsMed ?></div><div class="stat-lbl">Medium (60-79)</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--danger)"><?= $statsHigh ?></div><div class="stat-lbl">High Priority</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--muted)"><?= $statsUnk ?></div><div class="stat-lbl">No Data</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $statsQW ?></div><div class="stat-lbl">Quick Wins</div></div>
</div>
</div>
</div>

<?php if ($total === 0): ?>
<div class="alert alert-info"><span>💡</span><div>No content found. Create pages in <a href="/admin/pages" style="color:inherit">Pages</a> or articles in <a href="/admin/articles" style="color:inherit">Articles</a>.</div></div>
<?php else: ?>

<!-- Data Table -->
<div class="card">
<div class="card-head">
<span class="card-title"><span>📋</span> Content</span>
<span style="font-size:12px;color:var(--muted)"><?= $shown ?> of <?= $total ?></span>
</div>
<div class="card-body">
<div class="filters">
<span style="font-weight:600;color:var(--text2);font-size:12px">SEO Status:</span>
<div class="filter-group">
<a href="<?= esc($buildUrl(null, 'all', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
<a href="<?= esc($buildUrl(null, 'high', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'high' ? 'active danger' : '' ?>">🔴 High</a>
<a href="<?= esc($buildUrl(null, 'medium', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'medium' ? 'active warning' : '' ?>">🟡 Medium</a>
<a href="<?= esc($buildUrl(null, 'ok', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'ok' ? 'active success' : '' ?>">🟢 Good</a>
<a href="<?= esc($buildUrl(null, 'unknown', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'unknown' ? 'active' : '' ?>">⚪ No Data</a>
</div>

<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Opportunity:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, null, '', null, null)) ?>">All</option>
<option value="<?= esc($buildUrl(null, null, 'quick_win', null, null)) ?>" <?= $oppFilter === 'quick_win' ? 'selected' : '' ?>>⚡ Quick Win</option>
<option value="<?= esc($buildUrl(null, null, 'improve', null, null)) ?>" <?= $oppFilter === 'improve' ? 'selected' : '' ?>>📈 Improve</option>
<option value="<?= esc($buildUrl(null, null, 'competitive', null, null)) ?>" <?= $oppFilter === 'competitive' ? 'selected' : '' ?>>🏆 Competitive</option>
<option value="<?= esc($buildUrl(null, null, 'strong', null, null)) ?>" <?= $oppFilter === 'strong' ? 'selected' : '' ?>>💪 Strong</option>
</select>

<?php if (!empty($categories) && $typeFilter !== 'pages'): ?>
<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Category:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, null, null, null, '')) ?>">All</option>
<?php foreach ($categories as $cid => $cname): ?>
<option value="<?= esc($buildUrl(null, null, null, null, (string)$cid)) ?>" <?= $catFilter === (string)$cid ? 'selected' : '' ?>><?= esc($cname) ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>

<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Sort:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, null, null, '', null)) ?>">Default (updated)</option>
<option value="<?= esc($buildUrl(null, null, null, 'score_desc', null)) ?>" <?= $sortBy === 'score_desc' ? 'selected' : '' ?>>Score ↓</option>
<option value="<?= esc($buildUrl(null, null, null, 'score_asc', null)) ?>" <?= $sortBy === 'score_asc' ? 'selected' : '' ?>>Score ↑</option>
<option value="<?= esc($buildUrl(null, null, null, 'views_desc', null)) ?>" <?= $sortBy === 'views_desc' ? 'selected' : '' ?>>Views ↓</option>
<option value="<?= esc($buildUrl(null, null, null, 'opp', null)) ?>" <?= $sortBy === 'opp' ? 'selected' : '' ?>>Opportunity</option>
</select>
</div>

<?php if ($shown === 0): ?>
<div class="empty"><p>No content matches filters.</p></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr>
<th>Type</th><th>Title</th><th>Slug</th>
<?php if ($typeFilter !== 'pages'): ?><th>Category</th><?php endif; ?>
<th>Status</th><th>Score</th><th>SEO</th><th>Words</th><th>Keyword</th><th>KD</th><th>Opportunity</th><th>Freshness</th>
<?php if ($typeFilter !== 'pages'): ?><th>Views</th><?php endif; ?>
<th>Actions</th>
</tr></thead>
<tbody>
<?php foreach ($filtered as $item):
$scClass = $item['score'] === null ? 'muted' : ($item['score'] >= 80 ? 'success' : ($item['score'] >= 60 ? 'warning' : 'danger'));
$stClass = match($item['seo_status']) { 'ok'=>'success', 'medium'=>'warning', 'high'=>'danger', default=>'muted' };
$stLabel = match($item['seo_status']) { 'ok'=>'Good', 'medium'=>'Medium', 'high'=>'High Priority', default=>'No Data' };
$kdClass = $item['kd'] === null ? 'muted' : ($item['kd'] < 30 ? 'success' : ($item['kd'] < 60 ? 'warning' : 'danger'));
$oppClass = match($item['opportunity']) { 'quick_win'=>'success', 'improve'=>'info', 'competitive'=>'warning', 'strong'=>'purple', default=>'muted' };
$oppLabel = match($item['opportunity']) { 'quick_win'=>'⚡ Quick Win', 'improve'=>'📈 Improve', 'competitive'=>'🏆 Competitive', 'strong'=>'💪 Strong', 'maintain'=>'🔒 Maintain', default=>'—' };
$freshClass = match($item['freshness']) { 'fresh'=>'success', 'stale'=>'warning', 'outdated'=>'danger', default=>'muted' };
$freshLabel = match($item['freshness']) { 'fresh'=>'Fresh', 'stale'=>'Stale', 'outdated'=>'Outdated', default=>'No Report' };
$itemStatusClass = match($item['item_status']) { 'published'=>'success', 'draft'=>'warning', 'archived'=>'muted', default=>'muted' };
$isPage = $item['type'] === 'page';
$analyzeUrl = $isPage ? "/admin/ai-seo-assistant.php?page_id={$item['id']}" : "/admin/ai-seo-assistant.php?article_id={$item['id']}";
?>
<tr>
<td><span class="tag <?= $isPage ? 'page' : 'article' ?>"><?= $isPage ? '📄 Page' : '📝 Article' ?></span></td>
<td><strong class="truncate" style="display:block"><?= esc($item['title'] ?: '—') ?></strong></td>
<td style="font-family:monospace;font-size:11px;color:var(--muted)"><?= esc($item['slug'] ?: '—') ?></td>
<?php if ($typeFilter !== 'pages'): ?>
<td><?= $item['category_name'] ? '<span class="tag peach">' . esc($item['category_name']) . '</span>' : '<span style="color:var(--muted)">—</span>' ?></td>
<?php endif; ?>
<td><span class="tag <?= $itemStatusClass ?>"><?= ucfirst($item['item_status']) ?></span></td>
<td><span class="tag <?= $scClass ?>"><?= $item['score'] ?? '—' ?></span></td>
<td><span class="tag <?= $stClass ?>"><?= $stLabel ?></span></td>
<td>
<?= number_format($item['word_count']) ?>
<?php if ($item['rec_words']): ?><br><span style="font-size:10px;color:var(--muted)">/ <?= number_format($item['rec_words']) ?></span><?php endif; ?>
</td>
<td class="truncate"><?= esc($item['keyword'] ?? '—') ?></td>
<td><span class="tag <?= $kdClass ?>"><?= $item['kd'] ?? '—' ?></span></td>
<td><span class="tag <?= $oppClass ?>"><?= $oppLabel ?></span></td>
<td><span class="tag <?= $freshClass ?>"><?= $freshLabel ?></span></td>
<?php if ($typeFilter !== 'pages'): ?>
<td style="color:var(--muted)"><?= $item['views'] !== null ? number_format($item['views']) : '—' ?></td>
<?php endif; ?>
<td style="white-space:nowrap">
<?php if ($item['latest_id']): ?><a href="/admin/ai-seo-reports.php?id=<?= urlencode($item['latest_id']) ?>" class="btn btn-secondary" title="View Report">📄</a><?php endif; ?>
<a href="<?= esc($analyzeUrl) ?>" class="btn btn-success" title="Analyze">🔍</a>
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
