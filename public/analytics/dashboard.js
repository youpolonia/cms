document.addEventListener('DOMContentLoaded', function() {
    fetchMetrics();
});

async function fetchMetrics() {
    try {
        const response = await fetch('/api/metrics');
        if (!response.ok) throw new Error('Network response was not ok');
        
        const metrics = await response.json();
        renderCharts(metrics);
    } catch (error) {
        console.error('Failed to fetch metrics:', error);
        document.getElementById('charts-container').innerHTML = 
            '<div class="error">Failed to load metrics data. Please try again later.</div>';
    }
}

function renderCharts(metrics) {
    const container = document.getElementById('charts-container');
    container.innerHTML = ''; // Clear previous content

    if (!metrics || Object.keys(metrics).length === 0) {
        container.innerHTML = '<div class="empty-state">No metrics data available yet</div>';
        return;
    }

    // Response Times Chart
    if (metrics.response_times) {
        const responseTimesCtx = document.createElement('canvas');
        responseTimesCtx.id = 'responseTimesChart';
        container.appendChild(responseTimesCtx);
        
        new Chart(responseTimesCtx, {
            type: 'line',
            data: {
                labels: Object.keys(metrics.response_times),
                datasets: [{
                    label: 'Average Response Time (ms)',
                    data: Object.values(metrics.response_times).map(times => 
                        (times.reduce((a, b) => a + b, 0) / times.length).toFixed(2)),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1,
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
    }

    // Errors Chart
    if (metrics.errors) {
        const errorsCtx = document.createElement('canvas');
        errorsCtx.id = 'errorsChart';
        container.appendChild(errorsCtx);
        
        new Chart(errorsCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(metrics.errors),
                datasets: [{
                    label: 'Error Count',
                    data: Object.values(metrics.errors).map(errors => 
                        Object.values(errors).reduce((a, b) => a + b, 0)),
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
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
    }
}