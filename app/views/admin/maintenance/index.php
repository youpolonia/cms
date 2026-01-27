<?php
$title = 'Maintenance Mode';
ob_start();

// Calculate uptime if maintenance is enabled
$uptimeDisplay = '—';
if ($settings['is_enabled'] && !empty($settings['enabled_at'])) {
    $enabledTime = strtotime($settings['enabled_at']);
    $diff = time() - $enabledTime;
    if ($diff < 60) {
        $uptimeDisplay = $diff . 's';
    } elseif ($diff < 3600) {
        $uptimeDisplay = floor($diff / 60) . 'm';
    } elseif ($diff < 86400) {
        $uptimeDisplay = floor($diff / 3600) . 'h ' . floor(($diff % 3600) / 60) . 'm';
    } else {
        $uptimeDisplay = floor($diff / 86400) . 'd ' . floor(($diff % 86400) / 3600) . 'h';
    }
}
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc($error) ?></div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon <?= $settings['is_enabled'] ? 'danger' : 'success' ?>">
                <?= $settings['is_enabled'] ? '&#128274;' : '&#10003;' ?>
            </div>
        </div>
        <div class="stat-value"><?= $settings['is_enabled'] ? 'MAINTENANCE' : 'ONLINE' ?></div>
        <div class="stat-label">Current Status</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">&#9201;</div>
        </div>
        <div class="stat-value"><?= esc($uptimeDisplay) ?></div>
        <div class="stat-label">Maintenance Duration</div>
    </div>
</div>

<!-- Main Content Grid -->
<div style="display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem; margin-top: 1.5rem;">

    <!-- Left Column -->
    <div>
        <!-- Status Card -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h2 class="card-title">Maintenance Status</h2>
            </div>
            <div class="card-body" style="text-align: center; padding: 2.5rem 2rem;">
                <?php if ($settings['is_enabled']): ?>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(243, 139, 168, 0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                        <span style="font-size: 2.25rem;">&#128274;</span>
                    </div>
                    <h3 style="color: var(--color-danger); margin: 0 0 0.5rem; font-size: 1.25rem;">MAINTENANCE MODE ACTIVE</h3>
                    <p style="color: var(--color-text-muted); margin: 0 0 1.5rem;">
                        Site is offline since <?= date('M j, Y H:i', strtotime($settings['enabled_at'])) ?>
                    </p>
                <?php else: ?>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(166, 227, 161, 0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                        <span style="font-size: 2.25rem;">&#10003;</span>
                    </div>
                    <h3 style="color: var(--color-success); margin: 0 0 0.5rem; font-size: 1.25rem;">SITE IS ONLINE</h3>
                    <p style="color: var(--color-text-muted); margin: 0 0 1.5rem;">
                        All visitors can access the website
                    </p>
                <?php endif; ?>

                <form method="post" action="/admin/maintenance/toggle" onsubmit="return confirm('<?= $settings['is_enabled'] ? 'Disable maintenance mode and put site online?' : 'Enable maintenance mode? Visitors will see maintenance page.' ?>');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn <?= $settings['is_enabled'] ? 'btn-success' : 'btn-danger' ?>" style="min-width: 200px; padding: 0.75rem 1.5rem;">
                        <?= $settings['is_enabled'] ? '&#128275; Disable Maintenance' : '&#128274; Enable Maintenance' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Maintenance Settings</h2>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/maintenance/update">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="message">Maintenance Message</label>
                        <textarea id="message" name="message" class="form-input" rows="4" required><?= esc($settings['message'] ?? '') ?></textarea>
                        <p class="form-hint">This message will be shown to visitors during maintenance</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="retry_after">Retry After</label>
                        <select id="retry_after" name="retry_after" class="form-select">
                            <option value="300" <?= ($settings['retry_after'] ?? 3600) == 300 ? 'selected' : '' ?>>5 minutes</option>
                            <option value="900" <?= ($settings['retry_after'] ?? 3600) == 900 ? 'selected' : '' ?>>15 minutes</option>
                            <option value="1800" <?= ($settings['retry_after'] ?? 3600) == 1800 ? 'selected' : '' ?>>30 minutes</option>
                            <option value="3600" <?= ($settings['retry_after'] ?? 3600) == 3600 ? 'selected' : '' ?>>1 hour</option>
                            <option value="7200" <?= ($settings['retry_after'] ?? 3600) == 7200 ? 'selected' : '' ?>>2 hours</option>
                            <option value="86400" <?= ($settings['retry_after'] ?? 3600) == 86400 ? 'selected' : '' ?>>24 hours</option>
                        </select>
                        <p class="form-hint">HTTP Retry-After header value sent to browsers</p>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Allowed IPs Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Allowed IPs</h3>
            </div>
            <div class="card-body">
                <p style="font-size: 0.875rem; color: var(--color-text-muted); margin: 0 0 1rem;">
                    These IPs can access the site during maintenance.
                </p>

                <?php if (empty($allowedIps)): ?>
                    <p style="color: var(--color-text-muted); font-style: italic; margin-bottom: 1rem;">No IPs allowed yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                        <?php foreach ($allowedIps as $ip): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; background: var(--color-bg-tertiary); border-radius: var(--radius-md);">
                                <code style="font-size: 0.875rem; color: var(--color-text);"><?= esc($ip) ?></code>
                                <form method="post" action="/admin/maintenance/remove-ip" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="ip" value="<?= esc($ip) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">&times;</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/admin/maintenance/add-ip" style="margin-top: 1rem;">
                    <?= csrf_field() ?>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="ip" class="form-input" placeholder="IP address" style="flex: 1;">
                        <button type="submit" class="btn btn-secondary btn-sm">Add</button>
                    </div>
                </form>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border);">
                    <p style="font-size: 0.75rem; color: var(--color-text-muted); margin: 0 0 0.5rem;">Your current IP:</p>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <code style="font-size: 0.875rem; background: var(--color-bg-tertiary); padding: 0.25rem 0.5rem; border-radius: var(--radius-sm);"><?= esc($currentIp) ?></code>
                        <?php if (!in_array($currentIp, $allowedIps)): ?>
                            <form method="post" action="/admin/maintenance/add-ip" style="display: inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="ip" value="<?= esc($currentIp) ?>">
                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem;">Add Me</button>
                            </form>
                        <?php else: ?>
                            <span class="badge badge-success">Allowed</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-body" style="padding: 1rem;">
                <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                    <span style="font-size: 1.25rem;">&#8505;</span>
                    <div>
                        <h4 style="font-size: 0.875rem; margin: 0 0 0.375rem; color: var(--color-text);">How it works</h4>
                        <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 0; line-height: 1.5;">
                            When enabled, visitors see the maintenance page with a 503 response. The admin panel remains accessible. Allowed IPs bypass maintenance completely.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
