<?php
// Verify admin session and permissions
require_once __DIR__.'/../core/sessionmanager.php';
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/csrf.php';

SessionManager::verifyAdminSession();
if (!AccessChecker::hasPermission('admin_rbac')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

csrf_boot('admin');

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// Load RBAC data
$roles = require_once __DIR__.'/../config/roles.php';
$permissions = require_once __DIR__.'/../config/permissions.php';
$users = require_once __DIR__.'/../config/users.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Process role/permission assignments
    if (isset($_POST['assign_permission'])) {
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
        $permission = filter_input(INPUT_POST, 'permission', FILTER_SANITIZE_STRING);
        
        if ($role && $permission && isset($roles[$role])) {
            $roles[$role]['permissions'][] = $permission;
            file_put_contents(__DIR__.'/../config/roles.php', '<?php return '.var_export($roles, true).';');
        }
    }
    // Log the action
    file_put_contents(__DIR__.'/../logs/rbac-changes.log',
        date('Y-m-d H:i:s')." - ".SessionManager::getUserId()." modified RBAC\n",
        FILE_APPEND);
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>RBAC Administration</title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>Role-Based Access Control</h1>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2>Roles and Permissions</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $roleName => $roleData): ?>
                    <tr>
                        <td><?= htmlspecialchars($roleName) ?></td>
                        <td>
                            <?php if (!empty($roleData['permissions'])): ?>
                                <?= implode(', ', array_map('htmlspecialchars', $roleData['permissions'])) ?>
                            <?php else: ?>                                No permissions
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="role" value="<?= htmlspecialchars($roleName) ?>">
                                <select name="permission" class="form-control">
                                    <?php foreach ($permissions as $perm): ?>                                        <option value="<?= htmlspecialchars($perm) ?>"><?= htmlspecialchars($perm) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="assign_permission" class="button">Assign</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Users by Role</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $username => $userData): ?>
                    <tr>
                        <td><?= htmlspecialchars($username) ?></td>
                        <td><?= htmlspecialchars($userData['role'] ?? 'none') ?></td>
                        <td><?= htmlspecialchars($userData['last_login'] ?? 'never') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
