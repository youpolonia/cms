<?php
// Verify admin access
if (!check_admin_access('notification_rules')) {
    admin_redirect('dashboard.php', 'Access denied');
}

// CSRF protection
$csrf_token = generate_csrf_token('notification_rules_list');

// Get filtered rules
$rules = NotificationRules::getFilteredRules($_GET);

?><div class="admin-container">
    <h1>Notification Rules</h1>
    
    <div class="filter-bar">
        <form method="get" class="filter-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="filter-group">
                <label>Status:</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Type:</label>
                <select name="type">
                    <option value="">All</option>
                    <?php foreach (NotificationRules::getTypes() as $type): ?>                        <option value="<?= $type ?>"><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Filter</button>
        </form>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rules as $rule): ?>
                <tr>
                    <td><?= $rule['id'] ?></td>
                    <td><?= htmlspecialchars($rule['name']) ?></td>
                    <td><?= $rule['type'] ?></td>
                    <td><span class="status-badge status-<?= $rule['status'] ?>">
                        <?= ucfirst($rule['status']) 
?>                    </span></td>
                    <td>
                        <a href="rule_edit.php?id=<?= $rule['id'] ?>" class="btn-edit">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="table-footer">
        <a href="rule_edit.php" class="btn-add">Add New Rule</a>
    </div>
</div>
