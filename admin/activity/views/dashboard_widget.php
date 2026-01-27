<?php
/**
 * Activity Log Dashboard Widget
 * Shows recent activities and metrics
 */

$activityModel = new ClientActivity();

// Get recent activities
$recentActivities = $activityModel->getRecentActivities(5);

// Calculate metrics
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('-7 days'));

$todayCount = count($activityModel->getActivities(null, 1000, $today));
$weekCount = count($activityModel->getActivities(null, 1000, $weekStart));

?><div class="dashboard-widget activity-widget">
    <h3>Recent Activity</h3>
    
    <div class="activity-metrics">
        <div class="metric">
            <span class="value"><?= $todayCount ?></span>
            <span class="label">Today</span>
        </div>
        <div class="metric">
            <span class="value"><?= $weekCount ?></span>
            <span class="label">This Week</span>
        </div>
    </div>

    <ul class="activity-list">
        <?php foreach ($recentActivities as $activity): ?>
            <li>
                <span class="activity-time"><?= date('H:i', strtotime($activity['created_at'])) ?></span>
                <span class="activity-details">
                    <?= htmlspecialchars($activity['client_name'] ?: 'System') ?>: 
                    <?= htmlspecialchars($activity['activity_type']) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="widget-footer">
        <a href="/admin/activity" class="view-all">View Full Activity Log</a>
    </div>
</div>

<style>
.activity-widget {
    background: #fff;
    border-radius: 4px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.activity-metrics {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}
.metric {
    text-align: center;
    flex: 1;
}
.metric .value {
    font-size: 24px;
    font-weight: bold;
    display: block;
}
.metric .label {
    font-size: 12px;
    color: #666;
}
.activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.activity-list li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.activity-list li:last-child {
    border-bottom: none;
}
.activity-time {
    color: #666;
    font-size: 12px;
    margin-right: 10px;
}
.widget-footer {
    margin-top: 15px;
    text-align: right;
}
.view-all {
    color: #0066cc;
    text-decoration: none;
    font-size: 13px;
}
.view-all:hover {
    text-decoration: underline;
}
</style>
