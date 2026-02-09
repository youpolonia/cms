<?php
/**
 * Broken Links Checker — Find and fix broken links in content
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

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pdo = \core\Database::connection();

// ── Extract links from HTML content ──
function extractLinks(string $html): array {
    $links = [];
    if (preg_match_all('/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
        foreach ($matches[1] as $url) {
            $url = trim($url);
            if ($url === '' || $url === '#' || str_starts_with($url, 'javascript:') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) continue;
            $links[] = $url;
        }
    }
    return array_unique($links);
}

// ── Check a URL ──
function checkUrl(string $url): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_USERAGENT => 'CMS-LinkChecker/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $time = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => $code ?: 0,
        'time_ms' => $time,
        'error' => $error ?: null,
        'ok' => $code >= 200 && $code < 400,
    ];
}

$message = '';
$messageType = '';
$results = null;
$scanning = false;

// Handle scan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'scan') {
    csrf_validate_or_403();
    $scanning = true;
    $maxChecks = (int)($_POST['max_checks'] ?? 100);
    $checkExternal = !empty($_POST['check_external']);
    
    // Get SEO settings for base URL
    $seoConfig = CMS_ROOT . '/config/seo_settings.json';
    $seoSettings = file_exists($seoConfig) ? (json_decode(file_get_contents($seoConfig), true) ?? []) : [];
    $baseUrl = rtrim($seoSettings['canonical_base_url'] ?? '', '/');
    if (!$baseUrl) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    
    // Collect all links from pages and articles
    $allLinks = [];
    
    // Pages
    $rows = $pdo->query("SELECT id, title, slug, content FROM pages WHERE status = 'published'")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $links = extractLinks($row['content'] ?? '');
        foreach ($links as $url) {
            $allLinks[] = ['url' => $url, 'source_type' => 'page', 'source_id' => $row['id'], 'source_title' => $row['title'], 'source_slug' => $row['slug']];
        }
    }
    
    // Articles
    $rows = $pdo->query("SELECT id, title, slug, content FROM articles WHERE status = 'published'")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $links = extractLinks($row['content'] ?? '');
        foreach ($links as $url) {
            $allLinks[] = ['url' => $url, 'source_type' => 'article', 'source_id' => $row['id'], 'source_title' => $row['title'], 'source_slug' => $row['slug']];
        }
    }
    
    // Deduplicate URLs for checking
    $uniqueUrls = [];
    foreach ($allLinks as $link) {
        $url = $link['url'];
        // Resolve relative URLs
        if (str_starts_with($url, '/')) {
            $url = $baseUrl . $url;
        } elseif (!str_starts_with($url, 'http')) {
            continue; // Skip non-http links
        }
        
        $isExternal = !str_starts_with($url, $baseUrl);
        if ($isExternal && !$checkExternal) continue;
        
        if (!isset($uniqueUrls[$url])) {
            $uniqueUrls[$url] = ['url' => $url, 'original' => $link['url'], 'is_external' => $isExternal, 'sources' => []];
        }
        $uniqueUrls[$url]['sources'][] = $link;
    }
    
    // Check URLs (limit to max_checks)
    $results = [];
    $checked = 0;
    $broken = 0;
    
    foreach (array_slice($uniqueUrls, 0, $maxChecks, true) as $url => $data) {
        $check = checkUrl($url);
        $data['check'] = $check;
        $results[] = $data;
        $checked++;
        if (!$check['ok']) $broken++;
    }
    
    // Sort: broken first, then slow, then ok
    usort($results, function($a, $b) {
        if (!$a['check']['ok'] && $b['check']['ok']) return -1;
        if ($a['check']['ok'] && !$b['check']['ok']) return 1;
        return $b['check']['time_ms'] - $a['check']['time_ms'];
    });
    
    $message = "Checked {$checked} URLs. Found {$broken} broken links.";
    $messageType = $broken > 0 ? 'warning' : 'success';
}

// Load last scan results from seo_crawl_log
$lastResults = [];
try {
    $lastResults = $pdo->query("SELECT * FROM seo_crawl_log ORDER BY crawled_at DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}

// Save results to crawl log
if ($results !== null && !empty($results)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO seo_crawl_log (url, status_code, response_time_ms, crawler_type, crawled_at) VALUES (?, ?, ?, 'link_checker', NOW())");
        foreach ($results as $r) {
            $stmt->execute([$r['url'], $r['check']['status'], $r['check']['time_ms']]);
        }
    } catch (\Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Broken Links Checker - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--peach:#fab387;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.scan-form{display:flex;gap:12px;align-items:end;flex-wrap:wrap}
.form-group{margin-bottom:0}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select{padding:8px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus{outline:none;border-color:var(--accent)}
.cb-group{display:flex;align-items:center;gap:6px;padding:8px 0}
.cb-group input{width:16px;height:16px;accent-color:var(--accent)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none;font-family:'Inter',sans-serif}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:13px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-warning{background:rgba(249,226,175,.1);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th,.data-table td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg);white-space:nowrap}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.data-table tr.broken td{background:rgba(243,139,168,.05)}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.tag.muted{background:var(--bg3);color:var(--muted)}
.tag.info{background:rgba(137,220,235,.2);color:var(--cyan)}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:700px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:12px;padding:16px;text-align:center}
.stat-val{font-size:24px;font-weight:700}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
.source-list{font-size:11px;color:var(--muted);margin-top:4px}
.empty{text-align:center;padding:40px;color:var(--muted)}
.url-cell{max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace;font-size:11px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '🔗',
    'title' => 'Broken Links Checker',
    'description' => 'Find and fix broken links in your content',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--danger-color), var(--peach)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= esc($message) ?></div>
<?php endif; ?>

<!-- Scan form -->
<div class="card">
<div class="card-head"><span class="card-title"><span>🔍</span> Run Scan</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="scan">
<div class="scan-form">
<div class="form-group"><label>Max URLs to check</label><select name="max_checks"><option value="50">50</option><option value="100" selected>100</option><option value="200">200</option><option value="500">500</option></select></div>
<div class="cb-group"><input type="checkbox" name="check_external" id="checkExt"><label for="checkExt" style="font-size:12px;cursor:pointer">Check external links too</label></div>
<button type="submit" class="btn btn-primary">🔍 Start Scan</button>
</div>
</form>
</div>
</div>

<?php if ($results !== null): ?>
<!-- Scan Results -->
<?php
$totalChecked = count($results);
$brokenCount = count(array_filter($results, fn($r) => !$r['check']['ok']));
$slowCount = count(array_filter($results, fn($r) => $r['check']['ok'] && $r['check']['time_ms'] > 2000));
$okCount = $totalChecked - $brokenCount - $slowCount;
$extCount = count(array_filter($results, fn($r) => $r['is_external']));
?>

<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Scan Results</span></div>
<div class="card-body">
<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $totalChecked ?></div><div class="stat-lbl">Checked</div></div>
<div class="stat-box"><div class="stat-val" style="color:<?= $brokenCount ? 'var(--danger)' : 'var(--success)' ?>"><?= $brokenCount ?></div><div class="stat-lbl">Broken</div></div>
<div class="stat-box"><div class="stat-val" style="color:<?= $slowCount ? 'var(--warning)' : 'var(--success)' ?>"><?= $slowCount ?></div><div class="stat-lbl">Slow (&gt;2s)</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $okCount ?></div><div class="stat-lbl">OK</div></div>
</div>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Details</span><span style="font-size:12px;color:var(--muted)">Broken & slow shown first</span></div>
<div class="card-body" style="padding:0;overflow-x:auto">
<table class="data-table">
<thead><tr><th>Status</th><th>URL</th><th>Type</th><th>Response</th><th>Time</th><th>Found In</th></tr></thead>
<tbody>
<?php foreach ($results as $r):
$check = $r['check'];
$isBroken = !$check['ok'];
$isSlow = $check['ok'] && $check['time_ms'] > 2000;
$statusClass = $isBroken ? 'danger' : ($isSlow ? 'warning' : 'success');
$statusLabel = $isBroken ? '❌ Broken' : ($isSlow ? '⚠️ Slow' : '✅ OK');
$codeClass = $check['status'] === 0 ? 'danger' : ($check['status'] >= 400 ? 'danger' : ($check['status'] >= 300 ? 'warning' : 'success'));
?>
<tr class="<?= $isBroken ? 'broken' : '' ?>">
<td><span class="tag <?= $statusClass ?>"><?= $statusLabel ?></span></td>
<td class="url-cell" title="<?= esc($r['url']) ?>"><a href="<?= esc($r['url']) ?>" target="_blank" style="color:var(--accent)"><?= esc($r['url']) ?></a></td>
<td><span class="tag <?= $r['is_external'] ? 'info' : 'muted' ?>"><?= $r['is_external'] ? '🌐 External' : '🏠 Internal' ?></span></td>
<td><span class="tag <?= $codeClass ?>"><?= $check['status'] ?: 'ERR' ?></span><?php if ($check['error']): ?><br><span style="font-size:10px;color:var(--danger)"><?= esc(mb_substr($check['error'], 0, 40)) ?></span><?php endif; ?></td>
<td style="color:var(--muted)"><?= $check['time_ms'] ?>ms</td>
<td>
<?php $shown = 0; foreach (array_slice($r['sources'], 0, 3) as $src): ?>
<div style="font-size:11px"><?= $src['source_type'] === 'page' ? '📄' : '📝' ?> <?= esc(mb_substr($src['source_title'], 0, 25)) ?></div>
<?php $shown++; endforeach; ?>
<?php if (count($r['sources']) > 3): ?><div style="font-size:10px;color:var(--muted)">+<?= count($r['sources']) - 3 ?> more</div><?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<?php elseif (!empty($lastResults)): ?>
<!-- Last crawl results -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Last Scan History</span></div>
<div class="card-body" style="padding:0;overflow-x:auto">
<table class="data-table">
<thead><tr><th>URL</th><th>Status</th><th>Response Time</th><th>Scanned</th></tr></thead>
<tbody>
<?php foreach ($lastResults as $lr):
$ok = $lr['status_code'] >= 200 && $lr['status_code'] < 400;
$codeClass = !$ok ? 'danger' : 'success';
?>
<tr>
<td class="url-cell"><?= esc($lr['url']) ?></td>
<td><span class="tag <?= $codeClass ?>"><?= $lr['status_code'] ?></span></td>
<td style="color:var(--muted)"><?= $lr['response_time_ms'] ?>ms</td>
<td style="color:var(--muted)"><?= $lr['crawled_at'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<?php else: ?>
<div class="card"><div class="card-body"><div class="empty"><p style="font-size:28px;margin-bottom:12px">🔗</p><p>No scans yet. Run your first scan to find broken links.</p></div></div></div>
<?php endif; ?>

</div>
</body>
</html>
