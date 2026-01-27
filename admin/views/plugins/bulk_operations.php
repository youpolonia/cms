<?php
/**
 * Plugin Bulk Operations View
 */
require_once __DIR__ . '/../../core/csrf.php';
?><div class="container">
    <h1>Bulk Plugin Operations</h1>
    
    <form id="bulkPluginForm" method="post" action="/admin/plugins/bulk-action">
        <?= csrf_field();  ?>
        <div class="mb-3 d-flex gap-2">
            <select name="bulk_action" class="form-select bulk-action-select">
                <option value="">Choose action...</option>
                <option value="activate">Activate Selected</option>
                <option value="deactivate">Deactivate Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button type="submit" class="btn btn-primary" id="bulkSubmitBtn" disabled>
                Apply to Selected
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40px"><input type="checkbox" id="selectAll"></th>
                            <th>Name</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plugins as $plugin): ?>
                        <tr>
                            <td><input type="checkbox" name="plugin_ids[]" value="<?= $plugin['id'] ?>"></td>
                            <td><?= htmlspecialchars($plugin['name']) ?></td>
                            <td><?= htmlspecialchars($plugin['version']) ?></td>
                            <td>
                                <span class="badge bg-<?= $plugin['active'] ? 'success' : 'secondary' ?>">
                                    <?= $plugin['active'] ? 'Active' : 'Inactive' 
?>                                </span>
                            </td>
                            <td>
                                <a href="/admin/plugins/<?= $plugin['id'] ?>" class="btn btn-sm btn-info">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach;  ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script src="/assets/js/script.js"></script>
