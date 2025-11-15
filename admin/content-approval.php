<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

require_once __DIR__ . '/../includes/models/versionmodel.php';
require_once __DIR__ . '/../includes/tenantvalidator.php';

// Start session first
cms_session_start('admin');

csrf_boot();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$tenantId = $_SESSION['tenant_id'];
$versionModel = new VersionModel();

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $versionId = $_POST['version_id'] ?? null;
    $action = $_POST['action'] ?? '';
    $comment = $_POST['comment'] ?? '';
    
    if ($versionId && in_array($action, ['approve', 'reject'])) {
        try {
            $version = $versionModel->getById($versionId, $tenantId);
            
            if ($action === 'approve') {
                $versionModel->restoreVersion(
                    $version['content_id'],
                    $version['content'],
                    $versionId,
                    $tenantId
                );
            }
            
            $versionModel->logRestoration(
                $versionId,
                $_SESSION['admin_id'],
                $tenantId,
                [
                    'reason' => $comment,
                    'status' => $action === 'approve' ? 'approved' : 'rejected'
                ]
            );
            
            $message = "Version $action successful";
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = 'Processing failed';
        }
    }
}

// Get pending versions
$pendingVersions = $versionModel->getFilteredVersions([
    'status' => 'pending',
    'tenant_id' => $tenantId,
    'sort' => 'created_at',
    'order' => 'desc'
]);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Version Approval</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Content Version Approval</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="version-list">
            <?php if (empty($pendingVersions)): ?>
                <p>No pending versions to approve</p>
            <?php else: ?>
                <table class="table">
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
                                <td><?= htmlspecialchars($version['id']) ?></td>
                                <td><?= htmlspecialchars($version['content_type']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($version['created_at'])) ?></td>
                                <td><?= htmlspecialchars($version['username'] ?? 'System') ?></td>
                                <td><span class="badge badge-warning">Pending</span></td>
                                <td>
                                    <form method="post" class="approval-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="version_id" value="<?= $version['id'] ?>">
                                        <div class="form-group">
                                            <textarea name="comment" placeholder="Comment (required)" required></textarea>
                                        </div>
                                        <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
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
