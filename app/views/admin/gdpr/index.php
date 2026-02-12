<?php
$title = 'GDPR Tools';
ob_start();
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc($error) ?></div>
<?php endif; ?>


<div class="inline-help" id="help-gdpr">
    <span class="inline-help-icon">💡</span>
    <div><strong>GDPR Tools</strong> help you comply with data protection regulations. Export, anonymize, or delete user data on request. Required by law in the EU and many other regions.</div>
    <button class="inline-help-close" onclick="this.closest('.inline-help').style.display='none';localStorage.setItem('help-gdpr-hidden','1')" title="Dismiss">×</button>
</div>
<script>if(localStorage.getItem('help-gdpr-hidden'))document.getElementById('help-gdpr').style.display='none'</script>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">&#128202;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="stat-label">Total Actions</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">&#128230;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['exports'] ?? 0) ?></div>
        <div class="stat-label">Exports</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">&#128683;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['anonymizations'] ?? 0) ?></div>
        <div class="stat-label">Anonymizations</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon danger">&#128465;</div>
        </div>
        <div class="stat-value"><?= (int)($stats['deletions'] ?? 0) ?></div>
        <div class="stat-label">Deletions</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem; margin-top: 1.5rem;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent GDPR Actions</h2>
        </div>

        <?php if (empty($recentActions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128196;</div>
            <h3 class="empty-state-title">No Actions Yet</h3>
            <p class="empty-state-description">GDPR actions will appear here once you perform exports, anonymizations, or deletions.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Time</th>
                        <th style="width: 110px;">Action</th>
                        <th style="width: 90px;">User ID</th>
                        <th>Description</th>
                        <th style="width: 120px;">Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActions as $action): ?>
                    <tr>
                        <td style="color: var(--color-text-muted); font-size: 13px;">
                            <?= date('M j, Y H:i', strtotime($action['timestamp'] ?? 'now')) ?>
                        </td>
                        <td>
                            <?php
                            $actionType = $action['action'] ?? 'unknown';
                            $badgeClass = match($actionType) {
                                'export' => 'badge-success',
                                'anonymize' => 'badge-warning',
                                'delete' => 'badge-danger',
                                default => 'badge-default'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= esc(ucfirst($actionType)) ?></span>
                        </td>
                        <td style="color: var(--color-text-muted);">
                            <code style="font-size: 12px;"><?= esc($action['user_id'] ?? '-') ?></code>
                        </td>
                        <td><?= esc($action['description'] ?? '') ?></td>
                        <td style="color: var(--color-text-muted);">
                            <?= esc($action['admin_username'] ?? 'System') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div>
        <!-- Export Data -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Export Data</h3><span class="tip"><span class="tip-text">Download all data stored for a specific user (GDPR right of access).</span></span>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/gdpr/export">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="export_email">User Email</label>
                        <input type="email" class="form-input" id="export_email" name="email" placeholder="user@example.com" required>
                        <small class="form-hint">Export all data associated with this email.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Export User Data</button>
                </form>

                <?php if (!empty($_SESSION['gdpr_export'])): ?>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border);">
                    <a href="/admin/gdpr/download" class="btn btn-success" style="width: 100%;">Download Export</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Anonymize User -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Anonymize User</h3><span class="tip"><span class="tip-text">Replace personal data with anonymous values while keeping content.</span></span>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/gdpr/anonymize">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="anon_email">User Email</label>
                        <input type="email" class="form-input" id="anon_email" name="email" placeholder="user@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="anon_confirm">Type ANONYMIZE to confirm</label>
                        <input type="text" class="form-input" id="anon_confirm" name="confirm" placeholder="ANONYMIZE" required>
                        <small class="form-hint">This replaces all PII with anonymous data.</small>
                    </div>

                    <button type="submit" class="btn btn-warning" style="width: 100%;">Anonymize</button>
                </form>
            </div>
        </div>

        <!-- Delete User Data -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style="font-size: 1rem;">Delete User Data</h3><span class="tip"><span class="tip-text">Permanently remove all data for a user (GDPR right to erasure).</span></span>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/gdpr/delete" onsubmit="return confirm('WARNING: This will permanently delete all user data. This action cannot be undone. Are you sure?');">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label class="form-label" for="del_email">User Email</label>
                        <input type="email" class="form-input" id="del_email" name="email" placeholder="user@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="del_confirm">Type DELETE to confirm</label>
                        <input type="text" class="form-input" id="del_confirm" name="confirm" placeholder="DELETE" required>
                        <small class="form-hint">Right to be Forgotten - permanently removes all data.</small>
                    </div>

                    <button type="submit" class="btn btn-danger" style="width: 100%;">Delete All Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--color-text-muted);">
            <strong>GDPR Compliance:</strong> These tools help you comply with GDPR requirements including data portability (export), the right to be forgotten (delete), and data minimization (anonymize). All actions are logged for audit purposes.
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
