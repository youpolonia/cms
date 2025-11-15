<?php require_once __DIR__ . '/../includes/admin_header.php'; ?>
<div class="admin-container">
    <h1>Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?= $stats['total_users'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Today</h3>
            <p><?= $stats['active_today'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Recent Logs</h3>
            <p><?= $stats['recent_logs'] ?></p>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/admin/users" class="btn btn-primary">Manage Users</a>
            <a href="/admin/roles" class="btn btn-primary">Manage Roles</a>
            <a href="/admin/logs" class="btn btn-secondary">View Audit Logs</a>
        </div>
    </div>

    <div class="recent-activity">
        <h2>Recent Activity</h2>
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentActivity as $activity): ?>
<tr>
                    <td><?= $activity['timestamp'] ?></td>
                    <td><?= $activity['username'] ?></td>
                    <td><?= $activity['action'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php';
