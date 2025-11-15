<?php require_once __DIR__.'/../../../includes/views/templates/admin/header.php'; ?>
<div class="page-builder-container">
    <div class="page-builder-header">
        <h1>Page Builder</h1>
        <a href="/admin/page-builder/create" class="btn btn-primary">Create New Page</a>
    </div>

    <div class="page-list">
        <?php if (!empty($pages)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Last Modified</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><?= htmlspecialchars($page['title']) ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($page['updated_at'])) ?></td>
                        <td><?= ucfirst($page['status']) ?></td>
                        <td>
                            <a href="/admin/page-builder/<?= $page['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                            <a href="/admin/page-builder/<?= $page['id'] ?>/versions" class="btn btn-sm btn-secondary">Versions</a>
                            <form action="/admin/page-builder/<?= $page['id'] ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No pages found. Create your first page to get started.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__.'/../../../includes/views/templates/admin/footer.php';
