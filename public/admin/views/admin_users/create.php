/**
 * Create User Form
 */
?><div class="admin-form">
    <h2>Create New User</h2>
    
    <form method="POST" action="/admin/users/store">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>"
 required>
            <?php if (!empty($errors['username'])): ?>
                <span class="error"><?= htmlspecialchars($errors['username']) ?></span>
            <?php endif;  ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
 required>
            <?php if (!empty($errors['email'])): ?>
                <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif;  ?>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
 required>
            <?php if (!empty($errors['password'])): ?>
                <span class="error"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif;  ?>
        </div>
        
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
 required>
?>        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role"
 required>
                <option value="">Select Role</option>
                <?php foreach ($roles as $role): ?>                    <option value="<?= $role['id'] ?>" <?= ($role['id'] == ($old['role'] ?? '')) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($role['name'])  ?>
                    </option>
                <?php endforeach;  ?>
            </select>
            <?php if (!empty($errors['role'])): ?>
                <span class="error"><?= htmlspecialchars($errors['role']) ?></span>
            <?php endif;  ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">Create User</button>
            <a href="/admin/users" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
