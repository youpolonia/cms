<?php
/**
 * AI SEO Schema Generator - Modern Dark UI
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
require_once CMS_ROOT . '/core/ai_schema_generator.php';
require_once CMS_ROOT . '/core/database.php';

cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();


function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pages = [];
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title ASC LIMIT 200");
    $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\Exception $e) {}

$generatedSchema = null;
$selectedPageId = null;
$schemaType = 'Article';
$msg = '';

$schemaTypes = ['Article','BlogPosting','Product','FAQPage','HowTo','LocalBusiness','Organization','Person','Event','Recipe','Course','WebPage','BreadcrumbList'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate') {
        $selectedPageId = (int)($_POST['page_id'] ?? 0);
        $schemaType = $_POST['schema_type'] ?? 'Article';
        $customData = [];
        
        if (!empty($_POST['org_name'])) $customData['organization_name'] = $_POST['org_name'];
        if (!empty($_POST['logo_url'])) $customData['logo_url'] = $_POST['logo_url'];
        if (!empty($_POST['author'])) $customData['author'] = $_POST['author'];
        
        $result = ai_schema_generate($selectedPageId, $schemaType, $customData);
        if ($result['ok']) {
            $generatedSchema = $result['schema'];
            $msg = 'Schema generated successfully!';
        } else {
            $msg = 'Error: ' . ($result['error'] ?? 'Unknown');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schema Generator - CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-head.success{background:rgba(166,227,161,.1)}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:10px}
.card-body{padding:20px}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:16px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:6px;color:var(--text2)}
.form-group input,.form-group select{width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.form-group input:focus,.form-group select:focus{outline:none;border-color:var(--accent)}
.form-group small{display:block;margin-top:4px;font-size:11px;color:var(--muted)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.output-box{background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px;font-family:monospace;font-size:11px;white-space:pre-wrap;max-height:500px;overflow:auto}
.empty{text-align:center;padding:60px;color:var(--muted)}
.type-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:16px}
.type-btn{padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:12px;text-align:center;transition:.15s}
.type-btn:hover,.type-btn.active{border-color:var(--accent);background:rgba(137,180,250,.1)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '🏷️',
    'title' => 'Schema Generator',
    'description' => 'Structured data markup',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => '#fab387, var(--warning-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<div class="alert alert-info">💡 Generate Schema.org structured data to improve SEO and rich snippets in search results.</div>

<?php if ($msg && strpos($msg, 'Error') === false): ?>
<div class="alert alert-success">✅ <?= esc($msg) ?></div>
<?php elseif ($msg): ?>
<div class="alert" style="background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger)">❌ <?= esc($msg) ?></div>
<?php endif; ?>

<div class="grid">
<div>
<div class="card">
<div class="card-head"><span class="card-title"><span>⚙️</span> Generate Schema</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate">

<div class="form-group">
<label>Select Page</label>
<select name="page_id">
<option value="0">— Select a page —</option>
<?php foreach ($pages as $p): ?>
<option value="<?= $p['id'] ?>" <?= $selectedPageId === (int)$p['id'] ? 'selected' : '' ?>><?= esc($p['title']) ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="form-group">
<label>Schema Type</label>
<div class="type-grid">
<?php foreach ($schemaTypes as $t): ?>
<div class="type-btn <?= $schemaType === $t ? 'active' : '' ?>" onclick="this.parentNode.querySelectorAll('.type-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');document.querySelector('[name=schema_type]').value='<?= $t ?>'"><?= $t ?></div>
<?php endforeach; ?>
</div>
<input type="hidden" name="schema_type" value="<?= esc($schemaType) ?>">
</div>

<div class="form-row">
<div class="form-group"><label>Organization Name</label><input type="text" name="org_name" placeholder="Your Company"></div>
<div class="form-group"><label>Author</label><input type="text" name="author" placeholder="Author name"></div>
</div>

<div class="form-group"><label>Logo URL</label><input type="text" name="logo_url" placeholder="https://..."><small>Full URL to organization logo</small></div>

<button type="submit" class="btn btn-primary">🏷️ Generate Schema</button>
</form>
</div>
</div>

<div class="card">
<div class="card-head"><span class="card-title"><span>📖</span> Schema Types Guide</span></div>
<div class="card-body">
<div style="font-size:12px;color:var(--text2)">
<p><strong>Article/BlogPosting:</strong> News, blog posts</p>
<p><strong>Product:</strong> E-commerce products</p>
<p><strong>FAQPage:</strong> FAQ sections</p>
<p><strong>HowTo:</strong> Tutorial/guide pages</p>
<p><strong>LocalBusiness:</strong> Local business info</p>
<p><strong>Event:</strong> Events and happenings</p>
<p><strong>BreadcrumbList:</strong> Navigation breadcrumbs</p>
</div>
</div>
</div>
</div>

<div>
<div class="card">
<div class="card-head <?= $generatedSchema ? 'success' : '' ?>"><span class="card-title"><span><?= $generatedSchema ? '✅' : '📋' ?></span> Generated Schema</span>
<?php if ($generatedSchema): ?>
<button class="btn btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('schemaOutput').textContent)">📋 Copy</button>
<?php endif; ?>
</div>
<div class="card-body">
<?php if ($generatedSchema): ?>
<div class="output-box" id="schemaOutput"><?= esc(json_encode($generatedSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></div>
<div style="margin-top:16px">
<p style="font-size:12px;color:var(--text2);margin-bottom:8px">Add this to your page's &lt;head&gt;:</p>
<div class="output-box" style="font-size:10px">&lt;script type="application/ld+json"&gt;
<?= esc(json_encode($generatedSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>
&lt;/script&gt;</div>
</div>
<?php else: ?>
<div class="empty"><p style="font-size:32px;margin-bottom:12px">🏷️</p><p>Select a page and schema type to generate</p></div>
<?php endif; ?>
</div>
</div>
</div>
</div>

<div style="margin-top:20px">
<a href="/admin/ai-seo-dashboard.php" class="btn btn-secondary">📊 SEO Dashboard</a>
<a href="/admin/ai-seo-assistant.php" class="btn btn-secondary">🔍 SEO Assistant</a>
</div>
</div>
</body>
</html>
