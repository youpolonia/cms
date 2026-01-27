<?php
/**
 * Security Logs View
 * Displays security event logs with filtering
 */

if (!defined('ADMIN_SECURITY_INIT')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

$logs = $data['logs'] ?? [];
$pagination = $data['pagination'] ?? [];
$filters = $data['filters'] ?? [];
$eventTypes = $data['event_types'] ?? [];
$severities = $data['severities'] ?? ['low', 'medium', 'high', 'critical'];

// Helper function for escaping
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

$severityClasses = [
    'critical' => 'danger',
    'high' => 'warning',
    'medium' => 'info',
    'low' => 'secondary'
];
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-list-alt"></i> Security Logs</h1>
        <div>
            <a href="?action=dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="?action=export_logs&<?php echo http_build_query($filters); ?>" class="btn btn-outline-primary">
                <i class="fas fa-download"></i> Export
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="action" value="logs">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?php echo esc($type); ?>" <?php echo ($filters['event_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                    <?php echo esc($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Severity</label>
                        <select name="severity" class="form-select form-select-sm">
                            <option value="">All Severities</option>
                            <?php foreach ($severities as $sev): ?>
                                <option value="<?php echo esc($sev); ?>" <?php echo ($filters['severity'] ?? '') === $sev ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(esc($sev)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control form-control-sm"
                               value="<?php echo esc($filters['ip_address'] ?? ''); ?>"
                               placeholder="e.g. 192.168.1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="datetime-local" name="date_from" class="form-control form-control-sm"
                               value="<?php echo esc($filters['date_from'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="datetime-local" name="date_to" class="form-control form-control-sm"
                               value="<?php echo esc($filters['date_to'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="?action=logs" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">
                Showing <?php echo count($logs); ?> of <?php echo esc($pagination['total'] ?? 0); ?> records
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Time</th>
                            <th>Event Type</th>
                            <th>Severity</th>
                            <th>User ID</th>
                            <th>IP Address</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No logs found matching your criteria</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo esc($log['id']); ?></td>
                                    <td>
                                        <small><?php echo esc($log['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <code><?php echo esc($log['event_type']); ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $severityClasses[$log['severity']] ?? 'secondary'; ?>">
                                            <?php echo esc($log['severity']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['user_id'] ? esc($log['user_id']) : '<span class="text-muted">-</span>'; ?></td>
                                    <td>
                                        <code><?php echo esc($log['ip_address']); ?></code>
                                        <?php if (!empty($log['ip_address']) && $log['ip_address'] !== '127.0.0.1'): ?>
                                            <a href="?action=blocked_ips&block_ip=<?php echo urlencode($log['ip_address']); ?>"
                                               class="btn btn-link btn-sm p-0 text-danger" title="Block this IP">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($log['details'])): ?>
                                            <span title="<?php echo esc($log['details']); ?>">
                                                <?php echo esc(substr($log['details'], 0, 50)); ?>
                                                <?php if (strlen($log['details']) > 50): ?>...<?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="showLogDetails(<?php echo esc($log['id']); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
            <div class="card-footer">
                <nav aria-label="Log pagination">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php
                        $currentPage = $pagination['page'] ?? 1;
                        $totalPages = $pagination['total_pages'] ?? 1;

                        // Build query string without page
                        $queryParams = $filters;
                        $queryParams['action'] = 'logs';
                        ?>
                        <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $currentPage - 1])); ?>">
                                &laquo; Previous
                            </a>
                        </li>

                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])); ?>">
                                Next &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Store logs data for modal display
const logsData = <?php echo json_encode($logs); ?>;

function showLogDetails(logId) {
    const log = logsData.find(l => l.id == logId);
    if (!log) {
        alert('Log not found');
        return;
    }

    let metadata = '';
    if (log.metadata) {
        try {
            const parsed = typeof log.metadata === 'string' ? JSON.parse(log.metadata) : log.metadata;
            metadata = JSON.stringify(parsed, null, 2);
        } catch (e) {
            metadata = log.metadata;
        }
    }

    const content = `
        <dl class="row">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">${log.id}</dd>

            <dt class="col-sm-3">Event Type</dt>
            <dd class="col-sm-9"><code>${escapeHtml(log.event_type)}</code></dd>

            <dt class="col-sm-3">Severity</dt>
            <dd class="col-sm-9">${escapeHtml(log.severity)}</dd>

            <dt class="col-sm-3">User ID</dt>
            <dd class="col-sm-9">${log.user_id || '<em>None</em>'}</dd>

            <dt class="col-sm-3">IP Address</dt>
            <dd class="col-sm-9"><code>${escapeHtml(log.ip_address)}</code></dd>

            <dt class="col-sm-3">User Agent</dt>
            <dd class="col-sm-9"><small>${escapeHtml(log.user_agent || 'N/A')}</small></dd>

            <dt class="col-sm-3">Created At</dt>
            <dd class="col-sm-9">${escapeHtml(log.created_at)}</dd>

            <dt class="col-sm-3">Details</dt>
            <dd class="col-sm-9"><pre class="bg-light p-2 rounded">${escapeHtml(log.details || 'No details')}</pre></dd>

            ${metadata ? `
            <dt class="col-sm-3">Metadata</dt>
            <dd class="col-sm-9"><pre class="bg-light p-2 rounded">${escapeHtml(metadata)}</pre></dd>
            ` : ''}
        </dl>
    `;

    document.getElementById('logDetailsContent').innerHTML = content;

    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    modal.show();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
