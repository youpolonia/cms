<?php require_once __DIR__ . '/../../includes/admin_header.php'; 
?><h1>Manage Pages</h1>
<a href="/admin/pages/create" class="btn">Create New</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $page): ?>
        <tr>
            <td><?= htmlspecialchars($page['id']) ?></td>
            <td><?= htmlspecialchars($page['title']) ?></td>
            <td>
                <a href="/admin/pages/edit?id=<?= $page['id'] ?>" class="btn">Edit</a>
                <a href="/admin/pages/delete?id=<?= $page['id'] ?>" class="btn danger">Delete</a>
            </td>
        </tr>
        <?php endforeach;  ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../../includes/admin_footer.php';
