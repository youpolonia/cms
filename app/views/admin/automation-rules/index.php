<?php
/**
 * Automation Rules - MVC View
 * Catppuccin Mocha dark theme with card-based layout
 */

if (!function_exists('esc')) {
    function esc(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

ob_start();
?>
<style>
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

.automation-rules-page {
    padding: 24px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Header Section */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--ctp-surface1);
}

.page-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.page-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--ctp-mauve) 0%, var(--ctp-blue) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(203, 166, 247, 0.3);
}

.page-icon svg {
    width: 24px;
    height: 24px;
    color: var(--ctp-crust);
}

.page-title-group h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0 0 4px 0;
}

.page-title-group p {
    font-size: 0.875rem;
    color: var(--ctp-subtext0);
    margin: 0;
}

.header-badges {
    display: flex;
    gap: 12px;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-total {
    background: var(--ctp-surface1);
    color: var(--ctp-text);
}

.badge-active {
    background: rgba(166, 227, 161, 0.15);
    color: var(--ctp-green);
}

/* Notice/Alert Section */
.notice {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.notice-success {
    background: rgba(166, 227, 161, 0.1);
    border: 1px solid rgba(166, 227, 161, 0.3);
    color: var(--ctp-green);
}

.notice-error {
    background: rgba(243, 139, 168, 0.1);
    border: 1px solid rgba(243, 139, 168, 0.3);
    color: var(--ctp-red);
}

.notice svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

/* Two Column Layout */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 24px;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    .form-panel {
        order: -1;
    }
}

/* Rules List */
.rules-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.rules-empty {
    background: var(--ctp-surface0);
    border-radius: 12px;
    padding: 48px 24px;
    text-align: center;
    color: var(--ctp-subtext0);
}

.rules-empty svg {
    width: 48px;
    height: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.rules-empty p {
    margin: 0 0 8px 0;
    font-size: 1rem;
}

.rules-empty small {
    color: var(--ctp-overlay1);
}

/* Rule Card */
.rule-card {
    background: var(--ctp-surface0);
    border-radius: 12px;
    border-left: 4px solid var(--ctp-overlay0);
    overflow: hidden;
    transition: all 0.2s ease;
}

.rule-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.rule-card[data-active="true"] {
    border-left-color: var(--ctp-green);
}

.rule-card[data-active="false"] {
    border-left-color: var(--ctp-overlay0);
}

.rule-header {
    display: flex;
    align-items: center;
    padding: 16px;
    gap: 12px;
}

.rule-status {
    flex-shrink: 0;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: block;
}

.status-dot.active {
    background: var(--ctp-green);
    box-shadow: 0 0 8px rgba(166, 227, 161, 0.5);
}

.status-dot.inactive {
    background: var(--ctp-overlay0);
}

.rule-info {
    flex: 1;
    min-width: 0;
}

.rule-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rule-notes {
    font-size: 0.8125rem;
    color: var(--ctp-subtext0);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rule-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    background: var(--ctp-surface1);
    color: var(--ctp-subtext1);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-icon:hover {
    background: var(--ctp-surface2);
    color: var(--ctp-text);
}

.btn-icon.btn-danger:hover {
    background: rgba(243, 139, 168, 0.2);
    color: var(--ctp-red);
}

.btn-icon.btn-toggle-active {
    background: rgba(166, 227, 161, 0.15);
    color: var(--ctp-green);
}

.btn-icon.btn-toggle-active:hover {
    background: rgba(166, 227, 161, 0.25);
}

.btn-icon svg {
    width: 18px;
    height: 18px;
}

.inline-form {
    display: inline;
}

/* Rule Flow Display */
.rule-flow {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: var(--ctp-mantle);
    border-top: 1px solid var(--ctp-surface1);
}

.flow-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
}

.flow-trigger {
    background: rgba(250, 179, 135, 0.15);
    color: var(--ctp-peach);
}

.flow-action {
    background: rgba(137, 180, 250, 0.15);
    color: var(--ctp-blue);
}

.flow-item svg {
    width: 14px;
    height: 14px;
}

.flow-arrow {
    color: var(--ctp-overlay1);
    font-size: 1.25rem;
}

/* Form Panel */
.form-panel {
    position: sticky;
    top: 24px;
    align-self: start;
}

.panel-card {
    background: var(--ctp-surface0);
    border-radius: 12px;
    overflow: hidden;
}

.panel-header {
    padding: 16px 20px;
    background: var(--ctp-surface1);
    border-bottom: 1px solid var(--ctp-surface2);
}

.panel-header h2 {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.panel-header svg {
    width: 20px;
    height: 20px;
    color: var(--ctp-mauve);
}

.panel-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--ctp-subtext1);
    margin-bottom: 8px;
}

.form-group input[type="text"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px 14px;
    background: var(--ctp-mantle);
    border: 1px solid var(--ctp-surface2);
    border-radius: 8px;
    color: var(--ctp-text);
    font-size: 0.9375rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    font-family: inherit;
}

.form-group input[type="text"]:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--ctp-mauve);
    box-shadow: 0 0 0 3px rgba(203, 166, 247, 0.15);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: var(--ctp-overlay0);
}

.form-group select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c7086' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 40px;
}

.form-group select optgroup {
    background: var(--ctp-mantle);
    color: var(--ctp-subtext0);
    font-weight: 600;
}

.form-group select option {
    background: var(--ctp-surface0);
    color: var(--ctp-text);
    padding: 8px;
}

.form-group small {
    display: block;
    margin-top: 6px;
    font-size: 0.8125rem;
    color: var(--ctp-overlay1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--ctp-surface1);
}

.btn-primary {
    flex: 1;
    padding: 12px 20px;
    background: linear-gradient(135deg, var(--ctp-mauve) 0%, var(--ctp-blue) 100%);
    border: none;
    border-radius: 8px;
    color: var(--ctp-crust);
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(203, 166, 247, 0.4);
}

.btn-secondary {
    padding: 12px 20px;
    background: var(--ctp-surface1);
    border: none;
    border-radius: 8px;
    color: var(--ctp-subtext1);
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-secondary:hover {
    background: var(--ctp-surface2);
    color: var(--ctp-text);
}

/* Info Panel */
.info-panel {
    background: var(--ctp-surface0);
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.info-panel h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ctp-text);
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-panel h3 svg {
    width: 18px;
    height: 18px;
    color: var(--ctp-sky);
}

.info-panel p {
    font-size: 0.875rem;
    color: var(--ctp-subtext0);
    margin: 0 0 10px 0;
    line-height: 1.6;
}

.info-panel code {
    background: var(--ctp-mantle);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.8125rem;
    color: var(--ctp-peach);
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
}

/* Error State */
.error-panel {
    background: rgba(243, 139, 168, 0.1);
    border: 1px solid rgba(243, 139, 168, 0.3);
    border-radius: 12px;
    padding: 20px;
    color: var(--ctp-red);
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.error-panel svg {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.error-panel strong {
    display: block;
    margin-bottom: 4px;
}
</style>

<div class="automation-rules-page">
    <!-- Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/>
                    <path d="M6 21V9a9 9 0 0 0 9 9"/>
                </svg>
            </div>
            <div class="page-title-group">
                <h1>Automation Rules</h1>
                <p>Connect CMS events to n8n workflows</p>
            </div>
        </div>
        <div class="header-badges">
            <span class="badge badge-total"><?php echo (int)$totalCount; ?> Rules</span>
            <span class="badge badge-active"><?php echo (int)$activeCount; ?> Active</span>
        </div>
    </div>

    <?php if ($message !== null): ?>
    <div class="notice notice-<?php echo esc($messageType); ?>">
        <?php if ($messageType === 'success'): ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?php endif; ?>
        <span><?php echo esc($message); ?></span>
    </div>
    <?php endif; ?>

    <?php if ($loadError !== null): ?>
    <div class="error-panel">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <div>
            <strong>Configuration Error</strong>
            <?php echo esc($loadError); ?>
        </div>
    </div>
    <?php else: ?>

    <div class="content-grid">
        <!-- Rules List -->
        <div class="rules-column">
            <div class="rules-list">
                <?php if (empty($rules)): ?>
                <div class="rules-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/>
                        <path d="M6 21V9a9 9 0 0 0 9 9"/>
                    </svg>
                    <p>No automation rules defined yet</p>
                    <small>Use the form to create your first rule</small>
                </div>
                <?php else: ?>
                    <?php foreach ($rules as $rule): ?>
                    <div class="rule-card" data-active="<?php echo ($rule['active'] ?? false) ? 'true' : 'false'; ?>">
                        <div class="rule-header">
                            <div class="rule-status">
                                <span class="status-dot <?php echo ($rule['active'] ?? false) ? 'active' : 'inactive'; ?>"></span>
                            </div>
                            <div class="rule-info">
                                <h3 class="rule-name"><?php echo esc($rule['name']); ?></h3>
                                <?php if (!empty($rule['notes'])): ?>
                                <p class="rule-notes"><?php echo esc($rule['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="rule-actions">
                                <a href="?edit=<?php echo esc($rule['id']); ?>" class="btn-icon" title="Edit Rule">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <form method="post" class="inline-form">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_rule">
                                    <input type="hidden" name="rule_id" value="<?php echo esc($rule['id']); ?>">
                                    <button type="submit" class="btn-icon <?php echo ($rule['active'] ?? false) ? 'btn-toggle-active' : ''; ?>" title="<?php echo ($rule['active'] ?? false) ? 'Disable Rule' : 'Enable Rule'; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
                                            <line x1="12" y1="2" x2="12" y2="12"/>
                                        </svg>
                                    </button>
                                </form>
                                <form method="post" class="inline-form" onsubmit="return confirm('Delete this rule?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete_rule">
                                    <input type="hidden" name="rule_id" value="<?php echo esc($rule['id']); ?>">
                                    <button type="submit" class="btn-icon btn-danger" title="Delete Rule">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="rule-flow">
                            <div class="flow-item flow-trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                </svg>
                                <span><?php echo esc($rule['event_key'] ?? ''); ?></span>
                            </div>
                            <div class="flow-arrow">→</div>
                            <div class="flow-item flow-action">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                </svg>
                                <span><?php echo esc($rule['action_config']['event'] ?? ''); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Info Panel -->
            <div class="info-panel">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    How Automation Rules Work
                </h3>
                <p>
                    Rules defined here trigger n8n webhooks when specific events occur in the CMS.
                    To activate a rule, other parts of the CMS must call the event handler.
                </p>
                <p>
                    <strong>Example:</strong> When a blog post is published, the system calls:
                </p>
                <p>
                    <code>automation_rules_handle_event('blog.post_published', ['post_id' => 123])</code>
                </p>
                <p>
                    This triggers all active rules matching that event key, sending the payload to n8n.
                </p>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <div class="panel-card">
                <div class="panel-header">
                    <h2>
                        <?php if ($editRule !== null): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit Rule
                        <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add New Rule
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="panel-body">
                    <form method="post">
                        <?php csrf_field(); ?>
                        <?php if ($editRule !== null): ?>
                        <input type="hidden" name="action" value="update_rule">
                        <input type="hidden" name="rule_id" value="<?php echo esc($editRule['id']); ?>">
                        <?php else: ?>
                        <input type="hidden" name="action" value="add_rule">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Rule Name</label>
                            <input type="text" id="name" name="name" required
                                   placeholder="e.g., Notify on New Post"
                                   value="<?php echo esc($editRule['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="event_key">Trigger Event</label>
                            <select id="event_key" name="event_key" required>
                                <option value="">Select an event...</option>
                                <?php foreach ($availableEvents as $group => $events): ?>
                                <optgroup label="<?php echo esc($group); ?>">
                                    <?php foreach ($events as $key => $label): ?>
                                    <option value="<?php echo esc($key); ?>" <?php echo (($editRule['event_key'] ?? '') === $key) ? 'selected' : ''; ?>>
                                        <?php echo esc($label); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="n8n_event">n8n Event Name</label>
                            <input type="text" id="n8n_event" name="n8n_event" required
                                   placeholder="e.g., cms_blog_published"
                                   value="<?php echo esc($editRule['action_config']['event'] ?? ''); ?>">
                            <small>This name will be sent to n8n webhook</small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (optional)</label>
                            <textarea id="notes" name="notes" rows="3"
                                      placeholder="Description of what this rule does..."><?php echo esc($editRule['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <?php echo $editRule !== null ? 'Update Rule' : 'Save Rule'; ?>
                            </button>
                            <?php if ($editRule !== null): ?>
                            <a href="/admin/automation-rules" class="btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once CMS_ROOT . '/app/views/admin/layouts/topbar.php';
