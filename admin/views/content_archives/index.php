<?php require_once __DIR__ . '/../includes/admin_header.php'; ?>
<div class="container">
    <h1>Archived Content</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Content ID</th>
                <th>Reason</th>
                <th>Archived At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($archives as $archive): ?>
            <tr>
                <td><?= htmlspecialchars($archive['id']) ?></td>
                <td><?= htmlspecialchars($archive['content_id']) ?></td>
                <td><?= htmlspecialchars($archive['reason']) ?></td>
                <td><?= htmlspecialchars($archive['archived_at']) ?></td>
                <td>
                    <form method="POST" action="?action=restore&id=<?= $archive['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn btn-primary">Restore</button>
                    </form>
                </td>
            </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php';
