<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | CMS Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .admin-header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem;
        }
        .admin-nav {
            background-color: #34495e;
            color: white;
            padding: 0.5rem 1rem;
        }
        .admin-nav a {
            color: white;
            margin-right: 1rem;
            text-decoration: none;
        }
        .admin-content {
            padding: 1rem;
        }
        .stats-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
        }
        
        .version-restore-container {
            background: white;
            padding: 1rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>CMS Admin Panel</h1>
    </header>
    
    <nav class="admin-nav">
        <a href="/admin">Dashboard</a>
        <a href="/admin/content">Content</a>
        <a href="/admin/users">Users</a>
        <a href="/admin/plugins">Plugins</a>
        <a href="/admin/settings">Settings</a>
    </nav>

    <main class="admin-content">
        <h2><?= htmlspecialchars($pageTitle) ?></h2>
        <div class="stats-container">
            <div class="stat-card">
                <h3>Users</h3>
                <p><?= $content['users'] ?? 0 ?></p>
            </div>
            <div class="stat-card">
                <h3>Content Items</h3>
                <p><?= $content['content'] ?? 0 ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Plugins</h3>
                <p><?= $content['plugins'] ?? 0 ?></p>
            </div>

            <div class="version-restore-container">
                <h3>Content Version Restoration</h3>
                <form action="/admin/restore-version" method="POST">
                    <div class="form-group">
                        <label for="version-select">Select Version:</label>
                        <select id="version-select" name="version_id" class="form-control">
                            <?php foreach ($versions as $version): ?>                                <option value="<?= $version['id'] ?>">
                                    Version #<?= $version['id'] ?> - <?= date('Y-m-d H:i', strtotime($version['created_at']))  ?>
                                </option>
                            <?php endforeach;  ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Restore Version</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
