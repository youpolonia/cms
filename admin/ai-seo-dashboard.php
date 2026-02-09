<?php
/**
 * AI SEO Dashboard - Modern Dark UI
 */
if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) die('Cannot determine CMS_ROOT');
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


require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_seo_assistant.php';
require_once CMS_ROOT . '/core/ai_internal_linking.php';
require_once CMS_ROOT . '/core/ai_content_decay.php';

function esc($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

// Gather data
$totalPages = $publishedPages = 0;
try {
    $pdo = \core\Database::connection();
    $row = $pdo->query("SELECT COUNT(*) as t, SUM(status='published') as p FROM pages")->fetch(\PDO::FETCH_ASSOC);
    $totalPages = (int)($row['t'] ?? 0);
    $publishedPages = (int)($row['p'] ?? 0);
} catch (\Exception $e) {}

$reports = ai_seo_assistant_list_reports();
$totalReports = count($reports);
$reportsLast30 = 0;
$healthScores = [];
$scoreDist = ['excellent'=>0,'good'=>0,'needs_work'=>0,'poor'=>0];
$t30 = strtotime('-30 days');

foreach ($reports as $r) {
    if (strtotime($r['created_at'] ?? '') >= $t30) $reportsLast30++;
    $s = $r['health_score'] ?? $r['content_score'] ?? null;
    if ($s !== null) {
        $healthScores[] = (int)$s;
        if ($s >= 80) $scoreDist['excellent']++;
        elseif ($s >= 60) $scoreDist['good']++;
        elseif ($s >= 40) $scoreDist['needs_work']++;
        else $scoreDist['poor']++;
    }
}
$avgHealth = !empty($healthScores) ? (int)round(array_sum($healthScores)/count($healthScores)) : 0;

$linkData = ai_linking_load_analysis();
$hasLinks = $linkData && ($linkData['ok'] ?? false);
$orphans = $hasLinks ? count($linkData['orphan_pages'] ?? []) : 0;
$linkOpps = $hasLinks ? count($linkData['opportunities'] ?? []) : 0;

$decayData = ai_decay_analyze_all();
$hasDecay = $decayData['ok'] ?? false;
$criticalDecay = $hasDecay ? ($decayData['statistics']['critical_decay'] ?? 0) : 0;
$highDecay = $hasDecay ? ($decayData['statistics']['high_decay'] ?? 0) : 0;

$kwGroups = [];
$cannib = 0;
foreach ($reports as $r) {
    $kw = mb_strtolower(trim($r['keyword'] ?? ''));
    if ($kw) {
        if (!isset($kwGroups[$kw])) $kwGroups[$kw] = [];
        if ($r['url'] && !in_array($r['url'], $kwGroups[$kw])) $kwGroups[$kw][] = $r['url'];
    }
}
foreach ($kwGroups as $urls) if (count($urls) > 1) $cannib++;
$uniqueKw = count($kwGroups);

// Site health score
$siteHealth = 50;
$factors = [];
if ($avgHealth >= 70) { $siteHealth += 15; $factors[] = ['type'=>'good','text'=>"Strong SEO score ({$avgHealth})"]; }
elseif ($avgHealth >= 50) { $siteHealth += 5; $factors[] = ['type'=>'warn','text'=>"Moderate SEO ({$avgHealth})"]; }
elseif ($avgHealth > 0) { $siteHealth -= 10; $factors[] = ['type'=>'bad','text'=>"Low SEO score ({$avgHealth})"]; }

if ($orphans === 0) { $siteHealth += 10; $factors[] = ['type'=>'good','text'=>'No orphan pages']; }
elseif ($orphans <= 3) { $factors[] = ['type'=>'warn','text'=>"{$orphans} orphan pages"]; }
else { $siteHealth -= 5; $factors[] = ['type'=>'bad','text'=>"{$orphans} orphan pages"]; }

$decayIssues = $criticalDecay + $highDecay;
if ($decayIssues === 0) { $siteHealth += 10; $factors[] = ['type'=>'good','text'=>'No content decay']; }
elseif ($decayIssues <= 5) { $factors[] = ['type'=>'warn','text'=>"{$decayIssues} pages need refresh"]; }
else { $siteHealth -= 10; $factors[] = ['type'=>'bad','text'=>"{$decayIssues} pages decaying"]; }

if ($cannib === 0) { $siteHealth += 10; $factors[] = ['type'=>'good','text'=>'No cannibalization']; }
elseif ($cannib <= 3) { $factors[] = ['type'=>'warn','text'=>"{$cannib} cannibalized keywords"]; }
else { $siteHealth -= 10; $factors[] = ['type'=>'bad','text'=>"{$cannib} cannibalized"]; }

$siteHealth = max(0, min(100, $siteHealth));
$healthClass = $siteHealth >= 70 ? 'success' : ($siteHealth >= 50 ? 'warning' : 'danger');

$actions = [];
if ($criticalDecay > 0) $actions[] = ['p'=>'critical','t'=>"Refresh {$criticalDecay} outdated pages",'l'=>'/admin/ai-seo-decay.php'];
if ($orphans > 0) $actions[] = ['p'=>'high','t'=>"Fix {$orphans} orphan pages",'l'=>'/admin/ai-seo-linking.php'];
if ($cannib > 0) $actions[] = ['p'=>'high','t'=>"Resolve {$cannib} cannibalization issues",'l'=>'/admin/ai-seo-keywords.php?cannib=1'];
if ($scoreDist['poor'] > 0) $actions[] = ['p'=>'medium','t'=>"Improve {$scoreDist['poor']} poor-scoring pages",'l'=>'/admin/ai-seo-content.php?status=high'];

$recent = array_slice($reports, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEO Dashboard - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.grid{display:grid;gap:20px}
.grid-2{grid-template-columns:repeat(2,1fr)}
.grid-3{grid-template-columns:repeat(3,1fr)}
.grid-4{grid-template-columns:repeat(4,1fr)}
@media(max-width:1100px){.grid-4,.grid-3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.grid-2,.grid-3,.grid-4{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.warning{background:rgba(249,226,175,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.stat-box{background:var(--bg);border-radius:12px;padding:20px;text-align:center}
.stat-val{font-size:32px;font-weight:700}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.stat-sub{font-size:11px;margin-top:6px}
.health-circle{width:140px;height:140px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;margin:0 auto 20px;border:4px solid var(--border)}
.health-circle.success{border-color:var(--success);color:var(--success)}
.health-circle.warning{border-color:var(--warning);color:var(--warning)}
.health-circle.danger{border-color:var(--danger);color:var(--danger)}
.health-circle .score{font-size:42px;font-weight:700}
.health-circle .label{font-size:11px;text-transform:uppercase}
.factor{display:flex;align-items:center;gap:8px;padding:8px 0;font-size:13px}
.factor-icon{width:20px}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.progress-bar{height:8px;background:var(--bg3);border-radius:4px;overflow:hidden;margin-top:6px}
.progress-fill{height:100%;border-radius:4px}
.action-item{display:flex;align-items:center;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px;margin-bottom:10px}
.action-item:last-child{margin-bottom:0}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;text-decoration:none;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.tool-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
@media(max-width:700px){.tool-grid{grid-template-columns:repeat(2,1fr)}}
.tool-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;background:var(--bg);border:1px solid var(--border);border-radius:12px;text-decoration:none;color:var(--text);transition:.15s}
.tool-btn:hover{border-color:var(--accent);background:var(--bg3)}
.tool-btn .icon{font-size:24px;margin-bottom:8px}
.tool-btn .name{font-weight:500}
.report-item{display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--bg);border-radius:10px;margin-bottom:8px}
.report-item:last-child{margin-bottom:0}
.empty{text-align:center;padding:30px;color:var(--muted)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📊',
    'title' => 'SEO Dashboard',
    'description' => 'Your SEO command center',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/ai-seo-assistant', 'text' => '🔍 New Analysis', 'class' => 'primary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="grid grid-3" style="margin-bottom:20px">
<!-- Site Health -->
<div class="card">
<div class="card-head"><span class="card-title"><span>💪</span> Site Health</span></div>
<div class="card-body">
<div class="health-circle <?= $healthClass ?>">
<span class="score"><?= $siteHealth ?></span>
<span class="label"><?= $siteHealth >= 70 ? 'Healthy' : ($siteHealth >= 50 ? 'Fair' : 'Critical') ?></span>
</div>
<?php foreach ($factors as $f): ?>
<div class="factor">
<span class="factor-icon"><?= $f['type']==='good' ? '✅' : ($f['type']==='bad' ? '❌' : '⚠️') ?></span>
<span><?= esc($f['text']) ?></span>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- Key Metrics -->
<div class="card" style="grid-column:span 2">
<div class="card-head"><span class="card-title"><span>📈</span> Key Metrics</span></div>
<div class="card-body">
<div class="grid grid-4">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $totalPages ?></div><div class="stat-lbl">Pages</div><div class="stat-sub" style="color:var(--success)"><?= $publishedPages ?> published</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $totalReports ?></div><div class="stat-lbl">Reports</div><div class="stat-sub"><?= $reportsLast30 ?> last 30d</div></div>
<div class="stat-box"><div class="stat-val"><?= $avgHealth ?></div><div class="stat-lbl">Avg Score</div><div class="stat-sub"><span style="color:var(--success)"><?= $scoreDist['excellent'] ?></span> | <span style="color:var(--warning)"><?= $scoreDist['good'] ?></span> | <span style="color:var(--danger)"><?= $scoreDist['poor'] ?></span></div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $uniqueKw ?></div><div class="stat-lbl">Keywords</div><div class="stat-sub"><?= $cannib > 0 ? "<span style='color:var(--warning)'>{$cannib} cannib</span>" : '<span style="color:var(--success)">Clean</span>' ?></div></div>
<div class="stat-box"><div class="stat-val" style="color:<?= $orphans > 0 ? 'var(--warning)' : 'var(--success)' ?>"><?= $orphans ?></div><div class="stat-lbl">Orphans</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= $linkOpps ?></div><div class="stat-lbl">Link Opps</div></div>
<div class="stat-box"><div class="stat-val" style="color:<?= $decayIssues > 0 ? 'var(--danger)' : 'var(--success)' ?>"><?= $decayIssues ?></div><div class="stat-lbl">Decaying</div></div>
<div class="stat-box"><div class="stat-val"><?= $hasLinks ? ($linkData['statistics']['total_internal_links'] ?? 0) : '—' ?></div><div class="stat-lbl">Int Links</div></div>
</div>
</div>
</div>
</div>

<div class="grid grid-2">
<!-- Quick Actions -->
<div class="card">
<div class="card-head warning"><span class="card-title"><span>⚡</span> Quick Actions</span></div>
<div class="card-body">
<?php if (empty($actions)): ?>
<div class="empty"><p style="font-size:24px;margin-bottom:8px">🎉</p><p>No urgent issues!</p></div>
<?php else: ?>
<?php foreach ($actions as $a): 
$pClass = match($a['p']) { 'critical'=>'danger', 'high'=>'warning', default=>'info' };
?>
<div class="action-item">
<div><span class="tag <?= $pClass ?>"><?= ucfirst($a['p']) ?></span> <span style="margin-left:8px"><?= esc($a['t']) ?></span></div>
<a href="<?= esc($a['l']) ?>" class="btn btn-secondary">Fix →</a>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

<!-- Score Distribution -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Score Distribution</span></div>
<div class="card-body">
<?php $total = array_sum($scoreDist); if ($total > 0): ?>
<?php foreach ([['Excellent (80+)',$scoreDist['excellent'],'success'],['Good (60-79)',$scoreDist['good'],'warning'],['Needs Work (40-59)',$scoreDist['needs_work'],'info'],['Poor (<40)',$scoreDist['poor'],'danger']] as [$lbl,$cnt,$cls]): ?>
<div style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;font-size:13px"><span><?= $lbl ?></span><span style="color:var(--<?= $cls ?>)"><?= $cnt ?> pages</span></div>
<div class="progress-bar"><div class="progress-fill" style="width:<?= round($cnt/$total*100) ?>%;background:var(--<?= $cls ?>)"></div></div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty"><p>No data yet</p></div>
<?php endif; ?>
</div>
</div>
</div>

<div class="grid grid-2" style="margin-top:20px">
<!-- Recent Reports -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Recent Reports</span><a href="/admin/ai-seo-reports.php" class="btn btn-secondary">View All</a></div>
<div class="card-body">
<?php if (empty($recent)): ?>
<div class="empty"><p>No reports yet</p></div>
<?php else: ?>
<?php foreach ($recent as $r): 
$s = $r['health_score'] ?? $r['content_score'] ?? null;
$sClass = $s === null ? 'muted' : ($s >= 80 ? 'success' : ($s >= 60 ? 'warning' : 'danger'));
?>
<div class="report-item">
<div><strong><?= esc(mb_substr($r['title'] ?? 'Untitled', 0, 25)) ?></strong><br><small style="color:var(--muted)"><?= esc($r['keyword'] ?? '—') ?></small></div>
<div style="text-align:right"><?php if ($s !== null): ?><span class="tag <?= $sClass ?>"><?= $s ?></span><?php endif; ?><br><small style="color:var(--muted)"><?= substr($r['created_at'] ?? '', 0, 10) ?></small></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

<!-- SEO Tools -->
<div class="card">
<div class="card-head"><span class="card-title"><span>🧰</span> SEO Tools</span></div>
<div class="card-body">
<div class="tool-grid">
<a href="/admin/ai-seo-assistant.php" class="tool-btn"><span class="icon">🔍</span><span class="name">Assistant</span></a>
<a href="/admin/ai-seo-content.php" class="tool-btn"><span class="icon">📋</span><span class="name">Content</span></a>
<a href="/admin/ai-seo-keywords.php" class="tool-btn"><span class="icon">🎯</span><span class="name">Keywords</span></a>
<a href="/admin/ai-seo-reports.php" class="tool-btn"><span class="icon">📊</span><span class="name">Reports</span></a>
<a href="/admin/ai-seo-linking.php" class="tool-btn"><span class="icon">🔗</span><span class="name">Int Links</span></a>
<a href="/admin/ai-seo-decay.php" class="tool-btn"><span class="icon">⏰</span><span class="name">Decay</span></a>
<a href="/admin/ai-seo-bulk.php" class="tool-btn"><span class="icon">✏️</span><span class="name">Bulk Edit</span></a>
<a href="/admin/seo-sitemap.php" class="tool-btn"><span class="icon">🗺️</span><span class="name">Sitemap</span></a>
<a href="/admin/seo.php" class="tool-btn"><span class="icon">⚙️</span><span class="name">Settings</span></a>
<a href="/admin/seo-robots.php" class="tool-btn"><span class="icon">🤖</span><span class="name">Robots</span></a>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
