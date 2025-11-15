<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$title = 'Version Control Analytics';
?><div class="container-fluid">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Version Statistics</div>
                <div class="card-body">
                    <canvas id="versionStatsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Version Activity</div>
                <div class="card-body">
                    <canvas id="versionActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Top Content by Version Count</div>
                <div class="card-body">
                    <canvas id="topContentChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Load version stats
    $.get('/api/analytics/version-stats', function(data) {
        if (data.success) {
            // Render charts using Chart.js
            renderVersionStatsChart(data.stats);
            renderVersionActivityChart(data.activity);
            renderTopContentChart(data.top_content);
        }
    });
    
    function renderVersionStatsChart(stats) {
        // Chart implementation here
    }
    
    function renderVersionActivityChart(activity) {
        // Chart implementation here
    }
    
    function renderTopContentChart(topContent) {
        // Chart implementation here
    }
});
?></script>

require_once __DIR__ . '/../../includes/footer.php';
