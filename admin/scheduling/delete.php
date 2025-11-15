<?php
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

// Get schedule ID from query string
$scheduleId = $_GET['schedule_id'] ?? null;
if (!$scheduleId) {
    header('Location: /admin/scheduling');
    exit;
}

// Handle deletion
require_once __DIR__ . '/../../models/workerschedule.php';
require_once __DIR__ . '/../../includes/database/connection.php';
require_once __DIR__ . '/../core/csrf.php';

// Verify schedule exists
$schedule = WorkerSchedule::getById((int)$scheduleId);
if (!$schedule) {
    header('Location: /admin/scheduling');
    exit;
}

// Check approval state - only allow deletion of pending or denied schedules
if ($schedule->status === 'approved' && !$auth->isAdmin()) {
    $error = "Cannot delete an approved schedule. Please contact an administrator.";
}

// Perform deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)) {
    csrf_validate_or_403();
    try {
        WorkerSchedule::delete($schedule->id);
        header('Location: /admin/scheduling');
        exit;
    } catch (Exception $e) {
        $error = "Failed to delete schedule: " . $e->getMessage();
    }
}

// Prepare view
$title = 'Delete Schedule';
ob_start();
?><h2>Delete Schedule</h2>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <a href="/admin/scheduling" class="button">Back to Scheduling</a>
<?php else: ?>
    <p>Are you sure you want to delete this schedule?</p>
    <ul>
        <li>Worker: <?= htmlspecialchars($schedule->worker_id) ?></li>
        <li>Start: <?= date('Y-m-d H:i', strtotime($schedule->start_time)) ?></li>
        <li>End: <?= date('Y-m-d H:i', strtotime($schedule->end_time)) ?></li>
        <li>Status: <?= ucfirst(htmlspecialchars($schedule->status)) ?></li>
    </ul>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= SecurityService::generateCSRFToken() ?>">
        <button type="submit" class="button danger">Confirm Delete</button>
        <a href="/admin/scheduling" class="button">Cancel</a>
    </form>
<?php endif; ?><?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
