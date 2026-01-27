const ResourceChart = {
    template: `
        <div class="chart-container">
            <canvas ref="chartCanvas"></canvas>
            <div v-if="loading" class="loading-overlay">
                Loading data...
            </div>
        </div>
    `,
    props: {
        resourceData: {
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
        resourceData: {
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
                type: 'bar',
                data: {
                    labels: this.resourceData.labels || [],
                    datasets: [{
                        label: 'Resource Allocation',
                        data: this.resourceData.values || [],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    },
    mounted() {
        if (this.resourceData) {
            this.renderChart();
        }
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    }
};