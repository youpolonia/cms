<?php
require_once __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-welcome">
    <h1>Welcome to CMS Admin Dashboard</h1>
    <p>Last login: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></p>
</div>

<div class="dashboard-section">
    <h2>System Diagnostics</h2>
    <div class="dashboard-content">
        <p>System status: <span class="status-good">Operational</span></p>
        <p>Placeholder for system diagnostics widget</p>
    </div>
</div>

<div class="dashboard-section">
    <h2>Recent Activity Logs</h2>
    <div class="dashboard-content">
        <p>Placeholder for recent logs widget</p>
    </div>
</div>

<div class="dashboard-section">
    <h2>System Statistics</h2>
    <div class="dashboard-content">
        <p>Placeholder for statistics widget</p>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';