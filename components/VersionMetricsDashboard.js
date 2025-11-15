/**
 * VersionMetricsDashboard - Displays version statistics, rollback rates and status distribution
 */
class VersionMetricsDashboard {
    constructor(elementId, metricsData) {
        this.element = document.getElementById(elementId);
        this.metrics = metricsData;
        this.charts = {};
    }

    init() {
        this.renderDashboard();
    }

    renderDashboard() {
        // Create dashboard container
        this.element.innerHTML = `
            <div class="version-metrics-dashboard">
                <h2>Version Metrics</h2>
                <div class="metrics-grid">
                    <div class="metric-chart">
                        <h3>Version Statistics</h3>
                        <canvas id="versionStatsChart"></canvas>
                    </div>
                    <div class="metric-chart">
                        <h3>Rollback Rates</h3>
                        <canvas id="rollbackRatesChart"></canvas>
                    </div>
                    <div class="metric-chart">
                        <h3>Status Distribution</h3>
                        <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        `;

        // Initialize charts
        this.initVersionStatsChart();
        this.initRollbackRatesChart();
        this.initStatusDistributionChart();
    }

    initVersionStatsChart() {
        const ctx = document.getElementById('versionStatsChart').getContext('2d');
        this.charts.versionStats = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.metrics.versionStats.labels,
                datasets: [{
                    label: 'Versions Created',
                    data: this.metrics.versionStats.data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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

    initRollbackRatesChart() {
        const ctx = document.getElementById('rollbackRatesChart').getContext('2d');
        this.charts.rollbackRates = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.metrics.rollbackRates.labels,
                datasets: [{
                    label: 'Rollback Rate %',
                    data: this.metrics.rollbackRates.data,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }

    initStatusDistributionChart() {
        const ctx = document.getElementById('statusDistributionChart').getContext('2d');
        this.charts.statusDistribution = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: this.metrics.statusDistribution.labels,
                datasets: [{
                    data: this.metrics.statusDistribution.data,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    }

    updateMetrics(newMetrics) {
        this.metrics = newMetrics;
        this.updateCharts();
    }

    updateCharts() {
        if (this.charts.versionStats) {
            this.charts.versionStats.data.labels = this.metrics.versionStats.labels;
            this.charts.versionStats.data.datasets[0].data = this.metrics.versionStats.data;
            this.charts.versionStats.update();
        }

        if (this.charts.rollbackRates) {
            this.charts.rollbackRates.data.labels = this.metrics.rollbackRates.labels;
            this.charts.rollbackRates.data.datasets[0].data = this.metrics.rollbackRates.data;
            this.charts.rollbackRates.update();
        }

        if (this.charts.statusDistribution) {
            this.charts.statusDistribution.data.labels = this.metrics.statusDistribution.labels;
            this.charts.statusDistribution.data.datasets[0].data = this.metrics.statusDistribution.data;
            this.charts.statusDistribution.update();
        }
    }
}

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VersionMetricsDashboard;
}