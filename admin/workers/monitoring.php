<?php
require_once __DIR__ . '/../../../includes/coreloader.php';

// Include debug tools
require_once __DIR__ . '/../../../debug_worker_monitoring.php';
require_once __DIR__ . '/../../../debug_worker_monitoring_phase5.php';

// Check admin permissions
if (!WorkerAuthController::checkPermission('monitor_workers')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Log page access using the public logMessage method
DebugWorkerMonitoring::logMessage('Worker monitoring dashboard accessed at ' . date('Y-m-d H:i:s'));
DebugWorkerMonitoringPhase5::logMessage('PHASE5-WORKFLOW-STEP4 monitoring dashboard accessed at ' . date('Y-m-d H:i:s'));

$title = 'Worker Monitoring Dashboard';
require_once __DIR__ . '/../views/layout.php';
?><div class="container-fluid">
    <h1>Worker Monitoring Dashboard</h1>
    
    <div class="row">
        <!-- Real-time Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Current Worker Status</h3>
                    <div class="float-right">
                        <span class="badge badge-info" id="last-updated">Never updated</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="worker-status-container">
                        <p class="text-muted">Loading worker status...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Configuration -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Alert Thresholds</h3>
                </div>
                <div class="card-body">
                    <form id="alert-config-form">
                        <div class="form-group">
                            <label>Heartbeat Threshold (minutes)</label>
                            <input type="number" class="form-control" name="heartbeat_threshold" value="5" min="1">
                        </div>
                        <div class="form-group">
                            <label>CPU Usage Threshold (%)</label>
                            <input type="number" class="form-control" name="cpu_threshold" value="90" min="1" max="100">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- History Visualization -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Heartbeat History</h3>
                    <div class="float-right">
                        <select class="form-control" id="time-range">
                            <option value="1">Last hour</option>
                            <option value="6">Last 6 hours</option>
                            <option value="24" selected>Last 24 hours</option>
                            <option value="168">Last week</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="heartbeatChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Real-time worker status update
function updateWorkerStatus() {
    fetch('/api/workers/status')
        .then(response => response.json())
        .then(data => {
            let html = '
<table class="table table-striped">';
            html += '<thead><tr><th>Worker ID</th><th>Status</th><th>Last Heartbeat</th><th>CPU</th><th>Memory</th></tr></thead>';
            html += '<tbody>';
            
            data.workers.forEach(worker => {
                let statusClass = worker.status === 'active' ? 'success' : 
                                worker.status === 'idle' ? 'warning' : 'danger';
                html += `<tr>
                    <td>${worker.id}</td>
                    <td><span class="badge badge-${statusClass}">${worker.status}</span></td>
                    <td>${worker.last_heartbeat}</td>
                    <td>${worker.cpu_usage}%</td>
                    <td>${worker.memory_usage}%</td>
                </tr>`;
            });
            
            html += '</tbody></table>';
            document.getElementById('worker-status-container').innerHTML = html;
            document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
        });
}

// Initialize chart
let heartbeatChart;
function initChart() {
    const ctx = document.getElementById('heartbeatChart').getContext('2d');
    heartbeatChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Heartbeat Frequency',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    updateChartData();
}

// Update chart data based on selected time range
function updateChartData() {
    const hours = document.getElementById('time-range').value;
    fetch(`/api/workers/heartbeat-history?hours=${hours}`)
        .then(response => response.json())
        .then(data => {
            heartbeatChart.data.labels = data.labels;
            heartbeatChart.data.datasets[0].data = data.values;
            heartbeatChart.update();
        });
}

// Event listeners
document.getElementById('time-range').addEventListener('change', updateChartData);
document.getElementById('alert-config-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/api/workers/alert-config', {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.ok) {
            alert('Alert thresholds updated successfully');
        }
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    updateWorkerStatus();
    setInterval(updateWorkerStatus, 30000); // Update every 30 seconds
    
    // Add error handling to updateWorkerStatus function
    const originalUpdateWorkerStatus = updateWorkerStatus;
    updateWorkerStatus = function() {
        try {
            return originalUpdateWorkerStatus().catch(error => {
                console.error('Worker status update failed:', error);
                document.getElementById('worker-status-container').innerHTML +=
                    '
<div class="alert alert-danger">Update failed: ' + error.message + '</div>';
                
                // Log the error
                fetch('/api/debug/log-client-error', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        source: 'updateWorkerStatus',
                        message: error.message,
                        timestamp: new Date().toISOString()
                    })
                }).catch(() => {});
            });
        } catch (e) {
            console.error('Error in updateWorkerStatus wrapper:', e);
        }
    };
});

// Include client-side debug scripts
echo DebugWorkerMonitoring::getClientDebugScript();
echo DebugWorkerMonitoringPhase5::getClientDebugScript();


// Add response structure validation and token refresh handling
?><script>
// Enhance updateWorkerStatus with response structure validation and token refresh
const originalUpdateWorkerStatus = updateWorkerStatus;
updateWorkerStatus = function() {
    console.log('PHASE5-WORKFLOW-STEP4: Enhanced updateWorkerStatus called');
    
    return fetch('/api/workers/status')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            
            // Check for token refresh header
            const newToken = response.headers.get('X-JWT-Refresh');
            if (newToken) {
                console.log('Received new JWT token from server');
                
                // Store the new token in localStorage for future requests
                localStorage.setItem('worker_jwt_token', newToken);
                
                // Log the token refresh
                fetch('/api/debug/log-client-error-phase5', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        source: 'tokenRefresh',
                        message: 'JWT token refreshed',
                        timestamp: new Date().toISOString(),
                        phase: 'PHASE5-WORKFLOW-STEP4'
                    })
                }).catch(() => {});
            }
            
            return response.json();
        })
        .then(data => {
            // Validate response structure
            if (!data) {
                throw new Error('Response is empty or null');
            }
            if (!data.timestamp) {
                throw new Error('Response missing timestamp');
            }
            if (!Array.isArray(data.workers)) {
                throw new Error('Response workers is not an array');
            }
            
            // Continue with original processing
            let html = '
<table class="table table-striped">';
            html += '<thead><tr><th>Worker ID</th><th>Status</th><th>Last Heartbeat</th><th>CPU</th><th>Memory</th></tr></thead>';
            html += '<tbody>';
            
            data.workers.forEach(worker => {
                let statusClass = worker.status === 'active' ? 'success' :
                                worker.status === 'idle' ? 'warning' : 'danger';
                html += `<tr>
                    <td>${worker.id}</td>
                    <td><span class="badge badge-${statusClass}">${worker.status}</span></td>
                    <td>${worker.last_heartbeat}</td>
                    <td>${worker.cpu_usage}%</td>
                    <td>${worker.memory_usage}%</td>
                </tr>`;
            });
            
            html += '</tbody></table>';
            document.getElementById('worker-status-container').innerHTML = html;
            document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();
        })
        .catch(error => {
            console.error('Enhanced worker status update failed:', error);
            document.getElementById('worker-status-container').innerHTML +=
                '<div class="alert alert-danger">Update failed: ' + error.message + '</div>';
            
            // Check if this is an authentication error
            if (error.message.includes('401') ||
                error.message.includes('403') ||
                error.message.includes('Unauthorized') ||
                error.message.includes('Forbidden')) {
                
                // Show authentication error UI
                document.getElementById('worker-status-container').innerHTML +=
                    '
<div class="alert alert-warning">Authentication error detected. Try refreshing the page.</div>';
            }
            
            // Log the error
            fetch('/api/debug/log-client-error-phase5', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    source: 'enhancedUpdateWorkerStatus',
                    message: error.message,
                    timestamp: new Date().toISOString(),
                    phase: 'PHASE5-WORKFLOW-STEP4'
                })
            }).catch(() => {});
        });
};

// Add token refresh function
function refreshJwtToken() {
    const currentToken = localStorage.getItem('worker_jwt_token');
    if (currentToken) {
        fetch('/api/workers/refresh-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + currentToken
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Token refresh failed');
        })
        .then(data => {
            if (data.token) {
                localStorage.setItem('worker_jwt_token', data.token);
                console.log('JWT token manually refreshed');
            }
        })
        .catch(error => {
            console.error('Token refresh error:', error);
        });
    }
}

// Add manual refresh button
document.addEventListener('DOMContentLoaded', function() {
    const statusContainer = document.getElementById('worker-status-container');
    if (statusContainer) {
        const refreshButton = document.createElement('button');
        refreshButton.className = 'btn btn-sm btn-outline-secondary mt-2';
        refreshButton.textContent = 'Refresh Authentication';
        refreshButton.onclick = refreshJwtToken;
        
        statusContainer.parentNode.insertBefore(refreshButton, statusContainer);
    }
});
?></script>
</script>
