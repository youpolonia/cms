<?php
/**
 * AI Content Creator - Modern Dark UI
 * Local tools for drafting content (no external APIs)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit; }
csrf_boot();
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$source = trim($_GET['source'] ?? '');
$action = $_GET['action'] ?? '';

// Helper functions
function ac_sentences(string $txt): array {
    $txt = trim(preg_replace('/\s+/u', ' ', $txt));
    if ($txt === '') return [];
    return array_filter(array_map('trim', preg_split('/(?<=[.!?])\s+/u', $txt)));
}

function ac_outline(string $txt): array {
    $sents = ac_sentences($txt);
    return array_slice($sents, 0, 8);
}

function ac_summary(string $txt): string {
    $sents = ac_sentences($txt);
    if (!$sents) return '';
    $limit = max(1, (int)ceil(min(5, count($sents)) / 2));
    return implode(' ', array_slice($sents, 0, $limit));
}

function ac_paraphrase(string $txt): string {
    $map = ['/\bvery\b/i'=>'highly','/\breally\b/i'=>'truly','/\bimportant\b/i'=>'key','/\bproblem\b/i'=>'issue'];
    foreach ($map as $re => $rep) $txt = preg_replace($re, $rep, $txt);
    return $txt;
}

function ac_titles(string $txt): array {
    $txt = trim(preg_replace('/\s+/u', ' ', $txt));
    if ($txt === '') return [];
    $core = rtrim(mb_substr($txt, 0, 60, 'UTF-8'), " \t-–—:;,.");
    return array_map(fn($s) => "$core — $s", ['Guide','Overview','Checklist','Best Practices','How-To']);
}

function ac_slug(string $txt): string {
    return preg_replace('/-+/', '-', trim(preg_replace('/[^\pL\pN]+/u', '-', mb_strtolower($txt, 'UTF-8')), '-'));
}

$result = null;
$resultType = '';
if ($source !== '' && $action !== '') {
    $resultType = $action;
    $result = match($action) {
        'outline' => ac_outline($source),
        'summary' => ac_summary($source),
        'paraphrase' => ac_paraphrase($source),
        'titles' => ac_titles($source),
        'slug' => ac_slug($source),
        default => null
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Content Creator - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group textarea{width:100%;padding:12px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:13px;resize:vertical;min-height:200px;font-family:monospace}
.form-group textarea:focus{outline:none;border-color:var(--accent)}
.actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover{background:var(--bg4)}
.char-count{margin-left:auto;font-size:12px;color:var(--muted)}
.result-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px}
.result-item{padding:10px 12px;background:var(--bg2);border-radius:8px;margin-bottom:8px;font-size:13px}
.result-item:last-child{margin-bottom:0}
.result-item code{background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:12px}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📝',
    'title' => 'AI Content Creator',
    'description' => 'Local text processing tools',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--success-color), var(--cyan)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="alert alert-info">💡 Local tools for drafting content. No external APIs are used - all processing happens server-side.</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📝</span> Source Content</span></div>
<div class="card-body">
<form method="GET">
<div class="form-group">
<label>Paste your text</label>
<textarea name="source" placeholder="Enter or paste your content here..."><?= esc($source) ?></textarea>
</div>
<div class="actions">
<button type="submit" name="action" value="outline" class="btn btn-secondary">📋 Outline</button>
<button type="submit" name="action" value="summary" class="btn btn-secondary">📄 Summary</button>
<button type="submit" name="action" value="paraphrase" class="btn btn-secondary">🔄 Paraphrase</button>
<button type="submit" name="action" value="titles" class="btn btn-secondary">✨ Titles (5)</button>
<button type="submit" name="action" value="slug" class="btn btn-secondary">🔗 Slug</button>
<span class="char-count">Characters: <?= strlen($source) ?></span>
</div>
</form>
</div>
</div>

<?php if ($result !== null): ?>
<div class="card">
<div class="card-head"><span class="card-title"><span>✅</span> Result: <?= ucfirst($resultType) ?></span></div>
<div class="card-body">
<div class="result-box">
<?php if (is_array($result)): ?>
    <?php if (empty($result)): ?>
        <p style="color:var(--muted)">No results generated. Try adding more content.</p>
    <?php else: ?>
        <?php foreach ($result as $i => $item): ?>
            <div class="result-item"><?= ($resultType === 'outline' ? ($i+1).'. ' : '') ?><?= esc($item) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php else: ?>
    <?php if ($resultType === 'slug'): ?>
        <div class="result-item"><code><?= esc($result) ?></code></div>
    <?php else: ?>
        <div class="result-item"><?= nl2br(esc($result)) ?></div>
    <?php endif; ?>
<?php endif; ?>
</div>
</div>
</div>
<?php endif; ?>

<div class="card">
<div class="card-head"><span class="card-title"><span>ℹ️</span> Available Tools</span></div>
<div class="card-body">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
<div><strong>📋 Outline</strong><p style="font-size:12px;color:var(--text2);margin-top:4px">Extract key sentences as outline points</p></div>
<div><strong>📄 Summary</strong><p style="font-size:12px;color:var(--text2);margin-top:4px">Generate a condensed summary</p></div>
<div><strong>🔄 Paraphrase</strong><p style="font-size:12px;color:var(--text2);margin-top:4px">Reword text with synonyms</p></div>
<div><strong>✨ Titles</strong><p style="font-size:12px;color:var(--text2);margin-top:4px">Generate 5 title variations</p></div>
<div><strong>🔗 Slug</strong><p style="font-size:12px;color:var(--text2);margin-top:4px">Create URL-friendly slug</p></div>
</div>
</div>
</div>

<div style="margin-top:20px">
<a href="/admin/ai-copywriter.php" class="btn btn-secondary">✍️ AI Copywriter</a>
<a href="/admin/ai-translate.php" class="btn btn-secondary">🌐 Translate</a>
</div>
</div>
</body>
</html>
