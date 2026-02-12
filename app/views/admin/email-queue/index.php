<?php
$title = 'Email Queue';
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
.eq-container { padding: 0; }
.eq-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.eq-title { display: flex; align-items: center; gap: 0.75rem; color: var(--ctp-text); font-size: 1.5rem; font-weight: 600; margin: 0; }
.eq-title svg { color: var(--ctp-blue); }
.eq-actions { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
.eq-tabs { display: flex; gap: 0.25rem; background: var(--ctp-surface0); padding: 0.25rem; border-radius: 8px; margin-bottom: 1rem; }
.eq-tab { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; color: var(--ctp-subtext0); font-size: 0.875rem; font-weight: 500; transition: all 0.2s; }
.eq-tab:hover { background: var(--ctp-surface1); color: var(--ctp-text); }
.eq-tab.active { background: var(--ctp-blue); color: var(--ctp-crust); }
.eq-tab .count { background: rgba(0,0,0,0.2); padding: 0.125rem 0.5rem; border-radius: 10px; font-size: 0.75rem; }
.eq-tab.active .count { background: rgba(255,255,255,0.2); }
.eq-btn { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
.eq-btn-primary { background: linear-gradient(135deg, var(--ctp-blue), var(--ctp-sapphire)); color: var(--ctp-crust); }
.eq-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
.eq-btn-secondary { background: var(--ctp-surface1); color: var(--ctp-text); border: 1px solid var(--ctp-surface2); }
.eq-btn-secondary:hover { background: var(--ctp-surface2); }
.eq-btn-danger { background: var(--ctp-red); color: var(--ctp-crust); }
.eq-btn-danger:hover { opacity: 0.9; }
.eq-btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
.eq-btn-icon { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
.eq-clear-form { display: flex; gap: 0.375rem; align-items: center; }
.eq-select { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface2); color: var(--ctp-text); padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
.eq-select:focus { outline: none; border-color: var(--ctp-blue); }
.eq-card { background: var(--ctp-surface0); border-radius: 12px; border: 1px solid var(--ctp-surface1); overflow: hidden; }
.eq-table { width: 100%; border-collapse: collapse; }
.eq-table th { background: var(--ctp-mantle); color: var(--ctp-subtext0); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.875rem 1rem; text-align: left; border-bottom: 1px solid var(--ctp-surface1); }
.eq-table td { padding: 0.875rem 1rem; color: var(--ctp-text); border-bottom: 1px solid var(--ctp-surface1); vertical-align: middle; }
.eq-table tr:last-child td { border-bottom: none; }
.eq-table tr:hover td { background: var(--ctp-surface1); }
.eq-checkbox { width: 18px; height: 18px; accent-color: var(--ctp-blue); cursor: pointer; }
.eq-email-to { font-weight: 500; color: var(--ctp-text); }
.eq-email-name { font-size: 0.8125rem; color: var(--ctp-subtext0); margin-top: 0.125rem; }
.eq-subject { max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.eq-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.eq-badge-pending { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.eq-badge-sending { background: rgba(137, 180, 250, 0.15); color: var(--ctp-blue); }
.eq-badge-sent { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.eq-badge-failed { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }
.eq-attempts { font-size: 0.75rem; color: var(--ctp-subtext0); margin-left: 0.375rem; }
.eq-priority { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; font-size: 0.875rem; font-weight: 600; }
.eq-priority-high { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }
.eq-priority-medium { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.eq-priority-low { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.eq-date { font-size: 0.875rem; color: var(--ctp-subtext1); }
.eq-row-actions { display: flex; gap: 0.25rem; }
.eq-footer { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--ctp-mantle); border-top: 1px solid var(--ctp-surface1); }
.eq-bulk { display: flex; gap: 0.5rem; align-items: center; }
.eq-pagination { display: flex; gap: 0.5rem; align-items: center; color: var(--ctp-subtext0); font-size: 0.875rem; }
.eq-empty { text-align: center; padding: 4rem 2rem; color: var(--ctp-subtext0); }
.eq-empty svg { width: 64px; height: 64px; margin-bottom: 1rem; opacity: 0.5; }
.eq-empty-title { font-size: 1.125rem; font-weight: 500; color: var(--ctp-text); margin-bottom: 0.5rem; }
.eq-alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
.eq-alert-success { background: rgba(166, 227, 161, 0.15); border: 1px solid var(--ctp-green); color: var(--ctp-green); }
.eq-alert-error { background: rgba(243, 139, 168, 0.15); border: 1px solid var(--ctp-red); color: var(--ctp-red); }
.eq-stats { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; }
.eq-stat { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 10px; padding: 1rem 1.5rem; flex: 1; display: flex; align-items: center; gap: 1rem; }
.eq-stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.eq-stat-icon.pending { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.eq-stat-icon.sent { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.eq-stat-icon.failed { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }
.eq-stat-icon.total { background: rgba(137, 180, 250, 0.15); color: var(--ctp-blue); }
.eq-stat-value { font-size: 1.75rem; font-weight: 700; color: var(--ctp-text); }
.eq-stat-label { font-size: 0.8125rem; color: var(--ctp-subtext0); text-transform: uppercase; letter-spacing: 0.05em; }
</style>

<div class="eq-container">
    <?php if (!empty($success)): ?>
        <div class="eq-alert eq-alert-success">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= esc($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="eq-alert eq-alert-error">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <div class="eq-header">
        <h1 class="eq-title">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email Queue
        </h1>
        <div class="eq-actions">
            <a href="/admin/email-queue/compose" class="eq-btn eq-btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Compose
            </a>
            <form method="post" action="/admin/email-queue/clear" class="eq-clear-form" onsubmit="return confirm('Delete old emails?');">
                <?= csrf_field() ?>
                <select name="days" class="eq-select">
                    <option value="7">7 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="90">90 days</option>
                </select>
                <select name="clear_status" class="eq-select">
                    <option value="sent">Sent only</option>
                    <option value="failed">Failed only</option>
                    <option value="all">All statuses</option>
                </select>
                <button type="submit" class="eq-btn eq-btn-danger eq-btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Clear Old
                </button>
            </form>
        </div>
    </div>

    <div class="eq-stats">
        <div class="eq-stat">
            <div class="eq-stat-icon total"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
            <div><div class="eq-stat-value"><?= $counts['all'] ?? 0 ?></div><div class="eq-stat-label">Total</div></div>
        </div>
        <div class="eq-stat">
            <div class="eq-stat-icon pending"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><div class="eq-stat-value"><?= $counts['pending'] ?? 0 ?></div><div class="eq-stat-label">Pending</div></div>
        </div>
        <div class="eq-stat">
            <div class="eq-stat-icon sent"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><div class="eq-stat-value"><?= $counts['sent'] ?? 0 ?></div><div class="eq-stat-label">Sent</div></div>
        </div>
        <div class="eq-stat">
            <div class="eq-stat-icon failed"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><div class="eq-stat-value"><?= $counts['failed'] ?? 0 ?></div><div class="eq-stat-label">Failed</div></div>
        </div>
    </div>

    <div class="eq-tabs">
        <a href="/admin/email-queue" class="eq-tab <?= !$currentStatus ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            All <span class="count"><?= $counts['all'] ?? 0 ?></span>
        </a>
        <a href="/admin/email-queue?status=pending" class="eq-tab <?= $currentStatus === 'pending' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Pending <span class="count"><?= $counts['pending'] ?? 0 ?></span>
        </a>
        <a href="/admin/email-queue?status=sent" class="eq-tab <?= $currentStatus === 'sent' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Sent <span class="count"><?= $counts['sent'] ?? 0 ?></span>
        </a>
        <a href="/admin/email-queue?status=failed" class="eq-tab <?= $currentStatus === 'failed' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Failed <span class="count"><?= $counts['failed'] ?? 0 ?></span>
        </a>
    </div>

    <div class="eq-card">
        <?php if (empty($emails)): ?>
            <div class="eq-empty">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5-9.77l-6.478-3.489a2.25 2.25 0 00-2.134 0L2.25 9"/></svg>
                <div class="eq-empty-title">No emails in queue</div>
                <p>Emails will appear here when queued for sending</p>
            </div>
        <?php else: ?>
            <form method="post" action="/admin/email-queue/bulk" id="bulkForm">
                <?= csrf_field() ?>
                <table class="eq-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAll" class="eq-checkbox"></th>
                            <th>Recipient</th>
                            <th>Subject <span class="tip"><span class="tip-text">Email subject line.</span></span></th>
                            <th>Status <span class="tip"><span class="tip-text">Pending, Sent or Failed.</span></span></th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emails as $email): ?>
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="<?= (int)$email['id'] ?>" class="eq-checkbox email-checkbox"></td>
                                <td>
                                    <div class="eq-email-to"><?= esc($email['to_email']) ?></div>
                                    <?php if ($email['to_name']): ?><div class="eq-email-name"><?= esc($email['to_name']) ?></div><?php endif; ?>
                                </td>
                                <td><div class="eq-subject"><?= esc($email['subject']) ?></div></td>
                                <td>
                                    <span class="eq-badge eq-badge-<?= esc($email['status']) ?>"><?= esc($email['status']) ?></span>
                                    <?php if ($email['attempts'] > 0): ?><span class="eq-attempts">(<?= (int)$email['attempts'] ?>/<?= (int)$email['max_attempts'] ?>)</span><?php endif; ?>
                                </td>
                                <td><?php $p = (int)$email['priority']; $pc = $p <= 3 ? 'high' : ($p <= 6 ? 'medium' : 'low'); ?><span class="eq-priority eq-priority-<?= $pc ?>"><?= $p ?></span></td>
                                <td><span class="eq-date"><?= date('M j, H:i', strtotime($email['created_at'])) ?></span></td>
                                <td>
                                    <div class="eq-row-actions">
                                        <a href="/admin/email-queue/<?= (int)$email['id'] ?>" class="eq-btn eq-btn-secondary eq-btn-icon" title="View"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                                        <?php if ($email['status'] === 'failed'): ?>
                                            <form method="post" action="/admin/email-queue/<?= (int)$email['id'] ?>/retry" style="display:inline;"><?= csrf_field() ?><button type="submit" class="eq-btn eq-btn-icon" style="background:rgba(166,227,161,0.15);color:var(--ctp-green);" title="Retry"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button></form>
                                        <?php endif; ?>
                                        <form method="post" action="/admin/email-queue/<?= (int)$email['id'] ?>/delete" onsubmit="return confirm('Delete?');" style="display:inline;"><?= csrf_field() ?><button type="submit" class="eq-btn eq-btn-icon" style="background:rgba(243,139,168,0.15);color:var(--ctp-red);" title="Delete"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="eq-footer">
                    <div class="eq-bulk">
                        <select name="action" class="eq-select"><option value="">Bulk Action</option><option value="delete">Delete Selected</option><option value="retry">Retry Selected</option></select>
                        <button type="submit" class="eq-btn eq-btn-secondary eq-btn-sm">Apply</button>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div class="eq-pagination">
                            <?php if ($page > 1): ?><a href="/admin/email-queue?page=<?= $page - 1 ?><?= $currentStatus ? '&status=' . esc($currentStatus) : '' ?>" class="eq-btn eq-btn-secondary eq-btn-sm"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Prev</a><?php endif; ?>
                            <span>Page <?= $page ?> of <?= $totalPages ?></span>
                            <?php if ($page < $totalPages): ?><a href="/admin/email-queue?page=<?= $page + 1 ?><?= $currentStatus ? '&status=' . esc($currentStatus) : '' ?>" class="eq-btn eq-btn-secondary eq-btn-sm">Next <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.email-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
