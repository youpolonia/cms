<?php require_once __DIR__.'/../../includes/adminheader.php'; 
?><div class="container">
    <h1><?= empty($contentType) ? 'Add New' : 'Edit' ?> Content Type</h1>
    
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form method="post" action="/admin/content/types.php?action=save">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <?php if (!empty($contentType['id'])): ?>
            <input type="hidden" name="id" value="<?= $contentType['id'] ?>">
        <?php endif;  ?>
        <div class="mb-3">
            <label for="name" class="form-label">Name*</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?= htmlspecialchars($contentType['name'] ?? '') ?>"
 required>
?>        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug*</label>
            <input type="text" class="form-control" id="slug" name="slug" 
                   value="<?= htmlspecialchars($contentType['slug'] ?? '') ?>"
 required>
            <small class="text-muted">Lowercase letters, numbers and hyphens only</small>
?>        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" 
                      rows="3"><?= htmlspecialchars($contentType['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="/admin/content/types.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
    }
});
?></script>

<?php require_once __DIR__.'/../../includes/adminfooter.php';
