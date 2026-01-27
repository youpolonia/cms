const TenantUsageChart = {
    template: `
        <div class="chart-container">
            <canvas ref="chartCanvas"></canvas>
            <div v-if="loading" class="loading-overlay">
                Loading data...
            </div>
        </div>
    `,
    props: {
        usageData: {
            type: Object,
            required: true
        },
        loading: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            chart: null
        }
    },
    watch: {
        usageData: {
            handler(newData) {
                if (newData) {
                    this.renderChart();
                }
            },
            deep: true
        }
    },
    methods: {
        renderChart() {
            if (this.chart) {
                this.chart.destroy();
            }

            const ctx = this.$refs.chartCanvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.usageData.labels || [],
                    datasets: [{
                        label: 'Tenant Usage',
                        data: this.usageData.values || [],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        }
    },
    mounted() {
        if (this.usageData) {
            this.renderChart();
        }
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    }
};