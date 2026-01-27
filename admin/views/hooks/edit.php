/**
 * Edit Hook View
 */
?><div class="container">
    <h1>Edit Hook: <?= htmlspecialchars($hook['name']) ?></h1>
    <form method="POST" action="/admin/hooks/update/<?= $hook['id'] ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Hook Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?= htmlspecialchars($hook['name']) ?>"
 required>
?>        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3">
                <?= htmlspecialchars($hook['description'])  ?>
            </textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Hook</button>
        <a href="/admin/hooks" class="btn btn-secondary">Cancel</a>
    </form>
</div>
