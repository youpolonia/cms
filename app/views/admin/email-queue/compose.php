<?php
$title = 'Compose Email';
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
.ec-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.ec-title { display: flex; align-items: center; gap: 0.75rem; color: var(--ctp-text); font-size: 1.5rem; font-weight: 600; margin: 0; }
.ec-title svg { color: var(--ctp-green); }
.ec-btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
.ec-btn-primary { background: linear-gradient(135deg, var(--ctp-green), var(--ctp-teal)); color: var(--ctp-crust); }
.ec-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
.ec-btn-secondary { background: var(--ctp-surface1); color: var(--ctp-text); border: 1px solid var(--ctp-surface2); }
.ec-btn-secondary:hover { background: var(--ctp-surface2); }
.ec-card { background: var(--ctp-surface0); border-radius: 12px; border: 1px solid var(--ctp-surface1); padding: 2rem; max-width: 800px; }
.ec-form { display: flex; flex-direction: column; gap: 1.5rem; }
.ec-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.ec-field { display: flex; flex-direction: column; gap: 0.5rem; }
.ec-label { color: var(--ctp-subtext1); font-size: 0.875rem; font-weight: 500; }
.ec-label span { color: var(--ctp-red); }
.ec-input, .ec-select, .ec-textarea { background: var(--ctp-mantle); border: 1px solid var(--ctp-surface2); color: var(--ctp-text); padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.9375rem; transition: border-color 0.2s; }
.ec-input:focus, .ec-select:focus, .ec-textarea:focus { outline: none; border-color: var(--ctp-blue); }
.ec-input::placeholder, .ec-textarea::placeholder { color: var(--ctp-overlay0); }
.ec-textarea { min-height: 250px; resize: vertical; font-family: inherit; }
.ec-hint { color: var(--ctp-subtext0); font-size: 0.8125rem; }
.ec-actions { display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--ctp-surface1); margin-top: 0.5rem; }
.ec-alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; background: rgba(243, 139, 168, 0.15); border: 1px solid var(--ctp-red); color: var(--ctp-red); }
</style>

<?php if (!empty($error)): ?>
    <div class="ec-alert">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= esc($error) ?>
    </div>
<?php endif; ?>

<div class="ec-header">
    <h1 class="ec-title">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Compose Email
    </h1>
    <a href="/admin/email-queue" class="ec-btn ec-btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Queue
    </a>
</div>

<div class="ec-card">
    <form method="post" action="/admin/email-queue/send" class="ec-form">
        <?= csrf_field() ?>
        <div class="ec-row">
            <div class="ec-field">
                <label class="ec-label">To Email <span>*</span></label>
                <input type="email" name="to_email" class="ec-input" required placeholder="recipient@example.com">
            </div>
            <div class="ec-field">
                <label class="ec-label">To Name</label>
                <input type="text" name="to_name" class="ec-input" placeholder="Recipient Name">
            </div>
        </div>
        <div class="ec-field">
            <label class="ec-label">Subject <span>*</span></label>
            <input type="text" name="subject" class="ec-input" required placeholder="Email subject">
        </div>
        <div class="ec-field">
            <label class="ec-label">Message</label>
            <textarea name="body_html" class="ec-textarea" placeholder="Enter your message (HTML supported)"></textarea>
        </div>
        <div class="ec-row">
            <div class="ec-field">
                <label class="ec-label">Priority</label>
                <select name="priority" class="ec-select">
                    <option value="1">1 - Highest</option>
                    <option value="3">3 - High</option>
                    <option value="5" selected>5 - Normal</option>
                    <option value="7">7 - Low</option>
                    <option value="10">10 - Lowest</option>
                </select>
                <span class="ec-hint">Lower number = higher priority</span>
            </div>
        </div>
        <div class="ec-actions">
            <button type="submit" class="ec-btn ec-btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Add to Queue
            </button>
            <a href="/admin/email-queue" class="ec-btn ec-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
