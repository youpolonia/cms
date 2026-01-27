<?php
// Start session if not already started

// Validate CSRF token if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    // Clear all session data
    $_SESSION = [];
    session_destroy();

    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Generate new CSRF token for form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="/admin/assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Logout</h1>
        <p>Are you sure you want to logout?</p>
        
        <form method="POST" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <button type="submit" class="btn-login">Logout</button>
        </form>
    </div>
</body>
</html>
