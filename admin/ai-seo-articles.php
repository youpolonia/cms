<?php
/**
 * AI SEO Articles - Modern Dark UI
 * Based on ai-seo-pages.php but adapted for articles table
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
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/content_renderer.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Load articles with categories
$articles = [];
$categories = [];
try {
    $pdo = \core\Database::connection();

    // Get categories for filter
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    while ($row = $catStmt->fetch(\PDO::FETCH_ASSOC)) {
        $categories[$row['id']] = $row['name'];
    }

    // Get articles with category names
    $stmt = $pdo->query("
        SELECT a.id, a.title, a.slug, a.status, a.content, a.focus_keyword,
               a.published_at, a.views, a.updated_at, a.category_id, c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.updated_at DESC LIMIT 500
    ");
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $articles[] = $row;
    }
} catch (\Exception $e) { $articles = []; }

// Load reports
$reports = ai_seo_assistant_list_reports();
$reportsByArticleId = [];
foreach ($reports as $r) {
    $aid = $r['article_id'] ?? null;
    if ($aid) {
        if (!isset($reportsByArticleId[$aid])) $reportsByArticleId[$aid] = [];
        $reportsByArticleId[$aid][] = $r;
    }
}

// Process articles with SEO data
$articlesWithSeo = [];
foreach ($articles as $article) {
    $aid = (string)$article['id'];
    $latest = $reportsByArticleId[$aid][0] ?? null;

    $score = $latest['health_score'] ?? $latest['content_score'] ?? null;
    if ($score !== null) $score = (int)$score;

    $status = $score === null ? 'unknown' : ($score >= 80 ? 'ok' : ($score >= 60 ? 'medium' : 'high'));

    $contentText = ContentRenderer::toText($article['content'] ?? '');
    $wordCount = $contentText ? count(preg_split('/\s+/u', trim($contentText), -1, PREG_SPLIT_NO_EMPTY)) : 0;
    $recWords = $latest['recommended_word_count'] ?? 0;

    $lengthStatus = 'unknown';
    if ($recWords > 0 && $wordCount > 0) {
        $ratio = $wordCount / $recWords;
        $lengthStatus = $ratio < 0.8 ? 'short' : ($ratio <= 1.2 ? 'optimal' : 'long');
    }

    $keyword = $article['focus_keyword'] ?: ($latest['keyword'] ?? null);
    $kd = null;

    // Extract keyword difficulty from report if available
    if ($latest && !empty($latest['keyword_count']) && $latest['keyword_count'] > 0) {
        // Try to get KD from the report data
        $reportData = ai_seo_assistant_load_report($latest['id']);
        if ($reportData && isset($reportData['data']['keyword_difficulty'][0]['difficulty'])) {
            $kd = (int)$reportData['data']['keyword_difficulty'][0]['difficulty'];
        }
    }

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
        $articleTime = strtotime($article['updated_at'] ?? '');
        if ($repTime && $articleTime) {
            if ($articleTime > $repTime) $fresh = 'outdated';
            elseif ((time() - $repTime) / 86400 > 30) $fresh = 'stale';
            else $fresh = 'fresh';
        }
    }

    $articlesWithSeo[] = [
        'id' => $article['id'],
        'title' => $article['title'],
        'slug' => $article['slug'],
        'article_status' => $article['status'],
        'category_id' => $article['category_id'],
        'category_name' => $article['category_name'] ?: '—',
        'published_at' => $article['published_at'],
        'views' => (int)($article['views'] ?? 0),
        'updated_at' => $article['updated_at'],
        'score' => $score,
        'seo_status' => $status,
        'word_count' => $wordCount,
        'rec_words' => $recWords > 0 ? $recWords : null,
        'length_status' => $lengthStatus,
        'keyword' => $keyword,
        'kd' => $kd,
        'opportunity' => $opp,
        'freshness' => $fresh,
        'report_count' => count($reportsByArticleId[$aid] ?? []),
        'latest_id' => $latest['id'] ?? null,
    ];
}

// Filters
$statusFilter = $_GET['status'] ?? 'all';
$oppFilter = $_GET['opp'] ?? '';
$catFilter = $_GET['cat'] ?? '';
$sortBy = $_GET['sort'] ?? '';

$filtered = $articlesWithSeo;
if ($statusFilter !== 'all') {
    $filtered = array_filter($filtered, fn($a) => $a['seo_status'] === $statusFilter);
}
if ($oppFilter !== '') {
    $filtered = array_filter($filtered, fn($a) => $a['opportunity'] === $oppFilter);
}
if ($catFilter !== '') {
    $filtered = array_filter($filtered, fn($a) => (string)$a['category_id'] === $catFilter);
}

if ($sortBy === 'score_desc') usort($filtered, fn($a,$b) => ($b['score'] ?? -1) - ($a['score'] ?? -1));
elseif ($sortBy === 'score_asc') usort($filtered, fn($a,$b) => ($a['score'] ?? 999) - ($b['score'] ?? 999));
elseif ($sortBy === 'views_desc') usort($filtered, fn($a,$b) => $b['views'] - $a['views']);
elseif ($sortBy === 'views_asc') usort($filtered, fn($a,$b) => $a['views'] - $b['views']);
elseif ($sortBy === 'opp') usort($filtered, fn($a,$b) => ['quick_win'=>5,'improve'=>4,'competitive'=>3,'strong'=>2,'maintain'=>1,'no_data'=>0][$b['opportunity']] - ['quick_win'=>5,'improve'=>4,'competitive'=>3,'strong'=>2,'maintain'=>1,'no_data'=>0][$a['opportunity']]);

$total = count($articlesWithSeo);
$shown = count($filtered);

// Stats
$statsPublished = count(array_filter($articlesWithSeo, fn($a) => $a['article_status'] === 'published'));
$statsOk = count(array_filter($articlesWithSeo, fn($a) => $a['seo_status'] === 'ok'));
$statsMed = count(array_filter($articlesWithSeo, fn($a) => $a['seo_status'] === 'medium'));
$statsHigh = count(array_filter($articlesWithSeo, fn($a) => $a['seo_status'] === 'high'));
$statsUnk = count(array_filter($articlesWithSeo, fn($a) => $a['seo_status'] === 'unknown'));
$statsQW = count(array_filter($articlesWithSeo, fn($a) => $a['opportunity'] === 'quick_win'));
$scores = array_filter(array_column($articlesWithSeo, 'score'), fn($v) => $v !== null);
$avgScore = !empty($scores) ? (int)round(array_sum($scores) / count($scores)) : null;

$buildUrl = function($st = null, $op = null, $so = null, $ct = null) use ($statusFilter, $oppFilter, $sortBy, $catFilter) {
    $p = [];
    $s = $st !== null ? $st : $statusFilter;
    $o = $op !== null ? $op : $oppFilter;
    $r = $so !== null ? $so : $sortBy;
    $c = $ct !== null ? $ct : $catFilter;
    if ($s !== 'all') $p['status'] = $s;
    if ($o !== '') $p['opp'] = $o;
    if ($r !== '') $p['sort'] = $r;
    if ($c !== '') $p['cat'] = $c;
    return '/admin/ai-seo-articles.php' . (!empty($p) ? '?' . http_build_query($p) : '');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI SEO Articles - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--peach:#fab387;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1700px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:20px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:12px}
@media(max-width:1400px){.stat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:900px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
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
.form-select option{background:var(--bg2);color:var(--text)}
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
$pageHeader = [
    'icon' => '📝',
    'title' => 'SEO Articles Overview',
    'description' => 'SEO status for all CMS articles',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
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
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $total ?></div><div class="stat-lbl">Total Articles</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--peach)"><?= $statsPublished ?></div><div class="stat-lbl">Published</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $avgScore ?? '—' ?></div><div class="stat-lbl">Avg Score</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $statsOk ?></div><div class="stat-lbl">Good</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--warning)"><?= $statsMed ?></div><div class="stat-lbl">Medium</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--danger)"><?= $statsHigh ?></div><div class="stat-lbl">High Priority</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $statsQW ?></div><div class="stat-lbl">Quick Wins</div></div>
</div>
</div>
</div>

<?php if ($total === 0): ?>
<div class="alert alert-info"><span>💡</span><div>No articles found. Create articles in <a href="/admin/articles" style="color:inherit">Articles</a> section.</div></div>
<?php else: ?>

<div class="card">
<div class="card-head">
<span class="card-title"><span>📋</span> Articles</span>
<span style="font-size:12px;color:var(--muted)"><?= $shown ?> of <?= $total ?></span>
</div>
<div class="card-body">
<div class="filters">
<span style="font-weight:600;color:var(--text2);font-size:12px">Status:</span>
<div class="filter-group">
<a href="<?= esc($buildUrl('all', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All</a>
<a href="<?= esc($buildUrl('high', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'high' ? 'active danger' : '' ?>">🔴 High</a>
<a href="<?= esc($buildUrl('medium', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'medium' ? 'active warning' : '' ?>">🟡 Medium</a>
<a href="<?= esc($buildUrl('ok', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'ok' ? 'active success' : '' ?>">🟢 Good</a>
<a href="<?= esc($buildUrl('unknown', null, null, null)) ?>" class="filter-btn <?= $statusFilter === 'unknown' ? 'active' : '' ?>">⚪ No Data</a>
</div>

<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Opportunity:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, '', null, null)) ?>">All</option>
<option value="<?= esc($buildUrl(null, 'quick_win', null, null)) ?>" <?= $oppFilter === 'quick_win' ? 'selected' : '' ?>>⚡ Quick Win</option>
<option value="<?= esc($buildUrl(null, 'improve', null, null)) ?>" <?= $oppFilter === 'improve' ? 'selected' : '' ?>>📈 Improve</option>
<option value="<?= esc($buildUrl(null, 'competitive', null, null)) ?>" <?= $oppFilter === 'competitive' ? 'selected' : '' ?>>🏆 Competitive</option>
<option value="<?= esc($buildUrl(null, 'strong', null, null)) ?>" <?= $oppFilter === 'strong' ? 'selected' : '' ?>>💪 Strong</option>
</select>

<?php if (!empty($categories)): ?>
<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Category:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, null, null, '')) ?>">All Categories</option>
<?php foreach ($categories as $cid => $cname): ?>
<option value="<?= esc($buildUrl(null, null, null, (string)$cid)) ?>" <?= $catFilter === (string)$cid ? 'selected' : '' ?>><?= esc($cname) ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>

<span style="font-weight:600;color:var(--text2);font-size:12px;margin-left:12px">Sort:</span>
<select class="form-select" onchange="location.href=this.value">
<option value="<?= esc($buildUrl(null, null, '', null)) ?>">Default</option>
<option value="<?= esc($buildUrl(null, null, 'score_desc', null)) ?>" <?= $sortBy === 'score_desc' ? 'selected' : '' ?>>Score ↓</option>
<option value="<?= esc($buildUrl(null, null, 'score_asc', null)) ?>" <?= $sortBy === 'score_asc' ? 'selected' : '' ?>>Score ↑</option>
<option value="<?= esc($buildUrl(null, null, 'views_desc', null)) ?>" <?= $sortBy === 'views_desc' ? 'selected' : '' ?>>Views ↓</option>
<option value="<?= esc($buildUrl(null, null, 'views_asc', null)) ?>" <?= $sortBy === 'views_asc' ? 'selected' : '' ?>>Views ↑</option>
<option value="<?= esc($buildUrl(null, null, 'opp', null)) ?>" <?= $sortBy === 'opp' ? 'selected' : '' ?>>Opportunity</option>
</select>
</div>

<?php if ($shown === 0): ?>
<div class="empty"><p>No articles match filters.</p></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th><th>Score</th><th>SEO Status</th><th>Words</th><th>Focus Keyword</th><th>KD</th><th>Opportunity</th><th>Freshness</th><th>Views</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($filtered as $a):
$scClass = $a['score'] === null ? 'muted' : ($a['score'] >= 80 ? 'success' : ($a['score'] >= 60 ? 'warning' : 'danger'));
$stClass = match($a['seo_status']) { 'ok'=>'success', 'medium'=>'warning', 'high'=>'danger', default=>'muted' };
$stLabel = match($a['seo_status']) { 'ok'=>'Good', 'medium'=>'Medium', 'high'=>'High Priority', default=>'No Data' };
$kdClass = $a['kd'] === null ? 'muted' : ($a['kd'] < 30 ? 'success' : ($a['kd'] < 60 ? 'warning' : 'danger'));
$oppClass = match($a['opportunity']) { 'quick_win'=>'success', 'improve'=>'info', 'competitive'=>'warning', 'strong'=>'purple', default=>'muted' };
$oppLabel = match($a['opportunity']) { 'quick_win'=>'⚡ Quick Win', 'improve'=>'📈 Improve', 'competitive'=>'🏆 Competitive', 'strong'=>'💪 Strong', 'maintain'=>'🔒 Maintain', default=>'—' };
$freshClass = match($a['freshness']) { 'fresh'=>'success', 'stale'=>'warning', 'outdated'=>'danger', default=>'muted' };
$freshLabel = match($a['freshness']) { 'fresh'=>'Fresh', 'stale'=>'Stale', 'outdated'=>'Outdated', default=>'No Report' };
$artStatusClass = match($a['article_status']) { 'published'=>'success', 'draft'=>'warning', 'archived'=>'muted', default=>'muted' };
?>
<tr>
<td><?= $a['id'] ?></td>
<td><strong class="truncate" style="display:block"><?= esc($a['title'] ?: '—') ?></strong></td>
<td><span class="tag peach"><?= esc($a['category_name']) ?></span></td>
<td><span class="tag <?= $artStatusClass ?>"><?= ucfirst($a['article_status']) ?></span></td>
<td><span class="tag <?= $scClass ?>"><?= $a['score'] ?? '—' ?></span></td>
<td><span class="tag <?= $stClass ?>"><?= $stLabel ?></span></td>
<td>
<?= number_format($a['word_count']) ?>
<?php if ($a['rec_words']): ?><br><span style="font-size:10px;color:var(--muted)">/ <?= number_format($a['rec_words']) ?></span><?php endif; ?>
</td>
<td class="truncate"><?= esc($a['keyword'] ?? '—') ?></td>
<td><span class="tag <?= $kdClass ?>"><?= $a['kd'] ?? '—' ?></span></td>
<td><span class="tag <?= $oppClass ?>"><?= $oppLabel ?></span></td>
<td><span class="tag <?= $freshClass ?>"><?= $freshLabel ?></span></td>
<td style="color:var(--muted)"><?= number_format($a['views']) ?></td>
<td style="white-space:nowrap">
<?php if ($a['latest_id']): ?><a href="/admin/ai-seo-reports.php?id=<?= urlencode($a['latest_id']) ?>" class="btn btn-secondary" title="View Report">📄</a><?php endif; ?>
<a href="/admin/ai-seo-assistant.php?article_id=<?= $a['id'] ?>" class="btn btn-success" title="Analyze">🔍</a>
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
