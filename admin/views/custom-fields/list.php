<?php
require_once __DIR__ . '/../../includes/layout.php';
ob_start();

?><div class="admin-container">
    <h1>Custom Fields</h1>
    
    <a href="/admin/custom-fields/create" class="btn btn-primary">Create New Field</a>
    <a href="/admin/custom-fields/assign" class="btn btn-secondary">Assign to Content Types</a>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Label</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fields as $field): ?>
                <tr>
                    <td><?= htmlspecialchars($field['id']) ?></td>
                    <td><?= htmlspecialchars($field['name']) ?></td>
                    <td><?= htmlspecialchars($field['label']) ?></td>
                    <td><?= htmlspecialchars($field['type']) ?></td>
                    <td>
                        <a href="/admin/custom-fields/edit/<?= $field['id'] ?>" class="btn btn-sm">Edit</a>
                        <form method="POST" action="/admin/custom-fields/delete/<?= $field['id'] ?>" class="inline-form">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" data-confirm="Are you sure?">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
render_layout($content);
