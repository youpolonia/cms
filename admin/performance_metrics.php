<?php
require_once __DIR__ . '/../services/analyticsservice.php';
$analytics = AnalyticsService::getInstance();
$metrics = $analytics->getTenantAnalytics();

?><div class="analytics-container">
    <h2>Performance Metrics</h2>
    
    <div class="metrics-grid">
        <div class="metric-card">
            <h3>Response Times</h3>
            <div id="response-time-chart" class="chart-container"></div>
        </div>
        
        <div class="metric-card">
            <h3>Cache Statistics</h3>
            <div id="cache-stats-chart" class="chart-container"></div>
        </div>
    </div>
    
    <div class="historical-trends">
        <h3>Historical Trends</h3>
        <div id="trends-chart" class="chart-container-wide"></div>
    </div>
</div>

<script src="../assets/js/analytics-charts.js"></script>
<script>
    const metricsData = <?= json_encode($metrics) ?>;
    initAnalyticsCharts(metricsData);
</script>
