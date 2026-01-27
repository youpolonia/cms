<?php
/**
 * Content Suggestions Panel
 * Modern dark UI with AI-powered suggestions
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/database.php';

cms_session_start('admin');
csrf_boot();

require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

$suggestions = [];
$originalContent = '';
$message = '';

// Handle content analysis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $originalContent = trim($_POST['content'] ?? '');
    
    if (!empty($originalContent)) {
        // AI analysis simulation (replace with real API)
        $suggestions = [
            ['type' => 'readability', 'icon' => '📖', 'title' => 'Readability', 'text' => 'Consider breaking long paragraphs into shorter ones for better readability.'],
            ['type' => 'seo', 'icon' => '🔍', 'title' => 'SEO', 'text' => 'Add relevant keywords and meta description for better search visibility.'],
            ['type' => 'tone', 'icon' => '🎯', 'title' => 'Tone', 'text' => 'The content tone is professional. Consider adding more engaging elements.'],
            ['type' => 'grammar', 'icon' => '✍️', 'title' => 'Grammar', 'text' => 'No major grammar issues detected.'],
        ];
        $message = 'Analysis complete!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Suggestions - CMS</title>
    <style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.6}
.container{max-width:1400px;margin:0 auto;padding:24px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px}
@media(max-width:900px){.grid-2{grid-template-columns:1fr}}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:24px}
.card-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.card-title{font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid var(--success);color:var(--success)}
textarea{width:100%;min-height:300px;padding:16px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;resize:vertical;font-family:inherit}
textarea:focus{outline:none;border-color:var(--accent)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;font-size:14px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:all .15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px)}
.suggestion-item{background:var(--bg3);border-radius:10px;padding:16px;margin-bottom:12px}
.suggestion-item:last-child{margin-bottom:0}
.suggestion-header{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.suggestion-icon{font-size:20px}
.suggestion-title{font-weight:600;color:var(--text)}
.suggestion-text{color:var(--text2);font-size:14px}
.empty-state{text-align:center;padding:40px;color:var(--muted)}
.empty-state span{font-size:48px;display:block;margin-bottom:12px}
.form-actions{margin-top:16px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '💡',
    'title' => 'Content Suggestions',
    'description' => 'AI-powered content improvement recommendations',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--warning-color), var(--accent-color)'
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="grid-2">
        <div class="card">
            <div class="card-head">
                <span class="card-title"><span>📝</span> Your Content</span>
            </div>
            <form method="post">
                <?php csrf_field(); ?>
                <textarea name="content" placeholder="Paste your content here for AI analysis..."><?= htmlspecialchars($originalContent) ?></textarea>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">🔍 Analyze Content</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-head">
                <span class="card-title"><span>💡</span> Suggestions</span>
            </div>
            
            <?php if (empty($suggestions)): ?>
                <div class="empty-state">
                    <span>🤖</span>
                    <p>Paste content and click Analyze to get AI suggestions</p>
                </div>
            <?php else: ?>
                <?php foreach ($suggestions as $s): ?>
                    <div class="suggestion-item">
                        <div class="suggestion-header">
                            <span class="suggestion-icon"><?= $s['icon'] ?></span>
                            <span class="suggestion-title"><?= htmlspecialchars($s['title']) ?></span>
                        </div>
                        <p class="suggestion-text"><?= htmlspecialchars($s['text']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
