document.addEventListener('DOMContentLoaded', function() {
    // Initialize Chart.js if not already loaded
    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = initDashboard;
        document.head.appendChild(script);
    } else {
        initDashboard();
    }

    function initDashboard() {
        // Widget loading function
        function loadWidget(widgetType) {
            fetch(`/admin/api/widgets/${widgetType}.php`)
                .then(response => response.json())
                .then(data => {
                    const contentEl = document.querySelector(`#${widgetType}-widget .widget-content`);
                    if (widgetType === 'analytics') {
                        renderAnalyticsWidget(contentEl, data);
                    }
                });
        }

        // Analytics widget rendering
        function renderAnalyticsWidget(container, data) {
            // Create widget container if it doesn't exist
            if (!container.querySelector('.analytics-widget')) {
                container.innerHTML = `<?php include __DIR__ . '/../views/analytics/widget.php'; ?>`;
            }

            // Update metrics
            container.querySelector('#page-views').textContent = data.metrics.pageViews;
            container.querySelector('#unique-visitors').textContent = data.metrics.uniqueVisitors;
            container.querySelector('#avg-time').textContent = data.metrics.avgTime + ' min';

            // Render trend chart
            const ctx = container.querySelector('#analytics-trend-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.trends.labels,
                    datasets: [{
                        label: 'Page Views',
                        data: data.trends.values,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
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

        // Load widgets initially
        loadWidget('analytics');

        // Set up refresh buttons
        document.querySelectorAll('.refresh-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const widgetType = this.dataset.widget;
                loadWidget(widgetType);
            });
        });
    }
});