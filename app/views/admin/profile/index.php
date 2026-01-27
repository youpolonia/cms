<?php
$title = 'My Profile';
ob_start();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; max-width: 900px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Profile Information</h2>
        </div>
        <div class="card-body">
            <form method="post" action="/admin/profile/update">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-input" id="username" name="username" value="<?= esc($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-input" id="email" name="email" value="<?= esc($user['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Member Since</label>
                    <input type="text" class="form-input" value="<?= esc($user['created_at']) ?>" disabled>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Security</h2>
        </div>
        <div class="card-body">
            <div style="text-align: center; padding: 1rem 0;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">&#128274;</div>
                <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
                    Keep your account secure by using a strong password.
                </p>
                <a href="/admin/profile/password" class="btn btn-secondary">Change Password</a>
            </div>
        </div>
    </div>
</div>

<div class="card" style="max-width: 900px; margin-top: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <h4 style="font-size: 0.875rem; margin: 0 0 0.5rem;">&#8505; Account Security Tips</h4>
        <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 0;">
            Use a unique password with at least 12 characters including numbers and symbols. Never share your password with anyone.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
