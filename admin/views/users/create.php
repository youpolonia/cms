<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
?>
<?php require_once __DIR__ . '/../admin_header.php'; ?>
<div class="container">
    <h1>Create New User</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']);  ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']);  ?>
    <?php endif;  ?>
    <form method="POST" action="/admin/users/store" onsubmit="
return validateForm()">
        <?= csrf_field(); ?>
        <div class="form-group">
            <label for="username">Username (3-20 chars, letters, numbers, underscore)</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   pattern="[a-zA-Z0-9_]{3,20}"
 required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
 required>
        </div>
        
        <div class="form-group">
            <label for="password">Password (min 8 chars)</label>
            <input type="password" class="form-control" id="password" name="password"
                   minlength="8"
 required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                   minlength="8"
 required>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role">
                <option value="editor" <?= ($_POST['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
    
    <script>
    function validateForm() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const role = document.getElementById('role').value;
        
        if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
            alert('Username must be 3-20 characters (letters, numbers, underscore)');
            return false;
        }
        
        if (!/^[^@]+@[^@]+\.[^@]+$/.test(email)) {
            alert('Please enter a valid email address');
            return false;
        }
        
        if (password.length < 8) {
            alert('Password must be at least 8 characters');
            return false;
        }
        
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return false;
        }
        
        if (!['editor', 'admin'].includes(role)) {
            alert('Please select a valid role');
            return false;
        }
        
        return true;
    }
    </script>
</div>
<?php require_once __DIR__ . '/../admin_footer.php';
