<?php
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

// Get available workers and schedule data
require_once __DIR__ . '/../../models/worker.php';
require_once __DIR__ . '/../../models/workerschedule.php';
require_once __DIR__ . '/../../includes/database/connection.php';
require_once __DIR__ . '/../core/csrf.php';

$connection = new Connection();
$workerModel = new Worker($connection);

// Get schedule ID from query string
$scheduleId = $_GET['schedule_id'] ?? null;
if (!$scheduleId) {
    header('Location: /admin/scheduling');
    exit;
}

// Get schedule data
$schedule = WorkerSchedule::getById((int)$scheduleId);
if (!$schedule) {
    header('Location: /admin/scheduling');
    exit;
}

// Parse datetime into separate date/time components
$startDatetime = new DateTime($schedule->start_time);
$endDatetime = new DateTime($schedule->end_time);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Validate permissions for status changes
    $newStatus = $_POST['status'];
    $currentStatus = $schedule->status;
    
    // Only admins can change status to approved or denied
    if (($newStatus === 'approved' || $newStatus === 'denied') && !$auth->isAdmin()) {
        $error = 'You do not have permission to change status to ' . $newStatus;
    } else {
        $data = [
            'worker_id' => $_POST['worker_id'],
            'start_time' => $_POST['start_date'] . ' ' . $_POST['start_time'],
            'end_time' => $_POST['end_date'] . ' ' . $_POST['end_time'],
            'status' => $newStatus
        ];

        try {
            $schedule->update($data);
            header('Location: /admin/scheduling');
            exit;
        } catch (Exception $e) {
            $error = 'Failed to update schedule: ' . $e->getMessage();
        }
    }
}

// Get all workers
$workers = $workerModel->getAll();

// Prepare view
$title = 'Edit Shift';
ob_start();
?>
<h2>Edit Shift</h2>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= SecurityService::generateCSRFToken() ?>">
    <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($scheduleId) ?>">
    <div class="form-group">
        <label for="worker_id">Worker:</label>
        <select id="worker_id" name="worker_id" required>
            <option value="">Select Worker</option>
            <?php foreach ($workers as $worker): ?>
                <option value="<?= htmlspecialchars($worker['worker_id']) ?>"
                    <?= ($schedule->worker_id === (int)$worker['worker_id'] || (isset($_POST['worker_id']) && $_POST['worker_id'] === $worker['worker_id'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($worker['worker_id']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date"
               value="<?= htmlspecialchars($_POST['start_date'] ?? $startDatetime->format('Y-m-d')) ?>" required>
    </div>

    <div class="form-group">
        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time"
               value="<?= htmlspecialchars($_POST['start_time'] ?? $startDatetime->format('H:i')) ?>"
               required>
    </div>

    <div class="form-group">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date"
               value="<?= htmlspecialchars($_POST['end_date'] ?? $endDatetime->format('Y-m-d')) ?>"
               required>
    </div>

    <div class="form-group">
        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time"
               value="<?= htmlspecialchars($_POST['end_time'] ?? $endDatetime->format('H:i')) ?>"
               required>
    </div>

    <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="pending" <?= ($_POST['status'] ?? $schedule->status) === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= ($_POST['status'] ?? $schedule->status) === 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="denied" <?= ($_POST['status'] ?? $schedule->status) === 'denied' ? 'selected' : '' ?>>Denied</option>
        </select>
    </div>


    <button type="submit" class="button">Update Shift</button>
    <a href="/admin/scheduling" class="button">Cancel</a>
</form>

<script>
// Client-side validation
document.querySelector('form').addEventListener('submit', function(e) {
    const startDate = document.getElementById('start_date').value;
    const startTime = document.getElementById('start_time').value;
    const endDate = document.getElementById('end_date').value;
    const endTime = document.getElementById('end_time').value;

    const start = new Date(`${startDate}T${startTime}`);
    const end = new Date(`${endDate}T${endTime}`);

    if (start >= end) {
        alert('End time must be after start time');
        e.preventDefault();
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
