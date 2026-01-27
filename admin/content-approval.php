<?php
/**
 * Content Version Approval
 * Modern dark UI with central header
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/includes/models/versionmodel.php';

cms_session_start('admin');
csrf_boot();

require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login');
    exit;
}

$tenantId = $_SESSION['tenant_id'] ?? null;
$versionModel = new VersionModel();
$message = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $versionId = $_POST['version_id'] ?? null;
    $action = $_POST['action'] ?? '';
    
    if ($versionId && in_array($action, ['approve', 'reject'])) {
        try {
            $db = \core\Database::connection();
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt = $db->prepare("UPDATE content_versions SET status = ? WHERE id = ?");
            $stmt->execute([$status, $versionId]);
            $message = "Version " . $action . "d successfully";
        } catch (Exception $e) {
            $error = 'Processing failed';
        }
    }
}

// Get pending versions
$pendingVersions = $versionModel->getFilteredVersions([
    'sort' => 'created_at',
    'order' => 'desc'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Approval - CMS</title>
    <style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--cyan:#89dceb;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.6}
.container{max-width:1400px;margin:0 auto;padding:24px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.card-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}
.card-title{font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid var(--success);color:var(--success)}
.alert-danger{background:rgba(243,139,168,.15);border:1px solid var(--danger);color:var(--danger)}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;text-align:left;border-bottom:1px solid var(--border)}
th{background:var(--bg3);font-weight:600;font-size:13px;text-transform:uppercase;color:var(--text2)}
tr:hover{background:var(--bg3)}
.badge{padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500}
.badge-warning{background:rgba(249,226,175,.2);color:var(--warning)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:all .15s}
.btn-success{background:var(--success);color:#000}
.btn-danger{background:var(--danger);color:#000}
.btn-success:hover,.btn-danger:hover{filter:brightness(1.1);transform:translateY(-1px)}
.btn-sm{padding:6px 10px;font-size:12px}
.approval-form{display:flex;gap:8px;align-items:center}
.approval-form textarea{width:200px;padding:6px 10px;background:var(--bg3);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:12px;resize:none;height:32px}
.empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
.empty-state span{font-size:48px;display:block;margin-bottom:16px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '✅',
    'title' => 'Content Approval',
    'description' => 'Review and approve pending content versions',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--success-color), var(--cyan)'
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-head">
            <span class="card-title"><span>📋</span> Pending Versions</span>
        </div>
        
        <?php if (empty($pendingVersions)): ?>
            <div class="empty-state">
                <span>✨</span>
                <p>No pending versions to approve</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Content Type</th>
                        <th>Created</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingVersions as $version): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($version['id']) ?></td>
                            <td><?= htmlspecialchars($version['content_type']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($version['created_at'])) ?></td>
                            <td><?= htmlspecialchars($version['username'] ?? 'System') ?></td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td>
                                <form method="post" class="approval-form">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="version_id" value="<?= $version['id'] ?>">
                                    <textarea name="comment" placeholder="Comment..."></textarea>
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">✓ Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">✕ Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
