<?php
require_once __DIR__ . '/../../includes/sanitizer.php';

// Validate user data
if (!isset($user) || !is_array($user) || empty($user['id'])) {
    echo "
<p class='error'>Error: User data not found or invalid.</p>";
    exit;
}

// admin/users/edit.php
// Start output buffering to capture content for the layout
ob_start();

// Define APP_URL if not already defined (for standalone testing or if not set globally yet)
if (!defined('APP_URL')) {
    // Attempt to determine APP_URL, adjust as necessary for your environment
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Assuming the CMS is in the root or a subdirectory. If in a subdir, adjust SCRIPT_NAME.
    // This basic detection might need refinement for complex setups.
    $scriptName = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('APP_URL', rtrim($protocol . $host . $scriptName, '/'));
}

// Sample tenant data - in production this would come from a database
$allTenants = [
    ['id' => 1, 'name' => 'Tenant A'],
    ['id' => 2, 'name' => 'Tenant B'],
    ['id' => 3, 'name' => 'Tenant C']
];

// Sample permissions - in production this would come from a database
$allPermissions = [
    'content_manage' => 'Manage Content',
    'user_manage' => 'Manage Users',
    'settings_edit' => 'Edit Settings'
];

// Check if this is a login/session refresh that requires permission revalidation
$isPermissionRevalidation = isset($_GET['revalidate_permissions']);


?><h1>Edit User: <?php echo Sanitizer::text($user['username']); ?></h1>
<form action="<?php echo APP_URL; ?>/admin/users/update/<?php echo $user['id']; ?>" method="POST">
    <?php csrf_field('admin'); ?>
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" class="form-control" value="<?php echo Sanitizer::text($user['username']); ?>"
 required>
?>    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo Sanitizer::text($user['email']); ?>"
 required>
?>    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" class="form-control">
        <small class="form-text text-muted">Leave blank to keep current password.</small>
    </div>

    <div class="form-group">
        <label for="role">Role:</label>
        <select id="role" name="role" class="form-control"
 required>
            <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
            <option value="editor" <?php echo ($user['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
    </div>

    <div class="form-group">
        <label>Tenants:</label>
        <div class="tenant-checkboxes">
            <?php foreach ($allTenants as $tenant): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                        id="tenant_<?= $tenant['id'] ?>"
                        name="tenants[]"
                        value="<?= $tenant['id'] ?>"
                        <?= in_array($tenant['id'], $user['tenants']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="tenant_<?= $tenant['id'] ?>">
                        <?= Sanitizer::text($tenant['name']) 
?>                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label>Permissions:</label>
        <div class="permission-checkboxes">
            <?php foreach ($allPermissions as $key => $label): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                        id="perm_<?= $key ?>"
                        name="permissions[]"
                        value="<?= $key ?>"
                        <?= in_array($key, $user['permissions']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="perm_<?= $key ?>">
                        <?= Sanitizer::text($label) 
?>                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($isPermissionRevalidation): ?>
        <input type="hidden" name="revalidate_permissions" value="1">
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Update User</button>
    <a href="<?php echo APP_URL; ?>/admin/users" class="btn btn-secondary">Cancel</a>
</form>

<?php if ($isPermissionRevalidation): ?>
<script>
    // Show alert about permission revalidation
    alert('Your permissions have been updated. Some changes may require you to log in again.');
?></script>
<?php endif; ?><?php
// Get the buffered content
$pageContent = ob_get_clean();

// This part assumes your controller will handle including the layout.
// For example, the controller might look like:
//
// require_once __DIR__ . '/../../includes/core/view.php';
// $user = ['id' => 1, 'username' => 'testuser', 'email' => 'test@example.com', 'role' => 'admin']; // Fetched from DB
// View::render('admin/users/edit', ['user' => $user]);
//
// And View::render would then require_once 'admin/views/layout.php' and pass $pageContent to it.

// If you need to require_once the layout directly from here (less common for MVC):
// $content = $pageContent; // The layout.php might expect a $content variable
// require_once __DIR__ . '/../views/layout.php';

// For now, this file will just output its content if directly accessed,
// or make $pageContent available if included by a controller that handles the layout.
echo $pageContent;
