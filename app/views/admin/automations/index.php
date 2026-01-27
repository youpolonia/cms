<?php
/**
 * Automations Management
 * Modern Catppuccin dark theme UI with card-based layout
 */
$title = 'Automations';
ob_start();
?>

<style>
/* Automations Styles - Catppuccin Dark Theme */
.automations-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.automations-header-info { flex: 1; }
.automations-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}
.automations-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #cba6f7, #89b4fa);
    border-radius: 12px;
    font-size: 22px;
}
.page-subtitle {
    color: var(--text-muted);
    margin: 0.5rem 0 0;
    font-size: 0.95rem;
}

/* Stats Badges */
.stats-badges {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.stats-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}
.stats-badge.total {
    background: rgba(137, 180, 250, 0.15);
    color: #89b4fa;
}
.stats-badge.enabled {
    background: rgba(166, 227, 161, 0.15);
    color: #a6e3a1;
}
.stats-badge.disabled {
    background: rgba(108, 112, 134, 0.25);
    color: #6c7086;
}

/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: slideIn 0.3s ease;
}
.alert-success { background: var(--success-bg); color: var(--success); border: 1px solid var(--success); }
.alert-error { background: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); }
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.alert.fade-out {
    animation: fadeOut 0.5s ease forwards;
}
@keyframes fadeOut {
    to { opacity: 0; transform: translateY(-10px); }
}

/* Grid Layout */
.automations-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}
@media (max-width: 1100px) {
    .automations-grid { grid-template-columns: 1fr; }
}

/* Automation Cards */
.automation-card {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.automation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
}
.automation-card.disabled { opacity: 0.7; }
.automation-card.disabled:hover { opacity: 0.85; }

.card-top {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
}
.card-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

/* Task-specific colors */
.card-icon.backup-task { background: linear-gradient(135deg, #89b4fa, #74c7ec); }
.card-icon.email-queue-task { background: linear-gradient(135deg, #a6e3a1, #94e2d5); }
.card-icon.cache-refresher-task { background: linear-gradient(135deg, #fab387, #f9e2af); }
.card-icon.session-cleaner-task { background: linear-gradient(135deg, #cba6f7, #f5c2e7); }
.card-icon.temp-cleaner-task { background: linear-gradient(135deg, #f38ba8, #eba0ac); }
.card-icon.log-rotation-task { background: linear-gradient(135deg, #f9e2af, #f5c2e7); }

.card-info { flex: 1; min-width: 0; }
.card-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}
.card-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.card-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.5;
}
.card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: var(--text-muted);
}
.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.meta-item svg {
    width: 14px;
    height: 14px;
    fill: currentColor;
}

/* Toggle Switch */
.toggle-label {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}
.toggle-label input { display: none; }
.toggle-switch {
    width: 44px;
    height: 24px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 12px;
    position: relative;
    transition: all 0.2s;
}
.toggle-switch::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
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
    transform: translateX(20px);
    background: white;
}

/* Card Footer */
.card-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-top: 1px solid var(--border);
}

/* Run Button */
.btn-run {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    background: var(--accent-muted);
    color: var(--accent);
    border: 1px solid transparent;
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.15s;
}
.btn-run:hover {
    background: var(--accent);
    color: #fff;
}
.btn-run:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.btn-run svg {
    width: 16px;
    height: 16px;
    fill: currentColor;
}
.btn-run.loading svg {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 12px;
}
.status-badge.enabled {
    background: rgba(166, 227, 161, 0.15);
    color: #a6e3a1;
}
.status-badge.disabled {
    background: rgba(108, 112, 134, 0.25);
    color: #6c7086;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
}
.empty-icon {
    font-size: 4rem;
    display: block;
    margin-bottom: 1rem;
    opacity: 0.6;
}
.empty-state h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.empty-state p {
    color: var(--text-muted);
}

/* Info Panel */
.info-panel {
    grid-column: 1 / -1;
    margin-top: 1rem;
}
.info-panel .card-body {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}
.info-panel-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}
.info-panel-content h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}
.info-panel-content p {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin: 0;
}
.info-panel-content ul {
    margin: 0.5rem 0 0 1.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}
.info-panel-content li {
    margin: 0.25rem 0;
}
</style>

<?php if (!empty($notice)): ?>
    <div class="alert alert-<?= esc($noticeType) ?>" id="notice-alert">
        <?= $noticeType === 'success' ? '&#10003;' : '&#10007;' ?> <?= esc($notice) ?>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="automations-header">
    <div class="automations-header-info">
        <h1>
            <span class="automations-logo">&#9889;</span>
            Automations
        </h1>
        <p class="page-subtitle">Manage scheduled tasks and automated processes for your CMS</p>
    </div>
    <div class="stats-badges">
        <span class="stats-badge total">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
            <?= (int)$totalCount ?> Total
        </span>
        <span class="stats-badge enabled">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <?= (int)$enabledCount ?> Active
        </span>
        <span class="stats-badge disabled">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4 12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z"/></svg>
            <?= (int)$disabledCount ?> Inactive
        </span>
    </div>
</div>

<?php if (empty($automations)): ?>
    <div class="empty-state">
        <span class="empty-icon">&#128268;</span>
        <h3>No Automations Configured</h3>
        <p>Automation tasks will appear here once configured in the system.</p>
    </div>
<?php else: ?>
    <div class="automations-grid">
        <?php foreach ($automations as $automation): ?>
            <?php
            $autoId = esc($automation['id'] ?? '');
            $autoName = esc($automation['name'] ?? 'Unnamed');
            $autoDesc = esc($automation['description'] ?? '');
            $autoInterval = esc($automation['interval'] ?? 'Unknown');
            $autoEnabled = $automation['enabled'] ?? false;
            $autoLastRun = $automation['last_run'] ?? null;
            $autoLastRunDisplay = $autoLastRun ? esc($autoLastRun) : 'Never';

            // Icons for each task type
            $icons = [
                'backup-task' => '&#128190;',
                'email-queue-task' => '&#128231;',
                'cache-refresher-task' => '&#128260;',
                'session-cleaner-task' => '&#128100;',
                'temp-cleaner-task' => '&#128465;',
                'log-rotation-task' => '&#128196;'
            ];
            $icon = $icons[$autoId] ?? '&#9881;';
            ?>
            <div class="automation-card <?= $autoEnabled ? '' : 'disabled' ?>">
                <div class="card-top">
                    <div class="card-icon <?= $autoId ?>">
                        <?= $icon ?>
                    </div>
                    <div class="card-info">
                        <div class="card-title-row">
                            <span class="card-name"><?= $autoName ?></span>
                            <form method="post" action="/admin/automations" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_enabled">
                                <input type="hidden" name="id" value="<?= $autoId ?>">
                                <label class="toggle-label" title="<?= $autoEnabled ? 'Disable' : 'Enable' ?>">
                                    <input type="checkbox" <?= $autoEnabled ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <span class="toggle-switch"></span>
                                </label>
                            </form>
                        </div>
                        <p class="card-description"><?= $autoDesc ?></p>
                        <div class="card-meta">
                            <span class="meta-item" title="Interval">
                                <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                <?= $autoInterval ?>
                            </span>
                            <span class="meta-item" title="Last Run">
                                <svg viewBox="0 0 24 24"><path d="M13 3a9 9 0 0 0-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21a9 9 0 0 0 0-18zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                                <?= $autoLastRunDisplay ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="status-badge <?= $autoEnabled ? 'enabled' : 'disabled' ?>">
                        <?= $autoEnabled ? '&#9679; Active' : '&#9675; Inactive' ?>
                    </span>
                    <form method="post" action="/admin/automations" style="display: inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="run_now">
                        <input type="hidden" name="id" value="<?= $autoId ?>">
                        <button type="submit" class="btn-run" onclick="return confirmRun(this, '<?= $autoName ?>')">
                            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span>Run Now</span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Info Panel -->
        <div class="info-panel">
            <div class="card">
                <div class="card-body">
                    <span class="info-panel-icon">&#128161;</span>
                    <div class="info-panel-content">
                        <h4>About Automations</h4>
                        <p>Automations are scheduled tasks that run periodically to maintain your CMS. They handle:</p>
                        <ul>
                            <li><strong>Backup Task</strong> - Creates backup archives of config and memory-bank directories</li>
                            <li><strong>Email Queue</strong> - Processes and delivers queued emails</li>
                            <li><strong>Cache Refresher</strong> - Refreshes application cache for optimal performance</li>
                            <li><strong>Session Cleaner</strong> - Removes expired user sessions</li>
                            <li><strong>Temp Cleaner</strong> - Cleans up old temporary files</li>
                            <li><strong>Log Rotation</strong> - Rotates and archives old log files</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Confirm before running
function confirmRun(btn, name) {
    if (!confirm('Run "' + name + '" now?')) {
        return false;
    }
    // Show loading state
    btn.classList.add('loading');
    btn.disabled = true;
    btn.querySelector('span').textContent = 'Running...';
    return true;
}

// Auto-hide notice after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    var notice = document.getElementById('notice-alert');
    if (notice) {
        setTimeout(function() {
            notice.classList.add('fade-out');
            setTimeout(function() {
                notice.style.display = 'none';
            }, 500);
        }, 5000);
    }
});
</script>

<?php
$content = ob_get_clean();
require_once CMS_ROOT . '/app/views/admin/layouts/topbar.php';
