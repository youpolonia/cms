<?php

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

cms_session_start('admin');
csrf_boot('admin');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo '403 Forbidden - This page is only accessible in development mode.';
    exit;
}

cms_require_admin_role();

function esc($str) {
    if ($str === null) {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$success = null;
$bindings = [];
$available_workflows = [];
$n8n_configured = false;

$config = n8n_config_load();
$n8n_configured = n8n_is_configured($config);

if ($n8n_configured) {
    $listResult = n8n_list_workflows(100);

    if (!$listResult['ok'] || !is_array($listResult['workflows'])) {
        $errors[] = 'Could not fetch workflows from n8n.';
    } else {
        foreach ($listResult['workflows'] as $workflow) {
            if (!is_array($workflow)) {
                continue;
            }
            $available_workflows[] = [
                'id' => isset($workflow['id']) ? (string)$workflow['id'] : '',
                'name' => isset($workflow['name']) ? (string)$workflow['name'] : 'Unnamed workflow',
                'active' => isset($workflow['active']) ? (bool)$workflow['active'] : false
            ];
        }
    }
} else {
    $errors[] = 'n8n is not configured. Please configure it in n8n Settings before creating bindings.';
}

$bindingsPath = CMS_ROOT . '/config/n8n_bindings.json';
if (file_exists($bindingsPath)) {
    $bindingsJson = @file_get_contents($bindingsPath);
    if ($bindingsJson !== false) {
        $bindingsData = @json_decode($bindingsJson, true);
        if (is_array($bindingsData) && isset($bindingsData['bindings']) && is_array($bindingsData['bindings'])) {
            $bindings = $bindingsData['bindings'];
        } else {
            $errors[] = 'Bindings config is invalid, starting with empty list in this view.';
            $bindings = [];
        }
    } else {
        $bindings = [];
    }
} else {
    $bindings = [];
}

$formData = [
    'id' => '',
    'name' => '',
    'event_key' => '',
    'workflow_id' => '',
    'webhook_path' => '',
    'active' => true
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $event_key = isset($_POST['event_key']) ? trim($_POST['event_key']) : '';
    $workflow_id = isset($_POST['workflow_id']) ? trim($_POST['workflow_id']) : '';
    $webhook_path = isset($_POST['webhook_path']) ? trim($_POST['webhook_path']) : '';
    $active = isset($_POST['active']) && ($_POST['active'] === '1' || $_POST['active'] === 'on');

    $formData = [
        'id' => $id,
        'name' => $name,
        'event_key' => $event_key,
        'workflow_id' => $workflow_id,
        'webhook_path' => $webhook_path,
        'active' => $active
    ];

    $postErrors = [];

    if ($id === '') {
        $postErrors[] = 'Binding ID is required.';
    } elseif (!preg_match('/^[a-z0-9._-]+$/', $id)) {
        $postErrors[] = 'Binding ID must contain only lowercase letters, numbers, dots, underscores, and hyphens.';
    } elseif (strlen($id) > 64) {
        $postErrors[] = 'Binding ID must be 64 characters or less.';
    }

    if ($name === '') {
        $postErrors[] = 'Name is required.';
    } elseif (strlen($name) > 255) {
        $postErrors[] = 'Name must be 255 characters or less.';
    }

    if ($event_key === '') {
        $postErrors[] = 'Event key is required.';
    } elseif (!preg_match('/^[a-z0-9._-]+$/', $event_key)) {
        $postErrors[] = 'Event key must contain only lowercase letters, numbers, dots, underscores, and hyphens.';
    } elseif (strlen($event_key) > 255) {
        $postErrors[] = 'Event key must be 255 characters or less.';
    }

    if ($workflow_id === '') {
        $postErrors[] = 'Workflow ID is required.';
    }

    if ($webhook_path === '') {
        $postErrors[] = 'Webhook path is required.';
    } else {
        $webhook_path = trim($webhook_path, '/');
        $formData['webhook_path'] = $webhook_path;

        if (strlen($webhook_path) > 255) {
            $postErrors[] = 'Webhook path must be 255 characters or less.';
        } elseif (!preg_match('/^[a-z0-9\/_-]+$/', $webhook_path)) {
            $postErrors[] = 'Webhook path must contain only lowercase letters, numbers, hyphens, underscores, and slashes.';
        }
    }

    $idExists = false;
    foreach ($bindings as $binding) {
        if (isset($binding['id']) && $binding['id'] === $id) {
            $idExists = true;
            break;
        }
    }
    if ($idExists) {
        $postErrors[] = 'Binding ID already exists.';
    }

    if (!empty($postErrors)) {
        $errors = array_merge($errors, $postErrors);
    } else {
        $new_binding = [
            'id' => $id,
            'name' => $name,
            'event_key' => $event_key,
            'workflow_id' => $workflow_id,
            'webhook_path' => $webhook_path,
            'active' => $active
        ];

        $bindings[] = $new_binding;

        $data = [
            'bindings' => array_values($bindings)
        ];

        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            $errors[] = 'Failed to encode bindings data.';
        } else {
            $written = @file_put_contents($bindingsPath, $json, LOCK_EX);
            if ($written === false) {
                $errors[] = 'Failed to write bindings config file.';
            } else {
                @chmod($bindingsPath, 0644);
                $success = 'Binding created successfully.';
                $formData = [
                    'id' => '',
                    'name' => '',
                    'event_key' => '',
                    'workflow_id' => '',
                    'webhook_path' => '',
                    'active' => true
                ];
            }
        }
    }
}

require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<div class="admin-content">
    <div class="container">
        <h1>n8n Workflow Bindings</h1>

        <?php if ($success !== null): ?>
            <div style="padding: 16px; margin: 16px 0; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                <?php echo esc($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="padding: 16px; margin: 16px 0; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo esc($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="padding: 16px; margin: 16px 0; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
            <h3>n8n Status</h3>
            <?php if (!$n8n_configured): ?>
                <p style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 12px; border-radius: 4px;">
                    n8n is not configured. Please configure it in n8n Settings before creating bindings.
                </p>
            <?php elseif (empty($available_workflows)): ?>
                <p style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 12px; border-radius: 4px;">
                    No workflows could be loaded from n8n, or none are available.
                </p>
            <?php else: ?>
                <p style="color: #155724;">
                    <strong><?php echo count($available_workflows); ?></strong> workflow(s) loaded from n8n.
                </p>
            <?php endif; ?>
        </div>

        <h2>Existing Bindings</h2>

        <?php if (empty($bindings)): ?>
            <div style="padding: 20px; margin: 16px 0; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center; color: #6c757d;">
                No bindings defined yet.
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin: 16px 0; background-color: #fff; border: 1px solid #dee2e6;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">ID</th>
                        <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Name</th>
                        <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Event Key</th>
                        <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Workflow ID</th>
                        <th style="padding: 12px; text-align: left; border-right: 1px solid #dee2e6;">Webhook Path</th>
                        <th style="padding: 12px; text-align: left;">Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bindings as $binding): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($binding['id'] ?? ''); ?></td>
                            <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($binding['name'] ?? ''); ?></td>
                            <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($binding['event_key'] ?? ''); ?></td>
                            <td style="padding: 12px; border-right: 1px solid #dee2e6;"><?php echo esc($binding['workflow_id'] ?? ''); ?></td>
                            <td style="padding: 12px; border-right: 1px solid #dee2e6;">
                                <?php
                                $webhookPath = isset($binding['webhook_path']) ? trim((string)$binding['webhook_path']) : '';
                                echo $webhookPath !== '' ? esc($webhookPath) : '—';
                                ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php
                                $isActive = isset($binding['active']) && $binding['active'];
                                echo $isActive ? 'Yes' : 'No';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Create New Binding</h2>

        <form method="POST" action="" style="max-width: 600px; margin: 16px 0;">
            <?php csrf_field(); ?>

            <div style="margin-bottom: 16px;">
                <label for="id" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Binding ID <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="id"
                    name="id"
                    required
                    maxlength="64"
                    pattern="[a-z0-9._-]+"
                    value="<?php echo esc($formData['id']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;"
                    placeholder="e.g., user_registration_notify"
                />
                <small style="color: #6c757d; display: block; margin-top: 4px;">
                    Lowercase letters, numbers, dots, underscores, and hyphens only. Max 64 characters.
                </small>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="name" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Name <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    maxlength="255"
                    value="<?php echo esc($formData['name']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;"
                    placeholder="Human-readable label"
                />
            </div>

            <div style="margin-bottom: 16px;">
                <label for="event_key" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Event Key <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="event_key"
                    name="event_key"
                    required
                    maxlength="255"
                    pattern="[a-z0-9._-]+"
                    value="<?php echo esc($formData['event_key']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;"
                    placeholder="e.g., user.registered"
                />
                <small style="color: #6c757d; display: block; margin-top: 4px;">
                    Examples: user.registered, form.submitted, content.published, order.created
                </small>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="webhook_path" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Webhook Path <span style="color: #dc3545;">*</span>
                </label>
                <input
                    type="text"
                    id="webhook_path"
                    name="webhook_path"
                    required
                    maxlength="255"
                    value="<?php echo esc($formData['webhook_path']); ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;"
                    placeholder="cms-user-signup"
                />
                <small style="color: #6c757d; display: block; margin-top: 4px;">
                    The webhook path configured in your n8n workflow (without leading slash). Example: cms-user-signup
                </small>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="workflow_id" style="display: block; font-weight: bold; margin-bottom: 4px;">
                    Workflow <span style="color: #dc3545;">*</span>
                </label>
                <?php if (empty($available_workflows)): ?>
                    <select
                        id="workflow_id"
                        name="workflow_id"
                        disabled
                        style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; background-color: #e9ecef;"
                    >
                        <option value="">No workflows available</option>
                    </select>
                <?php else: ?>
                    <select
                        id="workflow_id"
                        name="workflow_id"
                        required
                        style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;"
                    >
                        <option value="">Select workflow</option>
                        <?php foreach ($available_workflows as $workflow): ?>
                            <option value="<?php echo esc($workflow['id']); ?>" <?php echo ($formData['workflow_id'] === (string)$workflow['id']) ? 'selected' : ''; ?>>
                                <?php
                                echo esc($workflow['name']);
                                if (isset($workflow['active'])) {
                                    echo $workflow['active'] ? ' (active)' : ' (inactive)';
                                }
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="active"
                        value="1"
                        <?php echo $formData['active'] ? 'checked' : ''; ?>
                        style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;"
                    />
                    <span style="font-weight: bold;">Active</span>
                </label>
                <small style="color: #6c757d; display: block; margin-top: 4px; margin-left: 26px;">
                    Enable this binding immediately upon creation
                </small>
            </div>

            <div style="margin-top: 24px;">
                <button
                    type="submit"
                    style="padding: 10px 24px; background-color: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;"
                    <?php if (empty($available_workflows)): ?>disabled<?php endif; ?>
                >
                    Create Binding
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
