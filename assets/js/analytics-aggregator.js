class MultiSiteAggregator {
    constructor(chartElementId, statsElementId) {
        this.chartElement = document.getElementById(chartElementId);
        this.statsElement = document.getElementById(statsElementId);
        this.chart = null;
        this.aggregateData = {};
        this.initChart();
    }

    initChart() {
        this.chart = new Chart(this.chartElement, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Visits',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    async loadAggregateData(tenantId = 'all', startDate, endDate) {
        try {
            const response = await fetch(`/api/v1/analytics/aggregate?tenant=${tenantId}&start=${startDate}&end=${endDate}`);
            this.aggregateData = await response.json();
            this.updateChart();
            this.updateStats();
        } catch (error) {
            console.error('Failed to load aggregate data:', error);
        }
    }

    updateChart() {
        const sites = Object.keys(this.aggregateData.sites);
        const visits = sites.map(site => this.aggregateData.sites[site].visits);

        this.chart.data.labels = sites;
        this.chart.data.datasets[0].data = visits;
        this.chart.update();
    }

    updateStats() {
        const stats = this.aggregateData.stats;
        const html = `
            <div class="stat-row">
                <span class="stat-label">Total Visits:</span>
                <span class="stat-value">${stats.totalVisits.toLocaleString()}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Unique Visitors:</span>
                <span class="stat-value">${stats.uniqueVisitors.toLocaleString()}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Avg. Visit Duration:</span>
                <span class="stat-value">${this.formatDuration(stats.avgDuration)}</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Top Site:</span>
                <span class="stat-value">${stats.topSite} (${stats.topSiteVisits.toLocaleString()} visits)</span>
            </div>
        `;
        this.statsElement.innerHTML = html;
    }

    formatDuration(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}m ${secs}s`;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const aggregator = new MultiSiteAggregator('site-comparison-chart', 'aggregate-stats');
    
    // Set default date range (last 30 days)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 30);
    
    // Load initial data
    aggregator.loadAggregateData(
        document.getElementById('tenant-select').value,
        startDate.toISOString().split('T')[0],
        endDate.toISOString().split('T')[0]
    );
    
    // Add event listeners for controls
    document.getElementById('apply-dates').addEventListener('click', () => {
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        const tenant = document.getElementById('tenant-select').value;
        aggregator.loadAggregateData(tenant, start, end);
    });
    
    document.getElementById('tenant-select').addEventListener('change', () => {
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        const tenant = document.getElementById('tenant-select').value;
        aggregator.loadAggregateData(tenant, start, end);
    });
});