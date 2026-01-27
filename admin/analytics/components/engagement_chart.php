<?php
if (empty($metrics['engagement'])) {
    echo '<div class="no-data">No engagement data available</div>';
    return;
}

?><div class="chart-container" style="position: relative; height:300px;">
    <canvas id="engagementChart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('engagementChart').getContext('2d');
    const chartData = {
        labels: <?= json_encode(array_column($metrics['engagement'], 'date')) ?>,
        datasets: [
            {
                type: 'bar',
                label: 'Avg. Time (min)',
                data: <?= json_encode(array_column($metrics['engagement'], 'avg_time')) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'Bounce Rate %',
                data: <?= json_encode(array_column($metrics['engagement'], 'bounce_rate')) ?>,
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                tension: 0.1,
                yAxisID: 'y1'
            }
        ]
    };

    new Chart(ctx, {
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'User Engagement Metrics'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Minutes'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Bounce Rate %'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
});
</script>
