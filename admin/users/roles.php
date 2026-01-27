<?php
require_once __DIR__ . '/../../core/rolemanager.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin permissions
$roleManager = RoleManager::getInstance();
if (!$roleManager->hasPermission($_SESSION['user_id'] ?? 0, 'manage_roles')) {
    Session::flash('error', 'Permission denied');
    header("Location: ../dashboard.php");
    exit;
}

// Handle flash messages
if (Session::has('success')) {
    echo '
<div class="alert success">' . Session::get('success') . '</div>';
    Session::remove('success');
}
if (Session::has('error')) {
    echo '
<div class="alert error">' . Session::get('error') . '</div>';
    Session::remove('error');
}

// Get all users
try {
    $pdo = \core\Database::connection();
    $users = $pdo->query("SELECT id, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    Session::flash('error', 'Database error, please try again later.');
    $users = [];
}

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    csrf_validate_or_403();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        Session::flash('error', 'Invalid CSRF token');
    } else {
        $roleManager = RoleManager::getInstance();
        if ($roleManager->assignRole($_POST['user_id'], $_POST['role'])) {
            Session::flash('success', 'Role updated successfully');
        } else {
            Session::flash('error', 'Failed to update role');
        }
    }
    header("Location: roles.php");
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title>User Roles Management</title>
    <link rel="stylesheet" href="../assets/css/users/roles.css">
</head>
<body>
    <div class="container">
        <h1>User Roles Management</h1>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Current Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?><?php
                    $roleManager = RoleManager::getInstance();
                    $currentRole = $roleManager->getUserRole($user['id']);
?>                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= $currentRole ?: 'none' ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role">
                                <option value="">-- Select Role --</option>
                                <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="editor" <?= $currentRole === 'editor' ? 'selected' : '' ?>>Editor</option>
                                <option value="viewer" <?= $currentRole === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                            </select>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] = bin2hex(random_bytes(32)) ?>">
                            <button type="submit">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
