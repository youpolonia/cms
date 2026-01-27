<?php
// Verify admin access
require_once __DIR__ . '/../../security/admin-check.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get database instance
require_once __DIR__ . '/../../core/database.php';

// Get content types
$contentTypes = ContentTypesFactory::getTypesInstance($db)->getAll();

$title = "Content Types";
ob_start();

?><h1>Content Types</h1>

<div class="admin-toolbar">
    <a href="create.php" class="button">Add New Content Type</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Machine Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contentTypes as $type): ?>
        <tr>
            <td><?= htmlspecialchars($type['name']) ?></td>
            <td><?= htmlspecialchars($type['machine_name']) ?></td>
            <td><?= htmlspecialchars($type['description']) ?></td>
            <td>
                <a href="edit.php?id=<?= $type['id'] ?>" class="button">Edit</a>
                <a href="fields/add.php?id=<?= $type['id'] ?>" class="button">Fields</a>
                <a href="preview.php?id=<?= $type['id'] ?>" class="button">Preview</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../admin/layout.php';
