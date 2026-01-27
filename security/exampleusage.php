<?php
require_once 'securityutilities.php';

// Example form processing with security measures
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('public');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!SecurityUtilities::validateCsrfToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // Sanitize all input
    $sanitizedData = SecurityUtilities::sanitizeInput($_POST);

    // Process form data...
    $name = SecurityUtilities::escapeOutput($sanitizedData['name']);
    echo "Hello, $name! Form submitted securely.";
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <title>Secure Form Example</title>
</head>
<body>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= SecurityUtilities::generateCsrfToken() ?>">
        Name: <input type="text" name="name"><br>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
