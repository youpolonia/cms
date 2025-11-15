<?php
require_once __DIR__ . '/../../core/bootstrap.php';
// Verify admin session
require_once __DIR__ . '/../admin-access.php';

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Database connection
require_once __DIR__ . '/../../includes/db_connect.php';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM menus");
$stmt->execute();
$total = $stmt->fetchColumn();

// Get paginated menus
$stmt = $pdo->prepare("SELECT * FROM menus ORDER BY id DESC LIMIT :offset, :per_page");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total pages
$total_pages = ceil($total / $per_page);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu Management</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Menu Management</h1>
        
        <div class="admin-actions">
            <a href="menus_create.php" class="btn">Create New Menu</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                <tr>
                    <td><?= htmlspecialchars($menu['id']) ?></td>
                    <td><?= htmlspecialchars($menu['name']) ?></td>
                    <td><?= htmlspecialchars($menu['slug']) ?></td>
                    <td>
                        <a href="menus_edit.php?id=<?= $menu['id'] ?>" class="btn">Edit</a>
                        <a href="menus_delete.php?id=<?= $menu['id'] ?>&csrf_token=<?= $csrf_token ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure?')">Delete
?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): 
?>                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
