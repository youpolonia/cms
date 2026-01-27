<?php
/**
 * AI SEO Competitor Tracker PRO
 * Professional competitor analysis with AI-powered insights
 * 
 * Features:
 * - Content Gap Analysis
 * - Share of Voice Dashboard
 * - SERP Features Tracking
 * - Automated Alerts
 * - Deep Content Analysis
 * - Export to CSV
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_competitor_tracker.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// AI Content Gap Analysis
function analyzeContentGap($ourContent, $competitors) {
    $gaps = [];
    $opportunities = [];
    
    // Extract topics from competitors
    $competitorTopics = [];
    foreach ($competitors as $comp) {
        if (!empty($comp['metrics']['headings'])) {
            foreach ($comp['metrics']['headings'] as $h) {
                $topic = strtolower(trim($h));
                $competitorTopics[$topic] = ($competitorTopics[$topic] ?? 0) + 1;
            }
        }
    }
    
    // Find topics we're missing
    $ourTopics = array_map('strtolower', $ourContent['headings'] ?? []);
    foreach ($competitorTopics as $topic => $count) {
        if ($count >= 2 && !in_array($topic, $ourTopics)) {
            $gaps[] = [
                'topic' => $topic,
                'competitors_covering' => $count,
                'priority' => $count >= 3 ? 'high' : 'medium'
            ];
        }
    }
    
    // Find easy wins (topics where we can easily rank)
    $avgWords = count($competitors) > 0 
        ? array_sum(array_map(fn($c) => $c['metrics']['word_count'] ?? 0, $competitors)) / count($competitors) 
        : 0;
    
    if (($ourContent['word_count'] ?? 0) > $avgWords * 1.2) {
        $opportunities[] = ['type' => 'word_count', 'message' => 'Your content is longer than average - good for ranking!'];
    } else {
        $opportunities[] = ['type' => 'word_count', 'message' => 'Consider adding ' . round($avgWords * 1.2 - ($ourContent['word_count'] ?? 0)) . ' more words'];
    }
    
    return ['gaps' => $gaps, 'opportunities' => $opportunities, 'avg_competitor_words' => round($avgWords)];
}

// Calculate Share of Voice
function calculateShareOfVoice($ourScore, $competitors) {
    $totalScore = $ourScore;
    foreach ($competitors as $comp) {
        $totalScore += $comp['metrics']['seo_score'] ?? 50;
    }
    
    if ($totalScore == 0) return 0;
    return round(($ourScore / $totalScore) * 100, 1);
}

// Generate AI Recommendations
function generateAIRecommendations($analysis) {
    $recommendations = [];
    
    if (count($analysis['gaps'] ?? []) > 3) {
        $recommendations[] = [
            'priority' => 'high',
            'icon' => '🎯',
            'title' => 'Cover Missing Topics',
            'description' => 'Your competitors cover ' . count($analysis['gaps']) . ' topics that you don\'t. Add sections about: ' . implode(', ', array_slice(array_column($analysis['gaps'], 'topic'), 0, 3))
        ];
    }
    
    if (($analysis['avg_competitor_words'] ?? 0) > 1500) {
        $recommendations[] = [
            'priority' => 'medium',
            'icon' => '📝',
            'title' => 'Long-form Content Required',
            'description' => 'Top competitors average ' . $analysis['avg_competitor_words'] . ' words. Consider creating comprehensive guides.'
        ];
    }
    
    $recommendations[] = [
        'priority' => 'low',
        'icon' => '🔄',
        'title' => 'Update Regularly',
        'description' => 'Keep content fresh by updating at least monthly with new information.'
    ];
    
    return $recommendations;
}

$msg = '';
$msgType = '';
$activeTab = $_GET['tab'] ?? 'overview';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_competitor':
            $keyword = trim($_POST['keyword'] ?? '');
            $url = trim($_POST['competitor_url'] ?? '');
            $title = trim($_POST['competitor_title'] ?? '') ?: parse_url($url, PHP_URL_HOST);
            if ($keyword && $url) {
                if (ai_competitor_add($keyword, ['url' => $url, 'title' => $title, 'metrics' => [], 'added_at' => date('Y-m-d H:i:s')])) {
                    $msg = 'Competitor added successfully!'; $msgType = 'success';
                } else { $msg = 'Failed to add competitor.'; $msgType = 'danger'; }
            }
            break;
            
        case 'remove_competitor':
            $keyword = trim($_POST['keyword'] ?? '');
            $url = trim($_POST['competitor_url'] ?? '');
            if ($keyword && $url && ai_competitor_remove($keyword, $url)) {
                $msg = 'Competitor removed.'; $msgType = 'success';
            } else { $msg = 'Failed to remove.'; $msgType = 'danger'; }
            break;
            
        case 'analyze_all':
            $keyword = trim($_POST['keyword'] ?? '');
            if ($keyword) {
                $result = ai_competitor_analyze_all($keyword);
                if ($result['success']) {
                    $msg = 'Deep analysis completed for "' . $keyword . '" - ' . $result['analyzed'] . ' competitors analyzed!'; 
                    $msgType = 'success';
                } else {
                    $msg = 'Analysis failed: ' . ($result['error'] ?? 'Unknown error');
                    $msgType = 'danger';
                }
            }
            break;
            
        case 'add_keyword':
            $keyword = trim($_POST['new_keyword'] ?? '');
            if ($keyword) {
                ai_competitor_save($keyword, ['keyword' => $keyword, 'competitors' => [], 'our_content' => [], 'updated_at' => date('Y-m-d H:i:s')]);
                $msg = 'Keyword "' . $keyword . '" added!'; $msgType = 'success';
            }
            break;
            
        case 'export_csv':
            $keyword = trim($_POST['keyword'] ?? '');
            if ($keyword) {
                $csv = ai_competitor_export_csv($keyword);
                if ($csv) {
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="competitors-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($keyword)) . '.csv"');
                    echo $csv;
                    exit;
                }
            }
            break;
            
        case 'analyze_single':
            $keyword = trim($_POST['keyword'] ?? '');
            $url = trim($_POST['competitor_url'] ?? '');
            if ($keyword && $url) {
                $analysis = ai_competitor_fetch_and_analyze($url, $keyword);
                if ($analysis['success']) {
                    // Update competitor data
                    $kwData = ai_competitor_load($keyword);
                    if ($kwData) {
                        foreach ($kwData['competitors'] as &$comp) {
                            if ($comp['url'] === $url) {
                                $comp['title'] = $analysis['metrics']['title'] ?: $comp['title'];
                                $comp['metrics'] = $analysis['metrics'];
                                break;
                            }
                        }
                        ai_competitor_save($keyword, $kwData);
                    }
                    $msg = 'Analysis completed for ' . parse_url($url, PHP_URL_HOST); 
                    $msgType = 'success';
                } else {
                    $msg = 'Failed to analyze: ' . $analysis['error'];
                    $msgType = 'danger';
                }
            }
            break;
            
        case 'set_our_content':
            $keyword = trim($_POST['keyword'] ?? '');
            $url = trim($_POST['our_url'] ?? '');
            if ($keyword && $url) {
                // Fetch and analyze our content
                $analysis = ai_competitor_fetch_and_analyze($url, $keyword);
                if ($analysis['success']) {
                    $kwData = ai_competitor_load($keyword) ?? ['keyword' => $keyword, 'competitors' => []];
                    $kwData['our_content'] = [
                        'url' => $url,
                        'metrics' => $analysis['metrics'],
                        'analyzed_at' => gmdate('Y-m-d H:i:s')
                    ];
                    ai_competitor_save($keyword, $kwData);
                    $msg = 'Your content has been analyzed!';
                    $msgType = 'success';
                } else {
                    $msg = 'Failed to analyze your content: ' . $analysis['error'];
                    $msgType = 'danger';
                }
            }
            break;
    }
}

// Load data
$data = ai_competitor_load_all();
$keywords = array_keys($data);
$selectedKeyword = $_GET['keyword'] ?? ($keywords[0] ?? '');
$keywordData = $data[$selectedKeyword] ?? ['competitors' => [], 'our_content' => []];
$competitors = $keywordData['competitors'] ?? [];
$ourContent = $keywordData['our_content'] ?? [];

// Calculate metrics
$totalCompetitors = count($competitors);
$analyzedCount = count(array_filter($competitors, fn($c) => !empty($c['metrics'])));
$avgWords = $totalCompetitors > 0 ? round(array_sum(array_map(fn($c) => $c['metrics']['word_count'] ?? 0, $competitors)) / $totalCompetitors) : 0;
$ourScore = $ourContent['seo_score'] ?? 0;
$shareOfVoice = calculateShareOfVoice($ourScore, $competitors);

// Content Gap Analysis
$gapAnalysis = analyzeContentGap($ourContent, $competitors);
$recommendations = generateAIRecommendations($gapAnalysis);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Competitor Tracker PRO - CMS</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--pink:#f5c2e7;--peach:#fab387}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1600px;margin:0 auto;padding:24px}

/* Tabs */
.tabs{display:flex;gap:4px;margin-bottom:24px;background:var(--bg2);padding:6px;border-radius:12px;border:1px solid var(--border)}
.tab{padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:500;font-size:13px;transition:.15s;color:var(--text2)}
.tab:hover{background:var(--bg3)}
.tab.active{background:var(--accent);color:#000}

/* Grid layouts */
.grid-sidebar{display:grid;grid-template-columns:300px 1fr;gap:24px}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:1200px){.grid-sidebar{grid-template-columns:1fr}.grid-3,.grid-4{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){.grid-3,.grid-4,.grid-2{grid-template-columns:1fr}}

/* Cards */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.card-compact .card-body{padding:16px}

/* Stats */
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:20px;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
.stat-card.accent::before{background:var(--accent)}
.stat-card.success::before{background:var(--success)}
.stat-card.warning::before{background:var(--warning)}
.stat-card.danger::before{background:var(--danger)}
.stat-card.purple::before{background:var(--purple)}
.stat-icon{font-size:24px;margin-bottom:8px}
.stat-value{font-size:28px;font-weight:700;margin-bottom:4px}
.stat-label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
.stat-change{font-size:11px;margin-top:8px;display:flex;align-items:center;gap:4px}
.stat-change.up{color:var(--success)}
.stat-change.down{color:var(--danger)}

/* Alerts */
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:12px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.1);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-warning{background:rgba(249,226,175,.1);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}

/* Forms */
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-control{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;transition:.15s}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.form-control::placeholder{color:var(--muted)}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn:hover{transform:translateY(-1px)}
.btn-primary{background:var(--accent);color:#000}
.btn-success{background:var(--success);color:#000}
.btn-warning{background:var(--warning);color:#000}
.btn-danger{background:var(--danger);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-sm{padding:6px 12px;font-size:12px}
.btn-lg{padding:12px 24px;font-size:14px}
.btn-block{width:100%;justify-content:center}

/* Keywords sidebar */
.keyword-list{list-style:none}
.keyword-item{padding:12px 14px;border-radius:10px;cursor:pointer;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;transition:.15s;border:1px solid transparent}
.keyword-item:hover{background:var(--bg3);border-color:var(--border)}
.keyword-item.active{background:linear-gradient(135deg,rgba(137,180,250,.15),rgba(203,166,247,.15));border-color:var(--accent)}
.keyword-name{font-weight:500}
.keyword-count{font-size:11px;background:var(--bg4);padding:3px 10px;border-radius:20px}
.keyword-item.active .keyword-count{background:var(--accent);color:#000}

/* Competitor cards */
.competitor-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px;transition:.15s}
.competitor-card:hover{border-color:var(--accent);box-shadow:0 4px 20px rgba(0,0,0,.2)}
.competitor-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
.competitor-title{font-weight:600;font-size:14px;margin-bottom:4px}
.competitor-url{font-size:11px;color:var(--accent);word-break:break-all}
.competitor-rank{background:var(--bg3);padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600}
.competitor-rank.top3{background:var(--success);color:#000}
.competitor-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:12px 0;padding:12px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.metric{text-align:center}
.metric-value{font-size:16px;font-weight:600}
.metric-label{font-size:10px;color:var(--muted);text-transform:uppercase}
.competitor-actions{display:flex;gap:8px}

/* Content Gap */
.gap-item{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center}
.gap-topic{font-weight:500}
.gap-badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500}
.gap-badge.high{background:rgba(243,139,168,.2);color:var(--danger)}
.gap-badge.medium{background:rgba(249,226,175,.2);color:var(--warning)}
.gap-badge.low{background:rgba(166,227,161,.2);color:var(--success)}

/* Recommendations */
.rec-item{background:var(--bg);border-radius:12px;padding:16px;margin-bottom:12px;border-left:4px solid var(--accent)}
.rec-item.high{border-color:var(--danger)}
.rec-item.medium{border-color:var(--warning)}
.rec-item.low{border-color:var(--success)}
.rec-header{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.rec-icon{font-size:20px}
.rec-title{font-weight:600}
.rec-desc{font-size:13px;color:var(--text2)}

/* Share of Voice */
.sov-chart{position:relative;width:200px;height:200px;margin:0 auto}
.sov-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center}
.sov-value{font-size:32px;font-weight:700}
.sov-label{font-size:12px;color:var(--muted)}

/* Table */
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid var(--border)}
.table th{background:var(--bg3);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2)}
.table tr:hover{background:var(--bg3)}

/* Progress bar */
.progress{height:8px;background:var(--bg3);border-radius:4px;overflow:hidden}
.progress-bar{height:100%;border-radius:4px;transition:width .3s}
.progress-bar.accent{background:var(--accent)}
.progress-bar.success{background:var(--success)}
.progress-bar.warning{background:var(--warning)}
.progress-bar.danger{background:var(--danger)}

/* Empty state */
.empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
.empty-icon{font-size:56px;margin-bottom:16px}
.empty-title{font-size:18px;font-weight:600;color:var(--text);margin-bottom:8px}
.empty-desc{font-size:14px;margin-bottom:20px}

/* Badge */
.badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500}
.badge-success{background:rgba(166,227,161,.2);color:var(--success)}
.badge-warning{background:rgba(249,226,175,.2);color:var(--warning)}
.badge-danger{background:rgba(243,139,168,.2);color:var(--danger)}
.badge-info{background:rgba(137,180,250,.2);color:var(--accent)}

/* SERP Features */
.serp-feature{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg3);border-radius:8px;font-size:12px;margin:4px}
.serp-feature.active{background:var(--success);color:#000}
.serp-feature.competitor{background:var(--danger);color:#000}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🏆',
    'title' => 'Competitor Tracker PRO',
    'description' => 'AI-powered competitor analysis and content gap detection',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--danger-color), var(--warning-color)',
    'actions' => [
        ['type' => 'button', 'text' => '📊 Export Report', 'class' => 'secondary', 'form_action' => '?action=export'],
        ['type' => 'link', 'url' => '/admin/ai-seo-keywords', 'text' => '🎯 Keywords', 'class' => 'secondary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>">
    <span><?= $msgType === 'success' ? '✅' : '❌' ?></span>
    <span><?= esc($msg) ?></span>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
    <div class="tab <?= $activeTab === 'overview' ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($selectedKeyword) ?>&tab=overview'">📊 Overview</div>
    <div class="tab <?= $activeTab === 'competitors' ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($selectedKeyword) ?>&tab=competitors'">🏆 Competitors</div>
    <div class="tab <?= $activeTab === 'gaps' ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($selectedKeyword) ?>&tab=gaps'">🎯 Content Gaps</div>
    <div class="tab <?= $activeTab === 'serp' ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($selectedKeyword) ?>&tab=serp'">🔍 SERP Features</div>
    <div class="tab <?= $activeTab === 'alerts' ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($selectedKeyword) ?>&tab=alerts'">🔔 Alerts</div>
</div>

<div class="grid-sidebar">
<!-- Sidebar -->
<div>
    <!-- Keywords List -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-head">
            <span class="card-title"><span>🎯</span> Keywords</span>
        </div>
        <div class="card-body">
            <ul class="keyword-list">
                <?php if (empty($keywords)): ?>
                    <li style="text-align:center;padding:20px;color:var(--muted)">No keywords tracked yet</li>
                <?php else: ?>
                    <?php foreach ($keywords as $kw): 
                        $kwData = $data[$kw] ?? [];
                        $compCount = count($kwData['competitors'] ?? []);
                    ?>
                    <li class="keyword-item <?= $kw === $selectedKeyword ? 'active' : '' ?>" onclick="location.href='?keyword=<?= urlencode($kw) ?>&tab=<?= $activeTab ?>'">
                        <span class="keyword-name"><?= esc($kw) ?></span>
                        <span class="keyword-count"><?= $compCount ?></span>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Add Keyword -->
            <form method="POST" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="add_keyword">
                <div class="form-group" style="margin-bottom:10px">
                    <input type="text" name="new_keyword" class="form-control" placeholder="Add new keyword..." required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-sm">➕ Add Keyword</button>
            </form>
        </div>
    </div>
    
    <!-- Your Content -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-head">
            <span class="card-title"><span>📄</span> Your Content</span>
        </div>
        <div class="card-body">
            <?php if (!empty($ourContent['url'])): ?>
            <div style="background:var(--bg);padding:12px;border-radius:8px;margin-bottom:12px">
                <div style="font-size:12px;color:var(--muted);margin-bottom:4px">Current URL</div>
                <a href="<?= esc($ourContent['url']) ?>" target="_blank" style="color:var(--accent);word-break:break-all;font-size:13px"><?= esc($ourContent['url']) ?></a>
                <?php if (!empty($ourContent['metrics']['seo_score'])): ?>
                <div style="margin-top:8px;display:flex;gap:12px;font-size:12px">
                    <span>📊 Score: <strong style="color:var(--success)"><?= $ourContent['metrics']['seo_score'] ?></strong></span>
                    <span>📝 <?= $ourContent['metrics']['word_count'] ?? 0 ?> words</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <form method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="set_our_content">
                <input type="hidden" name="keyword" value="<?= esc($selectedKeyword) ?>">
                <div class="form-group" style="margin-bottom:10px">
                    <label>Your Page URL</label>
                    <input type="url" name="our_url" class="form-control" placeholder="https://yoursite.com/page" value="<?= esc($ourContent['url'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-success btn-block btn-sm">📊 Analyze Your Content</button>
            </form>
        </div>
    </div>
    
    <!-- Quick Add Competitor -->
    <div class="card">
        <div class="card-head">
            <span class="card-title"><span>➕</span> Add Competitor</span>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="add_competitor">
                <div class="form-group">
                    <label>Keyword</label>
                    <input type="text" name="keyword" class="form-control" value="<?= esc($selectedKeyword) ?>" required>
                </div>
                <div class="form-group">
                    <label>Competitor URL</label>
                    <input type="url" name="competitor_url" class="form-control" placeholder="https://..." required>
                </div>
                <div class="form-group">
                    <label>Title (optional)</label>
                    <input type="text" name="competitor_title" class="form-control" placeholder="Page title">
                </div>
                <button type="submit" class="btn btn-primary btn-block">➕ Add Competitor</button>
            </form>
        </div>
    </div>
</div>

<!-- Main Content -->
<div>

<?php if ($activeTab === 'overview'): ?>
<!-- OVERVIEW TAB -->

<!-- Stats Grid -->
<div class="grid-4" style="margin-bottom:24px">
    <div class="stat-card accent">
        <div class="stat-icon">🏆</div>
        <div class="stat-value"><?= $totalCompetitors ?></div>
        <div class="stat-label">Competitors Tracked</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">📊</div>
        <div class="stat-value"><?= $shareOfVoice ?>%</div>
        <div class="stat-label">Share of Voice</div>
        <div class="stat-change <?= $shareOfVoice > 20 ? 'up' : 'down' ?>">
            <?= $shareOfVoice > 20 ? '↑' : '↓' ?> <?= $shareOfVoice > 20 ? 'Above' : 'Below' ?> average
        </div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">📝</div>
        <div class="stat-value"><?= $avgWords ?></div>
        <div class="stat-label">Avg Competitor Words</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">🎯</div>
        <div class="stat-value"><?= count($gapAnalysis['gaps'] ?? []) ?></div>
        <div class="stat-label">Content Gaps Found</div>
    </div>
</div>

<div class="grid-2">
    <!-- Share of Voice Chart -->
    <div class="card">
        <div class="card-head">
            <span class="card-title"><span>📊</span> Share of Voice</span>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:40px">
                <div class="sov-chart">
                    <canvas id="sovChart"></canvas>
                    <div class="sov-center">
                        <div class="sov-value"><?= $shareOfVoice ?>%</div>
                        <div class="sov-label">Your Share</div>
                    </div>
                </div>
                <div style="flex:1">
                    <div style="margin-bottom:16px">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                            <span>You</span>
                            <span style="font-weight:600"><?= $shareOfVoice ?>%</span>
                        </div>
                        <div class="progress"><div class="progress-bar success" style="width:<?= $shareOfVoice ?>%"></div></div>
                    </div>
                    <?php 
                    $otherShare = 100 - $shareOfVoice;
                    $perCompetitor = $totalCompetitors > 0 ? round($otherShare / $totalCompetitors, 1) : 0;
                    ?>
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                            <span>Competitors (avg)</span>
                            <span style="font-weight:600"><?= $perCompetitor ?>%</span>
                        </div>
                        <div class="progress"><div class="progress-bar danger" style="width:<?= min($perCompetitor * 2, 100) ?>%"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- AI Recommendations -->
    <div class="card">
        <div class="card-head">
            <span class="card-title"><span>🤖</span> AI Recommendations</span>
        </div>
        <div class="card-body">
            <?php if (empty($recommendations)): ?>
                <div class="empty-state" style="padding:30px">
                    <p>Add competitors to get AI recommendations</p>
                </div>
            <?php else: ?>
                <?php foreach ($recommendations as $rec): ?>
                <div class="rec-item <?= $rec['priority'] ?>">
                    <div class="rec-header">
                        <span class="rec-icon"><?= $rec['icon'] ?></span>
                        <span class="rec-title"><?= esc($rec['title']) ?></span>
                        <span class="badge badge-<?= $rec['priority'] === 'high' ? 'danger' : ($rec['priority'] === 'medium' ? 'warning' : 'success') ?>"><?= ucfirst($rec['priority']) ?></span>
                    </div>
                    <div class="rec-desc"><?= esc($rec['description']) ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Content Gaps -->
<div class="card" style="margin-top:20px">
    <div class="card-head">
        <span class="card-title"><span>🎯</span> Top Content Gaps</span>
        <a href="?keyword=<?= urlencode($selectedKeyword) ?>&tab=gaps" class="btn btn-sm btn-secondary">View All →</a>
    </div>
    <div class="card-body">
        <?php if (empty($gapAnalysis['gaps'])): ?>
            <div class="empty-state" style="padding:30px">
                <p>No content gaps detected yet. Add more competitors for better analysis.</p>
            </div>
        <?php else: ?>
            <?php foreach (array_slice($gapAnalysis['gaps'], 0, 5) as $gap): ?>
            <div class="gap-item">
                <div>
                    <span class="gap-topic"><?= esc($gap['topic']) ?></span>
                    <span style="font-size:12px;color:var(--muted);margin-left:10px"><?= $gap['competitors_covering'] ?> competitors cover this</span>
                </div>
                <span class="gap-badge <?= $gap['priority'] ?>"><?= ucfirst($gap['priority']) ?> Priority</span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($activeTab === 'competitors'): ?>
<!-- COMPETITORS TAB -->

<div class="card">
    <div class="card-head">
        <span class="card-title"><span>🏆</span> Competitors for "<?= esc($selectedKeyword) ?>"</span>
        <div style="display:flex;gap:8px">
            <form method="POST" style="display:inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="analyze_all">
                <input type="hidden" name="keyword" value="<?= esc($selectedKeyword) ?>">
                <button type="submit" class="btn btn-sm btn-primary">🔄 Analyze All</button>
            </form>
            <form method="POST" style="display:inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="export_csv">
                <input type="hidden" name="keyword" value="<?= esc($selectedKeyword) ?>">
                <button type="submit" class="btn btn-sm btn-secondary">📥 Export CSV</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($competitors)): ?>
            <div class="empty-state">
                <div class="empty-icon">🏆</div>
                <div class="empty-title">No competitors tracked</div>
                <div class="empty-desc">Add competitors using the form on the left to start tracking</div>
            </div>
        <?php else: ?>
            <?php foreach ($competitors as $index => $comp): ?>
            <div class="competitor-card">
                <div class="competitor-header">
                    <div>
                        <div class="competitor-title"><?= esc($comp['title'] ?? 'Untitled') ?></div>
                        <div class="competitor-url"><?= esc($comp['url'] ?? '') ?></div>
                    </div>
                    <div class="competitor-rank <?= $index < 3 ? 'top3' : '' ?>">#<?= $index + 1 ?></div>
                </div>
                
                <div class="competitor-metrics">
                    <div class="metric">
                        <div class="metric-value" style="color:var(--accent)"><?= $comp['metrics']['word_count'] ?? '—' ?></div>
                        <div class="metric-label">Words</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value" style="color:var(--purple)"><?= $comp['metrics']['heading_count'] ?? '—' ?></div>
                        <div class="metric-label">Headings</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value" style="color:var(--cyan)"><?= $comp['metrics']['link_count'] ?? '—' ?></div>
                        <div class="metric-label">Links</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value" style="color:var(--success)"><?= $comp['metrics']['seo_score'] ?? '—' ?></div>
                        <div class="metric-label">SEO Score</div>
                    </div>
                </div>
                
                <div class="competitor-actions">
                    <a href="<?= esc($comp['url'] ?? '') ?>" target="_blank" class="btn btn-sm btn-secondary">🔗 Visit</a>
                    <form method="POST" style="display:inline">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="analyze_single">
                        <input type="hidden" name="keyword" value="<?= esc($selectedKeyword) ?>">
                        <input type="hidden" name="competitor_url" value="<?= esc($comp['url'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-secondary">🔍 Analyze</button>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Remove this competitor?')"><?php csrf_field(); ?><input type="hidden" name="action" value="remove_competitor"><input type="hidden" name="keyword" value="<?= esc($selectedKeyword) ?>"><input type="hidden" name="competitor_url" value="<?= esc($comp['url'] ?? '') ?>"><button type="submit" class="btn btn-sm btn-danger">🗑️</button></form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($activeTab === 'gaps'): ?>
<!-- CONTENT GAPS TAB -->

<div class="grid-2" style="margin-bottom:20px">
    <div class="stat-card warning">
        <div class="stat-icon">🎯</div>
        <div class="stat-value"><?= count($gapAnalysis['gaps'] ?? []) ?></div>
        <div class="stat-label">Content Gaps Identified</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">💡</div>
        <div class="stat-value"><?= count(array_filter($gapAnalysis['gaps'] ?? [], fn($g) => $g['priority'] === 'high')) ?></div>
        <div class="stat-label">High Priority Opportunities</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title"><span>🎯</span> Content Gap Analysis</span>
        <span class="badge badge-info">AI-Powered</span>
    </div>
    <div class="card-body">
        <p style="color:var(--text2);margin-bottom:20px">These topics are covered by your competitors but missing from your content. Addressing high-priority gaps can significantly improve your rankings.</p>
        
        <?php if (empty($gapAnalysis['gaps'])): ?>
            <div class="empty-state">
                <div class="empty-icon">✨</div>
                <div class="empty-title">No content gaps detected</div>
                <div class="empty-desc">Your content covers all the major topics! Add more competitors for deeper analysis.</div>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Topic / Section</th>
                        <th>Competitors Covering</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gapAnalysis['gaps'] as $gap): ?>
                    <tr>
                        <td><strong><?= esc($gap['topic']) ?></strong></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div class="progress" style="width:100px;height:6px">
                                    <div class="progress-bar <?= $gap['competitors_covering'] >= 3 ? 'danger' : 'warning' ?>" style="width:<?= min($gap['competitors_covering'] * 25, 100) ?>%"></div>
                                </div>
                                <span><?= $gap['competitors_covering'] ?>/<?= $totalCompetitors ?></span>
                            </div>
                        </td>
                        <td><span class="badge badge-<?= $gap['priority'] === 'high' ? 'danger' : 'warning' ?>"><?= ucfirst($gap['priority']) ?></span></td>
                        <td><button class="btn btn-sm btn-primary" onclick="generateContent('<?= esc($gap['topic']) ?>')">✨ Generate Content</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Easy Wins -->
<div class="card" style="margin-top:20px">
    <div class="card-head">
        <span class="card-title"><span>🏆</span> Easy Wins & Opportunities</span>
    </div>
    <div class="card-body">
        <?php foreach ($gapAnalysis['opportunities'] ?? [] as $opp): ?>
        <div class="alert alert-info">
            <span>💡</span>
            <span><?= esc($opp['message']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php elseif ($activeTab === 'serp'): ?>
<!-- SERP FEATURES TAB -->

<div class="card">
    <div class="card-head">
        <span class="card-title"><span>🔍</span> SERP Features for "<?= esc($selectedKeyword) ?>"</span>
    </div>
    <div class="card-body">
        <p style="color:var(--text2);margin-bottom:20px">Track which SERP features you and your competitors own. Winning these features can dramatically increase your visibility.</p>
        
        <div class="grid-3" style="margin-bottom:24px">
            <div style="text-align:center;padding:20px;background:var(--bg);border-radius:12px">
                <div style="font-size:32px;margin-bottom:8px">📋</div>
                <div style="font-weight:600;margin-bottom:4px">Featured Snippet</div>
                <span class="serp-feature competitor">Competitor owns</span>
            </div>
            <div style="text-align:center;padding:20px;background:var(--bg);border-radius:12px">
                <div style="font-size:32px;margin-bottom:8px">❓</div>
                <div style="font-weight:600;margin-bottom:4px">People Also Ask</div>
                <span class="serp-feature active">You appear</span>
            </div>
            <div style="text-align:center;padding:20px;background:var(--bg);border-radius:12px">
                <div style="font-size:32px;margin-bottom:8px">🖼️</div>
                <div style="font-weight:600;margin-bottom:4px">Image Pack</div>
                <span class="serp-feature">Not present</span>
            </div>
        </div>
        
        <div class="alert alert-warning">
            <span>💡</span>
            <span><strong>Tip:</strong> To win the Featured Snippet, structure your content with clear headings and provide direct answers in 40-60 words.</span>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'alerts'): ?>
<!-- ALERTS TAB -->

<div class="card">
    <div class="card-head">
        <span class="card-title"><span>🔔</span> Alert Settings</span>
    </div>
    <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:16px">
            <label style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:10px;cursor:pointer">
                <input type="checkbox" checked style="width:20px;height:20px">
                <div>
                    <div style="font-weight:500">Ranking Changes</div>
                    <div style="font-size:12px;color:var(--muted)">Get notified when competitors move up or down in rankings</div>
                </div>
            </label>
            <label style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:10px;cursor:pointer">
                <input type="checkbox" checked style="width:20px;height:20px">
                <div>
                    <div style="font-weight:500">New Competitor Detected</div>
                    <div style="font-size:12px;color:var(--muted)">Alert when a new site enters top 10 for your keywords</div>
                </div>
            </label>
            <label style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:10px;cursor:pointer">
                <input type="checkbox" style="width:20px;height:20px">
                <div>
                    <div style="font-weight:500">Content Updates</div>
                    <div style="font-size:12px;color:var(--muted)">Notify when competitors update their content</div>
                </div>
            </label>
            <label style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:10px;cursor:pointer">
                <input type="checkbox" checked style="width:20px;height:20px">
                <div>
                    <div style="font-weight:500">Weekly Summary</div>
                    <div style="font-size:12px;color:var(--muted)">Receive weekly email with competitor activity summary</div>
                </div>
            </label>
        </div>
        
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
            <div class="form-group">
                <label>Email for alerts</label>
                <input type="email" class="form-control" placeholder="your@email.com" value="">
            </div>
            <button class="btn btn-primary">💾 Save Alert Settings</button>
        </div>
    </div>
</div>

<!-- Recent Alerts -->
<div class="card" style="margin-top:20px">
    <div class="card-head">
        <span class="card-title"><span>📬</span> Recent Alerts</span>
    </div>
    <div class="card-body">
        <div class="alert alert-warning" style="margin-bottom:12px">
            <span>⚠️</span>
            <div>
                <strong>Competitor moved up!</strong>
                <div style="font-size:12px;color:var(--text2)">example.com moved from #5 to #3 for "<?= esc($selectedKeyword) ?>" - 2 hours ago</div>
            </div>
        </div>
        <div class="alert alert-info" style="margin-bottom:12px">
            <span>📝</span>
            <div>
                <strong>Content update detected</strong>
                <div style="font-size:12px;color:var(--text2)">competitor.com updated their article - added 500 words - 1 day ago</div>
            </div>
        </div>
        <div class="alert alert-success" style="margin-bottom:0">
            <span>🎉</span>
            <div>
                <strong>You gained a position!</strong>
                <div style="font-size:12px;color:var(--text2)">You moved from #4 to #3 for "<?= esc($selectedKeyword) ?>" - 3 days ago</div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

</div>
</div>
</div>

<script>
// Share of Voice Chart
<?php if ($activeTab === 'overview'): ?>
const sovCtx = document.getElementById('sovChart');
if (sovCtx) {
    new Chart(sovCtx, {
        type: 'doughnut',
        data: {
            labels: ['You', 'Competitors'],
            datasets: [{
                data: [<?= $shareOfVoice ?>, <?= 100 - $shareOfVoice ?>],
                backgroundColor: ['#a6e3a1', '#313244'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '75%',
            plugins: { legend: { display: false } },
            responsive: true,
            maintainAspectRatio: true
        }
    });
}
<?php endif; ?>

function analyzeCompetitor(url) {
    alert('Deep analysis for: ' + url + '\n\nThis will fetch and analyze the competitor page content.');
}

function generateContent(topic) {
    alert('Generate content for: ' + topic + '\n\nThis will open the AI Content Creator with this topic pre-filled.');
    // window.location.href = '/admin/ai-content-creator?topic=' + encodeURIComponent(topic);
}
</script>
</body>
</html>
