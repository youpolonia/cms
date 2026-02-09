<?php
/**
 * AI Image SEO — Generate alt text for images using AI
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
require_once CMS_ROOT . '/core/ai_content.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pdo = \core\Database::connection();
$message = '';
$messageType = '';

// Get AI provider
function getAiProvider(): ?array {
    $settings = ai_config_load_full();
    if (empty($settings['providers'])) return null;
    foreach ($settings['providers'] as $name => $cfg) {
        if (!empty($cfg['api_key'])) return ['provider' => $name, 'model' => $cfg['default_model'] ?? ''];
    }
    return null;
}

// Generate alt text using AI
function generateAltText(string $filename, string $context = ''): ?string {
    $ai = getAiProvider();
    if (!$ai) return null;
    
    $system = "You are an SEO image alt text specialist. Generate concise, descriptive alt text for web images. Alt text should be 5-15 words, describe the image content, and include relevant keywords naturally. Return ONLY the alt text, no quotes, no explanation.";
    
    $prompt = "Generate SEO-optimized alt text for an image.\n";
    $prompt .= "Filename: {$filename}\n";
    if ($context) $prompt .= "Page context: {$context}\n";
    $prompt .= "\nReturn only the alt text (5-15 words, descriptive, keyword-rich).";
    
    $result = ai_universal_generate($ai['provider'], $ai['model'], $system, $prompt, ['max_tokens' => 100, 'temperature' => 0.7]);
    
    if ($result['ok'] && !empty($result['content'])) {
        $alt = trim($result['content']);
        $alt = trim($alt, '"\'');
        return mb_substr($alt, 0, 255);
    }
    return null;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $updates = $_POST['alts'] ?? [];
        $saved = 0;
        foreach ($updates as $id => $altText) {
            $stmt = $pdo->prepare("UPDATE media SET alt_text = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([trim($altText), (int)$id]);
            $saved++;
        }
        $message = "✅ Saved alt text for {$saved} images.";
        $messageType = 'success';
    }
    
    if ($action === 'generate_single') {
        $id = (int)($_POST['media_id'] ?? 0);
        $context = trim($_POST['context'] ?? '');
        $row = $pdo->query("SELECT filename, original_name FROM media WHERE id = {$id}")->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $alt = generateAltText($row['original_name'] ?? $row['filename'], $context);
            if ($alt) {
                $stmt = $pdo->prepare("UPDATE media SET alt_text = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$alt, $id]);
                $message = "✅ Generated: \"{$alt}\"";
                $messageType = 'success';
            } else {
                $message = "❌ AI generation failed. Check AI settings.";
                $messageType = 'error';
            }
        }
    }
    
    if ($action === 'generate_batch') {
        $ids = $_POST['batch_ids'] ?? [];
        $context = trim($_POST['batch_context'] ?? '');
        $generated = 0;
        $failed = 0;
        foreach ($ids as $id) {
            $row = $pdo->query("SELECT filename, original_name FROM media WHERE id = " . (int)$id)->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $alt = generateAltText($row['original_name'] ?? $row['filename'], $context);
                if ($alt) {
                    $pdo->prepare("UPDATE media SET alt_text = ?, updated_at = NOW() WHERE id = ?")->execute([$alt, (int)$id]);
                    $generated++;
                } else {
                    $failed++;
                }
            }
            usleep(200000); // Rate limit: 200ms between calls
        }
        $message = "✅ Generated {$generated} alt texts" . ($failed ? ", {$failed} failed" : "") . ".";
        $messageType = 'success';
    }
}

// Load images
$filter = $_GET['filter'] ?? 'all';
$images = $pdo->query("SELECT id, filename, original_name, mime_type, path, title, alt_text, folder, created_at FROM media WHERE mime_type LIKE 'image/%' ORDER BY created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);

$totalImages = count($images);
$noAlt = count(array_filter($images, fn($i) => empty(trim($i['alt_text'] ?? ''))));
$hasAlt = $totalImages - $noAlt;
$noTitle = count(array_filter($images, fn($i) => empty(trim($i['title'] ?? ''))));

if ($filter === 'missing') $images = array_filter($images, fn($i) => empty(trim($i['alt_text'] ?? '')));
if ($filter === 'has_alt') $images = array_filter($images, fn($i) => !empty(trim($i['alt_text'] ?? '')));
$images = array_values($images);

$hasAi = getAiProvider() !== null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Image SEO - CMS</title>
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
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:700px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
.stat-box{background:var(--bg);border-radius:12px;padding:16px;text-align:center;cursor:pointer;transition:.15s;border:2px solid transparent;text-decoration:none;display:block;color:inherit}
.stat-box:hover{border-color:var(--accent)}
.stat-box.active{border-color:var(--accent);background:rgba(137,180,250,.08)}
.stat-val{font-size:24px;font-weight:700;margin-bottom:4px}
.stat-lbl{font-size:11px;color:var(--muted);text-transform:uppercase}
.image-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px}
.image-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.image-preview{height:140px;background:var(--bg3);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.image-preview img{max-width:100%;max-height:100%;object-fit:contain}
.image-preview .no-img{color:var(--muted);font-size:48px}
.image-info{padding:12px}
.image-info .filename{font-size:11px;color:var(--muted);font-family:monospace;margin-bottom:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.image-info label{display:block;font-size:11px;font-weight:500;color:var(--text2);margin-bottom:4px}
.image-info input{width:100%;padding:6px 10px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:12px}
.image-info input:focus{outline:none;border-color:var(--accent)}
.image-info input.missing{border-color:var(--danger)}
.image-actions{padding:8px 12px;border-top:1px solid var(--border);display:flex;gap:6px;justify-content:flex-end}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:12px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none;font-family:'Inter',sans-serif}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-success{background:var(--success);color:#000}
.btn-sm{padding:5px 10px;font-size:11px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:13px}
.alert-success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert-error{background:rgba(243,139,168,.1);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert-info{background:rgba(137,180,250,.1);border:1px solid rgba(137,180,250,.3);color:var(--accent)}
.tag{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:500}
.tag.success{background:rgba(166,227,161,.2);color:var(--success)}
.tag.danger{background:rgba(243,139,168,.2);color:var(--danger)}
.batch-bar{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.cb{width:16px;height:16px;accent-color:var(--accent);cursor:pointer}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '🖼️',
    'title' => 'Image SEO',
    'description' => 'Manage alt text for all images',
    'back_url' => '/admin/ai-seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--peach), var(--purple)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= esc($message) ?></div>
<?php endif; ?>

<?php if (!$hasAi): ?>
<div class="alert alert-info">💡 AI provider not configured. Manual editing available. Configure AI in settings for auto-generation.</div>
<?php endif; ?>

<!-- Stats -->
<div class="card">
<div class="card-head"><span class="card-title"><span>📊</span> Overview</span></div>
<div class="card-body">
<div class="stat-grid">
<a href="?filter=all" class="stat-box <?= $filter === 'all' ? 'active' : '' ?>"><div class="stat-val" style="color:var(--accent)"><?= $totalImages ?></div><div class="stat-lbl">Total Images</div></a>
<a href="?filter=has_alt" class="stat-box <?= $filter === 'has_alt' ? 'active' : '' ?>"><div class="stat-val" style="color:var(--success)"><?= $hasAlt ?></div><div class="stat-lbl">With Alt Text</div></a>
<a href="?filter=missing" class="stat-box <?= $filter === 'missing' ? 'active' : '' ?>"><div class="stat-val" style="color:<?= $noAlt ? 'var(--danger)' : 'var(--success)' ?>"><?= $noAlt ?></div><div class="stat-lbl">Missing Alt</div></a>
<div class="stat-box"><div class="stat-val" style="color:var(--success)"><?= $totalImages > 0 ? round($hasAlt / $totalImages * 100) : 0 ?>%</div><div class="stat-lbl">Coverage</div></div>
</div>
</div>
</div>

<?php if ($hasAi && $noAlt > 0): ?>
<!-- Batch generate -->
<form method="POST" id="batchForm">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="generate_batch">
<div class="batch-bar">
<input type="checkbox" class="cb" id="selectAll" onclick="toggleAll(this)">
<label for="selectAll" style="font-size:12px;cursor:pointer">Select all missing</label>
<input type="text" name="batch_context" placeholder="Optional: page topic for better results…" style="flex:1;padding:8px 12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px;min-width:200px">
<button type="submit" class="btn btn-primary" onclick="return confirm('Generate alt text for selected images using AI?')">🤖 Generate for Selected</button>
</div>
<?php endif; ?>

<!-- Image grid -->
<form method="POST" id="saveForm">
<?php csrf_field(); ?>
<input type="hidden" name="action" value="save">

<?php if (empty($images)): ?>
<div class="card"><div class="card-body"><div style="text-align:center;padding:40px;color:var(--muted)"><p style="font-size:28px;margin-bottom:12px">🖼️</p><p>No images match the filter.</p></div></div></div>
<?php else: ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<span style="font-size:12px;color:var(--muted)"><?= count($images) ?> images</span>
<button type="submit" class="btn btn-success">💾 Save All Changes</button>
</div>

<div class="image-grid">
<?php foreach ($images as $img):
$hasAltText = !empty(trim($img['alt_text'] ?? ''));
$imgPath = '/' . ltrim($img['path'] ?? '', '/');
?>
<div class="image-card">
<div class="image-preview">
<?php if ($hasAi && !$hasAltText): ?>
<input type="checkbox" class="cb batch-cb" name="batch_ids[]" value="<?= $img['id'] ?>" form="batchForm" style="position:absolute;top:8px;left:8px;z-index:1">
<?php endif; ?>
<img src="<?= esc($imgPath) ?>" alt="<?= esc($img['alt_text'] ?? '') ?>" loading="lazy" onerror="this.outerHTML='<span class=no-img>🖼️</span>'">
</div>
<div class="image-info">
<div class="filename" title="<?= esc($img['original_name'] ?? $img['filename']) ?>"><?= esc($img['original_name'] ?? $img['filename']) ?></div>
<label>Alt Text <?= $hasAltText ? '<span class="tag success" style="margin-left:4px">✓</span>' : '<span class="tag danger" style="margin-left:4px">Missing</span>' ?></label>
<input type="text" name="alts[<?= $img['id'] ?>]" value="<?= esc($img['alt_text'] ?? '') ?>" placeholder="Descriptive alt text…" class="<?= $hasAltText ? '' : 'missing' ?>">
</div>
</div>
<?php endforeach; ?>
</div>

<div style="margin-top:16px;text-align:right">
<button type="submit" class="btn btn-success">💾 Save All Changes</button>
</div>
<?php endif; ?>
</form>

<?php if ($hasAi && $noAlt > 0): ?>
</form><!-- close batchForm -->
<?php endif; ?>

</div>

<script>
function toggleAll(el) {
    document.querySelectorAll('.batch-cb').forEach(cb => cb.checked = el.checked);
}
</script>
</body>
</html>
