<?php
// Check admin access
require_once __DIR__ . '/../../auth/authcontroller.php';
require_once __DIR__ . '/../../services/TranslationService.php';
$auth = new AuthController();
if (!$auth->isAdmin()) {
    header('Location: /admin/login');
    exit;
}

require_once __DIR__ . '/../../models/client.php';
require_once __DIR__ . '/../../includes/database/connection.php';
require_once __DIR__ . '/../core/csrf.php';

$connection = new Connection();
$clientModel = new Client($connection);

$clientId = $_GET['client_id'] ?? '';
if (!$clientId) {
    header('Location: /admin/clients');
    exit;
}

$errors = [];
$client = $clientModel->get((int)$clientId);
if (!$client) {
    header('Location: /admin/clients');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        if ($clientModel->update((int)$clientId, $_POST)) {
            header('Location: /admin/clients');
            exit;
        }
    } catch (\Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Prepare view
$title = TranslationService::trans('clients.edit_title');
ob_start();
?><h2><?= TranslationService::trans('clients.edit_title') ?></h2>

<?php if (!empty($errors)): ?>
<div class="error-message">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $auth->getCsrfToken() ?>">
    <div class="form-group">
        <label for="name"><?= TranslationService::trans('clients.name_label') ?> *</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($client['name']) ?>" required>    </div>

    <div class="form-group">
        <label for="email"><?= TranslationService::trans('clients.email_label') ?></label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="phone"><?= TranslationService::trans('clients.phone_label') ?></label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="address"><?= TranslationService::trans('clients.address_label') ?></label>
        <textarea id="address" name="address"><?= htmlspecialchars($client['address'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="status"><?= TranslationService::trans('clients.status_label') ?></label>
        <select id="status" name="status">
            <option value="active" <?= $client['status'] === 'active' ? 'selected' : '' ?>><?= TranslationService::trans('clients.status_active') ?></option>
            <option value="inactive" <?= $client['status'] === 'inactive' ? 'selected' : '' ?>><?= TranslationService::trans('clients.status_inactive') ?></option>
            <option value="pending" <?= $client['status'] === 'pending' ? 'selected' : '' ?>><?= TranslationService::trans('clients.status_pending') ?></option>
        </select>
    </div>

    <button type="submit" class="primary"><?= TranslationService::trans('clients.update_button') ?></button>
    <a href="/admin/clients" class="button"><?= TranslationService::trans('clients.cancel_button') ?></a>
</form>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
