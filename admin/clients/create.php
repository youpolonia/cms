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
require_once __DIR__ . '/../core/csrf.php';

$connection = new Connection();
$clientModel = new Client($connection);

$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'status' => 'active'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $formData = $_POST;
    $errors = [];
    
    // Validate required fields
    if (empty(trim($formData['name']))) {
        $errors[] = 'Name is required';
    }
    
    // Validate email format if provided
    if (!empty($formData['email']) && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        try {
            if ($clientModel->create($formData)) {
                header('Location: /admin/clients');
                exit;
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Prepare view
$title = 'Create New Client';
ob_start();
?><h2>Create New Client</h2>

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
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required>    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>">
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($formData['phone']) ?>">
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address"><?= htmlspecialchars($formData['address']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="pending" <?= $formData['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
        </select>
    </div>

    <button type="submit" class="primary">Create Client</button>
    <a href="/admin/clients" class="button">Cancel</a>
</form>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';
