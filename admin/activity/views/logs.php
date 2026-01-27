<?php
/**
 * Activity Log Viewer
 * Displays records from client_activities table
 */

// Verify admin access
require_once __DIR__ . '/../../../includes/middleware/secure-admin.php';

// Database connection
require_once __DIR__ . '/../../../includes/db/db-connection.php';

// Pagination settings
$perPage = 50;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$activityType = isset($_GET['activity_type']) ? $_GET['activity_type'] : null;
$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;

// Base query
$query = "SELECT * FROM client_activities WHERE 1=1";
$params = [];

// Add filters
if ($startDate) {
    $query .= " AND created_at >= ?";
    $params[] = $startDate;
}
if ($endDate) {
    $query .= " AND created_at <= ?";
    $params[] = $endDate;
}
if ($activityType) {
    $query .= " AND activity_type = ?";
    $params[] = $activityType;
}
if ($clientId) {
    $query .= " AND client_id = ?";
    $params[] = $clientId;
}

// Add sorting and pagination
$query .= " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";

// Execute query
$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM client_activities WHERE 1=1";
if ($startDate) $countQuery .= " AND created_at >= '$startDate'";
if ($endDate) $countQuery .= " AND created_at <= '$endDate'";
if ($activityType) $countQuery .= " AND activity_type = '$activityType'";
if ($clientId) $countQuery .= " AND client_id = $clientId";

$totalLogs = $db->query($countQuery)->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

// Get unique activity types for filter dropdown
$activityTypes = $db->query("SELECT DISTINCT activity_type FROM client_activities ORDER BY activity_type")->fetchAll(PDO::FETCH_COLUMN);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Activity Logs</h1>
        
        <form method="get" class="log-filters">
            <div class="filter-group">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
            </div>
            <div class="filter-group">
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
            </div>
            <div class="filter-group">
                <label for="activity_type">Action Type:</label>
                <select id="activity_type" name="activity_type">
                    <option value="">All Types</option>
                    <?php foreach ($activityTypes as $type): ?>                        <option value="<?= htmlspecialchars($type) ?>" <?= $activityType === $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
<div class="filter-group">
                <label for="client_id">Client ID:</label>
                <input type="number" id="client_id" name="client_id" value="<?= $clientId ?>" min="1">
            </div>
            <button type="submit">Filter</button>
            <a href="?" class="reset-filters">Reset</a>
            
            <form method="get" action="export.php" style="display: inline;">
                <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
                <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
                <input type="hidden" name="activity_type" value="<?= htmlspecialchars($activityType ?? '') ?>">
                <input type="hidden" name="client_id" value="<?= $clientId ?>">
                <button type="submit" class="export-btn">Export CSV</button>
            </form>
        </form>
        
        <div class="log-viewer">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
<tr>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                        <td><?= htmlspecialchars($log['client_id']) ?></td>
                        <td><?= htmlspecialchars($log['activity_type']) ?></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
<div class="pagination">
                <?php if ($page > 1): ?>
<a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
                <?php endif; ?>
<span>Page <?= $page ?> of <?= $totalPages ?></span>
                
                <?php if ($page < $totalPages): ?>
<a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
