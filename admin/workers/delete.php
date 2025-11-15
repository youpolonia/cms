<?php
// Check admin access
require_once __DIR__ . '/../../modules/auth/authcontroller.php';
require_once __DIR__ . '/../../services/authservice.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot();

$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../includes/validator.php';
require_once __DIR__ . '/../../models/worker.php';

$workerModel = new Worker(\core\Database::connection());

$workerId = $_GET['worker_id'] ?? '';
if (!$workerId) {
    header('Location: /admin/workers');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        $workerModel->delete($workerId);
        header('Location: /admin/workers');
        exit;
    } catch (\Exception $e) {
        http_response_code(500);
        error_log($e->getMessage());
        exit;
    }
}

// Prepare view
$title = 'Delete Worker';
ob_start();

?><h2>Delete Worker</h2>

<p>Are you sure you want to delete worker "<?= htmlspecialchars($workerId) ?>"?</p>
<form method="post">
    <?= csrf_field(); 
?><button type="submit" class="danger">Confirm Delete</button>
    <a href="/admin/workers">Cancel</a>
</form>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
