<?php
$title = 'View Email';
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
.ev-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.ev-title { display: flex; align-items: center; gap: 0.75rem; color: var(--ctp-text); font-size: 1.5rem; font-weight: 600; margin: 0; }
.ev-title svg { color: var(--ctp-lavender); }
.ev-btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
.ev-btn-primary { background: linear-gradient(135deg, var(--ctp-green), var(--ctp-teal)); color: var(--ctp-crust); }
.ev-btn-primary:hover { opacity: 0.9; }
.ev-btn-secondary { background: var(--ctp-surface1); color: var(--ctp-text); border: 1px solid var(--ctp-surface2); }
.ev-btn-secondary:hover { background: var(--ctp-surface2); }
.ev-btn-danger { background: var(--ctp-red); color: var(--ctp-crust); }
.ev-btn-danger:hover { opacity: 0.9; }
.ev-card { background: var(--ctp-surface0); border-radius: 12px; border: 1px solid var(--ctp-surface1); overflow: hidden; }
.ev-meta { padding: 1.5rem 2rem; display: grid; grid-template-columns: 140px 1fr; gap: 1rem; }
.ev-label { color: var(--ctp-subtext0); font-weight: 500; font-size: 0.875rem; }
.ev-value { color: var(--ctp-text); font-size: 0.9375rem; }
.ev-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.ev-badge-pending { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.ev-badge-sending { background: rgba(137, 180, 250, 0.15); color: var(--ctp-blue); }
.ev-badge-sent { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.ev-badge-failed { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }
.ev-error { color: var(--ctp-red); background: rgba(243, 139, 168, 0.1); padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
.ev-divider { height: 1px; background: var(--ctp-surface1); margin: 0; }
.ev-body-section { padding: 1.5rem 2rem; }
.ev-body-title { color: var(--ctp-text); font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem; }
.ev-body-title svg { color: var(--ctp-blue); }
.ev-body-content { background: var(--ctp-mantle); border: 1px solid var(--ctp-surface1); border-radius: 8px; padding: 1.5rem; max-height: 400px; overflow-y: auto; color: var(--ctp-text); line-height: 1.6; }
.ev-body-content pre { white-space: pre-wrap; margin: 0; font-family: inherit; }
.ev-actions { padding: 1.5rem 2rem; background: var(--ctp-mantle); border-top: 1px solid var(--ctp-surface1); display: flex; gap: 0.75rem; }
</style>

<div class="ev-header">
    <h1 class="ev-title">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Email Details
    </h1>
    <a href="/admin/email-queue" class="ev-btn ev-btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Queue
    </a>
</div>

<div class="ev-card">
    <div class="ev-meta">
        <span class="ev-label">Status</span>
        <span class="ev-value"><span class="ev-badge ev-badge-<?= esc($email['status']) ?>"><?= esc($email['status']) ?></span></span>
        
        <span class="ev-label">To</span>
        <span class="ev-value"><?= esc($email['to_email']) ?><?= $email['to_name'] ? ' <span style="color:var(--ctp-subtext0);">(' . esc($email['to_name']) . ')</span>' : '' ?></span>
        
        <span class="ev-label">Subject</span>
        <span class="ev-value"><?= esc($email['subject']) ?></span>
        
        <span class="ev-label">Priority</span>
        <span class="ev-value"><?= (int)$email['priority'] ?></span>
        
        <span class="ev-label">Attempts</span>
        <span class="ev-value"><?= (int)$email['attempts'] ?> / <?= (int)$email['max_attempts'] ?></span>
        
        <span class="ev-label">Created</span>
        <span class="ev-value"><?= date('M j, Y H:i:s', strtotime($email['created_at'])) ?></span>
        
        <?php if ($email['sent_at']): ?>
            <span class="ev-label">Sent</span>
            <span class="ev-value" style="color: var(--ctp-green);"><?= date('M j, Y H:i:s', strtotime($email['sent_at'])) ?></span>
        <?php endif; ?>
        
        <?php if ($email['last_error']): ?>
            <span class="ev-label">Last Error</span>
            <span class="ev-value"><span class="ev-error"><?= esc($email['last_error']) ?></span></span>
        <?php endif; ?>
    </div>
    
    <div class="ev-divider"></div>
    
    <div class="ev-body-section">
        <h3 class="ev-body-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Email Body
        </h3>
        <div class="ev-body-content">
            <?php if ($email['body_html']): ?>
                <?= $email['body_html'] ?>
            <?php elseif ($email['body_text']): ?>
                <pre><?= esc($email['body_text']) ?></pre>
            <?php else: ?>
                <span style="color: var(--ctp-overlay0);">No content</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="ev-actions">
        <?php if ($email['status'] === 'failed'): ?>
            <form method="post" action="/admin/email-queue/<?= (int)$email['id'] ?>/retry">
                <?= csrf_field() ?>
                <button type="submit" class="ev-btn ev-btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Retry
                </button>
            </form>
        <?php endif; ?>
        <form method="post" action="/admin/email-queue/<?= (int)$email['id'] ?>/delete" onsubmit="return confirm('Delete this email?');">
            <?= csrf_field() ?>
            <button type="submit" class="ev-btn ev-btn-danger">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
            </button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
