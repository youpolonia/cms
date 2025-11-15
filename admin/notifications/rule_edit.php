<?php
// Verify admin access
if (!check_admin_access('notification_rules_edit')) {
    admin_redirect('dashboard.php', 'Access denied');
}

// CSRF protection
$csrf_token = generate_csrf_token('notification_rule_edit');

// Check if editing existing rule
$rule = [];
if (!empty($_GET['id'])) {
    $rule = NotificationRules::getById($_GET['id']);
    if (!$rule) {
        admin_redirect('rules_listing.php', 'Rule not found');
    }
}

?><div class="admin-container">
    <h1><?= empty($rule) ? 'Create New' : 'Edit' ?> Notification Rule</h1>
    <form method="post" action="rule_save.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <?php if (!empty($rule['id'])): ?>
            <input type="hidden" name="id" value="<?= $rule['id'] ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="name">Rule Name</label>
            <input type="text" id="name" name="name" required
                   value="<?= !empty($rule['name']) ? htmlspecialchars($rule['name']) : '' ?>">        </div>
        
        <div class="form-group">
            <label for="type">Rule Type</label>
            <select id="type" name="type" required>
                <option value="">Select Type</option>
                <?php foreach (NotificationRules::getTypes() as $type): ?>
                    <option value="<?= $type ?>"
                        <?= (!empty($rule['type']) && $rule['type'] === $type) ? 'selected' : '' ?>>
                        <?= $type ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="active" <?= (!empty($rule['status']) && $rule['status'] === 'active') ? 'selected' : '' ?>>
                    Active
                </option>
                <option value="inactive" <?= (!empty($rule['status']) && $rule['status'] === 'inactive') ? 'selected' : '' ?>>
                    Inactive
                </option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="conditions">Conditions (JSON)</label>
            <textarea id="conditions" name="conditions" rows="6" required><?= !empty($rule['conditions']) ? htmlspecialchars($rule['conditions']) : '{}' ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="actions">Actions (JSON)</label>
            <textarea id="actions" name="actions" rows="6" required><?= !empty($rule['actions']) ? htmlspecialchars($rule['actions']) : '{}' ?></textarea>
        </div>
        
        <div class="form-actions">
            <a href="rules_listing.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-save">Save Rule</button>
        </div>
    </form>
</div>
