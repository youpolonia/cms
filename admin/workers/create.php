<?php
// Core dependencies
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../includes/validator.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot();

// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../models/worker.php';
require_once __DIR__ . '/../../includes/database/connection.php';

$connection = new Connection();
$workerModel = new Worker($connection);

$errors = [];
$workerData = [
    'worker_id' => '',
    'health_score' => 100,
    'failure_count' => 0,
    'needs_replacement' => false,
    'recovery_attempts' => 0
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    $workerData = array_merge($workerData, $_POST);
    $workerData['needs_replacement'] = isset($_POST['needs_replacement']);

    try {
        if ($workerModel->create($workerData)) {
            header('Location: /admin/workers');
            exit;
        }
    } catch (\Throwable $e) {
        $errors[] = 'Internal error';
        error_log($e->getMessage());
    }
}

// Prepare view
$title = 'Add New Worker';
ob_start();
?><h2>Add New Worker</h2>

<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<form method="post">
    <?php echo csrf_field(); ?>
    <div>
        <label for="worker_id">Worker ID:</label>
        <input type="text" id="worker_id" name="worker_id"
               required 
               value="<?php echo htmlspecialchars($workerData['worker_id']); ?>">
    </div>
    
    <div>
        <label for="health_score">Health Score:</label>
        <input type="number" id="health_score" name="health_score" min="0" max="100"
               value="<?php echo htmlspecialchars($workerData['health_score']); ?>">
    </div>
    
    <div>
        <label for="failure_count">Failure Count:</label>
        <input type="number" id="failure_count" name="failure_count" min="0"
               value="<?php echo htmlspecialchars($workerData['failure_count']); ?>">
    </div>
    
    <div>
        <label>
            <input type="checkbox" name="needs_replacement" 
                   <?php echo $workerData['needs_replacement'] ? 'checked' : ''; ?>>
            Needs Replacement
        </label>
    </div>
    
    <div>
        <label for="recovery_attempts">Recovery Attempts:</label>
        <input type="number" id="recovery_attempts" name="recovery_attempts" min="0"
               value="<?php echo htmlspecialchars($workerData['recovery_attempts']); ?>">
    </div>
    
    <button type="submit">Save Worker</button>
    <a href="/admin/workers">Cancel</a>
</form>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';