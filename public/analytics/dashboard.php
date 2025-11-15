<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/analyticsvisualizationserviceinit.php';

class AnalyticsDashboard {
    public static function render(): string {
        $html = '
<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Analytics Dashboard</title>
            <style>
                .dashboard-container { max-width: 1200px; margin: 0 auto; }
                .chart-container { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class="dashboard-container">
                <h1>Analytics Dashboard</h1>
                <div id="charts-container"></div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="dashboard.js"></script>
        </body>
        </html>';

        return $html;
    }
}

echo AnalyticsDashboard::render();
