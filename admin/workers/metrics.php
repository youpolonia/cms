<?php
/**
 * Worker Performance Metrics Dashboard
 *
 * @package CMS
 * @subpackage Admin\Workers
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use CMS\Includes\Database\Connection;
use CMS\Models\Worker;

// Initialize database connection
$db = new Connection();
$workerModel = new Worker($db);

// Get performance metrics
$metrics = [
    'workers' => $workerModel->getAll(),
    'stats' => calculateWorkerStats($db),
    'activity' => getRecentActivity($db)
];

/**
 * Calculate worker performance statistics
 */
function calculateWorkerStats(Connection $db): array
{
    $stats = [];
    
    // Success/failure rates
    $sql = "SELECT 
        worker_id,
        COUNT(*) as total_tasks,
        SUM(CASE WHEN action_type = 'success' THEN 1 ELSE 0 END) as success_count,
        SUM(CASE WHEN action_type = 'failure' THEN 1 ELSE 0 END) as failure_count,
        AVG(TIMESTAMPDIFF(SECOND, timestamp, NOW())) as avg_time_since_last_activity
    FROM worker_activity_logs
    WHERE timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY worker_id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $stats[$row['worker_id']] = [
            'success_rate' => $row['total_tasks'] > 0 ? round($row['success_count'] / $row['total_tasks'] * 100, 2) : 0,
            'failure_rate' => $row['total_tasks'] > 0 ? round($row['failure_count'] / $row['total_tasks'] * 100, 2) : 0,
            'avg_time_since_last_activity' => $row['avg_time_since_last_activity'],
            'total_tasks' => $row['total_tasks']
        ];
    }
    
    return $stats;
}

/**
 * Get recent worker activity
 */
function getRecentActivity(Connection $db): array
{
    $sql = "SELECT * FROM worker_activity_logs 
            ORDER BY timestamp DESC 
            LIMIT 50";
            
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

// Render the metrics dashboard
ob_start();
?>
<!-- Visualization Components -->
<div class="worker-metrics">
    <h2>Worker Performance Metrics</h2>
    
    <div class="metrics-grid">
        <!-- Success/Failure Rates Chart -->
        <div class="metric-card">
            <h3>Success/Failure Rates</h3>
            <canvas id="successFailureChart" width="400" height="300"></canvas>
        </div>
        
        <!-- Activity Timeline -->
        <div class="metric-card">
            <h3>Recent Activity</h3>
            <div class="activity-timeline">
                <?php foreach ($metrics['activity'] as $activity): ?>
                <div class="activity-item">
                    <span class="timestamp"><?php echo htmlspecialchars($activity['timestamp']); ?></span>
                    <span class="worker-id"><?php echo htmlspecialchars($activity['worker_id']); ?></span>
                    <span class="action-type <?php echo htmlspecialchars($activity['action_type']); ?>">
                        <?php echo htmlspecialchars($activity['action_type']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Implementation -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Success/Failure Chart
    const ctx = document.getElementById('successFailureChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($metrics['stats'])); ?>,
            datasets: [
                {
                    label: 'Success Rate %',
                    data: <?php echo json_encode(array_column($metrics['stats'], 'success_rate')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                },
                {
                    label: 'Failure Rate %',
                    data: <?php echo json_encode(array_column($metrics['stats'], 'failure_rate')); ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
});
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../admin/views/layout.php';