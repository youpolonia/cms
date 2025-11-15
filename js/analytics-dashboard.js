// Analytics Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    const visitsChart = initLineChart('visits-chart');
    const contentChart = initPieChart('content-chart');
    const sourcesChart = initBarChart('sources-chart');
    const engagementChart = initLineChart('engagement-chart');

    // Date range selector logic
    const dateRangeSelect = document.getElementById('date-range');
    dateRangeSelect.addEventListener('change', function() {
        const customRange = document.getElementById('custom-range');
        customRange.style.display = this.value === 'custom' ? 'block' : 'none';
        
        if (this.value !== 'custom') {
            updateCharts(this.value);
        }
    });

    // Custom date range handler
    const dateFrom = document.getElementById('date-from');
    const dateTo = document.getElementById('date-to');
    [dateFrom, dateTo].forEach(input => {
        input.addEventListener('change', function() {
            if (dateRangeSelect.value === 'custom' && dateFrom.value && dateTo.value) {
                updateCharts('custom', dateFrom.value, dateTo.value);
            }
        });
    });

    // Export buttons
    document.getElementById('export-pdf').addEventListener('click', exportPDF);
    document.getElementById('export-csv').addEventListener('click', exportCSV);

    // Initial load with default range
    updateCharts('7d');
});

function initLineChart(canvasId) {
    return new Chart(document.getElementById(canvasId), {
        type: 'line',
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function initBarChart(canvasId) {
    return new Chart(document.getElementById(canvasId), {
        type: 'bar',
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function initPieChart(canvasId) {
    return new Chart(document.getElementById(canvasId), {
        type: 'pie',
        options: {
            responsive: true
        }
    });
}

async function updateCharts(range, fromDate = null, toDate = null) {
    try {
        const params = new URLSearchParams();
        params.append('range', range);
        if (fromDate && toDate) {
            params.append('from', fromDate);
            params.append('to', toDate);
        }

        // Fetch data from API using standardized endpoint
        const response = await fetch(`/api/v1/versions/${currentVersionId}/analytics/metrics?${params.toString()}`);
        const data = await response.json();

        // Update charts with new data
        updateChartData('visits-chart', data.visits);
        updateChartData('content-chart', data.contentTypes);
        updateChartData('sources-chart', data.trafficSources);
        updateChartData('engagement-chart', data.userEngagement);
    } catch (error) {
        console.error('Error fetching analytics data:', error);
    }
}

function updateChartData(chartId, data) {
    const chart = Chart.getChart(chartId);
    if (chart) {
        chart.data = data;
        chart.update();
    }
}

async function exportPDF() {
    try {
        const response = await fetch(`/api/v1/versions/${currentVersionId}/analytics/export-pdf`);
        const blob = await response.blob();
        downloadFile(blob, 'analytics-report.pdf');
    } catch (error) {
        console.error('Error exporting PDF:', error);
    }
}

async function exportCSV() {
    try {
        const response = await fetch(`/api/v1/versions/${currentVersionId}/analytics/export-csv`);
        const blob = await response.blob();
        downloadFile(blob, 'analytics-data.csv');
    } catch (error) {
        console.error('Error exporting CSV:', error);
    }
}

function downloadFile(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}