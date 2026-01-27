<?php
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
require_once __DIR__ . '/../../core/csrf.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../includes/validator.php';
require_once __DIR__ . '/../../models/worker.php';
require_once __DIR__ . '/../../includes/database/connection.php';

$connection = new Connection();
$workerModel = new Worker($connection);

$errors = [];
$workerId = $_GET['worker_id'] ?? '';
$workerData = $workerModel->get($workerId);

if (!$workerData) {
    header('Location: /admin/workers');
    exit;
}

csrf_boot('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $updateData = $_POST;
    $updateData['needs_replacement'] = isset($_POST['needs_replacement']);

    try {
        if ($workerModel->update($workerId, $updateData)) {
            header('Location: /admin/workers');
            exit;
        }
    } catch (\Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Prepare view
$title = 'Edit Worker';
ob_start();
?><h2>Edit Worker: <?= htmlspecialchars($workerId) ?></h2>

<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<form method="post">
    <?= csrf_field(); 
?>    <div>
        <label for="health_score">Health Score:</label>
        <input type="number" id="health_score" name="health_score" min="0" max="100"
               value="<?= htmlspecialchars($workerData['health_score']) ?>">
    </div>
    
    <div>
        <label for="failure_count">Failure Count:</label>
        <input type="number" id="failure_count" name="failure_count" min="0"
               value="<?= htmlspecialchars($workerData['failure_count']) ?>">
    </div>
    
    <div>
        <label>
            <input type="checkbox" name="needs_replacement" 
                   <?= $workerData['needs_replacement'] ? 'checked' : '' ?>>
            Needs Replacement
        </label>
    </div>
    
    <div>
        <label for="recovery_attempts">Recovery Attempts:</label>
        <input type="number" id="recovery_attempts" name="recovery_attempts" min="0"
               value="<?= htmlspecialchars($workerData['recovery_attempts']) ?>">
    </div>
    
    <button type="submit">Update Worker</button>
    <a href="/admin/workers">Cancel</a>
</form>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
