const ComparisonChart = {
    template: `
        <div class="chart-container">
            <canvas ref="chart"></canvas>
        </div>
    `,
    data() {
        return {
            chart: null
        }
    },
    methods: {
        renderChart(data) {
            if (this.chart) {
                this.chart.destroy();
            }

            const ctx = this.$refs.chart.getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Engagement', 'Content'],
                    datasets: [
                        {
                            label: 'Current Period',
                            data: [data.current.engagement, data.current.content],
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Previous Period',
                            data: [data.previous.engagement, data.previous.content],
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
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
        },
        async loadData() {
            try {
                const response = await fetch('/api/analytics/comparison');
                if (!response.ok) throw new Error('Failed to fetch comparison data');
                
                const data = await response.json();
                this.renderChart({
                    current: {
                        engagement: data.current.engagement_score,
                        content: data.current.content_score
                    },
                    previous: {
                        engagement: data.previous.engagement_score,
                        content: data.previous.content_score
                    }
                });
            } catch (error) {
                console.error('Comparison data error:', error);
                // Fallback to mock data if API fails
                import('../../test/mock-data.js').then(({ mockAnalyticsData }) => {
                    this.renderChart(mockAnalyticsData.basic);
                }).catch(() => {
                    // Ultimate fallback if mock data fails
                    this.renderChart({
                        current: { engagement: 0, content: 0 },
                        previous: { engagement: 0, content: 0 }
                    });
                });
            }
        }
    },
    mounted() {
        this.loadData();
    }
};

app.component('comparison-chart', ComparisonChart);