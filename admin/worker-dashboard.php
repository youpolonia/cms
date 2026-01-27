<?php
require_once __DIR__ . '/../includes/routing/helpers.php';
require_once __DIR__ . '/../includes/services/workersupervisor.php';

$title = 'Worker Process Dashboard';
require_once __DIR__ . '/header.php';


?><div class="container-fluid">
    <h1 class="mt-4">Worker Process Dashboard</h1>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>CPU Usage</h4>
                </div>
                <div class="card-body">
                    <canvas id="cpuChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Memory Usage</h4>
                </div>
                <div class="card-body">
                    <canvas id="memoryChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Active Workers</h4>
                </div>
                <div class="card-body">
                    <canvas id="workersChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Scaling Recommendations</h4>
                </div>
                <div class="card-body" id="scalingActions">
                    <div class="alert alert-secondary">Loading recommendations...</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Worker Status</h4>
                </div>
                <div class="card-body" id="workerStatus">
                    <div class="alert alert-secondary">Loading worker status...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/admin/js/worker-dashboard.js"></script>

<?php require_once __DIR__ . '/footer.php';
