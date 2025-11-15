<?php
require_once __DIR__ . '/../../services/ContentRepository.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../core/csrf.php';

$title = "Content Management";
ob_start();

// Initialize repository
$repo = new ContentRepository(\core\Database::connection());

// Handle pagination
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get content items
$contents = $repo->findAll($limit, $offset);

?><h1>Content Management</h1>
<p><a href="/admin/content/create">Add New Content</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contents as $content): ?>
        <tr>
            <td><?= substr($content['id'], 0, 8) ?></td>
            <td><?= htmlspecialchars($content['title']) ?></td>
            <td><?= htmlspecialchars($content['state_name']) ?></td>
            <td><?= date('Y-m-d', strtotime($content['created_at'])) ?></td>
            <td>
                <a href="/admin/content/edit/<?= $content['id'] ?>">Edit</a> |
                <form action="/admin/content/delete/<?= $content['id'] ?>" method="POST" style="display:inline;">
                    <?= csrf_field(); 
?>                    <button type="submit" onclick="return confirm('Are you sure you want to delete this content item?');" class="link-button">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>">Previous</a>
<?php endif; ?>
<?php if (count($contents) === $limit): ?>
    <a href="?page=<?= $page + 1 ?>">Next</a>
<?php endif; ?>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
