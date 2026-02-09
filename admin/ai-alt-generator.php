<?php
/**
 * AI ALT Generator - Modern Dark UI
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
require_once CMS_ROOT . '/core/ai_alt_generator.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();


function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$msg = '';
$msgType = '';
$bulkResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'generate_single':
            $mediaId = $_POST['media_id'] ?? '';
            $keyword = trim($_POST['keyword'] ?? '');
            if ($mediaId) {
                $result = ai_alt_generate_and_save($mediaId, '', $keyword);
                $msg = $result['ok'] ? 'ALT generated: "' . esc($result['alt_text']) . '"' : 'Failed: ' . ($result['error'] ?? 'Unknown');
                $msgType = $result['ok'] ? 'success' : 'danger';
            }
            break;
        case 'bulk_generate':
            $keyword = trim($_POST['keyword'] ?? '');
            $limit = (int)($_POST['limit'] ?? 50);
            $bulkResult = ai_alt_bulk_generate($keyword, $limit);
            $msg = $bulkResult['ok'] ? "Done: {$bulkResult['success']} success, {$bulkResult['failed']} failed" : 'Failed: ' . ($bulkResult['error'] ?? 'Unknown');
            $msgType = $bulkResult['ok'] ? ($bulkResult['failed'] > 0 ? 'warning' : 'success') : 'danger';
            break;
        case 'update_alt':
            $mediaId = $_POST['media_id'] ?? '';
            $altText = trim($_POST['alt_text'] ?? '');
            if ($mediaId && $altText) {
                $saved = ai_alt_update($mediaId, $altText);
                $msg = $saved ? 'ALT updated.' : 'Update failed.';
                $msgType = $saved ? 'success' : 'danger';
            }
            break;
    }
}

$stats = ai_alt_get_stats();
$missing = ai_alt_get_missing();
$covClass = $stats['coverage_percent'] >= 90 ? 'success' : ($stats['coverage_percent'] >= 70 ? 'warning' : 'danger');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI ALT Generator - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
@media(max-width:800px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
.stat-val{font-size:32px;font-weight:700}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.primary{background:rgba(137,180,250,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;gap:10px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-warning{background:rgba(249,226,175,.15);border:1px solid rgba(249,226,175,.3);color:var(--warning)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.form-row{display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.form-group{margin-bottom:0}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select{width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus,.form-group select:focus{outline:none;border-color:var(--accent)}
.form-group small{display:block;margin-top:4px;font-size:11px;color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-success{background:var(--success);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-sm{padding:6px 12px;font-size:12px}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th,.data-table td{padding:10px;text-align:left;border-bottom:1px solid var(--border)}
.data-table th{font-weight:600;color:var(--text2);font-size:10px;text-transform:uppercase;background:var(--bg)}
.data-table tr:hover td{background:rgba(137,180,250,.05)}
.img-thumb{width:50px;height:50px;object-fit:cover;border-radius:6px;background:var(--bg3)}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.inline-form{display:flex;gap:8px;align-items:center}
.inline-form input{padding:6px 10px;font-size:12px;width:120px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🖼️',
    'title' => 'AI ALT Generator',
    'description' => 'SEO-friendly image alt tags',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><span><?= $msgType === 'success' ? '✅' : ($msgType === 'warning' ? '⚠️' : '❌') ?></span><span><?= esc($msg) ?></span></div>
<?php endif; ?>

<div class="stat-grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $stats['total_images'] ?></div><div class="stat-lbl">Total Images</div></div>
<div class="stat-box" style="border-color:var(--success)"><div class="stat-val" style="color:var(--success)"><?= $stats['with_alt'] ?></div><div class="stat-lbl">With ALT</div></div>
<div class="stat-box" style="border-color:var(--danger)"><div class="stat-val" style="color:var(--danger)"><?= $stats['without_alt'] ?></div><div class="stat-lbl">Missing ALT</div></div>
<div class="stat-box" style="border-color:var(--<?= $covClass ?>)"><div class="stat-val" style="color:var(--<?= $covClass ?>)"><?= $stats['coverage_percent'] ?>%</div><div class="stat-lbl">Coverage</div></div>
</div>

<?php if (count($missing) > 0): ?>
<div class="card">
<div class="card-head primary"><span class="card-title"><span>🔧</span> Bulk Generate</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="bulk_generate">
<div class="form-row">
<div class="form-group"><label>Focus Keyword (optional)</label><input type="text" name="keyword" placeholder="e.g., school events"><small>Include naturally in ALT tags</small></div>
<div class="form-group"><label>Max Images</label><select name="limit"><option value="10">10</option><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option><option value="0">All (<?= count($missing) ?>)</option></select></div>
<button type="submit" class="btn btn-primary" onclick="return confirm('Generate ALT tags? This calls the AI API.')">🚀 Generate All</button>
</div>
</form>

<?php if ($bulkResult && !empty($bulkResult['results'])): ?>
<div style="margin-top:16px;max-height:200px;overflow-y:auto">
<table class="data-table">
<thead><tr><th>File</th><th>ALT</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($bulkResult['results'] as $r): ?>
<tr>
<td><?= esc($r['filename']) ?></td>
<td style="max-width:300px"><?= esc($r['alt'] ?? $r['error'] ?? '-') ?></td>
<td><span class="tag <?= $r['status'] === 'success' ? 'success' : 'danger' ?>"><?= $r['status'] === 'success' ? '✓' : '✗' ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

<div class="card">
<div class="card-head"><span class="card-title"><span>📋</span> Missing ALT Tags</span><span class="tag danger"><?= count($missing) ?></span></div>
<div class="card-body">
<?php if (empty($missing)): ?>
<div class="alert alert-success" style="margin:0"><span>✅</span><span>All images have ALT tags! Great job.</span></div>
<?php else: ?>
<div style="overflow-x:auto">
<table class="data-table">
<thead><tr><th style="width:60px">Preview</th><th>Filename</th><th>Path</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach (array_slice($missing, 0, 30) as $img): ?>
<tr>
<td><img src="/uploads/<?= esc(ltrim($img['path'], '/')) ?>" class="img-thumb" onerror="this.style.background='var(--bg3)'"></td>
<td><strong><?= esc($img['basename']) ?></strong><br><small style="color:var(--muted)"><?= esc($img['mime']) ?></small></td>
<td><small style="color:var(--muted)"><?= esc($img['path']) ?></small></td>
<td>
<form method="POST" class="inline-form">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate_single">
<input type="hidden" name="media_id" value="<?= esc($img['id']) ?>">
<input type="text" name="keyword" placeholder="Keyword">
<button type="submit" class="btn btn-sm btn-success">🔧 Gen</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if (count($missing) > 30): ?>
<p style="margin-top:12px;font-size:12px;color:var(--muted)">Showing 30 of <?= count($missing) ?>. Use bulk generation for all.</p>
<?php endif; ?>
<?php endif; ?>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>✏️</span> Manual Entry</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="update_alt">
<div class="form-row">
<div class="form-group"><label>Media ID</label><input type="text" name="media_id" required placeholder="e.g., abc123"></div>
<div class="form-group"><label>ALT Text</label><input type="text" name="alt_text" required placeholder="Descriptive text" maxlength="200"></div>
<button type="submit" class="btn btn-primary">💾 Save</button>
</div>
</form>
</div>
</div>

<div style="margin-top:20px">
<a href="/admin/media.php" class="btn btn-secondary">📁 Media Manager</a>
<a href="/admin/ai-seo-dashboard.php" class="btn btn-secondary">📊 SEO Dashboard</a>
</div>
</div>
</body>
</html>
