/**
 * Analytics Charts - Vanilla JS implementation
 */
const charts = {};

function initAnalyticsCharts(metrics) {
    // Process metrics data
    const responseTimes = metrics.filter(m => m.metric_name === 'response_time');
    const cacheStats = metrics.filter(m => m.metric_name === 'cache_hit_rate');
    
    // Render response time chart
    renderLineChart('response-time-chart', {
        title: 'Response Times (ms)',
        data: responseTimes.map(m => ({
            x: new Date(m.timestamp),
            y: m.value
        }))
    });

    // Render cache stats chart
    renderBarChart('cache-stats-chart', {
        title: 'Cache Performance',
        hits: cacheStats.map(m => m.hits),
        misses: cacheStats.map(m => m.misses)
    });

    // Render trends chart
    renderTrendChart('trends-chart', {
        responseTimes,
        cacheStats
    });
}

function renderLineChart(elementId, {title, data}) {
    const canvas = document.createElement('canvas');
    document.getElementById(elementId).appendChild(canvas);
    
    // Simple canvas-based line chart implementation
    const ctx = canvas.getContext('2d');
    // ... chart rendering logic ...
}

// Tenant Analytics Charts
function renderTimeSeriesChart(data) {
    const ctx = document.getElementById('timeSeriesChart').getContext('2d');
    
    // Process data into chart format
    const labels = [...new Set(data.map(item => item.date))].sort();
    const datasets = [];
    
    // Group by event type
    const eventTypes = [...new Set(data.map(item => item.event_type))];
    const colorPalette = [
        '#4e79a7', '#f28e2b', '#e15759', '#76b7b2',
        '#59a14f', '#edc948', '#b07aa1', '#ff9da7'
    ];
    
    eventTypes.forEach((type, i) => {
        datasets.push({
            label: type,
            data: labels.map(date => {
                const entry = data.find(d => d.date === date && d.event_type === type);
                return entry ? entry.count : 0;
            }),
            borderColor: colorPalette[i % colorPalette.length],
            backgroundColor: `${colorPalette[i % colorPalette.length]}40`,
            borderWidth: 2,
            tension: 0.1,
            fill: true
        });
    });

    if (charts.timeSeries) charts.timeSeries.destroy();
    
    charts.timeSeries = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function renderEventTypeChart(data) {
    const ctx = document.getElementById('eventTypeChart').getContext('2d');
    const colorPalette = [
        '#4e79a7', '#f28e2b', '#e15759', '#76b7b2',
        '#59a14f', '#edc948', '#b07aa1', '#ff9da7'
    ];
    
    if (charts.eventType) charts.eventType.destroy();
    
    charts.eventType = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(item => item.event_type),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: data.map((_, i) => colorPalette[i % colorPalette.length]),
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}

// Accessibility-friendly color palette
const chartColors = {
    background: 'rgba(78, 121, 167, 0.2)',
    border: 'rgba(78, 121, 167, 1)',
    hoverBackground: 'rgba(78, 121, 167, 0.4)',
    palette: [
        '#4e79a7', // blue
        '#f28e2b', // orange
        '#e15759', // red
        '#76b7b2', // teal
        '#59a14f', // green
        '#edc948', // yellow
        '#b07aa1', // purple
        '#ff9da7'  // pink
    ]
};

function renderBarChart(elementId, {title, hits, misses}) {
    const canvas = document.createElement('canvas');
    document.getElementById(elementId).appendChild(canvas);
    
    // Simple canvas-based bar chart implementation
    const ctx = canvas.getContext('2d');
    // ... chart rendering logic ...
}

function renderTrendChart(elementId, {responseTimes, cacheStats}) {
    const canvas = document.createElement('canvas');
    document.getElementById(elementId).appendChild(canvas);
    
    // Combined trends visualization
    const ctx = canvas.getContext('2d');
    // ... chart rendering logic ...
}