<?php
/**
 * Security Settings View
 * Displays and manages security settings
 */

if (!defined('ADMIN_SECURITY_INIT')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

$settings = $data['settings'] ?? [];
$policies = $data['policies'] ?? [];
$message = $data['message'] ?? null;
$errors = $data['errors'] ?? [];

// Helper function for escaping
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

// Convert settings array to keyed array for easy access
$settingsMap = [];
foreach ($settings as $setting) {
    $settingsMap[$setting['setting_key']] = $setting;
}

// Get setting value helper
function getSetting($key, $settingsMap, $default = '') {
    return $settingsMap[$key]['setting_value'] ?? $default;
}
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-cog"></i> Security Settings</h1>
        <a href="?action=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo empty($errors) ? 'success' : 'warning'; ?> alert-dismissible fade show">
            <?php echo esc($message); ?>
            <?php if (!empty($errors)): ?>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo esc($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="?action=save_settings" id="settingsForm">
        <?php csrf_field(); ?>

        <div class="row">
            <!-- Login Security -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login Security</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Maximum Login Attempts</label>
                            <input type="number" name="settings[max_login_attempts]" class="form-control"
                                   value="<?php echo esc(getSetting('max_login_attempts', $settingsMap, '5')); ?>"
                                   min="1" max="20">
                            <small class="text-muted">Number of failed attempts before lockout</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lockout Duration (seconds)</label>
                            <input type="number" name="settings[lockout_duration]" class="form-control"
                                   value="<?php echo esc(getSetting('lockout_duration', $settingsMap, '900')); ?>"
                                   min="60" max="86400">
                            <small class="text-muted">How long to lock out after max attempts (default: 15 min)</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="settings[two_factor_enabled]" value="0">
                                <input type="checkbox" name="settings[two_factor_enabled]" class="form-check-input"
                                       id="two_factor_enabled" value="1"
                                       <?php echo getSetting('two_factor_enabled', $settingsMap, '0') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="two_factor_enabled">
                                    Enable Two-Factor Authentication
                                </label>
                            </div>
                            <small class="text-muted">Require 2FA for admin users</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Security -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Session Security</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Session Timeout (seconds)</label>
                            <input type="number" name="settings[session_timeout]" class="form-control"
                                   value="<?php echo esc(getSetting('session_timeout', $settingsMap, '1800')); ?>"
                                   min="300" max="86400">
                            <small class="text-muted">Idle timeout before requiring re-login (default: 30 min)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CSRF Token Lifetime (seconds)</label>
                            <input type="number" name="settings[csrf_token_lifetime]" class="form-control"
                                   value="<?php echo esc(getSetting('csrf_token_lifetime', $settingsMap, '3600')); ?>"
                                   min="300" max="86400">
                            <small class="text-muted">How long CSRF tokens remain valid (default: 1 hour)</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="settings[ip_whitelist_enabled]" value="0">
                                <input type="checkbox" name="settings[ip_whitelist_enabled]" class="form-check-input"
                                       id="ip_whitelist_enabled" value="1"
                                       <?php echo getSetting('ip_whitelist_enabled', $settingsMap, '0') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ip_whitelist_enabled">
                                    Enable IP Whitelist for Admin
                                </label>
                            </div>
                            <small class="text-muted">Only allow admin access from whitelisted IPs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Password Policy -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-key"></i> Password Policy</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Minimum Password Length</label>
                            <input type="number" name="settings[password_min_length]" class="form-control"
                                   value="<?php echo esc(getSetting('password_min_length', $settingsMap, '8')); ?>"
                                   min="6" max="32">
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="settings[require_uppercase]" value="0">
                                <input type="checkbox" name="settings[require_uppercase]" class="form-check-input"
                                       id="require_uppercase" value="1"
                                       <?php echo getSetting('require_uppercase', $settingsMap, '1') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="require_uppercase">
                                    Require Uppercase Letter
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="settings[require_number]" value="0">
                                <input type="checkbox" name="settings[require_number]" class="form-check-input"
                                       id="require_number" value="1"
                                       <?php echo getSetting('require_number', $settingsMap, '1') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="require_number">
                                    Require Number
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="settings[require_special_char]" value="0">
                                <input type="checkbox" name="settings[require_special_char]" class="form-check-input"
                                       id="require_special_char" value="1"
                                       <?php echo getSetting('require_special_char', $settingsMap, '1') === '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="require_special_char">
                                    Require Special Character
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="?action=blocked_ips" class="btn btn-outline-danger">
                                <i class="fas fa-ban"></i> Manage Blocked IPs
                            </a>
                            <a href="?action=login_attempts" class="btn btn-outline-warning">
                                <i class="fas fa-sign-in-alt"></i> View Login Attempts
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="runCleanup()">
                                <i class="fas fa-broom"></i> Cleanup Old Data
                            </button>
                            <a href="?action=run_audit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Run Security Audit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <button type="reset" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-undo"></i> Reset Changes
                </button>
            </div>
        </div>
    </form>

    <!-- Security Policies -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Security Policies</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($policies)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($policies as $policy): ?>
                                <tr>
                                    <td><strong><?php echo esc($policy['name']); ?></strong></td>
                                    <td><?php echo esc($policy['description'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $policy['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $policy['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo esc($policy['updated_at']); ?></small></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="viewPolicy(<?php echo esc($policy['id']); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No security policies configured.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function runCleanup() {
    if (!confirm('This will remove old login attempts and expired IP blocks. Continue?')) return;

    fetch('?action=cleanup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]')?.value || '')
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Cleanup complete!\n- Login attempts deleted: ${data.login_attempts_deleted}\n- Expired blocks removed: ${data.blocks_deleted}`);
        } else {
            alert('Cleanup failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

function viewPolicy(policyId) {
    // TODO: Implement policy viewer modal
    alert('Policy viewer not yet implemented');
}
</script>
