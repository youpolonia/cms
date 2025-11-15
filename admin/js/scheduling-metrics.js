/**
 * Worker Metrics Dashboard for Scheduling
 * Vanilla JS implementation for shared hosting compatibility
 */

// Update worker metrics
function updateWorkerMetrics() {
    const startDate = document.getElementById('metrics-start-date').value;
    const endDate = document.getElementById('metrics-end-date').value;
    
    let url = '/api/workers/status';
    if (startDate && endDate) {
        url += `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    }

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            // Calculate metrics
            const activeCount = data.workers.filter(w => w.status === 'active').length;
            const totalCount = data.workers.length;
            const avgCpu = data.workers.reduce((sum, w) => sum + (w.cpu_usage || 0), 0) / totalCount;
            const avgMem = data.workers.reduce((sum, w) => sum + (w.memory_usage || 0), 0) / totalCount;

            // Generate HTML
            let html = `
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h4>Active Workers</h4>
                        <div class="metric-value">${activeCount}/${totalCount}</div>
                    </div>
                    <div class="metric-card">
                        <h4>Avg CPU Usage</h4>
                        <div class="metric-value">${avgCpu.toFixed(1)}%</div>
                    </div>
                    <div class="metric-card">
                        <h4>Avg Memory Usage</h4>
                        <div class="metric-value">${avgMem.toFixed(1)}%</div>
                    </div>
                </div>
                <div class="recent-activity">
                    <h4>Recent Activity</h4>
                    <ul class="activity-list">
                        ${data.workers.slice(0, 5).map(w => `
                            <li>
                                <span class="worker-name">${w.name}</span>
                                <span class="worker-status ${w.status}">${w.status}</span>
                                <span class="worker-time">${w.last_heartbeat}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;

            document.getElementById('worker-metrics-container').innerHTML = html;
            document.getElementById('metrics-last-updated').textContent = new Date().toLocaleTimeString();
        })
        .catch(error => {
            document.getElementById('worker-metrics-container').innerHTML = `
                <div class="alert alert-error">Error loading metrics: ${error.message}</div>
            `;
        });
}

// Export metrics data
function exportMetricsData() {
    const startDate = document.getElementById('metrics-start-date').value;
    const endDate = document.getElementById('metrics-end-date').value;
    
    let url = '/api/workers/status/export';
    if (startDate && endDate) {
        url += `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    }

    window.location.href = url;
}

// Initialize date pickers with default range (last 7 days)
function initDatePickers() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 7);

    document.getElementById('metrics-start-date').valueAsDate = startDate;
    document.getElementById('metrics-end-date').valueAsDate = endDate;
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initDatePickers();
    updateWorkerMetrics();

    // Set up event listeners
    document.getElementById('metrics-apply-filter').addEventListener('click', updateWorkerMetrics);
    document.getElementById('metrics-export').addEventListener('click', exportMetricsData);

    // Auto-refresh every 30 seconds
    setInterval(updateWorkerMetrics, 30000);
});