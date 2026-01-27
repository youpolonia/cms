<?php
// Standard admin bootstrap
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

// Check if current user has permission to manage users
if (!has_permission('manage_users')) {
    header('HTTP/1.1 403 Forbidden');
    exit('You do not have permission to access this page');
}

$title = "User Management";
ob_start();

// Define APP_URL if not already defined (for standalone testing or if not set globally yet)
if (!defined('APP_URL')) {
    // Attempt to determine APP_URL, adjust as necessary for your environment
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('APP_URL', rtrim($protocol . $host . $scriptName, '/'));
}

// Users array should be passed from UserController::index()
if (!isset($users) || !is_array($users)) {
    throw new \RuntimeException('Users data not provided by controller');
}


?><h1>User Management</h1>
<?php if (has_permission('create_users')): ?>
    <p><a href="<?php echo APP_URL; ?>/admin/users/create" class="btn btn-primary">Add New User</a></p>
<?php endif; ?><?php if (!empty($users)): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars((string)$user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td>
                <?php if (has_permission('edit_users')): ?>
                    <a href="<?php echo APP_URL; ?>/admin/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                <?php endif; ?>
                <form action="<?php echo APP_URL; ?>/admin/users/delete/<?php echo $user['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <?php csrf_field('admin'); ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?><?php
$content = ob_get_clean();
// Ensure the layout path is correct. Adjust if your admin views are in a different subdirectory.
$layoutPath = __DIR__ . '/../views/layout.php';
if (file_exists($layoutPath)) {
    require_once $layoutPath;
} else {
    // Fallback or error if layout is not found
    echo "Layout file not found at: " . htmlspecialchars($layoutPath);
    echo $content; // Output content directly if layout is missing
}
