<?php
/**
 * SEO Score Timeline — Track SEO scores over time
 * Chart.js visualization + keyword tracking
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
require_once CMS_ROOT . '/core/ai_seo_assistant.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pdo = \core\Database::connection();
$days = (int)($_GET['days'] ?? 90);
if ($days < 7) $days = 7;
if ($days > 365) $days = 365;

// Get timeline data
$timeline = ai_seo_get_score_timeline($days);

// Get tracked keywords
$keywords = ai_seo_get_tracked_keywords();

// Get recent score history
$recentHistory = [];
try {
    $stmt = $pdo->prepare("SELECT h.*, 
        CASE h.entity_type WHEN 'page' THEN (SELECT title FROM pages WHERE id = h.entity_id)
                           WHEN 'article' THEN (SELECT title FROM articles WHERE id = h.entity_id) END as title
        FROM seo_score_history h 
        ORDER BY h.analyzed_at DESC LIMIT 20");
    $stmt->execute();
    $recentHistory = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}

// Stats
$totalAnalyses = 0;
$avgScore = null;
$bestPage = null;
$worstPage = null;
try {
    $totalAnalyses = (int)$pdo->query("SELECT COUNT(*) FROM seo_score_history")->fetchColumn();
    $avgRow = $pdo->query("SELECT AVG(seo_score) as avg_s FROM seo_score_history WHERE seo_score IS NOT NULL")->fetch(\PDO::FETCH_ASSOC);
    $avgScore = $avgRow['avg_s'] !== null ? (int)round($avgRow['avg_s']) : null;
    
    $bestRow = $pdo->query("SELECT entity_type, entity_id, MAX(seo_score) as score FROM seo_score_history WHERE seo_score IS NOT NULL GROUP BY entity_type, entity_id ORDER BY score DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    $worstRow = $pdo->query("SELECT entity_type, entity_id, MIN(seo_score) as score FROM seo_score_history WHERE seo_score IS NOT NULL GROUP BY entity_type, entity_id ORDER BY score ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    
    if ($bestRow) {
        $table = $bestRow['entity_type'] === 'page' ? 'pages' : 'articles';
        $title = $pdo->query("SELECT title FROM {$table} WHERE id = " . (int)$bestRow['entity_id'])->fetchColumn();
        $bestPage = ['title' => $title ?: 'Unknown', 'score' => (int)$bestRow['score']];
    }
    if ($worstRow) {
        $table = $worstRow['entity_type'] === 'page' ? 'pages' : 'articles';
        $title = $pdo->query("SELECT title FROM {$table} WHERE id = " . (int)$worstRow['entity_id'])->fetchColumn();
        $worstPage = ['title' => $title ?: 'Unknown', 'score' => (int)$worstRow['score']];
    }
} catch (\Throwable $e) {}

// Chart data
$chartLabels = array_map(fn($r) => $r['date'], $timeline);
$chartAvg = array_map(fn($r) => $r['avg_score'] !== null ? round($r['avg_score'], 1) : null, $timeline);
$chartMin = array_map(fn($r) => $r['min_score'], $timeline);
$chartMax = array_map(fn($r) => $r['max_score'], $timeline);
$chartCount = array_map(fn($r) => (int)$r['analyses'], $timeline);

// Unique tracked keywords
$uniqueKeywords = [];
foreach ($keywords as $kw) {
    $k = mb_strtolower(trim($kw['keyword']));
    if (!isset($uniqueKeywords[$k])) $uniqueKeywords[$k] = ['keyword' => $kw['keyword'], 'pages' => 0, 'best_score' => 0, 'last_tracked' => '', 'entities' => []];
    $uniqueKeywords[$k]['pages']++;
    if (($kw['score'] ?? 0) > $uniqueKeywords[$k]['best_score']) $uniqueKeywords[$k]['best_score'] = (int)$kw['score'];
    if ($kw['last_tracked'] > $uniqueKeywords[$k]['last_tracked']) $uniqueKeywords[$k]['last_tracked'] = $kw['last_tracked'];
    $uniqueKeywords[$k]['entities'][] = $kw;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEO Score Timeline - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--peach:#fab387;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.grid{display:grid;gap:20px}
.grid-2{grid-template-columns:1fr 1fr}
.grid-4{grid-template-columns:repeat(4,1fr)}
@media(max-width:900px){.grid-2,.grid-4{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-box{background:var(--bg);border-radius:12px;padding:20px;text-align:center}
.stat-val{font-size:28px;font-weight:700}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.stat-sub{font-size:11px;margin-top:6px}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.tag.page{background:rgba(137,180,250,.15);color:var(--accent)}
.tag.article{background:rgba(203,166,247,.15);color:var(--purple)}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th,.data-table td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg);white-space:nowrap}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.filter-group{display:flex;gap:6px}
.filter-btn{padding:6px 12px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text2);font-size:12px;text-decoration:none;transition:.15s}
.filter-btn:hover{background:var(--bg4)}
.filter-btn.active{background:var(--accent);color:#000;border-color:var(--accent)}
.empty{text-align:center;padding:40px;color:var(--muted)}
.chart-wrap{position:relative;height:300px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '📈',
    'title' => 'SEO Score Timeline',
    'description' => 'Track SEO improvements over time',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--success-color), var(--cyan)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<!-- Stats -->
<div class="grid grid-4" style="margin-bottom:20px">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $totalAnalyses ?></div><div class="stat-lbl">Total Analyses</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $avgScore ?? '—' ?></div><div class="stat-lbl">Avg Score</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $bestPage ? $bestPage['score'] : '—' ?></div><div class="stat-lbl">Best Score</div><?php if ($bestPage): ?><div class="stat-sub" style="color:var(--muted)"><?= esc(mb_substr($bestPage['title'], 0, 25)) ?></div><?php endif; ?></div>
<div class="stat-box"><div class="stat-val" style="color:var(--danger)"><?= $worstPage ? $worstPage['score'] : '—' ?></div><div class="stat-lbl">Lowest Score</div><?php if ($worstPage): ?><div class="stat-sub" style="color:var(--muted)"><?= esc(mb_substr($worstPage['title'], 0, 25)) ?></div><?php endif; ?></div>
</div>

<!-- Chart -->
<div class="card">
<div class="card-head">
<span class="card-title"><span>📈</span> Score Trend</span>
<div class="filter-group">
<a href="?days=30" class="filter-btn <?= $days == 30 ? 'active' : '' ?>">30d</a>
<a href="?days=90" class="filter-btn <?= $days == 90 ? 'active' : '' ?>">90d</a>
<a href="?days=180" class="filter-btn <?= $days == 180 ? 'active' : '' ?>">180d</a>
<a href="?days=365" class="filter-btn <?= $days == 365 ? 'active' : '' ?>">1y</a>
</div>
</div>
<div class="card-body">
<?php if (empty($timeline)): ?>
<div class="empty"><p style="font-size:28px;margin-bottom:12px">📊</p><p>No data yet. Run SEO analyses to start tracking scores.</p><p style="margin-top:8px"><a href="/admin/ai-seo-assistant.php" class="btn btn-secondary">🔍 Run Analysis</a></p></div>
<?php else: ?>
<div class="chart-wrap"><canvas id="scoreChart"></canvas></div>
<?php endif; ?>
</div>
</div>

<div class="grid grid-2">
<!-- Tracked Keywords -->
<div class="card">
<div class="card-head"><span class="card-title"><span>🎯</span> Tracked Keywords</span><span style="font-size:12px;color:var(--muted)"><?= count($uniqueKeywords) ?> keywords</span></div>
<div class="card-body" style="padding:0">
<?php if (empty($uniqueKeywords)): ?>
<div class="empty"><p>No keywords tracked yet.</p></div>
<?php else: ?>
<table class="data-table">
<thead><tr><th>Keyword</th><th>Pages</th><th>Best Score</th><th>Last Tracked</th></tr></thead>
<tbody>
<?php foreach ($uniqueKeywords as $kw): 
$sc = $kw['best_score'];
$scClass = $sc >= 80 ? 'success' : ($sc >= 60 ? 'warning' : ($sc > 0 ? 'danger' : 'muted'));
?>
<tr>
<td><strong><?= esc($kw['keyword']) ?></strong></td>
<td><?= $kw['pages'] ?></td>
<td><span class="tag <?= $scClass ?>"><?= $sc ?: '—' ?></span></td>
<td style="color:var(--muted)"><?= substr($kw['last_tracked'], 0, 10) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>

<!-- Recent Analyses -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Recent Analyses</span></div>
<div class="card-body" style="padding:0">
<?php if (empty($recentHistory)): ?>
<div class="empty"><p>No analyses recorded yet.</p></div>
<?php else: ?>
<table class="data-table">
<thead><tr><th>Content</th><th>Type</th><th>Score</th><th>Keyword</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($recentHistory as $h):
$sc = $h['seo_score'];
$scClass = $sc === null ? 'muted' : ($sc >= 80 ? 'success' : ($sc >= 60 ? 'warning' : 'danger'));
?>
<tr>
<td><strong><?= esc(mb_substr($h['title'] ?? 'Unknown', 0, 30)) ?></strong></td>
<td><span class="tag <?= $h['entity_type'] ?>"><?= $h['entity_type'] === 'page' ? '📄' : '📝' ?></span></td>
<td><span class="tag <?= $scClass ?>"><?= $sc ?? '—' ?></span></td>
<td style="color:var(--muted)"><?= esc(mb_substr($h['focus_keyword'] ?? '—', 0, 20)) ?></td>
<td style="color:var(--muted)"><?= substr($h['analyzed_at'], 0, 16) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>
</div>

</div>

<?php if (!empty($timeline)): ?>
<script>
const ctx = document.getElementById('scoreChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            {
                label: 'Avg Score',
                data: <?= json_encode($chartAvg) ?>,
                borderColor: '#89b4fa',
                backgroundColor: 'rgba(137,180,250,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: '#89b4fa',
            },
            {
                label: 'Min Score',
                data: <?= json_encode($chartMin) ?>,
                borderColor: '#f38ba8',
                borderDash: [5,5],
                tension: 0.3,
                pointRadius: 2,
                fill: false,
            },
            {
                label: 'Max Score',
                data: <?= json_encode($chartMax) ?>,
                borderColor: '#a6e3a1',
                borderDash: [5,5],
                tension: 0.3,
                pointRadius: 2,
                fill: false,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { min: 0, max: 100, grid: { color: '#313244' }, ticks: { color: '#6c7086' } },
            x: { grid: { color: '#313244' }, ticks: { color: '#6c7086', maxRotation: 45 } }
        },
        plugins: {
            legend: { labels: { color: '#cdd6f4' } },
            tooltip: { backgroundColor: '#1e1e2e', borderColor: '#313244', borderWidth: 1, titleColor: '#cdd6f4', bodyColor: '#a6adc8' }
        }
    }
});
</script>
<?php endif; ?>
</body>
</html>
