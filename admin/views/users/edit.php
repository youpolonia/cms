<?php require_once __DIR__ . '/../admin_header.php'; ?>
<div class="container">
    <h1>Edit User</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']);  ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']);  ?>
    <?php endif;  ?>
    <form method="POST" action="/admin/users/update" onsubmit="
return validateForm()">
        <?= csrf_field(); ?>
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <div class="form-group">
            <label for="username">Username (3-20 chars, letters, numbers, underscore)</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                   pattern="[a-zA-Z0-9_]{3,20}"
 required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
 required>
        </div>
        
        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password"
                   minlength="8">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                   minlength="8">
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role">
                <option value="<?= Roles::ADMIN ?>" <?= ($user['role'] ?? '') === Roles::ADMIN ? 'selected' : '' ?>>Admin</option>
                <option value="<?= Roles::EDITOR ?>" <?= ($user['role'] ?? '') === Roles::EDITOR ? 'selected' : '' ?>>Editor</option>
                <option value="<?= Roles::AUTHOR ?>" <?= ($user['role'] ?? '') === Roles::AUTHOR ? 'selected' : '' ?>>Author</option>
                <option value="<?= Roles::VIEWER ?>" <?= ($user['role'] ?? '') === Roles::VIEWER ? 'selected' : '' ?>>Viewer</option>
                <option value="<?= Roles::SENIOR_EDITOR ?>" <?= ($user['role'] ?? '') === Roles::SENIOR_EDITOR ? 'selected' : '' ?>>Senior Editor</option>
                <option value="<?= Roles::WORKER ?>" <?= ($user['role'] ?? '') === Roles::WORKER ? 'selected' : '' ?>>Worker</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Update User</button>
    </form>
    
    <script>
    function validateForm() {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (!validateUsername(username.value)) {
            alert('Username must be 3-20 chars (letters, numbers, underscore)');
            return false;
        }
        
        if (!validateEmail(email.value)) {
            alert('Please enter a valid email address');
            return false;
        }
        
        if (password.value && !validatePassword(password.value)) {
            alert('Password must be at least 8 characters');
            return false;
        }
        
        if (password.value && password.value !== confirmPassword.value) {
            alert('Passwords do not match');
            return false;
        }
        
        return true;
    }
    </script>
</div>
<?php require_once __DIR__ . '/../admin_footer.php';
