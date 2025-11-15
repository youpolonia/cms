<?php
require_once __DIR__.'/../../services/TokenMonitoringService.php';

// Check authentication
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// Get token usage data
$startDate = new DateTime('-7 days');
$endDate = new DateTime();
$tokenData = TokenMonitoringService::getUsageTrends($startDate, $endDate, Auth::tenantId());

// Prepare chart data
$chartData = [
    'labels' => [],
    'datasets' => [
        'ai_generation' => [],
        'api_request' => [],
        'background_process' => []
    ]
];

foreach ($tokenData as $entry) {
    if (!in_array($entry['day'], $chartData['labels'])) {
        $chartData['labels'][] = $entry['day'];
    }
    $chartData['datasets'][$entry['operation_type']][] = $entry['total_tokens'];
}
?><!DOCTYPE html>
<html>
<head>
    <title>Token Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .chart-container {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Token Usage Analytics</h1>
        
        <div class="chart-container">
            <canvas id="tokenUsageChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('tokenUsageChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chartData['labels']) ?>,
                    datasets: [
                        {
                            label: 'AI Generation',
                            data: <?= json_encode($chartData['datasets']['ai_generation']) ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        },
                        {
                            label: 'API Requests',
                            data: <?= json_encode($chartData['datasets']['api_request']) ?>,
                            borderColor: 'rgb(54, 162, 235)',
                            tension: 0.1
                        },
                        {
                            label: 'Background Processes',
                            data: <?= json_encode($chartData['datasets']['background_process']) ?>,
                            borderColor: 'rgb(255, 99, 132)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Token Usage Over Time'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Tokens Consumed'
                            }
                        }
                    }
                }
            });
        </script>
    </div>
</body>
</html>
