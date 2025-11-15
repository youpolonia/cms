<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/systemalert.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tenant_id = $_GET['tenant_id'] ?? null;
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$alerts = SystemAlert::get_alerts($tenant_id, $limit, $offset);
$total_alerts = count(SystemAlert::get_alerts($tenant_id, 0, 0));
$total_pages = ceil($total_alerts / $limit);

?><!DOCTYPE html>
<html>
<head>
    <title>System Alerts</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <script src="/assets/js/alerts.js" defer></script>
</head>
<body>
    <div class="admin-container">
        <h1>System Alerts</h1>
        
        <div class="filter-bar">
            <select id="tenant-filter">
                <option value="">All Tenants</option>
                <?php foreach(get_tenants() as $tenant): ?>                    <option value="<?= $tenant['id'] ?>" <?= $tenant_id == $tenant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tenant['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

<div class="alert-list">
            <?php foreach($alerts as $alert): ?>
<div class="alert-item" data-alert-id="<?= $alert['id'] ?>">
                    <div class="alert-header">
                        <span class="alert-type <?= $alert['type'] ?>"><?= ucfirst($alert['type']) ?></span>
                        <span class="alert-date"><?= date('M j, Y H:i', strtotime($alert['created_at'])) ?></span>
                    </div>
                    <div class="alert-message"><?= htmlspecialchars($alert['message']) ?></div>
                    <form class="resolve-form" method="post" action="/admin/alerts/resolve.php">
                        <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="resolve-btn" data-alert-id="<?= $alert['id'] ?>">
                            <span class="btn-text">Mark as Resolved</span>
                            <span class="loading-spinner" style="display:none">Processing...</span>
                        </button>
                    </form>
                    <div class="alert-status"></div>
                </div>
            <?php endforeach; ?>
        </div>

<div class="pagination">
            <?php if($page > 1): ?>
<a href="?page=<?= $page-1 ?>&tenant_id=<?= $tenant_id ?>">Previous</a>
            <?php endif; ?>
<span>Page <?= $page ?> of <?= $total_pages ?></span>
            
            <?php if($page < $total_pages): ?>
<a href="?page=<?= $page+1 ?>&tenant_id=<?= $tenant_id ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
