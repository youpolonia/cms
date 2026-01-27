const EngagementChart = {
    template: `
        <div class="chart-container">
            <canvas ref="chartCanvas"></canvas>
        </div>
    `,
    props: ['dateRange'],
    data() {
        return {
            chart: null,
            engagementData: {
                labels: [],
                datasets: [
                    {
                        label: 'Page Views',
                        data: [],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Time Spent (min)',
                        data: [],
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            }
        }
    },
    mounted() {
        this.fetchData();
        this.renderChart();
    },
    methods: {
        async fetchData() {
            try {
                const response = await fetch(`/api/analytics/summary?range=${this.dateRange}`);
                const data = await response.json();
                
                this.engagementData.labels = data.dates;
                this.engagementData.datasets[0].data = data.page_views;
                this.engagementData.datasets[1].data = data.time_spent;
                
                if (this.chart) {
                    this.chart.update();
                }
            } catch (error) {
                console.error('Error fetching engagement data:', error);
            }
        },
        renderChart() {
            const ctx = this.$refs.chartCanvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: this.engagementData,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'User Engagement Metrics'
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
    watch: {
        dateRange() {
            this.fetchData();
        }
    }
};

app.component('engagement-chart', EngagementChart);