const { createApp } = Vue;

const app = createApp({
    data() {
        return {
            dateRange: '7d'
        }
    },
    data() {
        return {
            dateRange: '7d',
            analyticsData: []
        }
    },
    methods: {
        handleRangeUpdate(newRange) {
            this.dateRange = newRange;
            this.loadAnalyticsData();
        },
        handleExportRequest(format) {
            const url = `/api/analytics/export?format=${format}&range=${this.dateRange}`;
            window.location.href = url;
        },
        loadAnalyticsData() {
            // TODO: Implement data loading from API
            // Will use Aggregator.php for calculations
        }
    },
    mounted() {
        this.loadAnalyticsData();
    }
});

app.component('comparison-chart', ComparisonChart);
app.mount('#analytics-app');

// Global error handler
window.addEventListener('error', function(event) {
    console.error('Global error:', event.error);
    // Could show user-friendly error message here
});