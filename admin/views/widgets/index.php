<?php
/**
 * Widget Management Dashboard
 */
require_once __DIR__ . '/../../includes/admin_auth.php';

$widgets = WidgetSettingsController::getAllWidgets();

?><div class="widget-management">
    <h1>Widget Management</h1>
    
    <div class="widget-list">
        <?php foreach ($widgets as $widget): ?>
            <div class="widget-item">
                <h3><?= htmlspecialchars($widget['name']) ?></h3>
                <p>Type: <?= htmlspecialchars($widget['type']) ?></p>
                <div class="widget-actions">
                    <a href="/admin/widgets/edit?id=<?= $widget['id'] ?>" class="btn btn-edit">Edit</a>
                    <a href="/admin/widgets/delete?id=<?= $widget['id'] ?>" class="btn btn-delete">Delete</a>
                </div>
            </div>
        <?php endforeach;  ?>
    </div>
    
    <a href="/admin/widgets/create" class="btn btn-primary">Create New Widget</a>
</div>
