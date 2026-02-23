<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'System Updates';
$layout = 'admin';
ob_start();

$ver = $currentVersion['version'] ?? 'unknown';
$released = $currentVersion['released'] ?? '';
$codename = $currentVersion['codename'] ?? '';
$latest = $latestVersion['version'] ?? null;
$diskPct = $diskTotal > 0 ? round(($diskTotal - $diskFree) / $diskTotal * 100) : 0;
?>
<style>
.upd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.upd-card { background: var(--bg-card, #1e293b); border: 1px solid var(--border, #334155); border-radius: 10px; padding: 24px; }
.upd-card h3 { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted, #94a3b8); margin-bottom: 12px; }
.upd-value { font-size: 1.8rem; font-weight: 700; color: var(--text, #e2e8f0); }
.upd-value small { font-size: 0.8rem; font-weight: 400; color: var(--muted, #94a3b8); }
.upd-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
.upd-badge.green { background: rgba(34,197,94,0.15); color: #22c55e; }
.upd-badge.blue { background: rgba(99,102,241,0.15); color: #6366f1; }
.upd-badge.yellow { background: rgba(234,179,8,0.15); color: #eab308; }
.upd-info { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 16px; }
.upd-info-item { font-size: 0.85rem; }
.upd-info-item label { color: var(--muted, #94a3b8); font-size: 0.75rem; display: block; margin-bottom: 2px; }
.upd-bar { height: 6px; background: var(--border, #334155); border-radius: 3px; margin-top: 8px; }
.upd-bar-fill { height: 100%; border-radius: 3px; background: <?= $diskPct > 90 ? '#ef4444' : ($diskPct > 70 ? '#eab308' : '#22c55e') ?>; width: <?= $diskPct ?>%; }
.upd-btn { padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border, #334155); background: transparent; color: var(--text, #e2e8f0); cursor: pointer; font-size: 0.85rem; transition: all 0.2s; }
.upd-btn:hover { background: var(--primary, #6366f1); border-color: var(--primary, #6366f1); }
</style>

<h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 24px;">🔄 System Updates</h1>

<div class="upd-grid">
    <div class="upd-card">
        <h3>Current Version</h3>
        <div class="upd-value">
            v<?= h($ver) ?>
            <?php if ($codename): ?><small>"<?= h($codename) ?>"</small><?php endif; ?>
        </div>
        <?php if ($released): ?><div style="color:var(--muted);font-size:0.8rem;margin-top:4px;">Released: <?= h($released) ?></div><?php endif; ?>
    </div>

    <div class="upd-card">
        <h3>Update Status</h3>
        <?php if ($updateAvailable): ?>
            <div class="upd-value">v<?= h($latest) ?></div>
            <span class="upd-badge yellow">Update Available</span>
        <?php elseif ($latest): ?>
            <div class="upd-value" style="color:#22c55e;">Up to date ✓</div>
            <span class="upd-badge green">Latest version</span>
        <?php else: ?>
            <div class="upd-value" style="color:var(--muted);">Unknown</div>
            <span class="upd-badge blue">Could not check</span>
        <?php endif; ?>
        <div style="margin-top: 12px;">
            <button class="upd-btn" onclick="checkForUpdates()">Check Now</button>
            <?php if ($lastCheck): ?><span style="font-size:0.75rem;color:var(--muted);margin-left:8px;">Last checked: <?= h($lastCheck) ?></span><?php endif; ?>
        </div>
    </div>
</div>

<div class="upd-card">
    <h3>System Information</h3>
    <div class="upd-info">
        <div class="upd-info-item">
            <label>PHP Version</label>
            <?= h($phpVersion) ?>
        </div>
        <div class="upd-info-item">
            <label>MySQL Version</label>
            <?= h($mysqlVersion) ?>
        </div>
        <div class="upd-info-item">
            <label>Server</label>
            <?= h(php_uname('s') . ' ' . php_uname('r')) ?>
        </div>
        <div class="upd-info-item">
            <label>Disk Usage</label>
            <?= round(($diskTotal - $diskFree) / 1024 / 1024 / 1024, 1) ?>GB / <?= round($diskTotal / 1024 / 1024 / 1024, 1) ?>GB (<?= $diskPct ?>%)
            <div class="upd-bar"><div class="upd-bar-fill"></div></div>
        </div>
        <div class="upd-info-item">
            <label>Memory Limit</label>
            <?= ini_get('memory_limit') ?>
        </div>
        <div class="upd-info-item">
            <label>Max Upload</label>
            <?= ini_get('upload_max_filesize') ?>
        </div>
    </div>
</div>

<script>
function checkForUpdates() {
    fetch('/admin/updates/check', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
        if (d.updateAvailable) {
            alert('Update available: v' + d.latest + ' (current: v' + d.current + ')');
        } else {
            alert('You are running the latest version (v' + d.current + ')');
        }
        location.reload();
    })
    .catch(() => alert('Could not check for updates.'));
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
