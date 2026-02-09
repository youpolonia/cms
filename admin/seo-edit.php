<?php
/**
 * SEO Metadata Edit — Catppuccin Dark UI
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/controllers/seocontroller.php';

$db = \core\Database::connection();
$controller = new SeoController($db);

$entityType = isset($_GET['type']) ? trim($_GET['type']) : 'page';
$entityId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!in_array($entityType, ['page', 'article', 'category', 'custom'], true)) $entityType = 'page';

if ($entityId <= 0) {
    $_SESSION['flash_error'] = 'Invalid entity ID';
    header('Location: seo-dashboard.php');
    exit;
}

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    if (isset($_POST['delete']) && $_POST['delete'] === '1') {
        require_once __DIR__ . '/../core/models/seomodel.php';
        $seoModel = new SeoModel($db);
        $metadata = $seoModel->getMetadata($entityType, $entityId);
        if ($metadata) {
            $seoModel->deleteMetadata((int) $metadata['id']);
            $_SESSION['flash_success'] = 'SEO data deleted';
            header('Location: seo-dashboard.php');
            exit;
        }
    }
    $result = $controller->save($entityType, $entityId, $_POST);
    if ($result['success']) $success = $result['message'] ?? 'Settings saved';
    else $errors = $result['errors'] ?? ['An error occurred'];
}

$data = $controller->edit($entityType, $entityId);
$data['errors'] = $errors;
$data['success'] = $success;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<title>Edit SEO - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1000px;margin:0 auto;padding:24px 32px}
/* Card */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px}
.card h2{font-size:16px;font-weight:600;margin-bottom:16px}
.card p.muted{color:var(--muted);font-size:13px;margin-bottom:12px}
/* Forms */
.form-row{margin-bottom:18px}
.form-row label{display:block;font-size:13px;font-weight:500;margin-bottom:6px;color:var(--text)}
.form-row small,.form-row small.muted{display:block;font-size:12px;color:var(--muted);margin-top:4px}
.form-control{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;font-family:'Inter',sans-serif;transition:.15s}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
textarea.form-control{resize:vertical}
select.form-control{cursor:pointer;appearance:auto}
.form-row-inline{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:0}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:6px}
textarea.code{font-family:'JetBrains Mono','Fira Code',monospace;font-size:13px}
/* Buttons */
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none;font-family:'Inter',sans-serif;background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn:hover{border-color:var(--accent)}
.btn.primary{background:var(--accent);color:#000;border-color:var(--accent)}
.btn.primary:hover{background:var(--purple);border-color:var(--purple)}
.btn.danger{background:rgba(243,139,168,.15);color:var(--danger);border-color:rgba(243,139,168,.3);margin-left:auto}
.btn.danger:hover{background:rgba(243,139,168,.25)}
.btn.small{padding:6px 12px;font-size:12px}
.form-actions{display:flex;align-items:center;gap:12px;padding-top:8px}
/* Alerts */
.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px}
.alert.success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert.error{background:rgba(243,139,168,.1);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert ul{margin:4px 0 0;padding-left:20px}
/* Score */
.score-preview{display:flex;align-items:center;gap:20px;padding:16px;background:var(--bg);border:1px solid var(--border);border-radius:12px;margin-bottom:20px}
.score-circle{width:72px;height:72px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;border:3px solid var(--border)}
.score-circle.good{border-color:var(--success);background:rgba(166,227,161,.1)}
.score-circle.fair{border-color:var(--warning);background:rgba(249,226,175,.1)}
.score-circle.poor{border-color:var(--danger);background:rgba(243,139,168,.1)}
.score-value{font-size:22px;font-weight:700}
.score-label{font-size:10px;color:var(--muted);text-transform:uppercase}
.last-analyzed{color:var(--muted);font-size:12px}
.char-counter{font-size:12px;color:var(--muted);margin-top:4px}
/* Misc */
.badge{display:inline-block;padding:3px 8px;background:var(--bg3);border-radius:5px;font-size:11px;color:var(--text2);text-transform:uppercase}
.page-header{margin-bottom:20px}
.page-header h1{font-size:20px;font-weight:600}
.page-header p{color:var(--text2);font-size:13px;margin-top:4px}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '✏️',
    'title' => 'Edit SEO Metadata',
    'description' => 'Edit SEO settings for ' . htmlspecialchars($data['entity_title'] ?? 'page', ENT_QUOTES, 'UTF-8'),
    'back_url' => '/admin/seo-metadata',
    'back_text' => 'SEO Metadata',
    'gradient' => 'var(--success-color), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">
<?php require_once __DIR__ . '/views/seo/edit.php'; ?>
</div>
</body>
</html>
