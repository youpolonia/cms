<?php require_once __DIR__ . '/../includes/navigation.php'; 
?><div class="admin-container">
    <h1>Edit Role: <?php echo htmlspecialchars($role['name']); ?></h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif;  ?>
    <form method="post" action="?action=update&id=<?php echo $role['id']; ?>">
        <?php echo \Core\Security\CSRFToken::getInputField();  ?>
        <div class="form-group">
            <label for="name">Role Name*</label>
            <input type="text" class="form-control" id="name" name="name"
                   required 
                   value="<?php echo htmlspecialchars($role['name']); ?>"
                   maxlength="50" pattern="[a-zA-Z0-9_\- ]+" 
                   title="Only letters, numbers, spaces, hyphens and underscores">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" 
                      rows="3" maxlength="255"><?php echo htmlspecialchars($role['description']); ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Role</button>
            <a href="?action=index" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>