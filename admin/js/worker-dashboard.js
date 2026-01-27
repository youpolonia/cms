class WorkerDashboard {
    constructor(apiBaseUrl = '/api/v1/workers') {
        this.apiBaseUrl = apiBaseUrl;
        this.initCharts();
        this.loadData();
        setInterval(() => this.loadData(), 10000); // Refresh every 10 seconds
    }

    initCharts() {
        this.cpuChart = new Chart(
            document.getElementById('cpuChart'),
            this.getChartConfig('CPU Usage %', 'rgba(54, 162, 235, 0.5)')
        );

        this.memoryChart = new Chart(
            document.getElementById('memoryChart'),
            this.getChartConfig('Memory Usage %', 'rgba(255, 99, 132, 0.5)')
        );

        this.workersChart = new Chart(
            document.getElementById('workersChart'),
            this.getChartConfig('Active Workers', 'rgba(75, 192, 192, 0.5)')
        );
    }

    getChartConfig(label, backgroundColor) {
        return {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: label,
                    data: [],
                    backgroundColor: backgroundColor,
                    borderColor: backgroundColor.replace('0.5', '1'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        };
    }

    async loadData() {
        try {
            const [metrics, scaling] = await Promise.all([
                this.fetchData(`${this.apiBaseUrl}/metrics`),
                this.fetchData(`${this.apiBaseUrl}/scaling-recommendations`)
            ]);

            this.updateCharts(metrics.data);
            this.updateScalingRecommendations(scaling.actions);
            this.updateWorkerStatus(metrics.data);
        } catch (error) {
            console.error('Dashboard update failed:', error);
        }
    }

    async fetchData(url) {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`API request failed: ${response.status}`);
        return await response.json();
    }

    updateCharts(metrics) {
        const timestamps = metrics.map(m => new Date(m.timestamp).toLocaleTimeString());
        const cpuData = metrics.map(m => m.cpu_usage);
        const memoryData = metrics.map(m => m.memory_usage);
        const workerCount = metrics.reduce((acc, m) => {
            acc[m.worker_id] = true;
            return acc;
        }, {});

        this.updateChart(this.cpuChart, timestamps, cpuData);
        this.updateChart(this.memoryChart, timestamps, memoryData);
        this.updateChart(this.workersChart, timestamps, 
            Array(timestamps.length).fill(Object.keys(workerCount).length));
    }

    updateChart(chart, labels, data) {
        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.update();
    }

    updateScalingRecommendations(actions) {
        const container = document.getElementById('scalingActions');
        container.innerHTML = actions.length 
            ? actions.map(a => `
                <div class="alert alert-${a.action === 'scale_up' ? 'warning' : 'info'}">
                    ${a.action.toUpperCase()}: ${a.reason} (${a.worker_count} workers)
                </div>
              `).join('')
            : '<div class="alert alert-success">No scaling actions recommended</div>';
    }

    updateWorkerStatus(metrics) {
        const container = document.getElementById('workerStatus');
        const workers = metrics.reduce((acc, m) => {
            if (!acc[m.worker_id]) {
                acc[m.worker_id] = m;
            }
            return acc;
        }, {});

        container.innerHTML = Object.values(workers).map(w => `
            <div class="worker-card">
                <h4>Worker ${w.worker_id}</h4>
                <p>CPU: ${w.cpu_usage}%</p>
                <p>Memory: ${w.memory_usage}%</p>
                <p>Jobs: ${w.jobs_processed}</p>
                <p>Last seen: ${new Date(w.timestamp).toLocaleTimeString()}</p>
            </div>
        `).join('');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new WorkerDashboard();
});