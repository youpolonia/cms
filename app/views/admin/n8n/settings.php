<?php
/**
 * n8n Integration Center
 * Professional settings UI with health monitoring and workflow management
 */
$title = 'n8n Integration';
ob_start();
?>

<style>
/* n8n Integration Center Styles */
.n8n-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.n8n-header-info { flex: 1; }
.n8n-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}
.n8n-header-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }
.n8n-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #ff6d5a, #f9a825);
    border-radius: 12px;
    font-size: 22px;
}
.page-subtitle {
    color: var(--text-muted);
    margin: 0.5rem 0 0;
    font-size: 0.95rem;
}
.btn-outline {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border);
}
.btn-outline:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}
.btn-lg { padding: 12px 24px; font-size: 15px; }

/* Grid Layout */
.n8n-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1200px) {
    .n8n-grid { grid-template-columns: 1fr; }
}
.n8n-main { display: flex; flex-direction: column; gap: 1.5rem; }
.n8n-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }

/* Status Card */
.status-card {
    background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-primary));
}
.status-card .card-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
}
.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-dot.status-checking { background: #f9e2af; animation: pulse 1.5s infinite; }
.status-dot.status-connected { background: #a6e3a1; }
.status-dot.status-error { background: #f38ba8; }
.status-dot.status-disabled { background: #6c7086; }
.status-dot.status-unconfigured { background: #fab387; }
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.status-text { font-weight: 600; font-size: 0.95rem; }
.status-details code {
    font-size: 0.8rem;
    color: var(--text-muted);
    background: var(--bg-secondary);
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
}

/* Form Styles */
.form-row { margin-bottom: 1.25rem; }
.form-row-inline {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 600px) {
    .form-row-inline { grid-template-columns: 1fr; }
}
label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}
input[type="text"],
input[type="url"],
input[type="password"],
input[type="number"],
select,
textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: var(--text-primary);
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: border-color 0.15s, box-shadow 0.15s;
}
input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-muted);
}
.form-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.35rem;
}
.required { color: #f38ba8; }
.auth-fields {
    background: var(--bg-secondary);
    padding: 1rem 1.25rem;
    border-radius: var(--radius);
    margin-bottom: 1.25rem;
    border: 1px solid var(--border);
}
.form-actions {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
}

/* Toggle Switch */
.toggle-label {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    user-select: none;
}
.toggle-label input { display: none; }
.toggle-switch {
    width: 48px;
    height: 26px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 13px;
    position: relative;
    transition: all 0.2s;
}
.toggle-switch::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: var(--text-muted);
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: all 0.2s;
}
.toggle-label input:checked + .toggle-switch {
    background: var(--accent);
    border-color: var(--accent);
}
.toggle-label input:checked + .toggle-switch::after {
    transform: translateX(22px);
    background: white;
}
.toggle-text { font-weight: 500; }

/* Workflows & Logs */
.workflow-list, .log-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.workflow-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--bg-tertiary);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}
.workflow-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.workflow-status.active { background: #a6e3a1; }
.workflow-status.inactive { background: #6c7086; }
.workflow-info { min-width: 0; flex: 1; }
.workflow-name {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.workflow-id {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.log-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    border: 1px solid transparent;
}
.log-item.log-success { background: rgba(166, 227, 161, 0.1); border-color: rgba(166, 227, 161, 0.3); }
.log-item.log-error { background: rgba(243, 139, 168, 0.1); border-color: rgba(243, 139, 168, 0.3); }
.log-item.log-info { background: var(--bg-tertiary); }
.log-time {
    color: var(--text-muted);
    font-family: monospace;
    flex-shrink: 0;
    font-size: 0.75rem;
}
.log-message {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Empty States */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--text-muted);
}
.empty-state.compact { padding: 1.5rem 1rem; }
.empty-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.75rem;
    opacity: 0.6;
}
.empty-state.compact .empty-icon { font-size: 2rem; }

/* Info Card */
.info-card { font-size: 0.875rem; }
.info-card h4 {
    margin: 0 0 0.75rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.info-card p {
    color: var(--text-secondary);
    margin: 0 0 0.75rem;
}
.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
    color: var(--text-secondary);
}
.info-card li { margin: 0.35rem 0; }

/* Result Box */
.result-box {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: var(--radius);
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.8rem;
    max-height: 200px;
    overflow: auto;
    white-space: pre-wrap;
    word-break: break-all;
}
.result-box.success { background: rgba(166, 227, 161, 0.15); border: 1px solid #a6e3a1; color: #a6e3a1; }
.result-box.error { background: rgba(243, 139, 168, 0.15); border: 1px solid #f38ba8; color: #f38ba8; }

/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.alert-success { background: var(--success-bg); color: var(--success); border: 1px solid var(--success); }
.alert-error { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); }

/* Card header with action */
.card-header { position: relative; }
.card-header .btn-sm {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
}
.card-header .btn-ghost:hover { background: var(--bg-tertiary); }
.clear-log-btn {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 1rem;
}
.clear-log-btn:hover { background: var(--bg-tertiary); color: var(--danger); }
</style>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">✓ <?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">✗ <?= esc($error) ?></div>
<?php endif; ?>

<!-- Page Header -->
<div class="n8n-header">
    <div class="n8n-header-info">
        <h1>
            <span class="n8n-logo">⚡</span>
            n8n Integration Center
        </h1>
        <p class="page-subtitle">Connect your CMS with n8n workflow automation platform</p>
    </div>
    <div class="n8n-header-actions">
        <button type="button" class="btn btn-secondary" onclick="runHealthCheck()" id="health-btn">
            <span id="health-icon">🔍</span> Test Connection
        </button>
        <a href="https://docs.n8n.io/" target="_blank" class="btn btn-outline">📖 Docs</a>
    </div>
</div>

<!-- Main Grid -->
<div class="n8n-grid">
    <!-- Left Column -->
    <div class="n8n-main">
        <!-- Connection Status -->
        <div class="card status-card">
            <div class="card-body">
                <div class="status-indicator" id="connection-status">
                    <?php if ($config['enabled'] && !empty($config['base_url'])): ?>
                        <div class="status-dot status-checking"></div>
                        <span class="status-text">Checking connection...</span>
                    <?php elseif (!$config['enabled']): ?>
                        <div class="status-dot status-disabled"></div>
                        <span class="status-text">Integration disabled</span>
                    <?php else: ?>
                        <div class="status-dot status-unconfigured"></div>
                        <span class="status-text">Not configured</span>
                    <?php endif; ?>
                </div>
                <div class="status-details" id="status-details">
                    <?php if (!empty($config['base_url'])): ?>
                        <code><?= esc($config['base_url']) ?></code>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚙️ Connection Settings</h2>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/n8n-settings" id="settings-form">
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <label class="toggle-label">
                            <input type="checkbox" name="enabled" value="1" <?= $config['enabled'] ? 'checked' : '' ?>>
                            <span class="toggle-switch"></span>
                            <span class="toggle-text">Enable n8n Integration</span>
                        </label>
                    </div>

                    <div class="form-row">
                        <label for="base_url">Base URL <span class="required">*</span></label>
                        <input type="url" id="base_url" name="base_url" 
                               value="<?= esc($config['base_url']) ?>" 
                               placeholder="https://your-n8n-instance.com">
                        <p class="form-hint">Your n8n instance URL (without trailing slash)</p>
                    </div>

                    <div class="form-row">
                        <label for="auth_type">Authentication Type</label>
                        <select id="auth_type" name="auth_type" onchange="toggleAuthFields()">
                            <option value="none" <?= $config['auth_type'] === 'none' ? 'selected' : '' ?>>None (Public instance)</option>
                            <option value="apikey" <?= $config['auth_type'] === 'apikey' ? 'selected' : '' ?>>API Key<span class="tip"><span class="tip-text">Authentication key for n8n. Keep this secret.</span></span></option>
                            <option value="basic" <?= $config['auth_type'] === 'basic' ? 'selected' : '' ?>>Basic Auth</option>
                        </select>
                    </div>

                    <div id="apikey-fields" class="auth-fields" style="display: <?= $config['auth_type'] === 'apikey' ? 'block' : 'none' ?>;">
                        <div class="form-row" style="margin-bottom: 0;">
                            <label for="api_key">API Key (MANAGE channel)</label>
                            <input type="password" id="api_key" name="api_key"
                                   placeholder="<?= !empty($config['api_key']) ? '••••••••••••••••' : 'Enter API key' ?>">
                            <p class="form-hint">Used for workflow management (create, edit, activate). Leave blank to keep existing.</p>
                        </div>
                    </div>

                    <div class="form-row">
                        <label for="webhook_secret">Webhook Secret (EXECUTE channel)</label>
                        <input type="password" id="webhook_secret" name="webhook_secret"
                               placeholder="<?= !empty($config['webhook_secret']) ? '••••••••••••••••' : 'Enter webhook secret' ?>">
                        <p class="form-hint">Used for triggering workflows via webhooks. Independent from API Key. Leave blank to keep existing.</p>
                    </div>

                    <div id="basic-fields" class="auth-fields" style="display: <?= $config['auth_type'] === 'basic' ? 'block' : 'none' ?>;">
                        <div class="form-row-inline">
                            <div class="form-row" style="margin-bottom: 0;">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" 
                                       value="<?= esc($config['username']) ?>">
                            </div>
                            <div class="form-row" style="margin-bottom: 0;">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" 
                                       placeholder="<?= !empty($config['password']) ? '••••••••' : 'Enter password' ?>">
                                <p class="form-hint">Leave blank to keep existing</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-row-inline">
                        <div class="form-row">
                            <label for="timeout">Timeout (seconds)</label>
                            <input type="number" id="timeout" name="timeout" 
                                   value="<?= (int)$config['timeout'] ?>" min="1" max="60">
                        </div>
                        <div class="form-row">
                            <label style="visibility: hidden;">SSL</label>
                            <label class="toggle-label">
                                <input type="checkbox" name="verify_ssl" value="1" <?= $config['verify_ssl'] ? 'checked' : '' ?>>
                                <span class="toggle-switch"></span>
                                <span class="toggle-text">Verify SSL Certificate</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Save Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Webhook Tester -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🔗 Webhook Tester</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <label for="webhook_url">Webhook URL</label>
                    <input type="url" id="webhook_url" placeholder="https://your-n8n.com/webhook/xxx">
                </div>
                <div class="form-row">
                    <label for="webhook_payload">Payload (JSON)</label>
                    <textarea id="webhook_payload" rows="4" placeholder='{"test": true, "message": "Hello from CMS"}'></textarea>
                </div>
                <button type="button" class="btn btn-secondary" onclick="testWebhook()">🚀 Send Test Request</button>
                <div id="webhook-result" class="result-box" style="display: none;"></div>
            </div>
        </div>
    </div>

    <!-- Right Column (Sidebar) -->
    <div class="n8n-sidebar">
        <!-- Workflows -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📋 Active Workflows</h3>
                <button type="button" class="btn btn-sm btn-ghost" onclick="refreshWorkflows()" title="Refresh">🔄</button>
            </div>
            <div class="card-body" id="workflows-container">
                <?php if (!$config['enabled']): ?>
                    <div class="empty-state compact">
                        <span class="empty-icon">🔌</span>
                        <p>Enable integration to see workflows</p>
                    </div>
                <?php elseif (empty($config['base_url'])): ?>
                    <div class="empty-state compact">
                        <span class="empty-icon">⚙️</span>
                        <p>Configure Base URL first</p>
                    </div>
                <?php else: ?>
                    <div class="empty-state compact">
                        <span class="empty-icon">📋</span>
                        <p>Click refresh to load workflows</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Connection Log -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📊 Connection Log</h3>
                <?php if (!empty($logs)): ?>
                <form method="post" action="/admin/n8n-settings/clear-log" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="clear-log-btn" title="Clear log">🗑️</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-body" id="log-container">
                <?php if (!empty($logs)): ?>
                    <div class="log-list">
                        <?php foreach (array_slice($logs, -10) as $log): ?>
                            <div class="log-item log-<?= strpos($log, 'success') !== false ? 'success' : (strpos($log, 'error') !== false || strpos($log, 'fail') !== false ? 'error' : 'info') ?>">
                                <span class="log-time"><?= esc(substr($log, 1, 16)) ?></span>
                                <span class="log-message"><?= esc(substr($log, 19)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state compact">
                        <span class="empty-icon">📝</span>
                        <p>No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card info-card">
            <div class="card-body">
                <h4>ℹ️ About n8n Integration</h4>
                <p>Connect your CMS with n8n to automate:</p>
                <ul>
                    <li>Content publishing workflows</li>
                    <li>Social media automation</li>
                    <li>Email notifications</li>
                    <li>Data synchronization</li>
                    <li>Custom triggers & actions</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAuthFields() {
    const authType = document.getElementById('auth_type').value;
    document.getElementById('apikey-fields').style.display = authType === 'apikey' ? 'block' : 'none';
    document.getElementById('basic-fields').style.display = authType === 'basic' ? 'block' : 'none';
}

function runHealthCheck() {
    const statusEl = document.getElementById('connection-status');
    const detailsEl = document.getElementById('status-details');
    const btn = document.getElementById('health-btn');
    
    statusEl.innerHTML = '<div class="status-dot status-checking"></div><span class="status-text">Connecting...</span>';
    btn.disabled = true;
    
    fetch('/admin/n8n-settings/health', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                statusEl.innerHTML = '<div class="status-dot status-connected"></div><span class="status-text">Connected</span>';
                if (data.version) {
                    detailsEl.innerHTML = '<code>n8n v' + escHtml(data.version) + '</code>';
                }
            } else {
                statusEl.innerHTML = '<div class="status-dot status-error"></div><span class="status-text">Connection failed</span>';
                detailsEl.innerHTML = '<code style="color: var(--danger);">' + escHtml(data.error || 'Unknown error') + '</code>';
            }
        })
        .catch(err => {
            statusEl.innerHTML = '<div class="status-dot status-error"></div><span class="status-text">Error</span>';
            detailsEl.innerHTML = '<code style="color: var(--danger);">' + escHtml(err.message) + '</code>';
        })
        .finally(() => { btn.disabled = false; });
}

function refreshWorkflows() {
    const container = document.getElementById('workflows-container');
    container.innerHTML = '<div class="empty-state compact"><span class="empty-icon">⏳</span><p>Loading...</p></div>';
    
    fetch('/admin/n8n-settings/workflows')
        .then(r => r.json())
        .then(data => {
            if (data.ok && data.workflows && data.workflows.length > 0) {
                let html = '<div class="workflow-list">';
                data.workflows.forEach(wf => {
                    html += '<div class="workflow-item">';
                    html += '<div class="workflow-status ' + (wf.active ? 'active' : 'inactive') + '"></div>';
                    html += '<div class="workflow-info">';
                    html += '<span class="workflow-name">' + escHtml(wf.name) + '</span>';
                    html += '<span class="workflow-id">ID: ' + escHtml(wf.id) + '</span>';
                    html += '</div></div>';
                });
                html += '</div>';
                container.innerHTML = html;
            } else if (data.ok && (!data.workflows || data.workflows.length === 0)) {
                container.innerHTML = '<div class="empty-state compact"><span class="empty-icon">📭</span><p>No workflows found</p></div>';
            } else {
                container.innerHTML = '<div class="empty-state compact"><span class="empty-icon">⚠️</span><p>' + escHtml(data.error || 'Failed to load') + '</p></div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div class="empty-state compact"><span class="empty-icon">❌</span><p>' + escHtml(err.message) + '</p></div>';
        });
}

function testWebhook() {
    const url = document.getElementById('webhook_url').value.trim();
    const payload = document.getElementById('webhook_payload').value.trim();
    const resultBox = document.getElementById('webhook-result');
    
    if (!url) {
        alert('Please enter a webhook URL');
        return;
    }
    
    resultBox.className = 'result-box';
    resultBox.textContent = 'Sending request...';
    resultBox.style.display = 'block';
    resultBox.style.background = 'var(--bg-tertiary)';
    resultBox.style.border = '1px solid var(--border)';
    resultBox.style.color = 'var(--text-secondary)';
    
    fetch('/admin/n8n-settings/webhook', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'webhook_url=' + encodeURIComponent(url) + '&payload=' + encodeURIComponent(payload)
    })
    .then(r => r.json())
    .then(data => {
        resultBox.className = 'result-box ' + (data.ok ? 'success' : 'error');
        let text = data.ok ? '✓ Success' : '✗ Failed';
        text += ' (HTTP ' + (data.statusCode || '?') + ')';
        if (data.response) text += '\n\nResponse:\n' + data.response;
        if (data.error) text += '\n\nError: ' + data.error;
        resultBox.textContent = text;
    })
    .catch(err => {
        resultBox.className = 'result-box error';
        resultBox.textContent = '✗ Request failed: ' + err.message;
    });
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Auto health check on load if configured
document.addEventListener('DOMContentLoaded', function() {
    const enabled = <?= $config['enabled'] ? 'true' : 'false' ?>;
    const hasUrl = <?= !empty($config['base_url']) ? 'true' : 'false' ?>;
    if (enabled && hasUrl) {
        setTimeout(runHealthCheck, 500);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once CMS_ROOT . '/app/views/admin/layouts/topbar.php';
