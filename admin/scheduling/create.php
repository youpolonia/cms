<?php
require_once __DIR__ . '/../../core/csrf.php';
header('Content-Type: text/html; charset=utf-8');
ob_start(); // Start output buffering at very beginning
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
require_once __DIR__ . '/../../core/services/securityservice.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

$title = 'Create New Shift';
// Get available workers
require_once __DIR__ . '/../../models/worker.php';
require_once __DIR__ . '/../../includes/database/connection.php';

$connection = new Connection();
$workerModel = new Worker($connection);
$workers = $workerModel->getAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    if (!SecurityService::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        header('HTTP/1.1 403 Forbidden');
        exit('Invalid CSRF token');
    }
    
    require_once __DIR__ . '/../../models/shift.php';
    $shiftModel = new Shift($connection);

    // Validate time format and logic
    try {
        $startTime = $_POST['start_date'] . ' ' . $_POST['start_time'];
        $endTime = $_POST['end_date'] . ' ' . $_POST['end_time'];
        
        if (!strtotime($startTime) || !strtotime($endTime)) {
            throw new Exception('Invalid date/time format');
        }
        
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        
        if ($start >= $end) {
            throw new Exception('End time must be after start time');
        }
        
        // Minimum shift duration (15 minutes)
        $minDuration = new DateInterval('PT15M');
        if ($end->diff($start) < $minDuration) {
            throw new Exception('Minimum shift duration is 15 minutes');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    if (!isset($error)) {
        $data = [
            'worker_id' => $_POST['worker_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $_POST['status'],
            'location' => $_POST['location'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];

        // Check for conflicts
        if ($shiftModel->checkShiftConflicts($data['worker_id'], $data['start_time'], $data['end_time'])) {
            $error = 'This worker already has a scheduled shift during this time period';
        } else {
            if ($shiftModel->create($data)) {
                header('Location: /admin/scheduling');
                exit;
            } else {
                $error = 'Failed to create shift';
            }
        }
    }
}
?><h2>Create New Shift</h2>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= SecurityService::generateCSRFToken() ?>">
    <div class="form-group">
        <label for="worker_id">Worker:</label>
        <select id="worker_id" name="worker_id" required>
            <option value="">Select Worker</option>
            <?php foreach ($workers as $worker): ?>                <option value="<?= htmlspecialchars($worker['worker_id']) ?>" 
                    <?= isset($_POST['worker_id']) && $_POST['worker_id'] === $worker['worker_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($worker['worker_id']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date"
               value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
?>    </div>

    <div class="form-group">
        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time"
               value="<?= htmlspecialchars($_POST['start_time'] ?? '09:00') ?>" required>
    </div>

    <div class="form-group">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date"
               value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time"
               value="<?= htmlspecialchars($_POST['end_time'] ?? '17:00') ?>" required>
    </div>

    <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="scheduled" <?= ($_POST['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
            <option value="in_progress" <?= ($_POST['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="completed" <?= ($_POST['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= ($_POST['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>

    <div class="form-group">
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" 
               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="notes">Notes:</label>
        <textarea id="notes" name="notes"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="button">Create Shift</button>
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

    // Minimum shift duration (15 minutes)
    const minDuration = 15 * 60 * 1000; // 15 minutes in ms
    if ((end - start) < minDuration) {
        alert('Minimum shift duration is 15 minutes');
        e.preventDefault();
    }
});
</script>
<?php
// Clean any existing output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Set default title if not defined
$title = $title ?? 'Schedule Management';

// Validate template file existence
// Validate and sanitize layout path

$layoutPath = realpath(implode(DIRECTORY_SEPARATOR, [
    __DIR__, '..', 'views', 'layout.php'
]));

// Prevent temp file execution
// Block execution from any temporary directories
$tempPaths = ['AppData/Local/Temp', 'AppData/Local/Programs/Microsoft VS Code'];
foreach ($tempPaths as $path) {
    if (strpos(__FILE__, $path) !== false) {
        header('HTTP/1.1 403 Forbidden');
        exit("Execution from restricted directory ($path) not allowed");
    }
}

// Final buffer check before inclusion
if (ob_get_level() > 0) {
    ob_end_clean();
}

// Verify layout file exists before inclusion
$layoutPath = realpath(implode(DIRECTORY_SEPARATOR, [
    __DIR__, '..', 'views', 'layout.php'
]));

if (!$layoutPath || !is_file($layoutPath)) {
    header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
    error_log("Layout file path validation failed: " . implode(DIRECTORY_SEPARATOR, [
        __DIR__, '..', 'views', 'layout.php'
    ]));
    exit('<h1>System Maintenance</h1><p>Core template missing</p>');
}

require_once $layoutPath;
