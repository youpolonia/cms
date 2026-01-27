/**
 * Analytics Dashboard Component
 */
class AnalyticsDashboard {
    constructor(apiEndpoint = '/api/v1/analytics') {
        this.apiEndpoint = apiEndpoint;
        this.chartColors = {
            primary: '#4e73df',
            success: '#1cc88a',
            info: '#36b9cc',
            warning: '#f6c23e',
            danger: '#e74a3b',
            secondary: '#858796',
            dark: '#5a5c69'
        };
    }

    async init() {
        await this.loadData();
        this.renderCharts();
        this.setupEventListeners();
    }

    async loadData() {
        try {
            const response = await fetch(`${this.apiEndpoint}/dashboard`);
            this.data = await response.json();
        } catch (error) {
            console.error('Failed to load analytics data:', error);
        }
    }

    renderCharts() {
        this.renderPageViewsChart();
        this.renderVisitorsChart();
        this.renderPerformanceChart();
        this.renderEngagementChart();
    }

    renderPageViewsChart() {
        const ctx = document.getElementById('pageViewsChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.data.trends.map(t => t.date),
                datasets: [{
                    label: 'Page Views',
                    data: this.data.trends.map(t => t.page_views),
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: this.chartColors.primary,
                    pointBackgroundColor: this.chartColors.primary,
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: this.chartColors.primary
                }]
            },
            options: this.getChartOptions('Page Views Trend')
        });
    }

    renderVisitorsChart() {
        const ctx = document.getElementById('visitorsChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Today', 'Yesterday', 'Last Week'],
                datasets: [{
                    label: 'Unique Visitors',
                    data: [
                        this.data.today.unique_visitors,
                        this.data.yesterday.unique_visitors,
                        this.data.last_week.unique_visitors
                    ],
                    backgroundColor: [
                        this.chartColors.success,
                        this.chartColors.info,
                        this.chartColors.secondary
                    ],
                }]
            },
            options: this.getChartOptions('Visitors Comparison')
        });
    }

    renderPerformanceChart() {
        // Implementation for performance metrics chart
    }

    renderEngagementChart() {
        // Implementation for engagement stats chart
    }

    getChartOptions(title) {
        return {
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: title
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        };
    }

    setupEventListeners() {
        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.refreshData();
        });
    }

    async refreshData() {
        await this.loadData();
        this.renderCharts();
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new AnalyticsDashboard();
    dashboard.init();
});