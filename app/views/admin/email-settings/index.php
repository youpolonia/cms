<?php
$title = 'Email Settings';
ob_start();
?>

<style>
:root {
    --ctp-rosewater: #f5e0dc; --ctp-flamingo: #f2cdcd; --ctp-pink: #f5c2e7;
    --ctp-mauve: #cba6f7; --ctp-red: #f38ba8; --ctp-maroon: #eba0ac;
    --ctp-peach: #fab387; --ctp-yellow: #f9e2af; --ctp-green: #a6e3a1;
    --ctp-teal: #94e2d5; --ctp-sky: #89dceb; --ctp-sapphire: #74c7ec;
    --ctp-blue: #89b4fa; --ctp-lavender: #b4befe; --ctp-text: #cdd6f4;
    --ctp-subtext1: #bac2de; --ctp-subtext0: #a6adc8; --ctp-overlay2: #9399b2;
    --ctp-overlay1: #7f849c; --ctp-overlay0: #6c7086; --ctp-surface2: #585b70;
    --ctp-surface1: #45475a; --ctp-surface0: #313244; --ctp-base: #1e1e2e;
    --ctp-mantle: #181825; --ctp-crust: #11111b;
}

/* Layout */
.es-container { padding: 0; max-width: 1200px; }
.es-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.es-title { display: flex; align-items: center; gap: 0.75rem; color: var(--ctp-text); font-size: 1.5rem; font-weight: 600; margin: 0; }
.es-title svg { color: var(--ctp-blue); }
.es-subtitle { color: var(--ctp-subtext0); font-size: 0.875rem; margin-top: 0.25rem; }

/* Alerts */
.es-alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.875rem; animation: slideDown 0.3s ease; }
.es-alert-success { background: rgba(166, 227, 161, 0.15); border: 1px solid rgba(166, 227, 161, 0.3); color: var(--ctp-green); }
.es-alert-error { background: rgba(243, 139, 168, 0.15); border: 1px solid rgba(243, 139, 168, 0.3); color: var(--ctp-red); }
.es-alert-info { background: rgba(137, 180, 250, 0.15); border: 1px solid rgba(137, 180, 250, 0.3); color: var(--ctp-blue); }
.es-alert-warning { background: rgba(249, 226, 175, 0.15); border: 1px solid rgba(249, 226, 175, 0.3); color: var(--ctp-yellow); }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

/* Stats Cards */
.es-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.es-stat { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.2s; }
.es-stat:hover { border-color: var(--ctp-surface2); transform: translateY(-2px); }
.es-stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.es-stat-icon.pending { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.es-stat-icon.sent { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.es-stat-icon.failed { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }
.es-stat-icon.total { background: rgba(137, 180, 250, 0.15); color: var(--ctp-blue); }
.es-stat-value { font-size: 1.75rem; font-weight: 700; color: var(--ctp-text); line-height: 1; }
.es-stat-label { font-size: 0.75rem; color: var(--ctp-subtext0); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; }

/* Grid Layout */
.es-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 900px) { .es-grid { grid-template-columns: 1fr; } }

/* Cards */
.es-card { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 14px; overflow: hidden; }
.es-card-header { display: flex; align-items: center; gap: 0.75rem; padding: 1.25rem 1.5rem; background: var(--ctp-mantle); border-bottom: 1px solid var(--ctp-surface1); }
.es-card-header svg { color: var(--ctp-blue); flex-shrink: 0; }
.es-card-title { font-size: 1rem; font-weight: 600; color: var(--ctp-text); margin: 0; }
.es-card-body { padding: 1.5rem; }

/* Form Elements */
.es-form-group { margin-bottom: 1.25rem; }
.es-form-group:last-child { margin-bottom: 0; }
.es-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--ctp-text); margin-bottom: 0.5rem; }
.es-label.required::after { content: '*'; color: var(--ctp-red); margin-left: 0.25rem; }
.es-input, .es-select { width: 100%; padding: 0.75rem 1rem; font-size: 0.875rem; color: var(--ctp-text); background: var(--ctp-base); border: 1px solid var(--ctp-surface2); border-radius: 8px; transition: all 0.2s; }
.es-input::placeholder { color: var(--ctp-overlay0); }
.es-input:focus, .es-select:focus { outline: none; border-color: var(--ctp-blue); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.2); }
.es-input:invalid:not(:placeholder-shown) { border-color: var(--ctp-red); }
.es-input:valid:not(:placeholder-shown) { border-color: var(--ctp-green); }
.es-hint { font-size: 0.8125rem; color: var(--ctp-subtext0); margin-top: 0.375rem; }

/* Buttons */
.es-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-size: 0.875rem; font-weight: 500; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.es-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.es-btn-primary { background: linear-gradient(135deg, var(--ctp-blue), var(--ctp-sapphire)); color: var(--ctp-crust); box-shadow: 0 2px 8px rgba(137, 180, 250, 0.3); }
.es-btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(137, 180, 250, 0.4); }
.es-btn-secondary { background: var(--ctp-surface1); color: var(--ctp-text); border: 1px solid var(--ctp-surface2); }
.es-btn-secondary:hover:not(:disabled) { background: var(--ctp-surface2); }
.es-btn-success { background: linear-gradient(135deg, var(--ctp-green), var(--ctp-teal)); color: var(--ctp-crust); }
.es-btn-success:hover:not(:disabled) { transform: translateY(-1px); }
.es-btn-loading { position: relative; color: transparent !important; }
.es-btn-loading::after { content: ''; position: absolute; width: 18px; height: 18px; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Form Actions */
.es-form-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--ctp-surface1); }

/* Status Badge */
.es-status { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.es-status-success { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.es-status-warning { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.es-status-error { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }

/* SMTP Config Display */
.es-config-table { width: 100%; }
.es-config-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--ctp-surface1); }
.es-config-row:last-child { border-bottom: none; }
.es-config-label { color: var(--ctp-subtext0); font-size: 0.875rem; }
.es-config-value { color: var(--ctp-text); font-size: 0.875rem; font-weight: 500; font-family: 'JetBrains Mono', monospace; }
.es-config-masked { color: var(--ctp-overlay0); }

/* Test Email Section */
.es-test-form { display: flex; gap: 0.75rem; align-items: flex-end; }
.es-test-form .es-form-group { flex: 1; margin-bottom: 0; }
@media (max-width: 600px) { .es-test-form { flex-direction: column; align-items: stretch; } }

/* Info Box */
.es-info { background: var(--ctp-mantle); border-radius: 8px; padding: 1rem; margin-top: 1rem; font-size: 0.8125rem; color: var(--ctp-subtext1); }
.es-info code { background: var(--ctp-surface0); padding: 0.125rem 0.375rem; border-radius: 4px; font-family: 'JetBrains Mono', monospace; color: var(--ctp-pink); }

/* Link to Queue */
.es-link { display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background: var(--ctp-mantle); border-radius: 8px; text-decoration: none; color: var(--ctp-text); transition: all 0.2s; margin-top: 1rem; }
.es-link:hover { background: var(--ctp-surface0); color: var(--ctp-blue); }
.es-link svg { color: var(--ctp-blue); }
.es-link-text { flex: 1; }
.es-link-arrow { color: var(--ctp-overlay0); }
</style>

<div class="es-container">
    <?php if (!empty($success)): ?>
        <div class="es-alert es-alert-success" id="successAlert">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= esc($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="es-alert es-alert-error" id="errorAlert">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <div class="es-header">
        <div>
            <h1 class="es-title">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email Settings
            </h1>
            <p class="es-subtitle">Configure email sender details and delivery method</p>
        </div>
    </div>

    <!-- Queue Stats -->
    <div class="es-stats">
        <div class="es-stat">
            <div class="es-stat-icon total">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="es-stat-value"><?= (int)($queueStats['total'] ?? 0) ?></div>
                <div class="es-stat-label">Total Queued</div>
            </div>
        </div>
        <div class="es-stat">
            <div class="es-stat-icon pending">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="es-stat-value"><?= (int)($queueStats['pending'] ?? 0) ?></div>
                <div class="es-stat-label">Pending</div>
            </div>
        </div>
        <div class="es-stat">
            <div class="es-stat-icon sent">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="es-stat-value"><?= (int)($queueStats['sent'] ?? 0) ?></div>
                <div class="es-stat-label">Sent</div>
            </div>
        </div>
        <div class="es-stat">
            <div class="es-stat-icon failed">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="es-stat-value"><?= (int)($queueStats['failed'] ?? 0) ?></div>
                <div class="es-stat-label">Failed</div>
            </div>
        </div>
    </div>

    <div class="es-grid">
        <!-- Main Settings Form -->
        <div class="es-card">
            <div class="es-card-header">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <h2 class="es-card-title">Sender Configuration</h2>
            </div>
            <div class="es-card-body">
                <form method="POST" action="/admin/email-settings" id="settingsForm">
                    <?= csrf_field() ?>

                    <div class="es-form-group">
                        <label for="from_name" class="es-label">From Name</label><span class="tip"><span class="tip-text">Display name shown as sender (e.g. your site name).</span></span>
                        <input type="text" id="from_name" name="from_name" class="es-input"
                               value="<?= esc($settings['from_name'] ?? '') ?>"
                               placeholder="My CMS">
                        <p class="es-hint">Displayed as the sender name in outgoing emails</p>
                    </div>

                    <div class="es-form-group">
                        <label for="from_email" class="es-label">From Email</label><span class="tip"><span class="tip-text">Email address shown as sender in outgoing emails.</span></span>
                        <input type="email" id="from_email" name="from_email" class="es-input"
                               value="<?= esc($settings['from_email'] ?? '') ?>"
                               placeholder="noreply@example.com">
                        <p class="es-hint">Email address used as the sender in outgoing messages</p>
                    </div>

                    <div class="es-form-group">
                        <label for="reply_to_email" class="es-label">Reply-To Email</label>
                        <input type="email" id="reply_to_email" name="reply_to_email" class="es-input"
                               value="<?= esc($settings['reply_to_email'] ?? '') ?>"
                               placeholder="support@example.com">
                        <p class="es-hint">Optional reply-to address. Leave empty to use the sender address</p>
                    </div>

                    <div class="es-form-group">
                        <label for="send_mode" class="es-label">Send Mode</label>
                        <select id="send_mode" name="send_mode" class="es-select">
                            <option value="smtp" <?= ($settings['send_mode'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP (Recommended)</option>
                            <option value="phpmail" <?= ($settings['send_mode'] ?? '') === 'phpmail' ? 'selected' : '' ?>>PHP mail()</option>
                        </select>
                        <p class="es-hint">SMTP is recommended for better deliverability</p>
                    </div>

                    <div class="es-form-actions">
                        <button type="submit" class="es-btn es-btn-primary" id="saveBtn">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Save Settings
                        </button>
                    </div>
                </form>

                <div class="es-info">
                    These settings are stored in <code>config/email_settings.json</code>
                </div>
            </div>
        </div>

        <!-- Right Column: SMTP Status & Test -->
        <div>
            <!-- SMTP Configuration Status -->
            <div class="es-card" style="margin-bottom: 1.5rem;">
                <div class="es-card-header">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    <h2 class="es-card-title">SMTP Configuration</h2><span class="tip"><span class="tip-text">Server settings for sending emails. Use SMTP for reliable delivery.</span></span>
                </div>
                <div class="es-card-body">
                    <?php if ($smtpConfigured): ?>
                        <div class="es-status es-status-success" style="margin-bottom: 1rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            SMTP Configured
                        </div>
                        <div class="es-config-table">
                            <div class="es-config-row">
                                <span class="es-config-label">Host</span>
                                <span class="es-config-value"><?= esc($smtpHost ?? 'Not set') ?></span>
                            </div>
                            <div class="es-config-row">
                                <span class="es-config-label">Port</span>
                                <span class="es-config-value"><?= esc($smtpPort ?? '587') ?></span>
                            </div>
                            <div class="es-config-row">
                                <span class="es-config-label">Username</span>
                                <span class="es-config-value"><?= $smtpUser ? esc($smtpUser) : '<span class="es-config-masked">Not set</span>' ?></span>
                            </div>
                            <div class="es-config-row">
                                <span class="es-config-label">Encryption</span>
                                <span class="es-config-value"><?= esc(strtoupper($smtpEncryption ?? 'TLS')) ?></span>
                            </div>
                        </div>
                        <div class="es-info">
                            SMTP credentials are configured in <code>config.php</code>
                        </div>
                    <?php else: ?>
                        <div class="es-status es-status-warning" style="margin-bottom: 1rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            SMTP Not Configured
                        </div>
                        <p style="color: var(--ctp-subtext0); font-size: 0.875rem; margin-bottom: 1rem;">
                            To use SMTP, add the following constants to your <code>config.php</code>:
                        </p>
                        <pre style="background: var(--ctp-mantle); padding: 1rem; border-radius: 8px; font-size: 0.8125rem; overflow-x: auto; color: var(--ctp-text);">define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@example.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_ENCRYPTION', 'tls');</pre>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Test Email -->
            <div class="es-card">
                <div class="es-card-header">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <h2 class="es-card-title">Send Test Email</h2>
                </div>
                <div class="es-card-body">
                    <form method="POST" action="/admin/email-settings/test" id="testForm">
                        <?= csrf_field() ?>
                        <div class="es-test-form">
                            <div class="es-form-group">
                                <label for="test_email" class="es-label">Recipient Email</label>
                                <input type="email" id="test_email" name="test_email" class="es-input"
                                       placeholder="test@example.com" required>
                            </div>
                            <button type="submit" class="es-btn es-btn-success" id="testBtn">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Send Test
                            </button>
                        </div>
                    </form>
                    <p class="es-hint" style="margin-top: 0.75rem;">
                        Sends a test email using the current configuration
                    </p>
                </div>
            </div>

            <!-- Link to Email Queue -->
            <a href="/admin/email-queue" class="es-link">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="es-link-text">
                    <strong>Email Queue</strong>
                    <span style="display: block; font-size: 0.8125rem; color: var(--ctp-subtext0);">View and manage queued emails</span>
                </span>
                <svg class="es-link-arrow" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.es-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });

    // Real-time email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            if (this.value && !this.checkValidity()) {
                this.style.borderColor = 'var(--ctp-red)';
            } else if (this.value) {
                this.style.borderColor = 'var(--ctp-green)';
            } else {
                this.style.borderColor = '';
            }
        });
    });

    // Loading state for buttons
    const settingsForm = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveBtn');
    if (settingsForm && saveBtn) {
        settingsForm.addEventListener('submit', function() {
            saveBtn.classList.add('es-btn-loading');
            saveBtn.disabled = true;
        });
    }

    const testForm = document.getElementById('testForm');
    const testBtn = document.getElementById('testBtn');
    if (testForm && testBtn) {
        testForm.addEventListener('submit', function() {
            testBtn.classList.add('es-btn-loading');
            testBtn.disabled = true;
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
