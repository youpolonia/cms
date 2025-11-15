<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware/checkpermission.php';
require_once __DIR__ . '/pluginupdatechecker.php';
require_once __DIR__ . '/../../core/csrf.php';

// Check admin permissions
$permissionMiddleware = new CheckPermission('manage_plugins');
$permissionMiddleware->handle();

// Get plugin updates
$updates = [];
try {
    $checker = new PluginUpdateChecker();
    $plugins = get_installed_plugins(); // Would be implemented elsewhere
    foreach ($plugins as $plugin) {
        $updateAvailable = $checker->checkForUpdates(
            $plugin['name'],
            $plugin['version'],
            $checker->getLatestVersion($plugin['name'])
        );
        if ($updateAvailable) {
            $updates[] = $plugin;
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle update action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_plugin'])) {
    csrf_validate_or_403();
    $pluginName = filter_input(INPUT_POST, 'plugin_name', FILTER_SANITIZE_STRING);
    if ($pluginName) {
        try {
            // Would implement actual update logic here
            $success = "Plugin $pluginName updated successfully";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Plugin Updates</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Plugin Updates</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>        <?php if (empty($updates)): ?>
            <p>All plugins are up to date.</p>
        <?php else: ?>
            <table class="plugin-table">
                <thead>
                    <tr>
                        <th>Plugin</th>
                        <th>Current Version</th>
                        <th>Latest Version</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($updates as $plugin): ?>
                        <tr>
                            <td><?= htmlspecialchars($plugin['name']) ?></td>
                            <td><?= htmlspecialchars($plugin['version']) ?></td>
                            <td><?= htmlspecialchars($checker->getLatestVersion($plugin['name'])) ?></td>
                            <td>
                                <form method="post">
                                    <?= csrf_field(); 
?>                                    <input type="hidden" name="plugin_name" value="<?= htmlspecialchars($plugin['name']) ?>">
                                    <button type="submit" name="update_plugin" class="btn btn-primary">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
