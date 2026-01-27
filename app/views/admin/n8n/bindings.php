<?php
/**
 * n8n Event Bindings Configuration
 * Modern Catppuccin dark theme UI with collapsible event groups
 */
$title = 'n8n Event Bindings';
ob_start();
?>

<style>
/* ==========================================
   CATPPUCCIN DARK THEME VARIABLES
   ========================================== */
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

/* ==========================================
   PAGE HEADER
   ========================================== */
.bindings-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.bindings-header-info { flex: 1; }
.bindings-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
    color: var(--ctp-text);
}
.bindings-header-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}
.n8n-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--ctp-peach), var(--ctp-red));
    border-radius: 12px;
    font-size: 22px;
}
.page-subtitle {
    color: var(--ctp-subtext0);
    margin: 0.5rem 0 0;
    font-size: 0.95rem;
}

/* ==========================================
   SUMMARY STATS
   ========================================== */
.bindings-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.stat-pill {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--ctp-surface0);
    border: 1px solid var(--ctp-surface1);
    border-radius: 20px;
    font-size: 0.875rem;
}
.stat-pill .stat-value {
    font-weight: 600;
    color: var(--ctp-text);
}
.stat-pill .stat-label {
    color: var(--ctp-subtext0);
}
.stat-pill.active .stat-value { color: var(--ctp-green); }
.stat-pill.inactive .stat-value { color: var(--ctp-overlay0); }

/* ==========================================
   EVENT GROUPS
   ========================================== */
.event-groups {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.event-group {
    background: var(--ctp-base);
    border: 1px solid var(--ctp-surface0);
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.2s;
}
.event-group:hover {
    border-color: var(--ctp-surface1);
}
.group-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: var(--ctp-mantle);
    cursor: pointer;
    user-select: none;
    transition: background 0.15s;
}
.group-header:hover {
    background: var(--ctp-surface0);
}
.group-header .group-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}
.group-header .group-title {
    flex: 1;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--ctp-text);
}
.group-count {
    font-size: 0.8rem;
    color: var(--ctp-subtext0);
    background: var(--ctp-surface1);
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
}
.group-chevron {
    color: var(--ctp-overlay0);
    transition: transform 0.2s;
    font-size: 1rem;
}
.event-group.open .group-chevron {
    transform: rotate(90deg);
}
.group-body {
    display: none;
    padding: 0.5rem;
}
.event-group.open .group-body {
    display: block;
}

/* ==========================================
   EVENT CARDS
   ========================================== */
.event-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: var(--ctp-surface0);
    border-radius: 8px;
    margin-bottom: 0.5rem;
    transition: background 0.15s;
}
.event-card:last-child {
    margin-bottom: 0;
}
.event-card:hover {
    background: var(--ctp-surface1);
}
.event-info {
    flex: 1;
    min-width: 0;
}
.event-key {
    display: inline-block;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.8rem;
    color: var(--ctp-mauve);
    background: rgba(203, 166, 247, 0.15);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.15s;
}
.event-key:hover {
    background: rgba(203, 166, 247, 0.25);
}
.event-key.copied {
    background: rgba(166, 227, 161, 0.25);
    color: var(--ctp-green);
}
.event-label {
    display: block;
    font-weight: 500;
    color: var(--ctp-text);
    margin: 0.35rem 0 0.2rem;
    font-size: 0.95rem;
}
.event-desc {
    font-size: 0.8rem;
    color: var(--ctp-subtext0);
    line-height: 1.4;
}
.event-binding {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}
.workflow-input {
    width: 180px;
    padding: 0.6rem 0.75rem;
    font-size: 0.85rem;
    font-family: 'Monaco', 'Menlo', monospace;
    color: var(--ctp-text);
    background: var(--ctp-mantle);
    border: 1px solid var(--ctp-surface1);
    border-radius: 6px;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.workflow-input:focus {
    outline: none;
    border-color: var(--ctp-blue);
    box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.2);
}
.workflow-input::placeholder {
    color: var(--ctp-overlay0);
}

/* ==========================================
   TOGGLE SWITCH
   ========================================== */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--ctp-surface1);
    border-radius: 24px;
    transition: all 0.2s;
}
.toggle-slider::before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: var(--ctp-overlay0);
    border-radius: 50%;
    transition: all 0.2s;
}
.toggle-switch input:checked + .toggle-slider {
    background-color: var(--ctp-green);
}
.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(20px);
    background-color: var(--ctp-crust);
}
.toggle-switch input:focus + .toggle-slider {
    box-shadow: 0 0 0 3px rgba(166, 227, 161, 0.3);
}

/* ==========================================
   OPEN IN N8N BUTTON
   ========================================== */
.btn-open {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--ctp-mantle);
    border: 1px solid var(--ctp-surface1);
    border-radius: 6px;
    color: var(--ctp-subtext0);
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.15s;
}
.btn-open:hover {
    background: var(--ctp-surface0);
    color: var(--ctp-blue);
    border-color: var(--ctp-blue);
}
.btn-open.disabled {
    opacity: 0.4;
    pointer-events: none;
}

/* ==========================================
   STICKY FOOTER
   ========================================== */
.sticky-footer {
    position: sticky;
    bottom: 0;
    background: var(--ctp-mantle);
    border-top: 1px solid var(--ctp-surface0);
    padding: 1rem 1.5rem;
    margin: 1.5rem -24px -24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    z-index: 10;
}
.btn-save {
    flex: 1;
    max-width: 300px;
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    background: linear-gradient(135deg, var(--ctp-blue), var(--ctp-mauve));
    color: var(--ctp-crust);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(137, 180, 250, 0.3);
}
.btn-save:active {
    transform: translateY(0);
}
.footer-hint {
    color: var(--ctp-subtext0);
    font-size: 0.85rem;
}

/* ==========================================
   ALERTS
   ========================================== */
.alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.alert-success {
    background: rgba(166, 227, 161, 0.15);
    border: 1px solid rgba(166, 227, 161, 0.4);
    color: var(--ctp-green);
}
.alert-error {
    background: rgba(243, 139, 168, 0.15);
    border: 1px solid rgba(243, 139, 168, 0.4);
    color: var(--ctp-red);
}
.alert-warning {
    background: rgba(249, 226, 175, 0.15);
    border: 1px solid rgba(249, 226, 175, 0.4);
    color: var(--ctp-yellow);
}

/* ==========================================
   EMPTY STATE
   ========================================== */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--ctp-subtext0);
}
.empty-state .empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 1rem;
    opacity: 0.6;
}

/* ==========================================
   RESPONSIVE
   ========================================== */
@media (max-width: 768px) {
    .bindings-header {
        flex-direction: column;
    }
    .bindings-stats {
        flex-direction: column;
    }
    .event-card {
        flex-direction: column;
        align-items: flex-start;
    }
    .event-binding {
        width: 100%;
        margin-top: 0.75rem;
    }
    .workflow-input {
        flex: 1;
    }
    .sticky-footer {
        flex-direction: column;
    }
    .btn-save {
        max-width: none;
        width: 100%;
    }
}
</style>

<?php if ($saveMessage !== null): ?>
    <div class="alert <?= $saveSuccess ? 'alert-success' : 'alert-error' ?>">
        <?= $saveSuccess ? '&#10003;' : '&#10007;' ?> <?= esc($saveMessage) ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">&#10003; <?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">&#10007; <?= esc($error) ?></div>
<?php endif; ?>

<!-- Page Header -->
<div class="bindings-header">
    <div class="bindings-header-info">
        <h1>
            <span class="n8n-logo">&#9889;</span>
            n8n Event Bindings
        </h1>
        <p class="page-subtitle">Configure which n8n workflows are triggered by CMS events</p>
    </div>
    <div class="bindings-header-actions">
        <a href="/admin/n8n-settings" class="btn btn-secondary">&#9881; n8n Settings</a>
        <a href="https://docs.n8n.io/" target="_blank" class="btn btn-ghost">&#128214; Docs</a>
    </div>
</div>

<?php if ($bindingsError !== null): ?>
    <div class="alert alert-error">
        <strong>Error:</strong> <?= esc($bindingsError) ?>
    </div>
<?php elseif (empty($knownEvents)): ?>
    <div class="empty-state">
        <span class="empty-icon">&#128268;</span>
        <p><strong>No known n8n events are defined.</strong></p>
        <p>Please contact the system administrator.</p>
    </div>
<?php else: ?>
    <!-- Summary Stats -->
    <?php
    $totalEvents = count($knownEvents);
    $activeBindings = 0;
    $configuredBindings = 0;
    foreach ($knownEvents as $event) {
        $binding = $bindings[$event['key']] ?? [];
        if (!empty($binding['workflow_id'])) {
            $configuredBindings++;
            if (!empty($binding['enabled'])) {
                $activeBindings++;
            }
        }
    }
    ?>
    <div class="bindings-stats">
        <div class="stat-pill">
            <span class="stat-value"><?= $totalEvents ?></span>
            <span class="stat-label">Total Events</span>
        </div>
        <div class="stat-pill">
            <span class="stat-value"><?= $configuredBindings ?></span>
            <span class="stat-label">Configured</span>
        </div>
        <div class="stat-pill active">
            <span class="stat-value"><?= $activeBindings ?></span>
            <span class="stat-label">Active</span>
        </div>
        <div class="stat-pill inactive">
            <span class="stat-value"><?= $configuredBindings - $activeBindings ?></span>
            <span class="stat-label">Disabled</span>
        </div>
    </div>

    <!-- Main Form -->
    <form method="post" action="/admin/n8n-bindings" id="bindings-form">
        <input type="hidden" name="action" value="save_bindings">
        <?= csrf_field() ?>

        <div class="event-groups">
            <?php foreach ($eventGroups as $groupKey => $group): ?>
                <div class="event-group open" data-group="<?= esc($groupKey) ?>">
                    <div class="group-header" onclick="toggleGroup(this)">
                        <span class="group-icon"><?= $group['icon'] ?></span>
                        <span class="group-title"><?= esc($group['label']) ?></span>
                        <span class="group-count"><?= count($group['events']) ?> events</span>
                        <span class="group-chevron">&#8250;</span>
                    </div>
                    <div class="group-body">
                        <?php foreach ($group['events'] as $event): ?>
                            <?php
                            $key = $event['key'];
                            $binding = $bindings[$key] ?? [];
                            $workflowId = $binding['workflow_id'] ?? '';
                            $enabled = !empty($binding['enabled']);
                            $n8nUrl = !empty($n8nConfig['base_url']) ? rtrim($n8nConfig['base_url'], '/') : '';
                            $workflowLink = ($n8nUrl && $workflowId) ? $n8nUrl . '/workflow/' . $workflowId : '';
                            ?>
                            <div class="event-card">
                                <div class="event-info">
                                    <span class="event-key" onclick="copyEventKey(this, '<?= esc($key) ?>')" title="Click to copy"><?= esc($key) ?></span>
                                    <span class="event-label"><?= esc($event['label']) ?></span>
                                    <span class="event-desc"><?= esc($event['description']) ?></span>
                                </div>
                                <div class="event-binding">
                                    <input type="text"
                                           class="workflow-input"
                                           name="bindings[<?= esc($key) ?>][workflow_id]"
                                           value="<?= esc($workflowId) ?>"
                                           placeholder="workflow_id"
                                           data-event="<?= esc($key) ?>">
                                    <label class="toggle-switch" title="<?= $enabled ? 'Enabled' : 'Disabled' ?>">
                                        <input type="checkbox"
                                               name="bindings[<?= esc($key) ?>][enabled]"
                                               value="1"
                                               <?= $enabled ? 'checked' : '' ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <a href="<?= $workflowLink ? esc($workflowLink) : '#' ?>"
                                       target="_blank"
                                       class="btn-open <?= $workflowLink ? '' : 'disabled' ?>"
                                       title="<?= $workflowLink ? 'Open in n8n' : 'Enter workflow ID first' ?>">&#8599;</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sticky Footer -->
        <div class="sticky-footer">
            <span class="footer-hint">Changes are saved when you click the button</span>
            <button type="submit" class="btn-save">
                &#128190; Save All Bindings
            </button>
        </div>
    </form>
<?php endif; ?>

<script>
/**
 * Toggle group collapse/expand
 */
function toggleGroup(el) {
    const group = el.closest('.event-group');
    if (group) {
        group.classList.toggle('open');
    }
}

/**
 * Copy event key to clipboard
 */
function copyEventKey(el, key) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(key).then(() => {
            el.classList.add('copied');
            el.textContent = 'Copied!';
            setTimeout(() => {
                el.classList.remove('copied');
                el.textContent = key;
            }, 1500);
        });
    }
}

/**
 * Update open-in-n8n button when workflow ID changes
 */
document.querySelectorAll('.workflow-input').forEach(input => {
    input.addEventListener('input', function() {
        const card = this.closest('.event-card');
        const openBtn = card.querySelector('.btn-open');
        const baseUrl = '<?= esc(!empty($n8nConfig['base_url']) ? rtrim($n8nConfig['base_url'], '/') : '') ?>';
        const workflowId = this.value.trim();

        if (baseUrl && workflowId) {
            openBtn.href = baseUrl + '/workflow/' + encodeURIComponent(workflowId);
            openBtn.classList.remove('disabled');
        } else {
            openBtn.href = '#';
            openBtn.classList.add('disabled');
        }
    });
});

/**
 * Form validation before submit
 */
document.getElementById('bindings-form')?.addEventListener('submit', function(e) {
    // Optional: Add validation logic here
    const inputs = this.querySelectorAll('.workflow-input');
    let hasWarnings = false;

    inputs.forEach(input => {
        const card = input.closest('.event-card');
        const toggle = card.querySelector('input[type="checkbox"]');

        // Warn if enabled but no workflow ID
        if (toggle.checked && !input.value.trim()) {
            input.style.borderColor = 'var(--ctp-yellow)';
            hasWarnings = true;
        } else {
            input.style.borderColor = '';
        }
    });

    if (hasWarnings) {
        if (!confirm('Some enabled bindings have no workflow ID. Save anyway?')) {
            e.preventDefault();
        }
    }
});

/**
 * Expand all / Collapse all
 */
function expandAll() {
    document.querySelectorAll('.event-group').forEach(g => g.classList.add('open'));
}
function collapseAll() {
    document.querySelectorAll('.event-group').forEach(g => g.classList.remove('open'));
}
</script>

<?php
$content = ob_get_clean();
require_once CMS_ROOT . '/app/views/admin/layouts/topbar.php';
