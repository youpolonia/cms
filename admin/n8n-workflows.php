<?php
/**
 * n8n Workflows - Admin Listing Page
 * Read-only view of workflows from n8n instance
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/auth.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/n8n_client.php';

// Start admin session
cms_session_start('admin');
csrf_boot('admin');

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

// Require admin role
cms_require_admin_role();

// Helper function for escaping output
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Load configuration
$config = n8n_config_load();
$configured = n8n_is_configured($config);

// Compute safe UI base URL for workflow links
$n8nUiBaseUrl = '';
if (!empty($config['base_url']) && is_string($config['base_url'])) {
    $n8nUiBaseUrl = rtrim($config['base_url'], "/");
}

// Get filter parameter
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch workflows
$listResult = null;
if ($configured) {
    $listResult = n8n_list_workflows(50);
} else {
    $listResult = [
        'ok' => false,
        'error' => 'n8n is not configured or disabled.',
        'workflows' => []
    ];
}

// Process workflows
$workflows = [];
if ($listResult['ok']) {
    $workflows = $listResult['workflows'];

    // Apply filter if search query is present
    if ($q !== '') {
        $filtered = [];
        foreach ($workflows as $workflow) {
            $idStr = (string)$workflow['id'];
            $nameStr = $workflow['name'];

            // Case-insensitive substring match on id or name
            if (stripos($idStr, $q) !== false || stripos($nameStr, $q) !== false) {
                $filtered[] = $workflow;
            }
        }
        $workflows = $filtered;
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>n8n Workflows</h1>

        <div class="config-info" style="padding: 16px; margin: 16px 0; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
            <h3>Current Configuration</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; font-weight: bold; width: 200px;">Base URL:</td>
                    <td style="padding: 8px;"><?php echo !empty($config['base_url']) ? esc($config['base_url']) : '<em>not set</em>'; ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: bold;">Enabled:</td>
                    <td style="padding: 8px;"><?php echo $config['enabled'] ? 'Yes' : 'No'; ?></td>
                </tr>
            </table>
            <?php if (!$configured): ?>
                <div style="margin-top: 12px; padding: 12px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; color: #856404;">
                    <strong>Warning:</strong> n8n integration is disabled or not configured. Update settings first.
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$listResult['ok']): ?>
            <div class="alert alert-error" style="padding: 12px; margin: 16px 0; border-radius: 4px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                <strong>Error:</strong> <?php echo esc($listResult['error']); ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="" style="margin: 20px 0;">
            <div class="form-group" style="display: flex; gap: 8px; align-items: center;">
                <label for="q" style="font-weight: bold;">Filter:</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="<?php echo esc($q); ?>"
                    placeholder="Search by name or ID"
                    style="flex: 1; max-width: 400px; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;"
                >
                <button
                    type="submit"
                    style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;"
                >
                    Filter
                </button>
            </div>
        </form>

        <?php if ($listResult['ok'] && count($workflows) > 0): ?>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left; font-weight: bold;">ID</th>
                        <th style="padding: 12px; text-align: left; font-weight: bold;">Name</th>
                        <th style="padding: 12px; text-align: left; font-weight: bold;">Active</th>
                        <th style="padding: 12px; text-align: left; font-weight: bold;">Created</th>
                        <th style="padding: 12px; text-align: left; font-weight: bold;">Updated</th>
                        <th style="padding: 12px; text-align: left; font-weight: bold;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workflows as $workflow): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px;"><?php echo esc((string)$workflow['id']); ?></td>
                            <td style="padding: 12px;"><?php echo esc($workflow['name']); ?></td>
                            <td style="padding: 12px;">
                                <?php if ($workflow['active']): ?>
                                    <span style="color: #28a745; font-weight: bold;">Yes</span>
                                <?php else: ?>
                                    <span style="color: #6c757d;">No</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;"><?php echo $workflow['created'] !== null ? esc($workflow['created']) : '—'; ?></td>
                            <td style="padding: 12px;"><?php echo $workflow['updated'] !== null ? esc($workflow['updated']) : '—'; ?></td>
                            <td style="padding: 12px;">
                                <?php if ($configured && $n8nUiBaseUrl !== ''): ?>
                                    <a href="<?php echo htmlspecialchars($n8nUiBaseUrl . '/workflow/' . urlencode((string) $workflow['id']), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                                        Open in n8n
                                    </a>
                                <?php else: ?>
                                    <span style="color: #6c757d;">Not available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($listResult['ok'] && count($workflows) === 0): ?>
            <div style="padding: 20px; margin: 20px 0; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center; color: #6c757d;">
                No workflows found. Check your n8n instance or reduce filters.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
