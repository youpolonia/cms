<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= $pageTitle ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="logo">CMS Admin</div>
        <nav class="admin-nav">
            <ul>
                <li><a href="/admin"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/admin/users"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="/admin/roles"><i class="fas fa-user-tag"></i> Roles</a></li>
                <li><a href="/admin/logs"><i class="fas fa-clipboard-list"></i> Audit Logs</a></li>
            </ul>
        </nav>
        <div class="user-menu">
            <span><i class="fas fa-user-circle"></i> <?= $_SESSION['admin_username'] ?></span>
            <a href="/admin/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    <main class="admin-main">
