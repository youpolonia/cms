<?php
/**
 * Security Dashboard View
 * Displays security overview and statistics
 */

if (!defined('ADMIN_SECURITY_INIT')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

$stats = $data['stats'] ?? [];
$logStats = $data['log_stats'] ?? [];
$settings = $data['settings'] ?? [];
$blockedIpsCount = $data['blocked_ips_count'] ?? 0;

// Helper function for escaping
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-shield-alt"></i> Security Dashboard</h1>
        <div>
            <a href="?action=logs" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> View Logs
            </a>
            <a href="?action=settings" class="btn btn-outline-secondary">
                <i class="fas fa-cog"></i> Settings
            </a>
            <button type="button" class="btn btn-primary" onclick="runAudit()">
                <i class="fas fa-search"></i> Run Audit
            </button>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo esc($stats['login_stats_24h']['successful'] ?? 0); ?></h4>
                            <small>Successful Logins (24h)</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-sign-in-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo esc($stats['login_stats_24h']['failed'] ?? 0); ?></h4>
                            <small>Failed Logins (24h)</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo esc($blockedIpsCount); ?></h4>
                            <small>Blocked IPs</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-ban fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="?action=blocked_ips" class="text-dark small">
                        <i class="fas fa-arrow-right"></i> Manage
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo esc($stats['active_sessions'] ?? 0); ?></h4>
                            <small>Active Sessions</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Events by Severity -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Events by Severity (24h)</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="p-2 bg-danger text-white rounded mb-2">
                                <h4 class="mb-0"><?php echo esc($stats['events_by_severity']['critical'] ?? 0); ?></h4>
                            </div>
                            <small>Critical</small>
                        </div>
                        <div class="col-3">
                            <div class="p-2 bg-warning text-dark rounded mb-2">
                                <h4 class="mb-0"><?php echo esc($stats['events_by_severity']['high'] ?? 0); ?></h4>
                            </div>
                            <small>High</small>
                        </div>
                        <div class="col-3">
                            <div class="p-2 bg-info text-white rounded mb-2">
                                <h4 class="mb-0"><?php echo esc($stats['events_by_severity']['medium'] ?? 0); ?></h4>
                            </div>
                            <small>Medium</small>
                        </div>
                        <div class="col-3">
                            <div class="p-2 bg-secondary text-white rounded mb-2">
                                <h4 class="mb-0"><?php echo esc($stats['events_by_severity']['low'] ?? 0); ?></h4>
                            </div>
                            <small>Low</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Top Event Types (7 days)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($logStats['by_type'])): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach (array_slice($logStats['by_type'], 0, 5) as $type): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?php echo esc($type['event_type']); ?></span>
                                    <span class="badge bg-primary"><?php echo esc($type['count']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-0">No events recorded</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Critical Events -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Recent Critical/High Severity Events</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($stats['critical_events'])): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['critical_events'] as $event): ?>
                                <tr>
                                    <td><?php echo esc($event['created_at']); ?></td>
                                    <td><?php echo esc($event['event_type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['severity'] === 'critical' ? 'danger' : 'warning'; ?>">
                                            <?php echo esc($event['severity']); ?>
                                        </span>
                                    </td>
                                    <td><code><?php echo esc($event['ip_address']); ?></code></td>
                                    <td><?php echo esc(substr($event['details'] ?? '', 0, 100)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-success mb-0">
                    <i class="fas fa-check-circle"></i> No critical or high severity events in the last 7 days
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function runAudit() {
    if (!confirm('Run a security audit now?')) return;

    fetch('?action=run_audit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.content || '')
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Audit complete! Check the logs for results.');
            location.reload();
        } else {
            alert('Audit failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error running audit: ' + error.message);
    });
}
</script>
