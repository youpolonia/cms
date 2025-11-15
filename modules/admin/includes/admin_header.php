<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../core/session_boot.php';

cms_session_start('admin');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .btn { padding: 8px 16px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px; }
        .btn.danger { background: #cc0000; }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
<nav>
    <a href="/admin/pages">Pages</a> |
    <a href="/admin/settings">Settings</a> |
    <a href="/admin/users">Users</a>
</nav>
<hr>
