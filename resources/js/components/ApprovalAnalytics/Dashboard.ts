import Chart from 'chart.js/auto';

interface AnalyticsData {
    total_approvals: number;
    avg_approval_time: number;
    rejection_rate: number;
    pending_approvals: number;
    recent_activity: Activity[];
}

interface Activity {
    theme: string;
    time: string;
    action: string;
    user: string;
    comment?: string;
}

interface TimelineData {
    labels: string[];
    values: number[];
}

interface StepsData {
    steps: string[];
    times: number[];
}

export default class ApprovalAnalyticsDashboard {
    private timelineChart: Chart;
    private stepsChart: Chart;

    constructor() {
        this.initCharts();
        this.loadData();
        this.setupEventListeners();
    }

    async loadData(): Promise<void> {
        try {
            const [stats, timeline, steps] = await Promise.all([
                this.fetchData<AnalyticsData>('/api/theme-approvals/stats'),
                this.fetchData<TimelineData>('/api/theme-approvals/timeline'),
                this.fetchData<StepsData>('/api/theme-approvals/steps')
            ]);

            this.updateStatsCards(stats);
            this.updateTimelineChart(timeline);
            this.updateStepsChart(steps);
            this.updateRecentActivity(stats.recent_activity);
        } catch (error) {
            console.error('Error loading analytics data:', error);
        }
    }

    async fetchData<T>(endpoint: string): Promise<T> {
        const response = await fetch(endpoint);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json() as T;
    }

    updateStatsCards(data: AnalyticsData): void {
        document.getElementById('total-approvals').textContent = data.total_approvals.toString();
        document.getElementById('avg-approval-time').textContent = `${data.avg_approval_time} days`;
        document.getElementById('rejection-rate').textContent = `${data.rejection_rate}%`;
        document.getElementById('pending-approvals').textContent = data.pending_approvals.toString();
    }

    initCharts(): void {
        this.timelineChart = this.createChart(
            document.getElementById('timeline-chart') as HTMLCanvasElement,
            {
                type: 'line',
                data: { labels: [], datasets: [] },
                options: this.getChartOptions('Approval Timeline (Days)')
            }
        );

        this.stepsChart = this.createChart(
            document.getElementById('steps-breakdown-chart') as HTMLCanvasElement,
            {
                type: 'bar',
                data: { labels: [], datasets: [] },
                options: this.getChartOptions('Steps Breakdown')
            }
        );
    }

    createChart(element: HTMLCanvasElement, config: Chart.ChartConfiguration): Chart {
        return new Chart(element, config);
    }

    getChartOptions(title: string): Chart.ChartOptions {
        return {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { 
                    display: true,
                    text: title,
                    font: { size: 16 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: { beginAtZero: true }
            },
            maintainAspectRatio: false
        };
    }

    updateTimelineChart(data: TimelineData): void {
        this.timelineChart.data.labels = data.labels;
        this.timelineChart.data.datasets = [{
            label: 'Approval Time (Days)',
            data: data.values,
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            tension: 0.1,
            fill: true
        }];
        this.timelineChart.update();
    }

    updateStepsChart(data: StepsData): void {
        this.stepsChart.data.labels = data.steps;
        this.stepsChart.data.datasets = [{
            label: 'Average Time (Days)',
            data: data.times,
            backgroundColor: 'rgba(79, 70, 229, 0.7)'
        }];
        this.stepsChart.update();
    }

    updateRecentActivity(activities: Activity[]): void {
        const container = document.getElementById('recent-activity-list');
        if (container) {
            container.innerHTML = activities.map(activity => `
                <div class="p-3 border-b border-gray-200 last:border-0">
                    <div class="flex justify-between">
                        <span class="font-medium">${activity.theme}</span>
                        <span class="text-sm text-gray-500">${activity.time}</span>
                    </div>
                    <p class="text-sm mt-1">${activity.action} by ${activity.user}</p>
                    ${activity.comment ? `<p class="text-xs italic mt-1">"${activity.comment}"</p>` : ''}
                </div>
            `).join('');
        }
    }

    setupEventListeners(): void {
        document.querySelectorAll('[data-export]').forEach(btn => {
            btn.addEventListener('click', () => this.handleExport(btn.getAttribute('data-export')));
        });
    }

    async handleExport(type: string): Promise<void> {
        try {
            const response = await fetch(`/api/theme-approvals/export?type=${type}`);
            if (!response.ok) throw new Error('Export failed');
            const blob = await response.blob();
            this.downloadFile(blob, `theme-approvals-${type}-${new Date().toISOString().slice(0,10)}.csv`);
        } catch (error) {
            console.error('Export error:', error);
            alert('Failed to export data');
        }
    }

    downloadFile(blob: Blob, filename: string): void {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}
