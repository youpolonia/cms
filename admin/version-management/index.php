<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware/checkpermission.php';
require_once __DIR__ . '/../../core/csrf.php';

// Check admin permissions
$permissionMiddleware = new CheckPermission('manage_versions');
$permissionMiddleware->handle();

// Get content ID from query
$contentId = filter_input(INPUT_GET, 'content_id', FILTER_VALIDATE_INT);
if (!$contentId) {
    die('Invalid content ID');
}

// Get versions from API
$versions = [];
try {
    $apiUrl = get_api_url("/api/content-versions?content_id=$contentId");
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);
    $versions = $data['versions'] ?? [];
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle restore action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_version'])) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    $versionId = filter_input(INPUT_POST, 'version_id', FILTER_VALIDATE_INT);
    
    if ($versionId) {
        try {
            $apiUrl = get_api_url("/api/content-versions/$versionId/restore");
            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($apiUrl, false, $context);
            $result = json_decode($response, true);
            
            if ($result['success']) {
                $success = 'Version restored successfully';
            } else {
                $error = $result['message'] ?? 'Restore failed';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle purge action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purge_versions'])) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    try {
        $apiUrl = get_api_url('/api/version-cleanup');
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                            "X-API-KEY: " . get_config('versions.cleanup_api_key') . "\r\n",
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($apiUrl, false, $context);
        $result = json_decode($response, true);
        
        if ($result['success']) {
            $success = "Purged {$result['total_deleted']} versions";
        } else {
            $error = $result['message'] ?? 'Purge failed';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render admin interface
?><!DOCTYPE html>
<html>
<head>
    <title>Version Management</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Version Management</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="version-actions">
            <form method="post">
                <?= csrf_field(); 
?>                <button type="submit" name="purge_versions" class="btn btn-danger">
                    Purge Old Versions
                </button>
            </form>
        </div>
        
        <table class="version-table">
            <thead>
                <tr>
                    <th>Version ID</th>
                    <th>Created</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Restored By</th>
                    <th>Restored At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versions as $version): ?>
                    <tr>
                        <td><?= htmlspecialchars($version['id']) ?></td>
                        <td><?= htmlspecialchars($version['created_at']) ?></td>
                        <td><?= htmlspecialchars($version['author_name'] ?? 'System') ?></td>
                        <td><?= $version['is_autosave'] ? 'Autosave' : 'Manual' ?></td>
                        <td><?= htmlspecialchars($version['restored_by_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($version['restored_at'] ?? '') ?></td>
                        <td>
                            <form method="post">
                                <?= csrf_field(); 
?>                                <input type="hidden" name="version_id" value="<?= $version['id'] ?>">
                                <button type="submit" name="restore_version" class="btn btn-primary">
                                    Restore
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
