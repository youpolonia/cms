<?php 
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../../core/csrf.php';

?><div class="container">
    <h1>System Settings</h1>
    
    <?php if (isset($_SERVER['HTTP_X_TENANT_ID'])): ?>
        <div class="alert alert-info mb-4">
            Current Tenant: <strong><?= htmlspecialchars($_SERVER['HTTP_X_TENANT_ID']) ?></strong>
            <?php if (!isset($_SESSION['tenant_id'])): ?>
                <span class="badge bg-warning text-dark ml-2">Global Fallback</span>
            <?php endif;  ?>
        </div>
    <?php endif;  ?>    
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Settings saved successfully!</div>
    <?php endif;  ?>
    <form method="post" action="<?= ADMIN_BASE ?>/system/settings/save">
        <?= csrf_field();  ?>        <?php if (!isset($_SESSION['tenant_id'])): ?>
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle"></i> You are editing global fallback settings that will apply to all tenants
            </div>
        <?php endif;  ?>
        <div class="card mb-4">
            <div class="card-header">Site Settings</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Site Title</label>
                    <input type="text" name="site_title" class="form-control" 
                           value="<?= htmlspecialchars($settings['site_title']) ?>">
                </div>
                <div class="form-group">
                    <label>Admin Email</label>
                    <input type="email" name="admin_email" class="form-control" 
                           value="<?= htmlspecialchars($settings['admin_email']) ?>">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="maintenance_mode" class="form-check-input" 
                           id="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Email Configuration</div>
            <div class="card-body">
                <div class="form-group">
                    <label>SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control" 
                           value="<?= htmlspecialchars($settings['smtp_host']) ?>">
                </div>
                <div class="form-group">
                    <label>SMTP Port</label>
                    <input type="number" name="smtp_port" class="form-control" 
                           value="<?= htmlspecialchars($settings['smtp_port']) ?>">
                </div>
                <div class="form-group">
                    <label>SMTP Username</label>
                    <input type="text" name="smtp_username" class="form-control" 
                           value="<?= htmlspecialchars($settings['smtp_username']) ?>">
                </div>
                <div class="form-group">
                    <label>SMTP Password</label>
                    <input type="password" name="smtp_password" class="form-control" 
                           value="<?= htmlspecialchars($settings['smtp_password']) ?>">
                </div>
                <div class="form-group">
                    <label>Security Protocol</label>
                    <select name="smtp_secure" class="form-control">
                        <option value="tls" <?= $settings['smtp_secure'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= $settings['smtp_secure'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/admin_footer.php';
