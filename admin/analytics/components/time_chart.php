<?php
if (empty($metrics['views'])) {
    echo '<div class="no-data">No view data available for selected period</div>';
    return;
}

?><div class="chart-container" style="position: relative; height:300px;">
    <canvas id="viewsChart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('viewsChart').getContext('2d');
    const chartData = {
        labels: <?= json_encode(array_column($metrics['views'], 'date')) ?>,
        datasets: [{
            label: 'Page Views',
            data: <?= json_encode(array_column($metrics['views'], 'count')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.1
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Page Views Over Time'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Views'
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

    // Handle window resize
    window.addEventListener('resize', function() {
        chart.resize();
    });
});
</script>
