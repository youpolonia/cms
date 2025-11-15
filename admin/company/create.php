<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../../admin/includes/auth.php';

csrf_boot('admin');

// Check admin permissions
if (!hasPermission('manage_companies')) {
    header('Location: /admin/');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        $name = trim($_POST['name'] ?? '');
        
        // Validate input
        if (empty($name)) {
            $errors[] = 'Company name is required';
        }

        if (empty($errors)) {
            // Create new company
            $result = CompanyController::create([
                'name' => $name
            ]);

            if ($result) {
                $success = true;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Failed to create company';
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
    <title>Create Company</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <div class="container">
        <h1>Create New Company</h1>
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
                <input type="text" id="name" name="name" required>            </div>
            
            <button type="submit" class="btn">Create Company</button>
        </form>
    </div>
</body>
</html>
