<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
}
?>
// Verify admin session
require_once __DIR__ . '/../admin-access.php';

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Database connection
require_once __DIR__ . '/../../includes/db_connect.php';

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $errors[] = 'Invalid CSRF token';
    }

    // Validate input
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if (empty($name)) {
        $errors[] = 'Menu name is required';
    }

    if (empty($slug)) {
        $errors[] = 'Menu slug is required';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = 'Slug can only contain lowercase letters, numbers and hyphens';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO menus (name, slug) VALUES (:name, :slug)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            
            $success = true;
            $_SESSION['flash_message'] = 'Menu created successfully';
            header('Location: menus.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Menu</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create New Menu</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label for="name">Menu Name</label>
                <input type="text" id="name" name="name"
 required 
                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
?>            </div>

            <div class="form-group">
                <label for="slug">Menu Slug</label>
                <input type="text" id="slug" name="slug"
 required 
                       value="<?= isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : '' ?>">
                <small>Only lowercase letters, numbers and hyphens allowed</small>
?>            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Menu</button>
                <a href="menus.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
