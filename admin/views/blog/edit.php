<?php require_once __DIR__ . '/../../includes/header.php'; 
?><div class="container">
    <h1>Edit Blog Post</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="/blog/<?= $post->id ?>" method="POST">
        <input type="hidden" name="_method" value="PUT">
        
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($post->title) ?>"
 required>
?>        </div>
        
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="10"
 required><?= htmlspecialchars($post->content) ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
        
        <button type="submit" class="btn">Update Post</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php';
