<?php
// CSRF guard (auto-inserted)
if (!defined('CMS_ROOT')) {
    $___root = dirname(__DIR__, 2);
    if (!is_file($___root . '/config.php')) { $___root = dirname(__DIR__); }
    require_once __DIR__ . '/../../config.php';
}
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }

require_once __DIR__ . '/../../admin/includes/auth.php';

// Check admin permissions
if (!hasPermission('manage_companies')) {
    header('Location: /admin/');
    exit;
}

$errors = [];
$success = false;

// Get company ID
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Get company data
$company = CompanyController::getById($id);
if (!$company) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        $name = trim($_POST['name'] ?? '');
        
        // Validate input
        if (empty($name)) {
            $errors[] = 'Company name is required';
        }

        if (empty($errors)) {
            // Update company
            $result = CompanyController::update($id, [
                'name' => $name
            ]);

            if ($result) {
                $success = true;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Failed to update company';
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $errors[] = 'Database error';
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Company</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <div class="container">
        <h1>Edit Company</h1>
        <a href="index.php" class="btn">Back to List</a>
        
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field(); 
?>            <div class="form-group">
                <label for="name">Company Name</label>
                <input type="text" id="name" name="name" 
                       value="<?= htmlspecialchars($company['name']) ?>" required>            </div>
            
            <button type="submit" class="btn">Update Company</button>
        </form>
    </div>
</body>
</html>
