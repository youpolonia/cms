<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/pluginmanager.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin permissions
if (!AccessChecker::hasPermission('plugins.manage')) {
    die('Access denied');
}

// Scan plugins directory
function scanPlugins() {
    $plugins = [];
    $pluginDirs = glob(__DIR__ . '/../plugins/*', GLOB_ONLYDIR);
    
    foreach ($pluginDirs as $dir) {
        $pluginFile = $dir . '/plugin.json';
        if (file_exists($pluginFile)) {
            $data = json_decode(file_get_contents($pluginFile), true);
            if ($data) {
                $plugins[basename($dir)] = $data;
            }
        }
    }
    return $plugins;
}

$message = $_GET['message'] ?? '';
$plugins = scanPlugins();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plugins Marketplace</title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>Plugins Marketplace</h1>
        </div>
    </div>

    <div class="container">
        <?php if ($message === 'install_success'): ?>
            <div class="alert alert-success">Plugin installed successfully</div>
        <?php elseif ($message === 'uninstall_success'): ?>
            <div class="alert alert-success">Plugin uninstalled successfully</div>
        <?php endif; ?>
        <div class="section">
            <h2>Available Plugins</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Version</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plugins as $slug => $plugin): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($plugin['name']) ?></strong></td>
                        <td><?= htmlspecialchars($plugin['version']) ?></td>
                        <td><?= htmlspecialchars($plugin['description']) ?></td>
                        <td><?= PluginManager::isPluginInstalled($slug) ? 'Installed' : 'Not installed' ?></td>
                        <td>
                            <?php if (PluginManager::isPluginInstalled($slug)): ?>
                                <form method="post" action="plugin-uninstall.php" style="display:inline">
                                    <?= csrf_field(); 
?>                                    <input type="hidden" name="plugin" value="<?= htmlspecialchars($slug) ?>">
                                    <button type="submit" class="button button-outline">Uninstall</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="plugin-install.php" style="display:inline">
                                    <?= csrf_field(); 
?>                                    <input type="hidden" name="plugin" value="<?= htmlspecialchars($slug) ?>">
                                    <button type="submit" class="button">Install</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
