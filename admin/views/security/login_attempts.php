<?php
/**
 * Login Attempts View
 * Displays login attempt history
 */

if (!defined('ADMIN_SECURITY_INIT')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

$attempts = $data['attempts'] ?? [];
$filters = $data['filters'] ?? [];

// Helper function for escaping
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-sign-in-alt"></i> Login Attempts</h1>
        <div>
            <a href="?action=dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="action" value="login_attempts">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="success" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" <?php echo ($filters['success'] ?? '') === true ? 'selected' : ''; ?>>Successful</option>
                            <option value="0" <?php echo isset($filters['success']) && $filters['success'] === false ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control form-control-sm"
                               value="<?php echo esc($filters['ip_address'] ?? ''); ?>"
                               placeholder="e.g. 192.168.1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control form-control-sm"
                               value="<?php echo esc($filters['username'] ?? ''); ?>"
                               placeholder="Search username">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="?action=login_attempts" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Attempts Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Login Attempts (<?php echo count($attempts); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Time</th>
                            <th>Username</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Failure Reason</th>
                            <th>User Agent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attempts)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No login attempts found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attempts as $attempt): ?>
                                <tr>
                                    <td>
                                        <small><?php echo esc($attempt['attempted_at']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($attempt['username']): ?>
                                            <code><?php echo esc($attempt['username']); ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo esc($attempt['ip_address']); ?></code>
                                    </td>
                                    <td>
                                        <?php if ($attempt['success']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Success
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> Failed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo esc($attempt['failure_reason'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="<?php echo esc($attempt['user_agent'] ?? ''); ?>">
                                            <?php echo esc(substr($attempt['user_agent'] ?? '', 0, 40)); ?>
                                            <?php if (strlen($attempt['user_agent'] ?? '') > 40): ?>...<?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (!$attempt['success'] && $attempt['ip_address'] !== '127.0.0.1'): ?>
                                            <a href="?action=blocked_ips&block_ip=<?php echo urlencode($attempt['ip_address']); ?>"
                                               class="btn btn-sm btn-outline-danger" title="Block this IP">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">
                        <?php echo count(array_filter($attempts, fn($a) => $a['success'])); ?>
                    </h3>
                    <small>Successful Logins</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">
                        <?php echo count(array_filter($attempts, fn($a) => !$a['success'])); ?>
                    </h3>
                    <small>Failed Attempts</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">
                        <?php echo count(array_unique(array_column($attempts, 'ip_address'))); ?>
                    </h3>
                    <small>Unique IP Addresses</small>
                </div>
            </div>
        </div>
    </div>
</div>
