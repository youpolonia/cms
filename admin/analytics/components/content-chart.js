const ContentChart = {
    template: `
        <div class="chart-container">
            <canvas ref="chartCanvas"></canvas>
        </div>
    `,
    props: ['dateRange'],
    data() {
        return {
            chart: null,
            contentData: {
                labels: ['Articles', 'Pages', 'Media', 'Custom Types'],
                datasets: [
                    {
                        label: 'Content Views',
                        data: [],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)',
                            'rgba(255, 159, 64, 0.5)',
                            'rgba(54, 162, 235, 0.5)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
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
                const response = await fetch(`/api/analytics/content?range=${this.dateRange}`);
                const data = await response.json();
                
                this.contentData.datasets[0].data = [
                    data.articles,
                    data.pages,
                    data.media,
                    data.custom_types
                ];
                
                if (this.chart) {
                    this.chart.update();
                }
            } catch (error) {
                console.error('Error fetching content data:', error);
            }
        },
        renderChart() {
            const ctx = this.$refs.chartCanvas.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: this.contentData,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Content Performance'
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

app.component('content-chart', ContentChart);