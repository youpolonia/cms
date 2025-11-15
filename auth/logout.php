<?php
/**
 * Logout Handler
 */
require_once __DIR__ . '/services/sessionservice.php';

// Verify CSRF token if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionService = new \Auth\Services\SessionService();
    $sessionService->start();
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $sessionService->get('csrf_token')) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
    
    // Destroy session
    $sessionService->destroy();
    
    // Redirect to login page
    header('Location: /auth/login');
    exit;
}

// If GET request, show confirmation form
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Logout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .logout-form {
            margin-top: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logout-form">
        <h2>Are you sure you want to logout?</h2>
        <form method="POST" action="/auth/logout">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit">Logout</button>
            <a href="/admin/dashboard">Cancel</a>
        </form>
    </div>
</body>
</html>
