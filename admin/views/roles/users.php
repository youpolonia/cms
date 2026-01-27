<?php require_once '../includes/navigation.php';  ?>
?><div class="admin-container">
    <h1>Assign Users to Role: <?= htmlspecialchars($role['name']) ?></h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']);  ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']);  ?>
    <?php endif;  ?>
    <form method="post" action="?action=updateUsers&id=<?= $role['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="users-grid">
            <?php foreach ($users as $user): ?>
                <div class="user-item">
                    <label>
                        <input type="checkbox" name="users[]" value="<?= $user['id'] ?>"
                            <?= in_array($user['id'], $roleUsers) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($user['username'])  ?>                        <small class="text-muted">ID: <?= $user['id'] ?></small>
                    </label>
                </div>
            <?php endforeach;  ?>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save User Assignments</button>
            <a href="?action=index" class="btn btn-secondary">Back to Roles</a>
        </div>
    </form>
</div>

<style>
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.user-item {
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}
.user-item label {
    display: block;
    cursor: pointer;
}
.user-item small {
    display: block;
    margin-top: 0.25rem;
}
</style>
