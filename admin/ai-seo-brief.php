<?php
/**
 * AI Content Brief Generator - Modern Dark UI
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
require_once CMS_ROOT . '/core/ai_content_brief.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit('Forbidden'); }

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$contentTypes = ai_brief_get_content_types();
$brief = null;
$keyword = '';
$contentType = 'blog_post';
$audience = 'general audience';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $keyword = trim($_POST['keyword'] ?? '');
    $contentType = trim($_POST['content_type'] ?? 'blog_post');
    $audience = trim($_POST['audience'] ?? 'general audience');
    if ($keyword) {
        $brief = ai_brief_generate($keyword, $contentType, ['audience' => $audience]);
    }
}

// Export markdown
if (isset($_GET['export']) && $_GET['export'] === 'markdown' && isset($_GET['keyword'])) {
    $kw = trim($_GET['keyword']);
    $ct = $_GET['content_type'] ?? 'blog_post';
    $aud = $_GET['audience'] ?? 'general audience';
    $b = ai_brief_generate($kw, $ct, ['audience' => $aud]);
    if ($b && $b['ok']) {
        header('Content-Type: text/markdown');
        header('Content-Disposition: attachment; filename="brief-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($kw)) . '.md"');
        echo ai_brief_to_markdown($b);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Content Brief Generator - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.success{background:rgba(166,227,161,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.form-row{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end}
@media(max-width:800px){.form-row{grid-template-columns:1fr}}
.form-group{margin-bottom:0}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select{width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus,.form-group select:focus{outline:none;border-color:var(--accent)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.section{margin-bottom:24px}
.section-title{font-size:14px;font-weight:600;color:var(--accent);margin-bottom:12px;display:flex;align-items:center;gap:8px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.stat-box{background:var(--bg);border-radius:10px;padding:14px;text-align:center}
.stat-val{font-size:20px;font-weight:700}
.stat-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;margin-top:4px}
.list-item{background:var(--bg);border-radius:8px;padding:12px;margin-bottom:8px}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;margin:2px}
.tag.primary{background:rgba(137,180,250,.2);color:var(--accent)}
.tag.secondary{background:var(--bg3);color:var(--text2)}
.outline-item{padding:10px 12px;background:var(--bg);border-radius:8px;margin-bottom:6px;border-left:3px solid var(--accent)}
.outline-item h4{font-size:13px;margin-bottom:4px}
.outline-item p{font-size:11px;color:var(--text2);margin:0}
.empty{text-align:center;padding:60px;color:var(--muted)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📝',
    'title' => 'Content Brief Generator',
    'description' => 'AI-powered writing briefs',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--cyan), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="card">
<div class="card-head"><span class="card-title"><span>🎯</span> Generate Brief</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<div class="form-row">
<div class="form-group"><label>Target Keyword *</label><input type="text" name="keyword" value="<?= esc($keyword) ?>" placeholder="e.g., digital marketing strategies" required></div>
<div class="form-group"><label>Content Type</label><select name="content_type"><?php foreach ($contentTypes as $k => $v): ?><option value="<?= $k ?>" <?= $contentType === $k ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Audience</label><input type="text" name="audience" value="<?= esc($audience) ?>" placeholder="e.g., marketers"></div>
<button type="submit" class="btn btn-primary">✨ Generate</button>
</div>
</form>
</div>
</div>

<?php if ($brief && $brief['ok']): ?>
<div class="card">
<div class="card-head success">
<span class="card-title"><span>✅</span> Brief: <?= esc($brief['keyword']) ?></span>
<a href="?export=markdown&keyword=<?= urlencode($keyword) ?>&content_type=<?= urlencode($contentType) ?>&audience=<?= urlencode($audience) ?>" class="btn btn-secondary">📥 Export MD</a>
</div>
<div class="card-body">

<div class="section">
<div class="section-title"><span>📊</span> Overview</div>
<div class="grid">
<div class="stat-box"><div class="stat-val" style="color:var(--accent)"><?= $brief['recommended_length']['min'] ?? 1000 ?>-<?= $brief['recommended_length']['max'] ?? 2000 ?></div><div class="stat-lbl">Words</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $brief['difficulty'] ?? 'Medium' ?></div><div class="stat-lbl">Difficulty</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--purple)"><?= count($brief['sections'] ?? []) ?></div><div class="stat-lbl">Sections</div></div>
<div class="stat-box"><div class="stat-val" style="color:var(--cyan)"><?= count($brief['keywords']['primary'] ?? []) + count($brief['keywords']['secondary'] ?? []) ?></div><div class="stat-lbl">Keywords</div></div>
</div>
</div>

<?php if (!empty($brief['title_suggestions'])): ?>
<div class="section">
<div class="section-title"><span>✏️</span> Title Suggestions</div>
<?php foreach ($brief['title_suggestions'] as $t): ?>
<div class="list-item"><?= esc($t) ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($brief['keywords'])): ?>
<div class="section">
<div class="section-title"><span>🎯</span> Keywords</div>
<div><strong style="font-size:11px;color:var(--muted)">PRIMARY:</strong> <?php foreach (($brief['keywords']['primary'] ?? []) as $k): ?><span class="tag primary"><?= esc($k) ?></span><?php endforeach; ?></div>
<div style="margin-top:8px"><strong style="font-size:11px;color:var(--muted)">SECONDARY:</strong> <?php foreach (($brief['keywords']['secondary'] ?? []) as $k): ?><span class="tag secondary"><?= esc($k) ?></span><?php endforeach; ?></div>
</div>
<?php endif; ?>

<?php if (!empty($brief['sections'])): ?>
<div class="section">
<div class="section-title"><span>📑</span> Content Outline</div>
<?php foreach ($brief['sections'] as $i => $s): ?>
<div class="outline-item">
<h4><?= $i + 1 ?>. <?= esc($s['heading'] ?? '') ?></h4>
<p><?= esc($s['description'] ?? '') ?></p>
<?php if (!empty($s['points'])): ?><p style="margin-top:4px">Points: <?= esc(implode(', ', $s['points'])) ?></p><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($brief['questions'])): ?>
<div class="section">
<div class="section-title"><span>❓</span> Questions to Answer</div>
<?php foreach ($brief['questions'] as $q): ?>
<div class="list-item"><?= esc($q) ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($brief['internal_links']) || !empty($brief['external_sources'])): ?>
<div class="section">
<div class="section-title"><span>🔗</span> Links & Sources</div>
<div class="grid">
<?php if (!empty($brief['internal_links'])): ?>
<div><strong style="font-size:11px;color:var(--muted)">INTERNAL:</strong><?php foreach ($brief['internal_links'] as $l): ?><div class="list-item" style="font-size:12px"><?= esc($l['title'] ?? $l) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if (!empty($brief['external_sources'])): ?>
<div><strong style="font-size:11px;color:var(--muted)">EXTERNAL:</strong><?php foreach ($brief['external_sources'] as $s): ?><div class="list-item" style="font-size:12px"><?= esc($s) ?></div><?php endforeach; ?></div>
<?php endif; ?>
</div>
</div>
<?php endif; ?>

</div>
</div>
<?php elseif ($brief && !$brief['ok']): ?>
<div class="card"><div class="card-body"><div class="alert" style="background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger);margin:0">❌ <?= esc($brief['error'] ?? 'Generation failed') ?></div></div></div>
<?php else: ?>
<div class="card"><div class="card-body"><div class="empty"><p style="font-size:32px;margin-bottom:12px">📝</p><p>Enter a keyword and generate your content brief</p></div></div></div>
<?php endif; ?>

<div style="margin-top:20px">
<a href="/admin/ai-seo-dashboard.php" class="btn btn-secondary">📊 SEO Dashboard</a>
<a href="/admin/ai-seo-assistant.php" class="btn btn-secondary">🔍 SEO Assistant</a>
</div>
</div>
</body>
</html>
