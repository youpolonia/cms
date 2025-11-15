<?php
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../models/client.php';
require_once __DIR__ . '/../../includes/database/connection.php';
require_once __DIR__ . '/../../services/TranslationService.php';
require_once __DIR__ . '/../core/csrf.php';
$translation = new TranslationService();

$connection = new Connection();
$clientModel = new Client($connection);

$clientId = $_GET['client_id'] ?? '';
if (!$clientId) {
    header('Location: /admin/clients');
    exit;
}

$client = $clientModel->get((int)$clientId);
if (!$client) {
    header('Location: /admin/clients');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid CSRF token';
    } else {
        try {
            if ($clientModel->delete((int)$clientId)) {
                header('Location: /admin/clients');
                exit;
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Prepare view
$title = $translation->get('clients.delete.title');
ob_start();
?><h2><?= $translation->get('clients.delete.heading') ?></h2>

<?php if (!empty($errors)): ?>
<div class="error-message">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<div class="confirmation-box">
    <p><?= sprintf($translation->get('clients.delete.confirm_text'), htmlspecialchars($client['name'])) ?></p>
    <p><?= $translation->get('clients.delete.warning') ?></p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= $auth->getCsrfToken() ?>">
        <button type="submit" class="danger"><?= $translation->get('clients.delete.confirm_button') ?></button>
        <a href="/admin/clients" class="button"><?= $translation->get('clients.delete.cancel_button') ?></a>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
