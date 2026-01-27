<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/auditlogger.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot('admin');

// Check admin permissions
if (!Auth::hasPermission('admin.audit.view')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Initialize filters from GET parameters
$filters = [];
if (!empty($_GET['user_id'])) {
    $filters['user_id'] = (int)$_GET['user_id'];
}
if (!empty($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}
if (!empty($_GET['target_type'])) {
    $filters['target_type'] = $_GET['target_type'];
}

// Get pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get logs with filters
$logs = AuditLogger::getLogs($filters);
$totalLogs = count($logs);
$paginatedLogs = array_slice($logs, $offset, $perPage);

// Handle export requests
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_logs.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'User ID', 'Action', 'Target Type', 'Target ID', 'Message', 'Timestamp']);
        foreach ($logs as $log) {
            fputcsv($output, $log);
        }
        fclose($output);
        exit;
    } elseif ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="audit_logs.json"');
        echo json_encode($logs, JSON_PRETTY_PRINT);
        exit;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <div class="container">
        <h1>Audit Logs</h1>
        
        <!-- Filter Form -->
        <form method="get" class="filters">
            <div class="filter-group">
                <label for="user_id">User ID:</label>
                <input type="number" id="user_id" name="user_id" value="<?= $_GET['user_id'] ?? '' ?>">
            </div>
            
            <div class="filter-group">
                <label for="action">Action:</label>
                <select id="action" name="action">
                    <option value="">All Actions</option>
                    <option value="create"<?= ($_GET['action'] ?? '') === 'create' ? ' selected' : '' ?>>Create</option>
                    <option value="update"<?= ($_GET['action'] ?? '') === 'update' ? ' selected' : '' ?>>Update</option>
                    <option value="delete"<?= ($_GET['action'] ?? '') === 'delete' ? ' selected' : '' ?>>Delete</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="target_type">Target Type:</label>
                <select id="target_type" name="target_type">
                    <option value="">All Types</option>
                    <option value="user"<?= ($_GET['target_type'] ?? '') === 'user' ? ' selected' : '' ?>>User</option>
                    <option value="page"<?= ($_GET['target_type'] ?? '') === 'page' ? ' selected' : '' ?>>Page</option>
                    <option value="post"<?= ($_GET['target_type'] ?? '') === 'post' ? ' selected' : '' ?>>Post</option>
                </select>
            </div>
            
            <button type="submit">Filter</button>
            <a href="?export=csv" class="export-btn">Export CSV</a>
            <a href="?export=json" class="export-btn">Export JSON</a>
        </form>
        
        <!-- Logs Table -->
        <table class="logs-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Action</th>
                    <th>Target Type</th>
                    <th>Target ID</th>
                    <th>Message</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paginatedLogs as $log): ?>
<tr>
                    <td><?= $log['id'] ?></td>
                    <td><?= $log['user_id'] ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['target_type']) ?></td>
                    <td><?= $log['target_id'] ?></td>
                    <td><?= htmlspecialchars($log['message']) ?></td>
                    <td><?= $log['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
<div class="pagination">
            <?php if ($page > 1): ?>
<a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Previous</a>
            <?php endif; ?>
<span>Page <?= $page ?> of <?= ceil($totalLogs / $perPage) ?></span>

            <?php if ($page * $perPage < $totalLogs): ?>
<a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
