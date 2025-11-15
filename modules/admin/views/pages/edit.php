<?php require_once __DIR__ . '/../../includes/admin_header.php'; 

?><h1>Edit Page</h1>

<form method="POST" action="/admin/pages/update?id=<?= $page['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($page['title']) ?>"
 required class="form-control">
?>    </div>

    <div class="form-group">
        <label for="content">Content</label>
        <textarea id="content" name="content" rows="10" class="form-control"><?= htmlspecialchars($page['content']) ?></textarea>
    </div>

    <button type="submit" class="btn">Update Page</button>
</form>

<?php require_once __DIR__ . '/../../includes/admin_footer.php';
