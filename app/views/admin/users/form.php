<?php
/**
 * User Form (Create/Edit)
 * Professional form interface for user management
 */
$title = $user ? 'Edit User' : 'New User';
$isEdit = $user !== null;
ob_start();
?>

<style>
/* User Form Styles */
.form-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.form-header-info { flex: 1; }
.form-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}
.form-header-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }
.form-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #cba6f7, #89b4fa);
    border-radius: 12px;
    font-size: 22px;
}
.page-subtitle {
    color: var(--text-muted);
    margin: 0.5rem 0 0;
    font-size: 0.95rem;
}

/* Form Layout */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 900px) {
    .form-grid { grid-template-columns: 1fr; }
}
.form-main { display: flex; flex-direction: column; gap: 1.5rem; }
.form-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }

/* Form Elements */
.form-section {
    margin-bottom: 1.5rem;
}
.form-section-title {
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}
.form-row {
    margin-bottom: 1.25rem;
}
.form-row:last-child {
    margin-bottom: 0;
}
.form-row label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}
.form-row label .required {
    color: #f38ba8;
    margin-left: 0.25rem;
}
.form-row input,
.form-row select {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: var(--text-primary);
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: border-color 0.15s, box-shadow 0.15s;
}
.form-row input:focus,
.form-row select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-muted);
}
.form-row input::placeholder {
    color: var(--text-muted);
}
.form-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.35rem;
}
.form-row-inline {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 600px) {
    .form-row-inline { grid-template-columns: 1fr; }
}

/* Role Selector */
.role-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.role-option {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--bg-tertiary);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.15s;
}
.role-option:hover {
    border-color: var(--accent-muted);
}
.role-option.selected {
    border-color: var(--accent);
    background: rgba(137, 180, 250, 0.05);
}
.role-option input {
    display: none;
}
.role-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.role-icon.admin { background: rgba(166, 227, 161, 0.15); }
.role-icon.editor { background: rgba(249, 226, 175, 0.15); }
.role-icon.viewer { background: rgba(108, 112, 134, 0.15); }
.role-info { flex: 1; }
.role-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.role-desc {
    font-size: 0.8125rem;
    color: var(--text-muted);
}

/* User Meta Card */
.user-meta {
    font-size: 0.875rem;
}
.user-meta-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
}
.user-meta-row:last-child {
    border-bottom: none;
}
.user-meta-label {
    color: var(--text-muted);
}
.user-meta-value {
    font-weight: 500;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
    margin-top: 1rem;
}

/* Password Strength */
.password-strength {
    height: 4px;
    background: var(--bg-tertiary);
    border-radius: 2px;
    margin-top: 0.5rem;
    overflow: hidden;
}
.password-strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s;
    border-radius: 2px;
}
.password-strength-bar.weak { width: 33%; background: #f38ba8; }
.password-strength-bar.medium { width: 66%; background: #f9e2af; }
.password-strength-bar.strong { width: 100%; background: #a6e3a1; }
</style>

<!-- Page Header -->
<div class="form-header">
    <div class="form-header-info">
        <h1>
            <span class="form-icon"><?= $isEdit ? '✏️' : '➕' ?></span>
            <?= $isEdit ? 'Edit User' : 'Create New User' ?>
        </h1>
        <p class="page-subtitle"><?= $isEdit ? 'Update user account and permissions' : 'Add a new administrator account' ?></p>
    </div>
    <div class="form-header-actions">
        <a href="/admin/users" class="btn btn-secondary">← Back to Users</a>
    </div>
</div>

<form method="post" action="<?= $isEdit ? '/admin/users/' . (int)$user['id'] : '/admin/users/' ?>">
    <?= csrf_field() ?>
    
    <div class="form-grid">
        <!-- Main Form -->
        <div class="form-main">
            <!-- Account Details -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Account Details</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" required 
                               value="<?= esc($user['username'] ?? '') ?>" 
                               placeholder="Enter username" 
                               minlength="3"
                               autocomplete="username">
                        <p class="form-hint">Minimum 3 characters. Used for login.</p>
                    </div>

                    <div class="form-row">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?= esc($user['email'] ?? '') ?>" 
                               placeholder="user@example.com"
                               autocomplete="email">
                        <p class="form-hint">Optional. Used for notifications and password recovery.</p>
                    </div>
                </div>
            </div>

            <!-- Security -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔐 Security</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <label for="password">Password <?= $isEdit ? '' : '<span class="required">*</span>' ?></label>
                        <input type="password" id="password" name="password" 
                               <?= $isEdit ? '' : 'required' ?> 
                               placeholder="<?= $isEdit ? 'Leave empty to keep current' : 'Enter password' ?>" 
                               minlength="8"
                               autocomplete="new-password">
                        <div class="password-strength">
                            <div class="password-strength-bar" id="password-strength-bar"></div>
                        </div>
                        <p class="form-hint">Minimum 8 characters<?= $isEdit ? '. Leave empty to keep current password.' : '' ?></p>
                    </div>

                    <div class="form-row">
                        <label for="password_confirm">Confirm Password <?= $isEdit ? '' : '<span class="required">*</span>' ?></label>
                        <input type="password" id="password_confirm" name="password_confirm" 
                               <?= $isEdit ? '' : 'required' ?> 
                               placeholder="Confirm password"
                               autocomplete="new-password">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $isEdit ? '💾 Save Changes' : '➕ Create User' ?>
                </button>
                <a href="/admin/users" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="form-sidebar">
            <!-- Role Selection -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Role</h3>
                </div>
                <div class="card-body">
                    <div class="role-options">
                        <label class="role-option <?= ($user['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>">
                            <input type="radio" name="role" value="admin" <?= ($user['role'] ?? 'admin') === 'admin' ? 'checked' : '' ?>>
                            <div class="role-icon admin">👑</div>
                            <div class="role-info">
                                <div class="role-name">Administrator</div>
                                <div class="role-desc">Full access to all features and settings</div>
                            </div>
                        </label>
                        <label class="role-option <?= ($user['role'] ?? '') === 'editor' ? 'selected' : '' ?>">
                            <input type="radio" name="role" value="editor" <?= ($user['role'] ?? '') === 'editor' ? 'checked' : '' ?>>
                            <div class="role-icon editor">✏️</div>
                            <div class="role-info">
                                <div class="role-name">Editor</div>
                                <div class="role-desc">Can manage content, pages, and articles</div>
                            </div>
                        </label>
                        <label class="role-option <?= ($user['role'] ?? '') === 'viewer' ? 'selected' : '' ?>">
                            <input type="radio" name="role" value="viewer" <?= ($user['role'] ?? '') === 'viewer' ? 'checked' : '' ?>>
                            <div class="role-icon viewer">👁️</div>
                            <div class="role-info">
                                <div class="role-name">Viewer</div>
                                <div class="role-desc">Read-only access to dashboard and content</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <?php if ($isEdit && !empty($user['created_at'])): ?>
            <!-- User Meta -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Account Info</h3>
                </div>
                <div class="card-body user-meta">
                    <div class="user-meta-row">
                        <span class="user-meta-label">User ID</span>
                        <span class="user-meta-value">#<?= (int)$user['id'] ?></span>
                    </div>
                    <div class="user-meta-row">
                        <span class="user-meta-label">Created</span>
                        <span class="user-meta-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                    <div class="user-meta-row">
                        <span class="user-meta-label">Last Login</span>
                        <span class="user-meta-value"><?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
// Role selection UI
document.querySelectorAll('.role-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
    });
});

// Password strength indicator
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('password-strength-bar');

if (passwordInput && strengthBar) {
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        strengthBar.className = 'password-strength-bar';
        if (password.length === 0) {
            strengthBar.style.width = '0%';
        } else if (strength <= 1) {
            strengthBar.classList.add('weak');
        } else if (strength <= 2) {
            strengthBar.classList.add('medium');
        } else {
            strengthBar.classList.add('strong');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
