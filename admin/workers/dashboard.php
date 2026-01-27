<?php
/**
 * Worker Dashboard View
 */

require_once __DIR__ . '/../../models/worker.php';
// Check authentication
if (!isset($auth) || !$auth->isLoggedIn()) {
    header('Location: /auth/worker/login');
    exit;
}

// Load notification system
require_once __DIR__ . '/notifications.php';
$notificationSystem = new WorkerNotificationSystem($db);

// Get worker stats
$workerModel = new \CMS\Models\Worker($db);
$workers = $workerModel->getAll();

// Calculate stats
$totalWorkers = count($workers);
$healthyWorkers = array_filter($workers, fn($w) => $w['health_score'] > 80);
$warningWorkers = array_filter($workers, fn($w) => $w['health_score'] <= 80 && $w['health_score'] > 50);
$criticalWorkers = array_filter($workers, fn($w) => $w['health_score'] <= 50);

// Prepare recent activity (simplified - would normally come from API)
$recentActivity = array_slice($workers, 0, 10); // Last 10 workers by last_seen

// Get persistent notifications
$notifications = $notificationSystem->getForWorker($_SESSION['worker_id'], true);
$unreadCount = $notificationSystem->getUnreadCount($_SESSION['worker_id']);

// Render view
$title = 'Worker Dashboard';
ob_start();
?><div class="dashboard-container">
    <h2>Worker Dashboard</h2>
    
    <div class="stats-row">
        <div class="stat-card">
            <h3>Total Workers</h3>
            <div class="stat-value"><?php echo $totalWorkers; ?></div>
        </div>
        <div class="stat-card healthy">
            <h3>Healthy</h3>
            <div class="stat-value"><?php echo count($healthyWorkers); ?></div>
        </div>
        <div class="stat-card warning">
            <h3>Warning</h3>
            <div class="stat-value"><?php echo count($warningWorkers); ?></div>
        </div>
        <div class="stat-card critical">
            <h3>Critical</h3>
            <div class="stat-value"><?php echo count($criticalWorkers); ?></div>
        </div>
    </div>

    <div class="dashboard-columns">
        <div class="activity-feed">
            <h3>Recent Activity</h3>
            <ul>
                <?php foreach ($recentActivity as $worker): ?>
                <li>
                    <span class="worker-id"><?php echo htmlspecialchars($worker['worker_id']); ?></span>
                    <span class="activity-time">Last seen: <?php echo $worker['last_seen']; ?></span>
                    <span class="health-score health-<?php echo $worker['health_score'] > 80 ? 'good' : ($worker['health_score'] > 50 ? 'warning' : 'critical'); ?>">
                        <?php echo $worker['health_score']; ?>%
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="location.href='/admin/workers/create'">Add Worker</button>
                <button class="btn btn-secondary" onclick="location.href='/admin/workers'">View All</button>
                <button class="btn btn-warning" onclick="restartAllWorkers()">Restart All</button>
            </div>
        </div>
    </div>

    <div class="notifications-panel">
        <h3>Notifications <span class="badge"><?php echo $unreadCount; ?></span></h3>
        <?php if (empty($notifications)): ?>
            <div class="notification none">No new notifications</div>
        <?php else: ?>
            <?php foreach ($notifications as $note): ?>
                <div class="notification <?php echo $note['type']; ?>">
                    <strong><?php echo htmlspecialchars($note['title']); ?></strong>
                    <p><?php echo htmlspecialchars($note['message']); ?></p>
                    <small><?php echo date('M j, Y g:i a', strtotime($note['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    // Poll for new notifications every 30 seconds
    (function poll() {
        fetch(`/admin/workers/notifications.php?api=poll&worker_id=<?php echo $_SESSION['worker_id']; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.unread_count > 0) {
                    // Refresh notifications if new ones exist
                    location.reload();
                }
            })
            .finally(() => {
                setTimeout(poll, 30000);
            });
    })();
    </script>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}
.stats-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}
.stat-card {
    flex: 1;
    padding: 1rem;
    border-radius: 4px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.stat-card.healthy { border-left: 4px solid #2ecc71; }
.stat-card.warning { border-left: 4px solid #f39c12; }
.stat-card.critical { border-left: 4px solid #e74c3c; }
.stat-value {
    font-size: 2rem;
    font-weight: bold;
}
.dashboard-columns {
    display: flex;
    gap: 2rem;
}
.activity-feed, .quick-actions {
    flex: 1;
}
.activity-feed ul {
    list-style: none;
    padding: 0;
}
.activity-feed li {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    background: white;
    border-radius: 4px;
}
.health-score {
    float: right;
}
.health-good { color: #2ecc71; }
.health-warning { color: #f39c12; }
.health-critical { color: #e74c3c; }
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary { background: #3498db; color: white; }
.btn-secondary { background: #95a5a6; color: white; }
.btn-warning { background: #f39c12; color: white; }
.notifications-panel {
    margin-top: 2rem;
}
.notification {
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
}
.notification.none { background: #ecf0f1; }
.notification.warning { background: #fef9e7; color: #f39c12; }
.notification.critical { background: #fdedec; color: #e74c3c; }
</style>

<script>
function restartAllWorkers() {
    if (confirm('Are you sure you want to restart all workers?')) {
        // AJAX call to restart endpoint would go here
        alert('Restart command sent to all workers');
    }
}
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/layout.php';