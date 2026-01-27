<?php
/**
 * Blocked IPs View
 * Displays and manages blocked IP addresses
 */

if (!defined('ADMIN_SECURITY_INIT')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

$blockedIps = $data['blocked_ips'] ?? [];
$includeExpired = $data['include_expired'] ?? false;
$message = $data['message'] ?? null;
$error = $data['error'] ?? null;

// Check if we should pre-fill an IP from query params
$prefillIp = $_GET['block_ip'] ?? '';

// Helper function for escaping
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-ban"></i> Blocked IP Addresses</h1>
        <div>
            <a href="?action=dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo esc($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo esc($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Block IP Form -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Block New IP</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?action=block_ip">
                        <?php csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">IP Address</label>
                            <input type="text" name="ip_address" class="form-control"
                                   value="<?php echo esc($prefillIp); ?>"
                                   placeholder="e.g. 192.168.1.100" required
                                   pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$">
                            <small class="text-muted">Enter a valid IPv4 address</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control"
                                   placeholder="e.g. Brute force attack">
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="permanent" class="form-check-input"
                                       id="permanent" onchange="toggleDuration()">
                                <label class="form-check-label" for="permanent">
                                    Permanent Block
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="durationField">
                            <label class="form-label">Block Duration</label>
                            <select name="duration" class="form-select">
                                <option value="3600">1 Hour</option>
                                <option value="86400" selected>24 Hours</option>
                                <option value="604800">1 Week</option>
                                <option value="2592000">30 Days</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-ban"></i> Block IP
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Blocked IPs List -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i>
                        Blocked IPs (<?php echo count($blockedIps); ?>)
                    </h5>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="showExpired"
                               <?php echo $includeExpired ? 'checked' : ''; ?>
                               onchange="toggleExpired()">
                        <label class="form-check-label" for="showExpired">
                            Show Expired
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Reason</th>
                                    <th>Blocked At</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($blockedIps)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                            <p class="mb-0">No blocked IP addresses</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($blockedIps as $block): ?>
                                        <?php
                                        $isExpired = !$block['is_permanent'] &&
                                                     $block['expires_at'] &&
                                                     strtotime($block['expires_at']) < time();
                                        ?>
                                        <tr class="<?php echo $isExpired ? 'table-secondary' : ''; ?>">
                                            <td>
                                                <code class="fs-6"><?php echo esc($block['ip_address']); ?></code>
                                            </td>
                                            <td><?php echo esc($block['reason'] ?? '-'); ?></td>
                                            <td>
                                                <small><?php echo esc($block['blocked_at']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($block['is_permanent']): ?>
                                                    <span class="badge bg-danger">Permanent</span>
                                                <?php elseif ($block['expires_at']): ?>
                                                    <small><?php echo esc($block['expires_at']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($isExpired): ?>
                                                    <span class="badge bg-secondary">Expired</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="?action=unblock_ip"
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Unblock this IP address?');">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="ip_address"
                                                           value="<?php echo esc($block['ip_address']); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                                            title="Unblock">
                                                        <i class="fas fa-unlock"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDuration() {
    const permanent = document.getElementById('permanent').checked;
    document.getElementById('durationField').style.display = permanent ? 'none' : 'block';
}

function toggleExpired() {
    const showExpired = document.getElementById('showExpired').checked;
    window.location.href = '?action=blocked_ips' + (showExpired ? '&include_expired=1' : '');
}
</script>
