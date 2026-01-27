<?php require_once __DIR__ . '/../includes/navigation.php'; 
?><div class="admin-container">
    <h1>Manage Permissions for Role: <?php echo htmlspecialchars($role['name']); ?></h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif;  ?>
    <form method="post" action="?action=updatePermissions&id=<?php echo $role['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="permissions-grid">
            <?php foreach ($permissions as $permission): ?>
                <div class="permission-item">
                    <label>
                        <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>"
                            <?php echo in_array($permission['id'], $rolePermissions) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($permission['name']);  ?>
                        <small class="text-muted"><?php echo htmlspecialchars($permission['description']); ?></small>
                    </label>
                </div>
            <?php endforeach;  ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Permissions</button>
            <a href="?action=index" class="btn btn-secondary">Back to Roles</a>
        </div>
    </form>
</div>

<style>
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.permission-item {
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}
.permission-item label {
    display: block;
    cursor: pointer;
}
.permission-item small {
    display: block;
    margin-top: 0.25rem;
}
</style>