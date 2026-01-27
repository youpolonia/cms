<?php
/**
 * AI-Powered n8n Workflow Builder
 * Generate, preview, and deploy n8n workflows using AI with model selection
 *
 * @package CMS\Admin
 * @since 2025-12
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/core/error_handler.php';
require_once CMS_ROOT . '/admin/includes/permissions.php';
require_once CMS_ROOT . '/core/ai_n8n_workflow.php';
require_once CMS_ROOT . '/core/ai_model_selector.php';
require_once CMS_ROOT . '/core/n8n_client.php';

// DEV_MODE check
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

// Start session and require admin
cms_session_start('admin');
csrf_boot('admin');
cms_require_admin_role();

// Helper function for escaping output
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    csrf_validate_or_403();

    $ajaxAction = $_POST['ajax_action'];

    switch ($ajaxAction) {
        case 'ai_generate':
            $description = trim($_POST['description'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $triggerType = trim($_POST['trigger_type'] ?? '');
            $integrations = trim($_POST['integrations'] ?? '');
            $provider = trim($_POST['provider'] ?? 'openai');
            $model = trim($_POST['model'] ?? '');

            if ($description === '') {
                echo json_encode(['ok' => false, 'error' => 'Please describe your automation.']);
                exit;
            }

            $result = ai_n8n_generate_workflow([
                'description' => $description,
                'name' => $name,
                'trigger_type' => $triggerType,
                'integrations' => $integrations,
                'provider' => $provider,
                'model' => $model
            ]);

            echo json_encode($result);
            exit;

        case 'deploy':
            $workflowJson = trim($_POST['workflow_json'] ?? '');
            $workflowName = trim($_POST['workflow_name'] ?? '');

            if ($workflowJson === '') {
                echo json_encode(['ok' => false, 'error' => 'No workflow JSON to deploy.']);
                exit;
            }

            $decoded = @json_decode($workflowJson, true);
            if ($decoded === null) {
                echo json_encode(['ok' => false, 'error' => 'Invalid workflow JSON.']);
                exit;
            }

            if ($workflowName !== '') {
                $decoded['name'] = $workflowName;
            }

            $decoded['active'] = false;

            $result = n8n_create_workflow($decoded);
            echo json_encode($result);
            exit;

        case 'activate':
            $workflowId = trim($_POST['workflow_id'] ?? '');
            $active = ($_POST['active'] ?? '1') === '1';

            if ($workflowId === '') {
                echo json_encode(['ok' => false, 'error' => 'Workflow ID is required.']);
                exit;
            }

            $result = n8n_activate_workflow($workflowId, $active);
            echo json_encode($result);
            exit;

        case 'save_blueprint':
            $workflowJson = trim($_POST['workflow_json'] ?? '');
            $workflowName = trim($_POST['workflow_name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($workflowJson === '' || $workflowName === '') {
                echo json_encode(['ok' => false, 'error' => 'Workflow name and JSON are required.']);
                exit;
            }

            $blueprintsFile = CMS_ROOT . '/cms_storage/n8n_blueprints.json';
            $blueprints = [];

            if (file_exists($blueprintsFile)) {
                $content = file_get_contents($blueprintsFile);
                $blueprints = json_decode($content, true) ?? [];
            }

            $blueprints[] = [
                'id' => uniqid('bp_'),
                'name' => $workflowName,
                'description' => $description,
                'workflow_json' => $workflowJson,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $dir = dirname($blueprintsFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $saved = file_put_contents($blueprintsFile, json_encode($blueprints, JSON_PRETTY_PRINT));
            echo json_encode(['ok' => (bool)$saved, 'error' => $saved ? null : 'Failed to save blueprint.']);
            exit;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
            exit;
    }
}

// Load n8n config for status display
$n8nConfig = n8n_config_load();
$n8nConfigured = n8n_is_configured($n8nConfig);

// Get trigger types and integrations
$triggerTypes = ai_n8n_get_trigger_types();
$commonIntegrations = ai_n8n_get_common_integrations();

// Page title
$pageTitle = 'AI Workflow Builder';

// Include header
require_once CMS_ROOT . '/admin/includes/header.php';
require_once CMS_ROOT . '/admin/includes/navigation.php';
?>

<style>
/* Catppuccin Mocha Theme Variables */
:root {
    --ctp-rosewater: #f5e0dc;
    --ctp-flamingo: #f2cdcd;
    --ctp-pink: #f5c2e7;
    --ctp-mauve: #cba6f7;
    --ctp-red: #f38ba8;
    --ctp-maroon: #eba0ac;
    --ctp-peach: #fab387;
    --ctp-yellow: #f9e2af;
    --ctp-green: #a6e3a1;
    --ctp-teal: #94e2d5;
    --ctp-sky: #89dceb;
    --ctp-sapphire: #74c7ec;
    --ctp-blue: #89b4fa;
    --ctp-lavender: #b4befe;
    --ctp-text: #cdd6f4;
    --ctp-subtext1: #bac2de;
    --ctp-subtext0: #a6adc8;
    --ctp-overlay2: #9399b2;
    --ctp-overlay1: #7f849c;
    --ctp-overlay0: #6c7086;
    --ctp-surface2: #585b70;
    --ctp-surface1: #45475a;
    --ctp-surface0: #313244;
    --ctp-base: #1e1e2e;
    --ctp-mantle: #181825;
    --ctp-crust: #11111b;
}

.workflow-builder {
    background: var(--ctp-base);
    min-height: calc(100vh - 120px);
    padding: 1.5rem;
    color: var(--ctp-text);
}

/* Header */
.workflow-builder .page-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--ctp-surface1);
}

.workflow-builder .page-header h1 {
    color: var(--ctp-text);
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.workflow-builder .page-header .subtitle {
    color: var(--ctp-subtext0);
    font-size: 0.9rem;
    margin-left: auto;
}

.workflow-builder .n8n-logo {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, var(--ctp-peach), var(--ctp-red));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
    color: var(--ctp-crust);
    box-shadow: 0 2px 8px rgba(250, 179, 135, 0.3);
}

.workflow-builder .status-badge {
    padding: 0.35rem 0.85rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.workflow-builder .status-badge.connected {
    background: var(--ctp-green);
    color: var(--ctp-crust);
}

.workflow-builder .status-badge.disconnected {
    background: var(--ctp-red);
    color: var(--ctp-crust);
}

/* 3-Column Grid Layout */
.workflow-builder .panels {
    display: grid;
    grid-template-columns: 380px 1fr 280px;
    gap: 1.5rem;
    min-height: 650px;
}

@media (max-width: 1400px) {
    .workflow-builder .panels {
        grid-template-columns: 1fr 1fr;
    }
    .workflow-builder .panel-actions {
        grid-column: span 2;
    }
}

@media (max-width: 992px) {
    .workflow-builder .panels {
        grid-template-columns: 1fr;
    }
    .workflow-builder .panel-actions {
        grid-column: span 1;
    }
}

/* Panel Base Styles */
.workflow-builder .panel {
    background: var(--ctp-mantle);
    border-radius: 12px;
    border: 1px solid var(--ctp-surface0);
    overflow: hidden;
    position: relative;
}

.workflow-builder .panel-header {
    padding: 1rem 1.25rem;
    background: var(--ctp-surface0);
    border-bottom: 1px solid var(--ctp-surface1);
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--ctp-subtext1);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.workflow-builder .panel-body {
    padding: 1.25rem;
}

/* === MODEL SELECTOR SECTION (PROMINENT) === */
.workflow-builder .model-selector-section {
    background: linear-gradient(135deg, var(--ctp-surface0) 0%, var(--ctp-surface1) 100%);
    padding: 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border: 2px solid var(--ctp-mauve);
    box-shadow: 0 4px 20px rgba(203, 166, 247, 0.15);
}

.workflow-builder .model-selector-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    font-size: 1rem;
    color: var(--ctp-mauve);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--ctp-surface2);
}

.workflow-builder .model-selector-icon {
    font-size: 1.25rem;
}

.workflow-builder .model-selector-row {
    margin-bottom: 0.875rem;
}

.workflow-builder .model-selector-row:last-of-type {
    margin-bottom: 0.5rem;
}

.workflow-builder .model-selector-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ctp-subtext0);
    margin-bottom: 0.4rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.workflow-builder .model-provider-select,
.workflow-builder .model-model-select {
    width: 100%;
    padding: 0.65rem 1rem;
    background: var(--ctp-crust);
    border: 1px solid var(--ctp-surface2);
    border-radius: 8px;
    color: var(--ctp-text);
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.workflow-builder .model-provider-select:hover,
.workflow-builder .model-model-select:hover {
    border-color: var(--ctp-mauve);
}

.workflow-builder .model-provider-select:focus,
.workflow-builder .model-model-select:focus {
    outline: none;
    border-color: var(--ctp-mauve);
    box-shadow: 0 0 0 3px rgba(203, 166, 247, 0.25);
}

.workflow-builder .model-provider-select option,
.workflow-builder .model-model-select option {
    background: var(--ctp-crust);
    color: var(--ctp-text);
    padding: 0.5rem;
}

.workflow-builder .model-provider-select option:disabled {
    color: var(--ctp-overlay0);
}

.workflow-builder .model-info {
    display: flex;
    gap: 1.25rem;
    font-size: 0.8rem;
    color: var(--ctp-subtext0);
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px dashed var(--ctp-surface2);
}

.workflow-builder .model-info-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.workflow-builder .model-info .info-label {
    color: var(--ctp-overlay1);
    font-weight: 500;
}

.workflow-builder .model-info .info-value {
    color: var(--ctp-teal);
    font-weight: 600;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
}

/* Form Elements */
.workflow-builder .form-group {
    margin-bottom: 1.25rem;
}

.workflow-builder .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--ctp-subtext1);
}

.workflow-builder .form-control,
.workflow-builder .form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 8px;
    color: var(--ctp-text);
    font-size: 0.9rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.workflow-builder .form-control:focus,
.workflow-builder .form-select:focus {
    outline: none;
    border-color: var(--ctp-mauve);
    box-shadow: 0 0 0 3px rgba(203, 166, 247, 0.2);
}

.workflow-builder .form-control::placeholder {
    color: var(--ctp-overlay0);
}

.workflow-builder textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* Integration Tags */
.workflow-builder .integration-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.workflow-builder .integration-tag {
    padding: 0.3rem 0.75rem;
    background: var(--ctp-surface1);
    border: 1px solid var(--ctp-surface2);
    border-radius: 9999px;
    font-size: 0.75rem;
    color: var(--ctp-subtext0);
    cursor: pointer;
    transition: all 0.2s;
}

.workflow-builder .integration-tag:hover,
.workflow-builder .integration-tag.selected {
    background: var(--ctp-mauve);
    border-color: var(--ctp-mauve);
    color: var(--ctp-crust);
}

/* Buttons */
.workflow-builder .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.workflow-builder .btn-primary {
    background: linear-gradient(135deg, var(--ctp-mauve), var(--ctp-pink));
    color: var(--ctp-crust);
}

.workflow-builder .btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(203, 166, 247, 0.4);
}

.workflow-builder .btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.workflow-builder .btn-success {
    background: linear-gradient(135deg, var(--ctp-green), var(--ctp-teal));
    color: var(--ctp-crust);
}

.workflow-builder .btn-success:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(166, 227, 161, 0.4);
}

.workflow-builder .btn-secondary {
    background: var(--ctp-surface1);
    color: var(--ctp-text);
    border: 1px solid var(--ctp-surface2);
}

.workflow-builder .btn-secondary:hover:not(:disabled) {
    background: var(--ctp-surface2);
}

.workflow-builder .btn-outline {
    background: transparent;
    border: 1px solid var(--ctp-surface2);
    color: var(--ctp-subtext1);
}

.workflow-builder .btn-outline:hover:not(:disabled) {
    background: var(--ctp-surface0);
    border-color: var(--ctp-mauve);
    color: var(--ctp-mauve);
}

.workflow-builder .btn-block {
    width: 100%;
}

/* JSON Preview */
.workflow-builder .json-preview {
    background: var(--ctp-crust);
    border-radius: 8px;
    padding: 1rem;
    font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', monospace;
    font-size: 0.8rem;
    line-height: 1.6;
    min-height: 350px;
    max-height: 450px;
    overflow: auto;
    color: var(--ctp-text);
    white-space: pre-wrap;
    word-break: break-word;
    border: 1px solid var(--ctp-surface0);
}

.workflow-builder .json-preview:empty::before {
    content: '{\n  "// Generated workflow will appear here..."\n}';
    color: var(--ctp-overlay0);
    font-style: italic;
}

/* Syntax Highlighting */
.workflow-builder .json-preview .json-key { color: var(--ctp-mauve); }
.workflow-builder .json-preview .json-string { color: var(--ctp-green); }
.workflow-builder .json-preview .json-number { color: var(--ctp-peach); }
.workflow-builder .json-preview .json-boolean { color: var(--ctp-sky); }
.workflow-builder .json-preview .json-null { color: var(--ctp-red); }

/* Workflow Status */
.workflow-builder .workflow-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: var(--ctp-surface0);
    border-radius: 8px;
    font-size: 0.875rem;
}

.workflow-builder .workflow-status .badge {
    padding: 0.25rem 0.6rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.workflow-builder .badge-draft { background: var(--ctp-yellow); color: var(--ctp-crust); }
.workflow-builder .badge-deployed { background: var(--ctp-green); color: var(--ctp-crust); }
.workflow-builder .badge-active { background: var(--ctp-teal); color: var(--ctp-crust); }

/* Actions Panel */
.workflow-builder .action-group {
    margin-bottom: 1.5rem;
}

.workflow-builder .action-group:last-child {
    margin-bottom: 0;
}

.workflow-builder .action-group h4 {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--ctp-overlay1);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
}

.workflow-builder .action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

/* Toggle Switch */
.workflow-builder .toggle-switch {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--ctp-surface0);
    border-radius: 8px;
    cursor: pointer;
}

.workflow-builder .toggle-switch input { display: none; }

.workflow-builder .toggle-slider {
    width: 44px;
    height: 24px;
    background: var(--ctp-surface2);
    border-radius: 12px;
    position: relative;
    transition: background 0.2s;
}

.workflow-builder .toggle-slider::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: var(--ctp-text);
    border-radius: 50%;
    top: 3px;
    left: 3px;
    transition: transform 0.2s;
}

.workflow-builder .toggle-switch input:checked + .toggle-slider {
    background: var(--ctp-green);
}

.workflow-builder .toggle-switch input:checked + .toggle-slider::after {
    transform: translateX(20px);
}

.workflow-builder .toggle-label {
    font-size: 0.875rem;
    color: var(--ctp-subtext1);
}

/* n8n Link */
.workflow-builder .n8n-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: var(--ctp-surface0);
    border-radius: 8px;
    color: var(--ctp-sapphire);
    text-decoration: none;
    font-size: 0.875rem;
    transition: background 0.2s;
}

.workflow-builder .n8n-link:hover {
    background: var(--ctp-surface1);
}

/* Loading Overlay */
.workflow-builder .loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(17, 17, 27, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    z-index: 10;
    border-radius: 12px;
}

.workflow-builder .loading-spinner {
    width: 48px;
    height: 48px;
    border: 4px solid var(--ctp-surface2);
    border-top-color: var(--ctp-mauve);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.workflow-builder .loading-text {
    color: var(--ctp-subtext1);
    font-size: 0.9rem;
    font-weight: 500;
}

/* Alerts */
.workflow-builder .alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.workflow-builder .alert-success {
    background: rgba(166, 227, 161, 0.15);
    border: 1px solid var(--ctp-green);
    color: var(--ctp-green);
}

.workflow-builder .alert-danger {
    background: rgba(243, 139, 168, 0.15);
    border: 1px solid var(--ctp-red);
    color: var(--ctp-red);
}

.workflow-builder .alert-warning {
    background: rgba(249, 226, 175, 0.15);
    border: 1px solid var(--ctp-yellow);
    color: var(--ctp-yellow);
}
</style>

<div class="workflow-builder">
    <div class="page-header">
        <h1>
            <span class="n8n-logo">n8n</span>
            AI Workflow Builder
        </h1>
        <span class="subtitle">Describe automation in plain words, AI builds the workflow</span>
        <span class="status-badge <?php echo $n8nConfigured ? 'connected' : 'disconnected'; ?>">
            <?php echo $n8nConfigured ? '✓ n8n Connected' : '✗ n8n Not Configured'; ?>
        </span>
    </div>

    <div id="globalMessage"></div>

    <div class="panels">
        <!-- Left Panel: AI Generator -->
        <div class="panel panel-generator">
            <div class="panel-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M12 2v4m0 12v4M2 12h4m12 0h4m-3.3-6.7l-2.8 2.8m-5.8 5.8l-2.8 2.8m11.4 0l-2.8-2.8m-5.8-5.8l-2.8-2.8"/>
                </svg>
                AI Generator
            </div>
            <div class="panel-body">
                <form id="generateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo esc(csrf_token()); ?>">

                    <!-- PROMINENT MODEL SELECTOR AT TOP -->
                    <?php echo ai_model_selector_render('workflow'); ?>

                    <div class="form-group">
                        <label class="form-label">What do you want to automate?</label>
                        <textarea
                            class="form-control"
                            name="description"
                            id="description"
                            placeholder="Example: When a new blog post is published, send an email notification to subscribers and post a summary to Slack"
                            required
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Workflow Name (optional)</label>
                        <input type="text" class="form-control" name="name" id="workflowNameInput" placeholder="My Automation">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trigger Type</label>
                        <select class="form-select" name="trigger_type" id="triggerType">
                            <?php foreach ($triggerTypes as $trigger): ?>
                                <option value="<?php echo esc($trigger['value']); ?>"><?php echo esc($trigger['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Integrations (click to add)</label>
                        <input type="hidden" name="integrations" id="integrationsInput">
                        <div class="integration-tags" id="integrationTags">
                            <?php foreach (array_slice($commonIntegrations, 0, 12) as $integration): ?>
                                <span class="integration-tag" data-integration="<?php echo esc($integration); ?>"><?php echo esc($integration); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="generateBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        Generate Workflow
                    </button>
                </form>
            </div>
        </div>

        <!-- Center Panel: Preview -->
        <div class="panel panel-preview">
            <div class="panel-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M9 9h6m-6 4h6m-6 4h4"/>
                </svg>
                Workflow Preview
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Workflow Name</label>
                    <input type="text" class="form-control" id="finalWorkflowName" placeholder="Enter workflow name">
                </div>

                <div class="workflow-status">
                    <span>Status:</span>
                    <span class="badge badge-draft" id="workflowStatusBadge">Draft</span>
                    <span id="deployedWorkflowId" style="display: none; margin-left: auto; font-size: 0.75rem; color: var(--ctp-overlay0);">
                        ID: <span id="workflowIdValue"></span>
                    </span>
                </div>

                <label class="form-label">Generated JSON</label>
                <div class="json-preview" id="jsonPreview" contenteditable="true"></div>

                <div id="previewLoading" class="loading-overlay" style="display: none;">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Generating workflow with AI...</div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Actions -->
        <div class="panel panel-actions">
            <div class="panel-header">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                </svg>
                Actions
            </div>
            <div class="panel-body">
                <div class="action-group">
                    <h4>Deploy to n8n</h4>
                    <div class="action-buttons">
                        <button class="btn btn-success btn-block" id="deployBtn" disabled>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                            </svg>
                            Deploy Workflow
                        </button>
                    </div>
                </div>

                <div class="action-group" id="activateGroup" style="display: none;">
                    <h4>Activation</h4>
                    <label class="toggle-switch">
                        <input type="checkbox" id="activateToggle">
                        <span class="toggle-slider"></span>
                        <span class="toggle-label">Workflow Active</span>
                    </label>
                </div>

                <div class="action-group">
                    <h4>Save Locally</h4>
                    <div class="action-buttons">
                        <button class="btn btn-secondary btn-block" id="saveBlueprintBtn" disabled>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Save Blueprint
                        </button>
                    </div>
                </div>

                <div class="action-group" id="n8nLinkGroup" style="display: none;">
                    <h4>External</h4>
                    <a href="#" class="n8n-link" id="openInN8nLink" target="_blank">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Open in n8n Editor
                    </a>
                </div>

                <div class="action-group">
                    <h4>Utilities</h4>
                    <div class="action-buttons">
                        <button class="btn btn-outline btn-block" id="copyJsonBtn" disabled>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                            Copy JSON
                        </button>
                        <button class="btn btn-outline btn-block" id="clearBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php echo ai_model_selector_js('workflow'); ?>

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '<?php echo esc(csrf_token()); ?>';
    const n8nBaseUrl = '<?php echo esc(rtrim($n8nConfig['base_url'] ?? '', '/')); ?>';
    const n8nConfigured = <?php echo $n8nConfigured ? 'true' : 'false'; ?>;

    // DOM elements
    const generateForm = document.getElementById('generateForm');
    const generateBtn = document.getElementById('generateBtn');
    const jsonPreview = document.getElementById('jsonPreview');
    const previewLoading = document.getElementById('previewLoading');
    const finalWorkflowName = document.getElementById('finalWorkflowName');
    const workflowStatusBadge = document.getElementById('workflowStatusBadge');
    const deployedWorkflowId = document.getElementById('deployedWorkflowId');
    const workflowIdValue = document.getElementById('workflowIdValue');
    const deployBtn = document.getElementById('deployBtn');
    const activateGroup = document.getElementById('activateGroup');
    const activateToggle = document.getElementById('activateToggle');
    const saveBlueprintBtn = document.getElementById('saveBlueprintBtn');
    const copyJsonBtn = document.getElementById('copyJsonBtn');
    const clearBtn = document.getElementById('clearBtn');
    const n8nLinkGroup = document.getElementById('n8nLinkGroup');
    const openInN8nLink = document.getElementById('openInN8nLink');
    const globalMessage = document.getElementById('globalMessage');
    const integrationTags = document.getElementById('integrationTags');
    const integrationsInput = document.getElementById('integrationsInput');
    const descriptionInput = document.getElementById('description');

    let currentWorkflowId = null;
    let selectedIntegrations = [];

    // Integration tag selection
    integrationTags.addEventListener('click', function(e) {
        if (e.target.classList.contains('integration-tag')) {
            const integration = e.target.dataset.integration;
            const index = selectedIntegrations.indexOf(integration);

            if (index === -1) {
                selectedIntegrations.push(integration);
                e.target.classList.add('selected');
            } else {
                selectedIntegrations.splice(index, 1);
                e.target.classList.remove('selected');
            }

            integrationsInput.value = selectedIntegrations.join(', ');
        }
    });

    // Show message helper
    function showMessage(message, type = 'success') {
        const icon = type === 'success' ? '✓' : type === 'danger' ? '✗' : '⚠';
        globalMessage.innerHTML = `<div class="alert alert-${type}">${icon} ${message}</div>`;
        setTimeout(() => { globalMessage.innerHTML = ''; }, 5000);
    }

    // Update button states
    function updateButtonStates(hasContent) {
        deployBtn.disabled = !hasContent || !n8nConfigured;
        saveBlueprintBtn.disabled = !hasContent;
        copyJsonBtn.disabled = !hasContent;
    }

    // Syntax highlight JSON
    function highlightJson(json) {
        return json
            .replace(/"([^"]+)":/g, '<span class="json-key">"$1"</span>:')
            .replace(/: "([^"]*)"/g, ': <span class="json-string">"$1"</span>')
            .replace(/: (\d+)/g, ': <span class="json-number">$1</span>')
            .replace(/: (true|false)/g, ': <span class="json-boolean">$1</span>')
            .replace(/: null/g, ': <span class="json-null">null</span>');
    }

    // Generate workflow
    generateForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const modelSelection = window.workflowModelSelector ? window.workflowModelSelector.getSelection() : { provider: 'openai', model: 'gpt-4.1-mini' };

        const formData = new FormData(this);
        formData.append('ajax_action', 'ai_generate');
        formData.append('provider', modelSelection.provider);
        formData.append('model', modelSelection.model);

        generateBtn.disabled = true;
        previewLoading.style.display = 'flex';

        try {
            const response = await fetch('n8n-workflow-builder.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.ok) {
                const prettyJson = JSON.stringify(JSON.parse(result.json), null, 2);
                jsonPreview.innerHTML = highlightJson(prettyJson);
                finalWorkflowName.value = result.name || '';
                workflowStatusBadge.textContent = 'Draft';
                workflowStatusBadge.className = 'badge badge-draft';
                deployedWorkflowId.style.display = 'none';
                activateGroup.style.display = 'none';
                n8nLinkGroup.style.display = 'none';
                currentWorkflowId = null;
                updateButtonStates(true);
                showMessage(`Workflow generated using ${modelSelection.provider}/${modelSelection.model}`);
            } else {
                showMessage(result.error || 'Failed to generate workflow.', 'danger');
            }
        } catch (error) {
            showMessage('Network error. Please try again.', 'danger');
            console.error(error);
        } finally {
            generateBtn.disabled = false;
            previewLoading.style.display = 'none';
        }
    });

    // Deploy to n8n
    deployBtn.addEventListener('click', async function() {
        const json = jsonPreview.textContent.trim();
        const name = finalWorkflowName.value.trim();

        if (!json) {
            showMessage('No workflow to deploy.', 'warning');
            return;
        }

        if (!n8nConfigured) {
            showMessage('n8n is not configured. Please configure it in n8n Settings.', 'danger');
            return;
        }

        const formData = new FormData();
        formData.append('ajax_action', 'deploy');
        formData.append('csrf_token', csrfToken);
        formData.append('workflow_json', json);
        formData.append('workflow_name', name);

        deployBtn.disabled = true;

        try {
            const response = await fetch('n8n-workflow-builder.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.ok && result.workflow) {
                currentWorkflowId = result.workflow.id;
                workflowStatusBadge.textContent = 'Deployed';
                workflowStatusBadge.className = 'badge badge-deployed';
                deployedWorkflowId.style.display = 'block';
                workflowIdValue.textContent = currentWorkflowId;
                activateGroup.style.display = 'block';
                activateToggle.checked = result.workflow.active || false;

                if (n8nBaseUrl) {
                    n8nLinkGroup.style.display = 'block';
                    openInN8nLink.href = n8nBaseUrl + '/workflow/' + currentWorkflowId;
                }

                showMessage('Workflow deployed successfully!');
            } else {
                showMessage(result.error || 'Failed to deploy workflow.', 'danger');
            }
        } catch (error) {
            showMessage('Network error. Please try again.', 'danger');
            console.error(error);
        } finally {
            deployBtn.disabled = false;
        }
    });

    // Activate toggle
    activateToggle.addEventListener('change', async function() {
        if (!currentWorkflowId) return;

        const active = this.checked;
        const formData = new FormData();
        formData.append('ajax_action', 'activate');
        formData.append('csrf_token', csrfToken);
        formData.append('workflow_id', currentWorkflowId);
        formData.append('active', active ? '1' : '0');

        try {
            const response = await fetch('n8n-workflow-builder.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.ok) {
                workflowStatusBadge.textContent = active ? 'Active' : 'Deployed';
                workflowStatusBadge.className = active ? 'badge badge-active' : 'badge badge-deployed';
                showMessage(active ? 'Workflow activated!' : 'Workflow deactivated.');
            } else {
                this.checked = !active;
                showMessage(result.error || 'Failed to update workflow status.', 'danger');
            }
        } catch (error) {
            this.checked = !active;
            showMessage('Network error. Please try again.', 'danger');
            console.error(error);
        }
    });

    // Save blueprint
    saveBlueprintBtn.addEventListener('click', async function() {
        const json = jsonPreview.textContent.trim();
        const name = finalWorkflowName.value.trim() || 'Untitled Blueprint';
        const description = descriptionInput.value.trim();

        if (!json) {
            showMessage('No workflow to save.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('ajax_action', 'save_blueprint');
        formData.append('csrf_token', csrfToken);
        formData.append('workflow_json', json);
        formData.append('workflow_name', name);
        formData.append('description', description);

        saveBlueprintBtn.disabled = true;

        try {
            const response = await fetch('n8n-workflow-builder.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.ok) {
                showMessage('Blueprint saved successfully!');
            } else {
                showMessage(result.error || 'Failed to save blueprint.', 'danger');
            }
        } catch (error) {
            showMessage('Network error. Please try again.', 'danger');
            console.error(error);
        } finally {
            saveBlueprintBtn.disabled = false;
        }
    });

    // Copy JSON
    copyJsonBtn.addEventListener('click', function() {
        const json = jsonPreview.textContent.trim();
        if (!json) return;

        navigator.clipboard.writeText(json).then(() => {
            showMessage('JSON copied to clipboard!');
        }).catch(() => {
            showMessage('Failed to copy to clipboard.', 'danger');
        });
    });

    // Clear
    clearBtn.addEventListener('click', function() {
        jsonPreview.innerHTML = '';
        finalWorkflowName.value = '';
        descriptionInput.value = '';
        document.getElementById('workflowNameInput').value = '';
        workflowStatusBadge.textContent = 'Draft';
        workflowStatusBadge.className = 'badge badge-draft';
        deployedWorkflowId.style.display = 'none';
        activateGroup.style.display = 'none';
        n8nLinkGroup.style.display = 'none';
        currentWorkflowId = null;
        selectedIntegrations = [];
        integrationsInput.value = '';
        document.querySelectorAll('.integration-tag').forEach(tag => tag.classList.remove('selected'));
        updateButtonStates(false);
    });

    // Initial state
    updateButtonStates(false);
});
</script>

<?php
require_once CMS_ROOT . '/admin/includes/footer.php';
