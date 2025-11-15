<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/admin-auth.php';

$pageTitle = "Analytics Dashboard";
$activeNav = "analytics";

// Check admin permissions
if (!AdminAuth::hasPermission('view_analytics')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Denied');
}

// Get AI metrics data
$aiMetrics = [];
try {
    $aiMetrics = json_decode(file_get_contents('http://localhost/api/ai-metrics'), true);
} catch (Exception $e) {
    $error = "Failed to load AI metrics: " . $e->getMessage();
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php require_once __DIR__ . '/admin-nav.php'; ?>
        <main class="admin-content">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            
            <!-- AI Metrics Section -->
            <section class="dashboard-section">
                <h2>AI Usage Metrics</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="chart-container" style="position: relative; height:400px; width:100%">
                    <canvas id="aiMetricsChart"></canvas>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('aiMetricsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['Today', 'This Week', 'This Month'],
                                datasets: [{
                                    label: 'AI API Calls',
                                    data: [
                                        <?= $aiMetrics['today'] ?? 0 ?>, ?>
                                        <?= $aiMetrics['week'] ?? 0 ?>, ?>
                                        <?= $aiMetrics['month'] ?? 0 ?>                                    ],
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    });
                </script>
            </section>
        </main>
    </div>
</body>
</html>
