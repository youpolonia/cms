<?php
require_once __DIR__ . '/../core/bootstrap.php';
http_response_code(503);
header('Retry-After: 3600'); // 1 hour
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            color: #333;
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2em;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <h1>Under Maintenance</h1>
    <p>We're currently performing scheduled maintenance. Please check back later.</p>
    <p>Thank you for your patience.</p>
</body>
</html>
