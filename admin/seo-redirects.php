<?php
/**
 * SEO Redirects — Catppuccin Dark UI
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

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    if (isset($_POST['delete_id'])) {
        $result = $controller->deleteRedirect((int) $_POST['delete_id']);
        if ($result['success']) $success = $result['message'];
        else $errors = $result['errors'];
    } elseif (isset($_POST['source_url'])) {
        $result = $controller->saveRedirect($_POST);
        if ($result['success']) $success = $result['message'];
        else $errors = $result['errors'];
    }
}

$data = $controller->listRedirects();
$data['success'] = $success;
$data['errors'] = $errors;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<title>SEO Redirects - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1200px;margin:0 auto;padding:24px 32px}
/* Card & Table */
.card{background:var(--bg2);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px}
.data-table,.table{width:100%;border-collapse:collapse}
.data-table th,.table th{padding:10px 16px;font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;background:var(--bg);text-align:left;border-bottom:1px solid var(--border)}
.data-table td,.table td{padding:12px 16px;border-bottom:1px solid var(--border)}
.data-table tr:hover td,.table tr:hover td{background:rgba(137,180,250,.03)}
/* Forms */
.form-row{margin-bottom:18px}
.form-row label{display:block;font-size:13px;font-weight:500;margin-bottom:6px}
.form-control{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;font-family:'Inter',sans-serif;transition:.15s}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
select.form-control{appearance:auto;cursor:pointer}
.form-row-inline{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:6px}
/* Buttons */
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none;font-family:'Inter',sans-serif;background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn:hover{border-color:var(--accent)}
.btn.primary{background:var(--accent);color:#000;border-color:var(--accent)}
.btn.primary:hover{background:var(--purple);border-color:var(--purple)}
.btn.danger,.btn-danger{background:rgba(243,139,168,.15);color:var(--danger);border:1px solid rgba(243,139,168,.3)}
.btn.danger:hover,.btn-danger:hover{background:rgba(243,139,168,.25)}
.btn.small,.btn-sm{padding:6px 12px;font-size:12px}
/* Alerts */
.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px}
.alert.success{background:rgba(166,227,161,.1);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.alert.error{background:rgba(243,139,168,.1);border:1px solid rgba(243,139,168,.3);color:var(--danger)}
.alert ul{margin:4px 0 0;padding-left:20px}
/* Tags */
.tag,.badge{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:600}
.tag-success,.badge-success{background:rgba(166,227,161,.15);color:var(--success)}
.tag-warning,.badge-warning{background:rgba(249,226,175,.15);color:var(--warning)}
.tag-danger,.badge-danger{background:rgba(243,139,168,.15);color:var(--danger)}
.tag-muted,.badge-muted{background:var(--bg3);color:var(--muted)}
code{background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:12px;font-family:'JetBrains Mono','Fira Code',monospace}
/* Page header in view */
.page-header{margin-bottom:20px;display:flex;align-items:center;justify-content:space-between}
.page-header h1{font-size:20px;font-weight:600}
.page-header p{color:var(--text2);font-size:13px;margin-top:4px}
.form-actions{display:flex;gap:12px;padding-top:8px}
/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center}
.modal-overlay.active{display:flex}
.modal{background:var(--bg2);border:1px solid var(--border);border-radius:16px;width:90%;max-width:560px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.modal h3{font-size:18px;font-weight:600;margin-bottom:20px}
.modal-close{position:absolute;top:16px;right:16px;background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px}
/* Misc */
.empty{text-align:center;padding:40px;color:var(--muted)}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:16px;padding:16px}
.pagination a{padding:6px 12px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;color:var(--text);text-decoration:none;font-size:13px}
.pagination a:hover{border-color:var(--accent)}
.pagination a.active{background:var(--accent);color:#000;border-color:var(--accent)}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>
<?php
$pageHeader = [
    'icon' => '🔀',
    'title' => 'URL Redirects',
    'description' => 'Manage 301/302 redirects for changed or moved URLs',
    'back_url' => '/admin/seo-dashboard',
    'back_text' => 'SEO Dashboard',
    'gradient' => 'var(--warning-color), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>
<div class="container">
<?php require_once __DIR__ . '/views/seo/redirects.php'; ?>
</div>
</body>
</html>
