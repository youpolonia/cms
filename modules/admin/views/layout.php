/**
 * Admin Layout Template
 * Base template for all admin views
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <?= $this->section('head')  ?>
</head>
<body>
    <header class="admin-header">
        <h1>CMS Admin</h1>
        <nav class="admin-nav">
            <a href="/admin">Dashboard</a>
            <a href="/admin/users">Users</a>
        </nav>
    </header>

    <main class="admin-content">
        <?= $this->section('content')  ?>
    </main>

    <script src="/admin/js/admin.js"></script>
    <?= $this->section('scripts')  ?>
</body>
</html>
