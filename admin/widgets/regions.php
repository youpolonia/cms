<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../../includes/init.php';

csrf_boot('admin');

// Check admin permissions
if (!Auth::hasPermission('manage_widgets')) {
    header('Location: /admin/login.php');
    exit;
}

$tenantId = Tenant::currentId();
$widgets = WidgetManager::getAvailableWidgets($tenantId);
$regions = ThemeManager::getAvailableRegions($tenantId);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    $action = $_POST['action'] ?? '';
    $widgetId = $_POST['widget_id'] ?? '';
    $regionId = $_POST['region_id'] ?? '';
    $settings = json_decode($_POST['settings'] ?? '{}', true) ?? [];
    
    try {
        switch ($action) {
            case 'create':
                WidgetManager::createBinding($tenantId, $widgetId, $regionId, $settings);
                break;
            case 'update':
                $bindingId = $_POST['binding_id'] ?? '';
                WidgetManager::updateBinding($tenantId, $bindingId, $settings);
                break;
            case 'delete':
                $bindingId = $_POST['binding_id'] ?? '';
                WidgetManager::deleteBinding($tenantId, $bindingId);
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current bindings
$bindings = WidgetManager::getRegionBindings($tenantId);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Region Bindings</title>
    <link rel="stylesheet" href="/admin/assets/css/widgets.css">
    <script src="/admin/assets/js/jsoneditor.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Widget Region Bindings</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="binding-form">
            <h2>Create New Binding</h2>
            <form method="post">
                <?= csrf_field() 
?>                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="widget_id">Widget:</label>
                    <select name="widget_id" id="widget_id"
 required>
                        <option value="">Select Widget</option>
                        <?php foreach ($widgets as $widget): ?>                            <option value="<?= htmlspecialchars($widget['id']) ?>">
                                <?= htmlspecialchars($widget['name']) 
?>                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="region_id">Region:</label>
                    <select name="region_id" id="region_id"
 required>
                        <option value="">Select Region</option>
                        <?php foreach ($regions as $region): ?>                            <option value="<?= htmlspecialchars($region) ?>">
                                <?= htmlspecialchars($region) 
?>                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="settings">Settings:</label>
                    <div id="settings_editor" style="height: 300px;"></div>
                    <input type="hidden" name="settings" id="settings">
                </div>
                
                <button type="submit" class="btn">Create Binding</button>
            </form>
        </div>
        
        <div class="bindings-list">
            <h2>Current Bindings</h2>
            <?php if (empty($bindings)): ?>
                <p>No widget-region bindings found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Widget</th>
                            <th>Region</th>
                            <th>Settings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bindings as $binding): ?>
                            <tr>
                                <td><?= htmlspecialchars($binding['widget_name']) ?></td>
                                <td><?= htmlspecialchars($binding['region']) ?></td>
                                <td>
                                    <pre><?= htmlspecialchars(json_encode($binding['settings'], JSON_PRETTY_PRINT)) ?></pre>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?= csrf_field() 
?>                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="binding_id" value="<?= $binding['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Initialize JSON editor
        const container = document.getElementById('settings_editor');
        const editor = new JSONEditor(container, {
            mode: 'code',
            modes: ['code', 'form', 'text', 'tree', 'view'],
            onError: function(err) {
                alert(err.toString());
            },
            onChange: function() {
                try {
                    document.getElementById('settings').value = JSON.stringify(editor.get());
                } catch (e) {
                    console.error(e);
                }
            }
        });
        
        // Set empty object as default
        editor.set({});
?>    </script>
</body>
</html>
