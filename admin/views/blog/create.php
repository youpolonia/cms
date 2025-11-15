<?php require_once __DIR__ . '/../../includes/header.php'; 
?><div class="container">
    <h1>Create New Blog Post</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="/blog" method="POST">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title"
 required>
?>        </div>
        
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="10"
 required></textarea>
?>        </div>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft">Draft</option>
                <option value="published" selected>Published</option>
            </select>
        </div>
        
        <button type="submit" class="btn">Create Post</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php';
