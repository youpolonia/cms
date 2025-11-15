require_once __DIR__.'/../../assets/js/analytics-charts.js';

?><!DOCTYPE html>
<html>
<head>
    <title>Tenant Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .loading {
            text-align: center;
            padding: 50px;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Tenant Analytics Dashboard</h1>
    
    <div class="chart-container">
        <h2>Event Types Over Time</h2>
        <div id="loading-time-series" class="loading">Loading time series data...</div>
        <canvas id="timeSeriesChart"></canvas>
    </div>

    <div class="chart-container">
        <h2>Event Type Distribution</h2>
        <div id="loading-event-type" class="loading">Loading event type data...</div>
        <canvas id="eventTypeChart"></canvas>
    </div>

    <script>
        // Fetch and render data with improved error handling
        async function loadAnalytics() {
            const tenantId = <?php echo json_encode($_GET['tenant_id'] ?? ''); ?>; ?>
            const timeRange = <?php echo json_encode($_GET['time_range'] ?? '7 DAY'); ?>; ?>
            
            // Show loading states
            document.getElementById('loading-time-series').textContent = 'Loading analytics data...';
            document.getElementById('loading-event-type').textContent = 'Loading analytics data...';
            
            // Validate inputs
            if (!tenantId || !tenantId.match(/^[a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12}$/i)) {
                showError('Invalid tenant ID format. Must be a valid UUID.');
                return;
            }
            
            if (!['1 DAY', '7 DAY', '30 DAY', '90 DAY'].includes(timeRange)) {
                showError('Invalid time range. Must be one of: 1 DAY, 7 DAY, 30 DAY, 90 DAY');
                return;
            }
            
            try {
                const response = await fetch(`/api/v1/analytics/tenants/${tenantId}?range=${timeRange}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    document.getElementById('loading-time-series').style.display = 'none';
                    document.getElementById('loading-event-type').style.display = 'none';
                    
                    // Render charts
                    renderTimeSeriesChart(data.data);
                    renderEventTypeChart(aggregateByEventType(data.data));
                } else {
                    throw new Error(data.message || 'Failed to load analytics');
                }
            } catch (error) {
                console.error('Analytics error:', error);
                showError(error.message);
            }
        }

        // Helper to aggregate data by event type
        function aggregateByEventType(data) {
            const result = {};
            data.forEach(item => {
                if (!result[item.event_type]) {
                    result[item.event_type] = 0;
                }
                result[item.event_type] += parseInt(item.count);
            });
            return Object.entries(result).map(([event_type, count]) => ({ event_type, count }));
        }

        // Show error message with improved styling
        function showError(message) {
            document.getElementById('loading-time-series').style.display = 'none';
            document.getElementById('loading-event-type').style.display = 'none';
            
            // Remove any existing error messages
            const existingErrors = document.querySelectorAll('.error-message');
            existingErrors.forEach(el => el.remove());
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `
                <div style="color: #dc3545; padding: 20px; text-align: center; background: #f8d7da; border-radius: 4px; margin: 20px;">
                    <strong>Error:</strong> ${message}
                    <button onclick="loadAnalytics()"
                            style="margin-left: 10px;
                                   padding: 5px 10px;
                                   background: #dc3545;
                                   color: white;
                                   border: none;
                                   border-radius: 4px;
                                   cursor: pointer;">
                        Retry
                    </button>
                </div>
            `;
            
            document.querySelector('body').appendChild(errorDiv);
        }

        // Standard chart colors
        const chartColors = {
            background: 'rgba(54, 162, 235, 0.2)',
            border: 'rgba(54, 162, 235, 1)',
            hoverBackground: 'rgba(54, 162, 235, 0.4)'
        };

        // Initialize on load
        window.addEventListener('DOMContentLoaded', loadAnalytics);
?>    </script>
</body>
</html>
