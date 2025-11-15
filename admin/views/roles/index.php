<?php require_once __DIR__ . '/../includes/navigation.php'; 
?><div class="admin-container">
    <h1>User Roles Management</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif;  ?>    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif;  ?>
    <a href="?action=create" class="btn btn-primary mb-3">Create New Role</a>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role['id']); ?></td>
                        <td><?php echo htmlspecialchars($role['name']); ?></td>
                        <td><?php echo htmlspecialchars($role['description']); ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo $role['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="?action=permissions&id=<?php echo $role['id']; ?>" class="btn btn-sm btn-info">Permissions</a>
                            <a href="?action=users&id=<?php echo $role['id']; ?>" class="btn btn-sm btn-warning">Assign Users</a>
                            <form method="post" action="?action=destroy&id=<?php echo $role['id']; ?>" style="display:inline;">
                                <?php echo \Core\Security\CSRFToken::getInputField();  ?>
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;  ?>
            </tbody>
        </table>
    </div>
</div>