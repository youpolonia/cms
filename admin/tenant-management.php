<?php
/**
 * Tenant Management Panel
 * Full CRUD for multi-tenant CMS administration
 */

if (!defined('CMS_ROOT')) {
    $cmsRoot = realpath(__DIR__ . '/..');
    if ($cmsRoot === false) {
        die('Cannot determine CMS_ROOT');
    }
    define('CMS_ROOT', $cmsRoot);
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/database.php';

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

cms_require_admin_role();

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// Tenant storage
define('TENANTS_FILE', CMS_ROOT . '/cms_storage/tenants.json');

/**
 * Ensure tenants file exists
 */
function tenants_ensure_storage(): bool
{
    $dir = dirname(TENANTS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!file_exists(TENANTS_FILE)) {
        file_put_contents(TENANTS_FILE, json_encode([], JSON_PRETTY_PRINT));
    }
    return true;
}

/**
 * Load all tenants
 */
function tenants_load(): array
{
    tenants_ensure_storage();
    $data = json_decode(file_get_contents(TENANTS_FILE), true);
    return is_array($data) ? $data : [];
}

/**
 * Save all tenants
 */
function tenants_save(array $tenants): bool
{
    tenants_ensure_storage();
    return file_put_contents(TENANTS_FILE, json_encode($tenants, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

/**
 * Get single tenant by ID
 */
function tenant_get(string $id): ?array
{
    $tenants = tenants_load();
    return $tenants[$id] ?? null;
}

/**
 * Create new tenant
 */
function tenant_create(array $data): array
{
    $tenants = tenants_load();

    $id = 'tenant_' . uniqid();

    // Validate required fields
    if (empty(trim($data['name'] ?? ''))) {
        return ['ok' => false, 'error' => 'Tenant name is required'];
    }

    // Check for duplicate slug
    $slug = $data['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['name']));
    foreach ($tenants as $t) {
        if (($t['slug'] ?? '') === $slug) {
            return ['ok' => false, 'error' => 'Slug already exists'];
        }
    }

    $tenant = [
        'id' => $id,
        'name' => trim($data['name']),
        'slug' => $slug,
        'domain' => trim($data['domain'] ?? ''),
        'status' => $data['status'] ?? 'active',
        'plan' => $data['plan'] ?? 'basic',
        'quotas' => [
            'storage_mb' => (int)($data['quota_storage'] ?? 500),
            'pages' => (int)($data['quota_pages'] ?? 50),
            'users' => (int)($data['quota_users'] ?? 5),
            'bandwidth_gb' => (int)($data['quota_bandwidth'] ?? 10),
        ],
        'usage' => [
            'storage_mb' => 0,
            'pages' => 0,
            'users' => 1,
            'bandwidth_gb' => 0,
        ],
        'settings' => [
            'custom_domain' => !empty($data['domain']),
            'ssl_enabled' => true,
            'maintenance_mode' => false,
        ],
        'contact_email' => trim($data['contact_email'] ?? ''),
        'created_at' => gmdate('Y-m-d H:i:s'),
        'updated_at' => gmdate('Y-m-d H:i:s'),
    ];

    $tenants[$id] = $tenant;

    if (!tenants_save($tenants)) {
        return ['ok' => false, 'error' => 'Failed to save tenant'];
    }

    return ['ok' => true, 'id' => $id, 'tenant' => $tenant];
}

/**
 * Update tenant
 */
function tenant_update(string $id, array $data): array
{
    $tenants = tenants_load();

    if (!isset($tenants[$id])) {
        return ['ok' => false, 'error' => 'Tenant not found'];
    }

    $tenant = $tenants[$id];

    // Update fields
    if (isset($data['name'])) {
        $tenant['name'] = trim($data['name']);
    }
    if (isset($data['domain'])) {
        $tenant['domain'] = trim($data['domain']);
        $tenant['settings']['custom_domain'] = !empty($data['domain']);
    }
    if (isset($data['status'])) {
        $tenant['status'] = $data['status'];
    }
    if (isset($data['plan'])) {
        $tenant['plan'] = $data['plan'];
    }
    if (isset($data['contact_email'])) {
        $tenant['contact_email'] = trim($data['contact_email']);
    }

    // Update quotas
    if (isset($data['quota_storage'])) {
        $tenant['quotas']['storage_mb'] = (int)$data['quota_storage'];
    }
    if (isset($data['quota_pages'])) {
        $tenant['quotas']['pages'] = (int)$data['quota_pages'];
    }
    if (isset($data['quota_users'])) {
        $tenant['quotas']['users'] = (int)$data['quota_users'];
    }
    if (isset($data['quota_bandwidth'])) {
        $tenant['quotas']['bandwidth_gb'] = (int)$data['quota_bandwidth'];
    }

    // Update settings
    if (isset($data['maintenance_mode'])) {
        $tenant['settings']['maintenance_mode'] = (bool)$data['maintenance_mode'];
    }

    $tenant['updated_at'] = gmdate('Y-m-d H:i:s');

    $tenants[$id] = $tenant;

    if (!tenants_save($tenants)) {
        return ['ok' => false, 'error' => 'Failed to save tenant'];
    }

    return ['ok' => true, 'tenant' => $tenant];
}

/**
 * Delete tenant
 */
function tenant_delete(string $id): array
{
    $tenants = tenants_load();

    if (!isset($tenants[$id])) {
        return ['ok' => false, 'error' => 'Tenant not found'];
    }

    unset($tenants[$id]);

    if (!tenants_save($tenants)) {
        return ['ok' => false, 'error' => 'Failed to delete tenant'];
    }

    return ['ok' => true];
}

/**
 * Toggle tenant status
 */
function tenant_toggle_status(string $id): array
{
    $tenants = tenants_load();

    if (!isset($tenants[$id])) {
        return ['ok' => false, 'error' => 'Tenant not found'];
    }

    $currentStatus = $tenants[$id]['status'] ?? 'active';
    $newStatus = $currentStatus === 'active' ? 'suspended' : 'active';

    $tenants[$id]['status'] = $newStatus;
    $tenants[$id]['updated_at'] = gmdate('Y-m-d H:i:s');

    if (!tenants_save($tenants)) {
        return ['ok' => false, 'error' => 'Failed to update status'];
    }

    return ['ok' => true, 'new_status' => $newStatus];
}

/**
 * Get tenant statistics
 */
function tenants_get_stats(): array
{
    $tenants = tenants_load();

    $stats = [
        'total' => count($tenants),
        'active' => 0,
        'suspended' => 0,
        'by_plan' => [],
        'total_storage_used' => 0,
        'total_pages' => 0,
    ];

    foreach ($tenants as $t) {
        if (($t['status'] ?? 'active') === 'active') {
            $stats['active']++;
        } else {
            $stats['suspended']++;
        }

        $plan = $t['plan'] ?? 'basic';
        $stats['by_plan'][$plan] = ($stats['by_plan'][$plan] ?? 0) + 1;

        $stats['total_storage_used'] += ($t['usage']['storage_mb'] ?? 0);
        $stats['total_pages'] += ($t['usage']['pages'] ?? 0);
    }

    return $stats;
}

// Available plans
$plans = [
    'basic' => ['label' => 'Basic', 'color' => 'secondary'],
    'standard' => ['label' => 'Standard', 'color' => 'primary'],
    'premium' => ['label' => 'Premium', 'color' => 'warning'],
    'enterprise' => ['label' => 'Enterprise', 'color' => 'success'],
];

$message = '';
$messageType = 'info';
$editTenant = null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $result = tenant_create($_POST);
            if ($result['ok']) {
                $message = 'Tenant created successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error: ' . ($result['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;

        case 'update':
            $id = $_POST['tenant_id'] ?? '';
            $result = tenant_update($id, $_POST);
            if ($result['ok']) {
                $message = 'Tenant updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error: ' . ($result['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;

        case 'delete':
            $id = $_POST['tenant_id'] ?? '';
            $result = tenant_delete($id);
            if ($result['ok']) {
                $message = 'Tenant deleted.';
                $messageType = 'success';
            } else {
                $message = 'Error: ' . ($result['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;

        case 'toggle_status':
            $id = $_POST['tenant_id'] ?? '';
            $result = tenant_toggle_status($id);
            if ($result['ok']) {
                $message = 'Status changed to: ' . $result['new_status'];
                $messageType = 'success';
            } else {
                $message = 'Error: ' . ($result['error'] ?? 'Unknown error');
                $messageType = 'danger';
            }
            break;
    }
}

// Handle edit mode
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editTenant = tenant_get($_GET['edit']);
}

// Load data
$tenants = tenants_load();
$stats = tenants_get_stats();

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <div>
                <h1 class="mb-0">Tenant Management</h1>
                <p class="text-muted mb-0">Manage multi-tenant CMS instances</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                + Add Tenant
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-4"><?= $stats['total'] ?></div>
                        <small class="text-muted">Total Tenants</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <div class="display-4 text-success"><?= $stats['active'] ?></div>
                        <small class="text-muted">Active</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-danger">
                    <div class="card-body text-center">
                        <div class="display-4 text-danger"><?= $stats['suspended'] ?></div>
                        <small class="text-muted">Suspended</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="display-4"><?= number_format($stats['total_storage_used']) ?></div>
                        <small class="text-muted">Storage Used (MB)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <?php if ($editTenant): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Edit Tenant: <?= esc($editTenant['name']) ?></h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="tenant_id" value="<?= esc($editTenant['id']) ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tenant Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= esc($editTenant['name']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Custom Domain</label>
                            <input type="text" name="domain" class="form-control"
                                   value="<?= esc($editTenant['domain'] ?? '') ?>"
                                   placeholder="e.g., client.example.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Plan</label>
                            <select name="plan" class="form-select">
                                <?php foreach ($plans as $key => $plan): ?>
                                    <option value="<?= $key ?>" <?= ($editTenant['plan'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= esc($plan['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($editTenant['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= ($editTenant['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control"
                                   value="<?= esc($editTenant['contact_email'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="mt-3">Quotas</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Storage (MB)</label>
                            <input type="number" name="quota_storage" class="form-control"
                                   value="<?= esc($editTenant['quotas']['storage_mb'] ?? 500) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Pages</label>
                            <input type="number" name="quota_pages" class="form-control"
                                   value="<?= esc($editTenant['quotas']['pages'] ?? 50) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Users</label>
                            <input type="number" name="quota_users" class="form-control"
                                   value="<?= esc($editTenant['quotas']['users'] ?? 5) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bandwidth (GB)</label>
                            <input type="number" name="quota_bandwidth" class="form-control"
                                   value="<?= esc($editTenant['quotas']['bandwidth_gb'] ?? 10) ?>">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode"
                               <?= ($editTenant['settings']['maintenance_mode'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="maintenance_mode">
                            Maintenance Mode
                        </label>
                    </div>

                    <button type="submit" class="btn btn-warning">Update Tenant</button>
                    <a href="?" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tenants List -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">All Tenants</h5>
            </div>
            <div class="card-body">
                <?php if (empty($tenants)): ?>
                    <div class="alert alert-info mb-0">
                        No tenants yet. Click "Add Tenant" to create your first tenant.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Usage</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tenants as $t): ?>
                                    <?php
                                    $statusColor = ($t['status'] ?? 'active') === 'active' ? 'success' : 'danger';
                                    $planInfo = $plans[$t['plan'] ?? 'basic'] ?? $plans['basic'];
                                    $storagePercent = ($t['quotas']['storage_mb'] ?? 1) > 0
                                        ? round((($t['usage']['storage_mb'] ?? 0) / $t['quotas']['storage_mb']) * 100)
                                        : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($t['name']) ?></strong>
                                            <br><small class="text-muted"><?= esc($t['slug'] ?? '') ?></small>
                                            <?php if (!empty($t['domain'])): ?>
                                                <br><small class="text-primary"><?= esc($t['domain']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $planInfo['color'] ?>"><?= esc($planInfo['label']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($t['status'] ?? 'active') ?></span>
                                            <?php if ($t['settings']['maintenance_mode'] ?? false): ?>
                                                <br><small class="text-warning">Maintenance</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small">
                                            <div class="mb-1">
                                                Storage: <?= $t['usage']['storage_mb'] ?? 0 ?>/<?= $t['quotas']['storage_mb'] ?? 0 ?> MB
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar bg-<?= $storagePercent > 80 ? 'danger' : 'primary' ?>"
                                                         style="width: <?= min(100, $storagePercent) ?>%"></div>
                                                </div>
                                            </div>
                                            Pages: <?= $t['usage']['pages'] ?? 0 ?>/<?= $t['quotas']['pages'] ?? 0 ?> |
                                            Users: <?= $t['usage']['users'] ?? 0 ?>/<?= $t['quotas']['users'] ?? 0 ?>
                                        </td>
                                        <td class="small"><?= esc($t['created_at'] ?? '') ?></td>
                                        <td>
                                            <a href="?edit=<?= esc($t['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="tenant_id" value="<?= esc($t['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-<?= $statusColor ?>">
                                                    <?= ($t['status'] ?? 'active') === 'active' ? 'Suspend' : 'Activate' ?>
                                                </button>
                                            </form>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this tenant? This cannot be undone!');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="tenant_id" value="<?= esc($t['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Tenant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tenant Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="e.g., Acme Corporation">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug (auto-generated if empty)</label>
                            <input type="text" name="slug" class="form-control"
                                   placeholder="e.g., acme-corp">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Custom Domain (optional)</label>
                            <input type="text" name="domain" class="form-control"
                                   placeholder="e.g., cms.acme.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control"
                                   placeholder="admin@acme.com">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plan</label>
                            <select name="plan" class="form-select">
                                <?php foreach ($plans as $key => $plan): ?>
                                    <option value="<?= $key ?>"><?= esc($plan['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h6 class="mt-3">Quotas</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Storage (MB)</label>
                            <input type="number" name="quota_storage" class="form-control" value="500">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Pages</label>
                            <input type="number" name="quota_pages" class="form-control" value="50">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Max Users</label>
                            <input type="number" name="quota_users" class="form-control" value="5">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bandwidth (GB)</label>
                            <input type="number" name="quota_bandwidth" class="form-control" value="10">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
