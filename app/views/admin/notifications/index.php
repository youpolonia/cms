<?php
$title = 'Notifications';
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

.notif-container { padding: 0; max-width: 1200px; }
.notif-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.notif-title { display: flex; align-items: center; gap: 0.75rem; color: var(--ctp-text); font-size: 1.5rem; font-weight: 600; margin: 0; }
.notif-title svg { color: var(--ctp-yellow); }
.notif-subtitle { color: var(--ctp-subtext0); font-size: 0.875rem; margin-top: 0.25rem; }
.notif-actions { display: flex; gap: 0.75rem; }

.notif-alert { display: flex; align-items: center; gap: 0.75rem; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.875rem; animation: slideDown 0.3s ease; }
.notif-alert-success { background: rgba(166, 227, 161, 0.15); border: 1px solid rgba(166, 227, 161, 0.3); color: var(--ctp-green); }
.notif-alert-error { background: rgba(243, 139, 168, 0.15); border: 1px solid rgba(243, 139, 168, 0.3); color: var(--ctp-red); }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

.notif-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.notif-stat { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.2s; }
.notif-stat:hover { border-color: var(--ctp-surface2); transform: translateY(-2px); }
.notif-stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.25rem; }
.notif-stat-icon.total { background: rgba(137, 180, 250, 0.15); }
.notif-stat-icon.unread { background: rgba(249, 226, 175, 0.15); }
.notif-stat-icon.info { background: rgba(137, 180, 250, 0.15); }
.notif-stat-icon.warning { background: rgba(249, 226, 175, 0.15); }
.notif-stat-icon.error { background: rgba(243, 139, 168, 0.15); }
.notif-stat-icon.system { background: rgba(166, 227, 161, 0.15); }
.notif-stat-value { font-size: 1.5rem; font-weight: 700; color: var(--ctp-text); line-height: 1; }
.notif-stat-label { font-size: 0.75rem; color: var(--ctp-subtext0); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; }

.notif-filters { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.notif-filter-btn { padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 8px; border: 1px solid var(--ctp-surface1); background: var(--ctp-surface0); color: var(--ctp-subtext0); cursor: pointer; transition: all 0.2s; text-decoration: none; }
.notif-filter-btn:hover { background: var(--ctp-surface1); color: var(--ctp-text); }
.notif-filter-btn.active { background: var(--ctp-blue); color: var(--ctp-crust); border-color: var(--ctp-blue); }

.notif-list { display: flex; flex-direction: column; gap: 1rem; }
.notif-card { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; padding: 1.25rem; transition: all 0.2s; position: relative; }
.notif-card:hover { border-color: var(--ctp-surface2); }
.notif-card.unread { border-left: 3px solid var(--ctp-yellow); }
.notif-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; }
.notif-card-meta { display: flex; align-items: center; gap: 0.75rem; }
.notif-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.625rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.notif-badge.info { background: rgba(137, 180, 250, 0.15); color: var(--ctp-blue); }
.notif-badge.warning { background: rgba(249, 226, 175, 0.15); color: var(--ctp-yellow); }
.notif-badge.error { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }
.notif-badge.system { background: rgba(166, 227, 161, 0.15); color: var(--ctp-green); }
.notif-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ctp-yellow); }
.notif-time { font-size: 0.8125rem; color: var(--ctp-overlay1); }
.notif-message { font-size: 0.9375rem; color: var(--ctp-text); line-height: 1.5; margin-bottom: 0.75rem; }
.notif-context { font-size: 0.8125rem; color: var(--ctp-subtext0); background: var(--ctp-mantle); padding: 0.75rem; border-radius: 8px; font-family: monospace; word-break: break-all; }
.notif-delete-btn { background: transparent; border: none; color: var(--ctp-overlay1); cursor: pointer; padding: 0.5rem; border-radius: 6px; transition: all 0.2s; }
.notif-delete-btn:hover { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); }

.notif-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 500; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.notif-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.notif-btn-primary { background: linear-gradient(135deg, var(--ctp-blue), var(--ctp-sapphire)); color: var(--ctp-crust); }
.notif-btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(137, 180, 250, 0.3); }
.notif-btn-secondary { background: var(--ctp-surface1); color: var(--ctp-text); border: 1px solid var(--ctp-surface2); }
.notif-btn-secondary:hover:not(:disabled) { background: var(--ctp-surface2); }
.notif-btn-danger { background: rgba(243, 139, 168, 0.15); color: var(--ctp-red); border: 1px solid rgba(243, 139, 168, 0.3); }
.notif-btn-danger:hover:not(:disabled) { background: rgba(243, 139, 168, 0.25); }

.notif-empty { text-align: center; padding: 4rem 2rem; background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; }
.notif-empty-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
.notif-empty-title { font-size: 1.25rem; font-weight: 600; color: var(--ctp-text); margin-bottom: 0.5rem; }
.notif-empty-text { color: var(--ctp-subtext0); font-size: 0.9375rem; }
</style>

<div class="notif-container">
    <?php if (!empty($success)): ?>
        <div class="notif-alert notif-alert-success" id="successAlert">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= esc($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="notif-alert notif-alert-error" id="errorAlert">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <div class="notif-header">
        <div>
            <h1 class="notif-title">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notifications
            </h1>
            <p class="notif-subtitle">System alerts and notifications</p>
        </div>
        <div class="notif-actions">
            <?php if ($stats['unread'] > 0): ?>
            <form method="POST" action="/admin/notifications/mark-all-read" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="notif-btn notif-btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Mark All Read
                </button>
            </form>
            <?php endif; ?>
            <?php if ($stats['total'] > 0): ?>
            <form method="POST" action="/admin/notifications/clear-all" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete all notifications?');">
                <?= csrf_field() ?>
                <button type="submit" class="notif-btn notif-btn-danger">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Clear All
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="notif-stats">
        <div class="notif-stat">
            <div class="notif-stat-icon total">📬</div>
            <div>
                <div class="notif-stat-value"><?= (int)$stats['total'] ?></div>
                <div class="notif-stat-label">Total</div>
            </div>
        </div>
        <div class="notif-stat">
            <div class="notif-stat-icon unread">🔔</div>
            <div>
                <div class="notif-stat-value"><?= (int)$stats['unread'] ?></div>
                <div class="notif-stat-label">Unread</div>
            </div>
        </div>
        <div class="notif-stat">
            <div class="notif-stat-icon info">ℹ️</div>
            <div>
                <div class="notif-stat-value"><?= (int)$stats['info'] ?></div>
                <div class="notif-stat-label">Info</div>
            </div>
        </div>
        <div class="notif-stat">
            <div class="notif-stat-icon warning">⚠️</div>
            <div>
                <div class="notif-stat-value"><?= (int)$stats['warning'] ?></div>
                <div class="notif-stat-label">Warning</div>
            </div>
        </div>
        <div class="notif-stat">
            <div class="notif-stat-icon error">❌</div>
            <div>
                <div class="notif-stat-value"><?= (int)$stats['error'] ?></div>
                <div class="notif-stat-label">Error</div>
            </div>
        </div>
        <div class="notif-stat">
            <div class="notif-stat-icon system">⚙️</div>
            <div>
                <div class="notif-stat-value"><?= (int)$stats['system'] ?></div>
                <div class="notif-stat-label">System</div>
            </div>
        </div>
    </div>

    <div class="notif-filters">
        <?php foreach ($validFilters as $f): ?>
            <a href="?filter=<?= $f ?>" class="notif-filter-btn <?= $filter === $f ? 'active' : '' ?>">
                <?= ucfirst($f) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="notif-empty">
            <div class="notif-empty-icon">🔕</div>
            <div class="notif-empty-title">No notifications</div>
            <div class="notif-empty-text">
                <?= $filter !== 'all' ? "No {$filter} notifications found." : "You're all caught up!" ?>
            </div>
        </div>
    <?php else: ?>
        <div class="notif-list">
            <?php foreach ($notifications as $n): ?>
                <div class="notif-card <?= empty($n['read']) ? 'unread' : '' ?>">
                    <div class="notif-card-header">
                        <div class="notif-card-meta">
                            <span class="notif-badge <?= esc($n['type'] ?? 'info') ?>"><?= esc($n['type'] ?? 'info') ?></span>
                            <?php if (empty($n['read'])): ?>
                                <span class="notif-unread-dot" title="Unread"></span>
                            <?php endif; ?>
                            <span class="notif-time"><?= date('M j, Y \a\t g:i A', $n['timestamp'] ?? time()) ?></span>
                        </div>
                        <form method="POST" action="/admin/notifications/delete" style="margin:0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= esc($n['id'] ?? '') ?>">
                            <button type="submit" class="notif-delete-btn" title="Delete" onclick="return confirm('Delete this notification?');">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                    <div class="notif-message"><?= esc($n['message'] ?? '') ?></div>
                    <?php if (!empty($n['context'])): ?>
                        <div class="notif-context"><?= esc(is_array($n['context']) ? json_encode($n['context'], JSON_PRETTY_PRINT) : $n['context']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.notif-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
