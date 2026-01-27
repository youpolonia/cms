<?php
require_once __DIR__.'/../../includes/middleware/checkpermission.php';

$permission = new CheckPermission('view_tenant_analytics');
if (!$permission->check()) {
    header('Location: /admin/403');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Analytics Dashboard</title>
    <link rel="stylesheet" href="/assets/css/analytics.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="tenant-analytics-app">
        <tenant-header></tenant-header>
        <div class="dashboard-grid">
            <div class="metric-card">
                <h3>Tenant Usage</h3>
                <tenant-usage-chart></tenant-usage-chart>
            </div>
            <div class="metric-card">
                <h3>Resource Allocation</h3>
                <resource-chart></resource-chart>
            </div>
        </div>
    </div>

    <script src="/admin/analytics/components/tenant-header.js"></script>
    <script src="/admin/analytics/components/tenant-usage-chart.js"></script>
    <script src="/admin/analytics/components/resource-chart.js"></script>
    <script src="/admin/analytics/tenant-app.js"></script>
</body>
</html>
