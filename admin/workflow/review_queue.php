<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/security.php';
verifyAdminAccess();

require_once __DIR__ . '/../../core/permissionmanager.php';
require_once __DIR__ . '/../../core/workflowmanager.php';
require_once __DIR__.'/../../core/database.php';
// session boot (admin)
require_once __DIR__ . '/../../core/session_boot.php';

// Check admin login and permissions
cms_session_start('admin');
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Check if user has content review permissions
if (!PermissionManager::checkPermission($_SESSION['user_id'], 'content.edit')) {
    die('Access denied - You do not have permission to review content');
}

$workflowManager = new WorkflowManager();

// Check permissions
$canReview = isset($_SESSION['user_role']) &&
             ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['content_id'])) {
    csrf_validate_or_403();
    // Verify CSRF token
    if (!$canReview) {
        die('Insufficient permissions');
    }
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
    
    $contentId = (int)$_POST['content_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'approve') {
            $workflowManager->approveContent($contentId, $_SESSION['user_id']);
            $_SESSION['flash_message'] = 'Content approved successfully';
        } elseif ($action === 'reject') {
            $workflowManager->rejectContent($contentId, $_SESSION['user_id']);
            $_SESSION['flash_message'] = 'Content rejected';
        }
        
        // Redirect to prevent form resubmission
        header('Location: review_queue.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error processing request: ' . $e->getMessage();
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get filter parameters
$contentType = $_GET['content_type'] ?? null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build base query
$query = "
    SELECT c.id, c.title, c.author_id, u.username as author_name,
           c.created_at, c.content_type
    FROM content c
    LEFT JOIN users u ON c.author_id = u.id
    WHERE c.workflow_state = 'pending_review'
";

$params = [];
$where = [];

// Add filters if provided
if ($contentType) {
    $where[] = "c.content_type = ?";
    $params[] = $contentType;
}

if ($startDate) {
    $where[] = "c.created_at >= ?";
    $params[] = $startDate;
}

if (!empty($filters['date_from'])) {
    $where[] = "c.created_at >= ?";
    $params[] = $filters['date_from'];
}

if (!empty($filters['date_to'])) {
    $where[] = "c.created_at <= ?";
    $params[] = $filters['date_to'];
}

if (!empty($where)) {
    $query .= " AND " . implode(" AND ", $where);
}

$query .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

// Get pending review items
$db = \core\Database::connection();
$stmt = $db->prepare($query);
$stmt->execute($params);
$pendingItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination (without LIMIT/OFFSET)
$countQuery = "SELECT COUNT(*) FROM content c WHERE c.workflow_state = 'pending_review'";
if (!empty($where)) {
    $countQuery .= " AND " . implode(" AND ", $where);
}

$totalStmt = $db->prepare($countQuery);
$totalStmt->execute(array_slice($params, 0, -2));
$totalItems = $totalStmt->fetchColumn();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Review Queue</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Content Review Queue</h1>
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message success"><?php echo htmlspecialchars($_SESSION['flash_message']); ?></div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="flash-message error"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        <div class="filters">
            <form method="get" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="content_type">Content Type:</label>
                        <select name="content_type" id="content_type">
                            <option value="">All Types</option>
                            <option value="article" <?php echo ($filters['content_type'] ?? '') === 'article' ? 'selected' : ''; ?>>Article</option>
                            <option value="page" <?php echo ($filters['content_type'] ?? '') === 'page' ? 'selected' : ''; ?>>Page</option>
                            <option value="blog" <?php echo ($filters['content_type'] ?? '') === 'blog' ? 'selected' : ''; ?>>Blog Post</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">From:</label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">To:</label>
                        <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn-filter">Apply Filters</button>
                    <a href="review_queue.php" class="btn-clear">Clear Filters</a>
                </div>
                
                <div class="filter-group">
                    <label for="start_date">From:</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($startDate ?? ''); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="end_date">To:</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($endDate ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn-filter">Filter</button>
                <a href="review_queue.php" class="btn-clear">Clear</a>
            </form>
        </div>
        
        <?php if (empty($pendingItems)): ?>
            <p class="no-items">No items pending review matching your criteria.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo htmlspecialchars($item['author_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($item['content_type']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($item['created_at'])); ?></td>
                        <td class="actions">
                            <form method="post" action="" style="display:inline">
                                <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                <?php endif; ?>
                <span>Page <?php echo $page; ?> of <?php echo ceil($totalItems / $perPage); ?></span>
                
                <?php if ($page * $perPage < $totalItems): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>