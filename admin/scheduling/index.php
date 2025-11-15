<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/security.php';
verifyAdminAccess();

// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

// Get all schedules
require_once __DIR__ . '/../../models/workerschedule.php';
require_once __DIR__ . '/../../includes/database/connection.php';

// Handle status filter
$statusFilter = $_GET['status'] ?? 'all';
$validStatuses = ['all', 'pending', 'approved', 'denied'];
if (!in_array($statusFilter, $validStatuses)) {
    $statusFilter = 'all';
}

// Get schedules for current month
$currentMonth = date('Y-m');
$startDate = date('Y-m-01', strtotime($currentMonth));
$endDate = date('Y-m-t', strtotime($currentMonth));

// Build query based on filter
$db = \core\Database::connection();
$sql = "SELECT * FROM worker_schedules WHERE DATE(start_time) BETWEEN :start AND :end";
$params = [':start' => $startDate, ':end' => $endDate];

if ($statusFilter !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY start_time";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare view
$title = 'Worker Schedule Management';
ob_start();
?><h2>Worker Schedule Management</h2>
<a href="/admin/scheduling/create" class="button">Create New Schedule</a>

<!-- Status Filter -->
<div class="filter-container">
    <form method="get" class="filter-form">
        <div class="form-group">
            <label for="status">Filter by Status:</label>
            <select id="status" name="status" onchange="this.form.submit()">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="denied" <?= $statusFilter === 'denied' ? 'selected' : '' ?>>Denied</option>
            </select>
        </div>
    </form>
</div>

<!-- Worker Metrics Dashboard -->
<div class="metrics-dashboard">
    <div class="card">
        <div class="card-header">
            <h3>Worker Metrics</h3>
            <div class="float-right">
                <span class="badge" id="metrics-last-updated">Never updated</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div id="worker-metrics-container">
                        <p class="text-muted">Loading worker metrics...</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Date Range</label>
                        <div class="input-group">
                            <input type="date" id="metrics-start-date" class="form-control">
                            <span class="input-group-text">to</span>
                            <input type="date" id="metrics-end-date" class="form-control">
                            <button id="metrics-apply-filter" class="btn btn-primary">Apply</button>
                        </div>
                    </div>
                    <button id="metrics-export" class="btn btn-secondary">Export Data</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="calendar-container">
    <!-- Calendar will be implemented with JavaScript -->
    <div id="calendar"></div>
</div>

<h3>Worker Schedules <?= $statusFilter !== 'all' ? '(' . ucfirst($statusFilter) . ')' : '' ?></h3>
<table>
    <thead>
        <tr>
            <th>Worker ID</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($schedules)): ?>
        <tr>
            <td colspan="5" class="text-center">No schedules found with the selected filter.</td>
        </tr>
        <?php else: ?>            <?php foreach ($schedules as $schedule): ?>
            <tr>
                <td><?= htmlspecialchars($schedule['worker_id']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($schedule['start_time'])) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($schedule['end_time'])) ?></td>
                <td>
                    <span class="status-badge status-<?= htmlspecialchars($schedule['status']) ?>">
                        <?= ucfirst(htmlspecialchars($schedule['status'])) 
?>                    </span>
                </td>
                <td>
                    <a href="/admin/scheduling/edit?schedule_id=<?= urlencode($schedule['id']) ?>">Edit</a>
                    <a href="/admin/scheduling/delete?schedule_id=<?= urlencode($schedule['id']) ?>"
                       onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<style>
.filter-container {
    margin: 20px 0;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 5px;
}

.filter-form {
    display: flex;
    align-items: center;
}

.filter-form .form-group {
    margin-right: 15px;
    margin-bottom: 0;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: bold;
}

.status-pending {
    background-color: #ffeeba;
    color: #856404;
}

.status-approved {
    background-color: #d4edda;
    color: #155724;
}

.status-denied {
    background-color: #f8d7da;
    color: #721c24;
}
</style>

<script src="/admin/js/scheduling-metrics.js"></script>
<script>
// Will implement calendar functionality with FullCalendar or similar
document.addEventListener('DOMContentLoaded', function() {
    // Calendar initialization will go here
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
