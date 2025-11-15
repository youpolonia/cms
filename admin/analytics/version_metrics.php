<?php
require_once __DIR__ . '/../../core/contentversion.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__.'/../../core/database.php';

// Verify admin access
if (!Auth::checkAdmin()) {
    header('Location: /admin/login.php');
    exit;
}

// Get version metrics
$db = \core\Database::connection();
$metrics = [];

// Version creation frequency (last 30 days)
$stmt = $db->prepare("SELECT 
    DATE(created_at) as day, 
    COUNT(*) as count 
    FROM content_versions 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY day 
    ORDER BY day DESC");
$stmt->execute();
$metrics['creation_frequency'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Version restore statistics
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_restores,
    COUNT(DISTINCT content_id) as unique_content_restores
    FROM version_restore_log");
$stmt->execute();
$metrics['restore_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Storage usage metrics
$stmt = $db->prepare("SELECT 
    COUNT(*) as total_versions,
    SUM(LENGTH(version_data)) as total_storage_bytes,
    AVG(LENGTH(version_data)) as avg_version_size
    FROM content_versions");
$stmt->execute();
$metrics['storage_usage'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Include header template
require_once __DIR__.'/../../templates/admin_header.php';

?><div class="container">
    <h1>Version Control Metrics</h1>
    
    <div class="metric-section">
        <h2>Version Creation Frequency (Last 30 Days)</h2>
        <div class="chart-container">
            <canvas id="creationChart"></canvas>
        </div>
    </div>

    <div class="metric-section">
        <h2>Restore Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Restores</h3>
                <p><?= $metrics['restore_stats']['total_restores'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Unique Content Restored</h3>
                <p><?= $metrics['restore_stats']['unique_content_restores'] ?></p>
            </div>
        </div>
    </div>

    <div class="metric-section">
        <h2>Storage Usage</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Versions</h3>
                <p><?= $metrics['storage_usage']['total_versions'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Storage</h3>
                <p><?= formatBytes($metrics['storage_usage']['total_storage_bytes']) ?></p>
            </div>
            <div class="stat-card">
                <h3>Avg Version Size</h3>
                <p><?= formatBytes($metrics['storage_usage']['avg_version_size']) ?></p>
            </div>
        </div>
    </div>
</div>

<script>
// Chart.js initialization for creation frequency
const ctx = document.getElementById('creationChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($metrics['creation_frequency'], 'day')) ?>,
        datasets: [{
            label: 'Versions Created',
            data: <?= json_encode(array_column($metrics['creation_frequency'], 'count')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    }
});
</script>

<?php
// Include footer template
require_once __DIR__.'/../../templates/admin_footer.php';
