<?php
// Core dependencies
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../includes/core/view.php';

// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

// Get all workers
require_once __DIR__ . '/../../models/worker.php';
require_once __DIR__ . '/../../includes/database/connection.php';

$connection = new Connection();
$workerModel = new Worker($connection);
$workers = $workerModel->getAll();

// Prepare view
$title = 'Worker Management';
ob_start();
?><h2>Worker Management</h2>
<a href="/admin/workers/create" class="button">Add New Worker</a>

<table>
    <thead>
        <tr>
            <th>Worker ID</th>
            <th>Health Score</th>
            <th>Failure Count</th>
            <th>Last Seen</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($workers as $worker): ?>
        <tr>
            <td><?php echo htmlspecialchars($worker['worker_id']); ?></td>
            <td><?php echo $worker['health_score']; ?></td>
            <td><?php echo $worker['failure_count']; ?></td>
            <td><?php echo $worker['last_seen'] ? date('Y-m-d H:i', strtotime($worker['last_seen'])) : 'Never'; ?></td>
            <td>
                <a href="/admin/workers/edit?worker_id=<?php echo urlencode($worker['worker_id']); ?>">Edit</a>
                <a href="/admin/workers/delete?worker_id=<?php echo urlencode($worker['worker_id']); ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';