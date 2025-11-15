<?php
use admin\permissions\Access;
use admin\permissions\Roles;

// Get current user data from session or database
$user = [
    'name' => $_SESSION['user_name'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'role' => $_SESSION['user_role'] ?? 'viewer'
];

// Check permissions
$currentRole = $user['role'];
$canEditEmail = Access::userHasPermission($currentRole, 'edit_user_email');
$canEditRole = Access::userHasPermission($currentRole, 'edit_user_role');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="/admin/assets/css/users/profile.css">
</head>
<body>
    <div class="profile-edit-container">
        <h2>Edit Profile</h2>
        <form method="post" action="/admin/users/update_profile">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <!-- Personal Information Section -->
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($user['name']) ?>"
 required>
?>                </div>
                
                <?php if ($canEditEmail): ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($user['email']) ?>"
 required>
?>                </div>
                <?php endif;  ?>
            </div>

            <!-- Account Settings Section -->
            <?php if ($canEditRole): ?>
            <div class="form-section">
                <h3>Account Settings</h3>
                <div class="form-group">
                    <label for="role">User Role</label>
                    <select id="role" name="role">
                        <?php foreach (Roles::all() as $role): ?>                        <option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>>
                            <?= ucfirst($role)  ?>
                        </option>
                        <?php endforeach;  ?>
                    </select>
                </div>
            </div>
            <?php endif;  ?>
            <button type="submit" class="save-button">Save Changes</button>
        </form>
    </div>
</body>
</html>
