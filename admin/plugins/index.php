<?php
// Admin Plugins Dashboard
require_once __DIR__ . '/../../includes/plugins/pluginmanager.php';

$pluginManager = new PluginManager();

// Basic admin header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Plugins Manager</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <script src="/admin/assets/js/plugins.js"></script>
</head>
<body>
    <div class="admin-container">
        <h1>Plugins Manager</h1>

        <?php
        // Plugin listing section
        ?>
    <div class="section">
    <h2>Installed Plugins</h2>
    <table class="plugin-table">
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

        <?php
        // Plugin listing
        if (isset($pluginManager)) {
            foreach ($pluginManager->getPlugins() as $pluginName => $plugin) {
                $isEnabled = $pluginManager->isPluginEnabled($pluginName);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($plugin['name']); ?></td>
                    <td><?php echo htmlspecialchars($plugin['version']); ?></td>
                    <td><?php echo htmlspecialchars($plugin['description'] ?? ''); ?></td>
                    <td><?php echo $isEnabled ? 'Enabled' : 'Disabled'; ?></td>
                    <td>
                        <button class="btn toggle-plugin"
                                data-plugin="<?php echo htmlspecialchars($pluginName); ?>"
                                data-action="<?php echo $isEnabled ? 'disable' : 'enable'; ?>">
                            <?php echo $isEnabled ? 'Disable' : 'Enable'; ?>
                        </button>
                        <a href="#" class="btn">Configure</a>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
</div>

</div>
</body>
</html>';
