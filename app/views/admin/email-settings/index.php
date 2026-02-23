<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = 'Email Settings';
ob_start();
?>

<style>
/* Catppuccin dark theme */
.es-wrap { max-width: 1100px; padding: 0; }
.es-header { margin-bottom: 1.5rem; }
.es-title { display: flex; align-items: center; gap: 0.75rem; color: #e2e8f0; font-size: 1.5rem; font-weight: 600; margin: 0; }
.es-title svg { color: #89b4fa; }
.es-subtitle { color: #94a3b8; font-size: 0.875rem; margin-top: 0.25rem; }

/* Alerts */
.es-alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.875rem; animation: esFadeIn .3s ease; }
.es-alert-ok { background: rgba(166,227,161,.12); border: 1px solid rgba(166,227,161,.3); color: #a6e3a1; }
.es-alert-err { background: rgba(243,139,168,.12); border: 1px solid rgba(243,139,168,.3); color: #f38ba8; }
@keyframes esFadeIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

/* Grid */
.es-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 900px) { .es-grid { grid-template-columns: 1fr; } }

/* Card */
.es-card { background: #1e293b; border: 1px solid #334155; border-radius: 14px; overflow: hidden; }
.es-card-hd { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; background: #0f172a; border-bottom: 1px solid #334155; }
.es-card-hd svg { color: #89b4fa; flex-shrink: 0; }
.es-card-hd h2 { font-size: 1rem; font-weight: 600; color: #e2e8f0; margin: 0; }
.es-card-bd { padding: 1.25rem; }

/* Form */
.fg { margin-bottom: 1.1rem; }
.fg:last-child { margin-bottom: 0; }
.fg label { display: block; font-size: .875rem; font-weight: 500; color: #e2e8f0; margin-bottom: .4rem; }
.fg .req::after { content: ' *'; color: #f38ba8; }
.fg input, .fg select { width: 100%; padding: .65rem .85rem; font-size: .875rem; color: #e2e8f0; background: #0f172a; border: 1px solid #334155; border-radius: 8px; transition: border-color .2s; }
.fg input:focus, .fg select:focus { outline: none; border-color: #89b4fa; box-shadow: 0 0 0 3px rgba(137,180,250,.18); }
.fg input::placeholder { color: #64748b; }
.fg .hint { font-size: .8rem; color: #94a3b8; margin-top: .3rem; }

/* Row */
.fg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 600px) { .fg-row { grid-template-columns: 1fr; } }

/* Buttons */
.es-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .7rem 1.4rem; font-size: .875rem; font-weight: 500; border-radius: 8px; border: none; cursor: pointer; transition: all .2s; }
.es-btn:disabled { opacity: .55; cursor: not-allowed; }
.es-btn-primary { background: linear-gradient(135deg, #6366f1, #818cf8); color: #fff; }
.es-btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.35); }
.es-btn-green { background: linear-gradient(135deg, #10b981, #34d399); color: #fff; }
.es-btn-green:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16,185,129,.35); }

.es-actions { display: flex; gap: .75rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #334155; }

/* Test form */
.es-test { display: flex; gap: .75rem; align-items: flex-end; }
.es-test .fg { flex: 1; margin-bottom: 0; }
@media (max-width: 600px) { .es-test { flex-direction: column; align-items: stretch; } }

/* Status badge */
.es-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .7rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
.es-badge-ok  { background: rgba(166,227,161,.12); color: #a6e3a1; }
.es-badge-warn { background: rgba(249,226,175,.12); color: #f9e2af; }
</style>

<div class="es-wrap">

<?php if (!empty($success)): ?>
    <div class="es-alert es-alert-ok">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= h($success) ?>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="es-alert es-alert-err">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= h($error) ?>
    </div>
<?php endif; ?>

<div class="es-header">
    <h1 class="es-title">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Email Settings
    </h1>
    <p class="es-subtitle">Configure SMTP server, sender details, and test delivery</p>
</div>

<div class="es-grid">
    <!-- Left: SMTP Configuration -->
    <div class="es-card">
        <div class="es-card-hd">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
            <h2>SMTP Server</h2>
            <?php if (!empty($settings['smtp_host'])): ?>
                <span class="es-badge es-badge-ok">✓ Configured</span>
            <?php else: ?>
                <span class="es-badge es-badge-warn">Not set</span>
            <?php endif; ?>
        </div>
        <div class="es-card-bd">
            <form method="POST" action="/admin/email-settings" id="smtpForm">
                <?= csrf_field() ?>

                <div class="fg">
                    <label><span class="req">SMTP Host</span></label>
                    <input type="text" name="smtp_host" value="<?= h($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                    <p class="hint">e.g. smtp.gmail.com, smtp.mailgun.org</p>
                </div>

                <div class="fg-row">
                    <div class="fg">
                        <label>Port</label>
                        <input type="number" name="smtp_port" value="<?= h($settings['smtp_port'] ?? '587') ?>" placeholder="587" min="1" max="65535">
                    </div>
                    <div class="fg">
                        <label>Encryption</label>
                        <select name="smtp_encryption">
                            <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (port 587)</option>
                            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (port 465)</option>
                            <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None (port 25)</option>
                        </select>
                    </div>
                </div>

                <div class="fg">
                    <label>Username</label>
                    <input type="text" name="smtp_user" value="<?= h($settings['smtp_user'] ?? '') ?>" placeholder="user@example.com" autocomplete="off">
                </div>

                <div class="fg">
                    <label>Password</label>
                    <input type="password" name="smtp_pass" value="" placeholder="<?= !empty($settings['smtp_pass']) ? '••••••••' : '' ?>" autocomplete="new-password">
                    <p class="hint">Leave blank to keep the current password</p>
                </div>

                <div class="es-actions">
                    <button type="submit" class="es-btn es-btn-primary">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save SMTP Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right column -->
    <div>
        <!-- Sender Info -->
        <div class="es-card" style="margin-bottom: 1.5rem;">
            <div class="es-card-hd">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h2>Sender Details</h2>
            </div>
            <div class="es-card-bd">
                <form method="POST" action="/admin/email-settings" id="senderForm">
                    <?= csrf_field() ?>
                    <!-- Carry existing SMTP values so they don't get blanked -->
                    <input type="hidden" name="smtp_host" value="<?= h($settings['smtp_host'] ?? '') ?>">
                    <input type="hidden" name="smtp_port" value="<?= h($settings['smtp_port'] ?? '587') ?>">
                    <input type="hidden" name="smtp_user" value="<?= h($settings['smtp_user'] ?? '') ?>">
                    <input type="hidden" name="smtp_pass" value="">
                    <input type="hidden" name="smtp_encryption" value="<?= h($settings['smtp_encryption'] ?? 'tls') ?>">

                    <div class="fg">
                        <label>From Name</label>
                        <input type="text" name="smtp_from_name" value="<?= h($settings['smtp_from_name'] ?? '') ?>" placeholder="My Website">
                    </div>
                    <div class="fg">
                        <label>From Email</label>
                        <input type="email" name="smtp_from_email" value="<?= h($settings['smtp_from_email'] ?? '') ?>" placeholder="noreply@example.com">
                    </div>

                    <div class="es-actions">
                        <button type="submit" class="es-btn es-btn-primary">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Save Sender
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Test Email -->
        <div class="es-card">
            <div class="es-card-hd">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <h2>Send Test Email</h2>
            </div>
            <div class="es-card-bd">
                <form method="POST" action="/admin/email-settings/test" id="testForm">
                    <?= csrf_field() ?>
                    <div class="es-test">
                        <div class="fg">
                            <label>Recipient</label>
                            <input type="email" name="test_email" placeholder="test@example.com" required>
                        </div>
                        <button type="submit" class="es-btn es-btn-green">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Test
                        </button>
                    </div>
                    <p class="hint" style="margin-top:.6rem;">Uses the saved SMTP configuration above</p>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
    // auto-dismiss alerts
    document.querySelectorAll('.es-alert').forEach(function(a){
        setTimeout(function(){
            a.style.transition='opacity .3s,transform .3s';
            a.style.opacity='0';
            a.style.transform='translateY(-8px)';
            setTimeout(function(){ a.remove(); },300);
        },6000);
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
