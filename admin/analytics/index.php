<?php
require_once __DIR__ . '/../../config.php';

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot();


// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
// Handle filter submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_action'])) {
    csrf_validate_or_403();
    switch ($_POST['filter_action']) {
        case 'apply_tenant':
            $_SESSION['analytics_tenant'] = $_POST['tenant'] ?? '';
            break;
        case 'apply_range':
            $_SESSION['analytics_range'] = $_POST['range'] ?? 'week';
            if ($_SESSION['analytics_range'] === 'custom') {
                $_SESSION['analytics_custom_start'] = $_POST['custom_start'] ?? '';
                $_SESSION['analytics_custom_end'] = $_POST['custom_end'] ?? date('Y-m-d');
            }
            break;
    }
}

// Get data for components
$tenants = getTenantList(); // Implement this function in data_fetcher.php
$metrics = getDashboardMetrics(); // Implement this function in data_fetcher.php
$timeSeriesData = getTimeSeriesData(); // Implement this function in data_fetcher.php
$engagementData = getEngagementData(); // Implement this function in data_fetcher.php

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php require_once __DIR__ . '/../includes/admin_header.php'; ?>
        <main class="analytics-dashboard">
            <h1>Analytics Dashboard</h1>
            
            <div class="dashboard-controls">
                <?php require_once __DIR__ . '/components/tenant_filter.php'; ?>
                <?php require_once __DIR__ . '/components/time_range.php'; ?>
            </div>
            
            <div class="dashboard-content">
                <section class="metric-cards">
                    <?php require_once __DIR__ . '/components/metric_cards.php'; ?>
                </section>
                
                <section class="chart-row">
                    <div class="chart-container">
                        <?php require_once __DIR__ . '/components/time_chart.php'; ?>
                    </div>

                    <div class="chart-container">
                        <?php require_once __DIR__ . '/components/engagement_chart.php'; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>

<style>
.analytics-dashboard {
    padding: 2rem;
}

.dashboard-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 2rem;
}

.dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.metric-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.chart-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

.chart-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
}

@media (min-width: 992px) {
    .chart-row {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
