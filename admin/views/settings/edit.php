<?php
/**
 * Settings Edit View
 */
require_once __DIR__ . '/../../includes/security/InputValidator.php';

// CSRF token generation
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// Get existing settings or initialize empty array
$settings = $settings ?? [];
$errors = $errors ?? [];

?><div class="settings-container">
    <h2>Edit Settings</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach;  ?>
            </ul>
        </div>
    <?php endif;  ?>
    <form method="post" action="/admin/settings/update">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="form-group">
            <label for="site_name">Site Name</label>
            <input type="text" class="form-control" id="site_name" name="site_name" 
                   value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
            <?php if (isset($errors['site_name'])): ?>                <small class="text-danger"><?= $errors['site_name'] ?></small>
            <?php endif;  ?>
        </div>

        <div class="form-group">
            <label for="admin_email">Admin Email</label>
            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                   value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>">
            <?php if (isset($errors['admin_email'])): ?>                <small class="text-danger"><?= $errors['admin_email'] ?></small>
            <?php endif;  ?>
        </div>

        <div class="form-group">
            <label for="items_per_page">Items Per Page</label>
            <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                   value="<?= htmlspecialchars($settings['items_per_page'] ?? '10') ?>" min="1" max="100">
            <?php if (isset($errors['items_per_page'])): ?>                <small class="text-danger"><?= $errors['items_per_page'] ?></small>
            <?php endif;  ?>
        </div>

        <div class="form-group">
            <label for="site_url">Site URL</label>
            <input type="url" class="form-control" id="site_url" name="site_url" 
                   value="<?= htmlspecialchars($settings['site_url'] ?? '') ?>">
            <?php if (isset($errors['site_url'])): ?>                <small class="text-danger"><?= $errors['site_url'] ?></small>
            <?php endif;  ?>
        </div>


        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
