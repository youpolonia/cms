<?php
/**
 * Gallery Manager - Index/List
 * Lists all galleries with search and pagination
 */

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/csrf.php';

cms_session_start('admin');
csrf_boot('admin');

// RBAC: Require galleries.read permission
if (!function_exists('cms_require_permission')) {
    function cms_require_permission(string $permission): void {
        if (!isset($_SESSION['user_permissions']) || !in_array($permission, $_SESSION['user_permissions'], true)) {
            http_response_code(403);
            exit('Permission denied: ' . htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'));
        }
    }
}
cms_require_permission('galleries.read');

$db = \core\Database::connection();

// Check if albums table exists
$tableCheck = $db->query("SHOW TABLES LIKE 'albums'");
$tableExists = $tableCheck && $tableCheck->rowCount() > 0;

$galleries = [];
$totalGalleries = 0;
$currentPage = 1;
$perPage = 20;
$search = '';
$message = '';
$messageType = '';

if ($tableExists) {
    // Handle search
    $search = $_GET['search'] ?? '';
    $currentPage = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($currentPage - 1) * $perPage;

    // Build query
    if ($search !== '') {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM albums WHERE title LIKE ? OR slug LIKE ?");
        $searchParam = '%' . $search . '%';
        $stmt->execute([$searchParam, $searchParam]);
    } else {
        $stmt = $db->query("SELECT COUNT(*) as total FROM albums");
    }

    $totalGalleries = $stmt ? (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'] : 0;
    $totalPages = (int)ceil($totalGalleries / $perPage);

    // Fetch galleries
    if ($search !== '') {
        $stmt = $db->prepare("SELECT * FROM albums WHERE title LIKE ? OR slug LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$searchParam, $searchParam, $perPage, $offset]);
    } else {
        $stmt = $db->prepare("SELECT * FROM albums ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$perPage, $offset]);
    }

    $galleries = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
}

// Handle success message
if (isset($_GET['success'])) {
    $message = match($_GET['success']) {
        'created' => 'Gallery created successfully',
        'updated' => 'Gallery updated successfully',
        'deleted' => 'Gallery deleted successfully',
        default => ''
    };
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #333; }
        .alert { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; }
        .search-form { display: flex; gap: 10px; flex: 1; max-width: 500px; }
        .search-form input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        tr:hover { background: #f8f9fa; }
        .pagination { display: flex; gap: 5px; justify-content: center; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination a:hover { background: #007bff; color: white; border-color: #007bff; }
        .pagination .current { background: #007bff; color: white; border-color: #007bff; }
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .schema-block { background: #f8f9fa; padding: 20px; border-radius: 4px; margin-top: 20px; }
        .schema-block pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .action-links { display: flex; gap: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gallery Manager</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!$tableExists): ?>
            <div class="alert alert-warning">
                <strong>Database table missing:</strong> The <code>albums</code> table does not exist.
            </div>
            <div class="schema-block">
                <h3>Create the albums table:</h3>
                <pre>CREATE TABLE IF NOT EXISTS albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    cover_image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</pre>
            </div>
        <?php else: ?>
            <div class="actions">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by title or slug..."
                           value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if ($search): ?>
                        <a href="index.php" class="btn btn-primary">Clear</a>
                    <?php endif; ?>
                </form>
                <a href="create.php" class="btn btn-success">Create Gallery</a>
            </div>

            <?php if (empty($galleries)): ?>
                <div class="empty-state">
                    <p>No galleries found. <?= $search ? 'Try a different search term.' : 'Create your first gallery to get started.' ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($galleries as $gallery): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$gallery['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($gallery['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($gallery['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="images.php?id=<?= htmlspecialchars((string)$gallery['id'], ENT_QUOTES, 'UTF-8') ?>"
                                           class="btn btn-primary btn-sm">Images</a>
                                        <a href="upload.php?id=<?= htmlspecialchars((string)$gallery['id'], ENT_QUOTES, 'UTF-8') ?>"
                                           class="btn btn-success btn-sm">Upload</a>
                                        <a href="edit.php?id=<?= htmlspecialchars((string)$gallery['id'], ENT_QUOTES, 'UTF-8') ?>"
                                           class="btn btn-primary btn-sm">Edit</a>
                                        <a href="delete.php?id=<?= htmlspecialchars((string)$gallery['id'], ENT_QUOTES, 'UTF-8') ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this gallery and all its images?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $currentPage): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="../dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
