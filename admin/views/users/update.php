require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../includes/validation_helpers.php';

?><div class="container">
    <h1>Update User</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']);  ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']);  ?>
    <?php endif;  ?>
    <form method="POST" action="/admin/users/<?= $user['id'] ?>/update">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label for="username">Username (3-20 chars, letters, numbers, underscore)</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                   pattern="[a-zA-Z0-9_]{3,20}"
 required>
?>        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
 required>
?>        </div>
        
        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password"
                   minlength="8">
        </div>
        
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" id="role" name="role">
                    <option value="user" <?= ($user['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="editor" <?= ($user['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                    <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
        <?php endif;  ?>
        <button type="submit" class="btn btn-primary">Update User</button>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php';
