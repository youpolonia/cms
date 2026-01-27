<?php
$title = 'Change Password';
ob_start();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width: 500px;">
    <div class="card-header">
        <h2 class="card-title">Change Password</h2>
    </div>
    <div class="card-body">
        <form method="post" action="/admin/profile/password/update">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" class="form-input" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" class="form-input" id="new_password" name="new_password" required minlength="8">
                <p class="form-hint">Minimum 8 characters</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-input" id="confirm_password" name="confirm_password" required>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="/admin/profile" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
