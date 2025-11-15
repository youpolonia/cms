<?php
// Verify admin session
require_once __DIR__ . '/../includes/auth.php';
if (!check_admin_session()) {
    header('Location: /admin/login.php');
    exit;
}

// Get current tenant ID from session
$tenant_id = $_SESSION['tenant_id'] ?? 0;

// Database connection
require_once __DIR__ . '/../../core/database.php';
try {
    $db = \core\Database::connection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get monthly summary with tenant isolation
    $stmt = $db->prepare("
        SELECT SUM(view_count) as total_views
        FROM analytics_monthly_summary
        WHERE tenant_id = :tenant_id
    ");
    $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_views = $result['total_views'] ?? 0;

    // Get monthly engagement data
    $monthlyStmt = $db->prepare("
        SELECT
            DATE_FORMAT(month, '%Y-%m') as month,
            SUM(view_count) as views,
            SUM(interaction_count) as interactions
        FROM analytics_monthly_summary
        WHERE tenant_id = :tenant_id
        GROUP BY DATE_FORMAT(month, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    $monthlyStmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $monthlyStmt->execute();
    $monthlyData = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get top content by interaction
    $topContentStmt = $db->prepare("
        SELECT
            content_id,
            content_title,
            SUM(interaction_count) as total_interactions
        FROM analytics_content_interactions
        WHERE tenant_id = :tenant_id
        GROUP BY content_id, content_title
        ORDER BY total_interactions DESC
        LIMIT 5
    ");
    $topContentStmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $topContentStmt->execute();
    $topContent = $topContentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Analytics Dashboard Error: " . $e->getMessage());
    $total_views = 0;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Analytics Dashboard</h1>
        </header>
        
        <main class="admin-content">
            <section class="analytics-section">
                <h2>Content Views</h2>
                <?php if ($total_views > 0): ?>
                    <div class="metric-card">
                        <h3>Total Views</h3>
                        <p class="metric-value"><?= number_format($total_views) ?></p>
                        <p class="metric-description">All content views for your tenant</p>
                    </div>

                    <div class="chart-container">
                        <h3>Monthly Engagement Trends</h3>
                        <canvas id="monthlyChart"></canvas>
                    </div>

                    <div class="top-content-section">
                        <h3>Top 5 Content by Interactions</h3>
                        <div class="chart-container">
                            <canvas id="topContentChart"></canvas>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No analytics data available</p>
                        <p>Check back later or ensure tracking is enabled</p>
                    </div>
                <?php endif; ?>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if ($total_views > 0 && !empty($monthlyData)): ?>
                        // Monthly engagement chart
                        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
                        new Chart(monthlyCtx, {
                            type: 'line',
                            data: {
                                labels: <?= json_encode(array_column($monthlyData, 'month')) ?>,
                                datasets: [
                                    {
                                        label: 'Views',
                                        data: <?= json_encode(array_column($monthlyData, 'views')) ?>,
                                        borderColor: 'rgb(75, 192, 192)',
                                        tension: 0.1
                                    },
                                    {
                                        label: 'Interactions',
                                        data: <?= json_encode(array_column($monthlyData, 'interactions')) ?>,
                                        borderColor: 'rgb(255, 99, 132)',
                                        tension: 0.1
                                    }
                                ]
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

                        // Top content chart
                        const topContentCtx = document.getElementById('topContentChart').getContext('2d');
                        new Chart(topContentCtx, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($topContent, 'content_title')) ?>,
                                datasets: [{
                                    label: 'Interactions',
                                    data: <?= json_encode(array_column($topContent, 'total_interactions')) ?>,
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
                                },
                                indexAxis: 'y'
                            }
                        });
                    <?php endif; ?>
                });
            </script>
        </main>
        
        <footer class="admin-footer">
            <p>&copy; <?= date('Y') ?> CMS Analytics</p>
        </footer>
    </div>
</body>
</html>
