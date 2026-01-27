<?php
require_once __DIR__ . '/../../core/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/../../core/csrf.php';
    csrf_validate_or_403();
}

require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/commentmanager.php';

// Check admin auth
if (!Auth::isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        CommentManager::approveComment((int)$_POST['id']);
    } elseif (isset($_POST['delete'])) {
        CommentManager::deleteComment((int)$_POST['id']);
    }
    header('Location: comments.php');
    exit;
}

// Get filter and pagination params
$status = $_GET['status'] ?? 'pending';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$search = $_GET['search'] ?? '';

// Get comments count for pagination
$pdo = \core\Database::connection();
$where = ['status' => $status];
if ($search) {
    $where['content LIKE'] = "%$search%";
}
$totalComments = $db->count('comments', $where);

// Calculate pagination
$totalPages = ceil($totalComments / $perPage);
$offset = ($page - 1) * $perPage;

// Get comments for current page
$comments = $db->select('comments', $where, '*', 'created_at DESC', $perPage, $offset);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Moderation</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Comments Moderation</h1>
        
        <!-- Status Filter Tabs -->
        <div class="status-tabs">
            <a href="?status=pending" class="<?= $status === 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="?status=approved" class="<?= $status === 'approved' ? 'active' : '' ?>">Approved</a>
            <a href="?status=spam" class="<?= $status === 'spam' ? 'active' : '' ?>">Spam</a>
        </div>
        
        <!-- Search Form -->
        <form method="get" class="search-form">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <input type="text" name="search" placeholder="Search comments..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
        
        <!-- Comments Table -->
        <table class="comments-table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($comment['author_name']) ?></strong><br>
                        <a href="mailto:<?= htmlspecialchars($comment['author_email']) ?>">
                            <?= htmlspecialchars($comment['author_email']) 
?>                        </a>
                    </td>
                    <td><?= htmlspecialchars($comment['content']) ?></td>
                    <td><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></td>
                    <td class="actions">
                        <?php if ($status === 'pending'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                            <?= csrf_field(); ?>
                            <button type="submit" name="approve" class="btn-approve">Approve</button>
                        </form>
                        <?php endif; ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                            <?= csrf_field(); ?>
                            <button type="submit" name="delete" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?status=<?= urlencode($status) ?>&page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">&laquo; Previous</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?status=<?= urlencode($status) ?>&page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
