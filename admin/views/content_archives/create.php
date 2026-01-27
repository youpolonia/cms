<?php require_once __DIR__ . '/../includes/admin_header.php'; ?>
<div class="container">
    <h1>Archive Content</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form method="POST" action="?action=store">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="form-group">
            <label for="content_id">Content ID</label>
            <input type="number" class="form-control" id="content_id" name="content_id" required>
        </div>
        
        <div class="form-group">
            <label for="reason">Reason for Archival</label>
            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Archive Content</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php';
