<?php
/**
 * User Management
 * Professional admin interface for managing CMS users
 */
$title = 'User Management';
ob_start();

// Calculate stats
$totalUsers = count($users);
$adminCount = count(array_filter($users, fn($u) => ($u['role'] ?? 'admin') === 'admin'));
$editorCount = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'editor'));
$viewerCount = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'viewer'));
?>

<style>
/* User Management Styles */
.users-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.users-header-info { flex: 1; }
.users-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}
.users-header-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }
.users-icon {
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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
@media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
    .stats-grid { grid-template-columns: 1fr; }
}
.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.stat-icon.total { background: rgba(137, 180, 250, 0.15); }
.stat-icon.admin { background: rgba(166, 227, 161, 0.15); }
.stat-icon.editor { background: rgba(249, 226, 175, 0.15); }
.stat-icon.viewer { background: rgba(108, 112, 134, 0.15); }
.stat-info { min-width: 0; }
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}
.stat-label {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* Table Enhancements */
.users-table {
    width: 100%;
    border-collapse: collapse;
}
.users-table th {
    text-align: left;
    padding: 0.875rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    background: var(--bg-tertiary);
}
.users-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.users-table tbody tr {
    transition: background-color 0.15s;
}
.users-table tbody tr:hover {
    background: var(--bg-tertiary);
}
.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #cba6f7, #89b4fa);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: #1e1e2e;
    font-size: 1rem;
    flex-shrink: 0;
}
.user-info { min-width: 0; }
.user-name {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.user-email {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin-top: 0.125rem;
}
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    border-radius: 6px;
}
.badge-you {
    background: rgba(137, 180, 250, 0.2);
    color: #89b4fa;
}
.badge-admin {
    background: rgba(166, 227, 161, 0.2);
    color: #a6e3a1;
}
.badge-editor {
    background: rgba(249, 226, 175, 0.2);
    color: #f9e2af;
}
.badge-viewer {
    background: rgba(108, 112, 134, 0.2);
    color: #a6adc8;
}
.login-time {
    font-size: 0.8125rem;
}
.login-time.never {
    color: var(--text-muted);
    font-style: italic;
}
.actions-cell {
    display: flex;
    gap: 0.5rem;
}
.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.15s;
}
.btn-edit {
    background: rgba(137, 180, 250, 0.1);
    color: #89b4fa;
    border: 1px solid rgba(137, 180, 250, 0.3);
}
.btn-edit:hover {
    background: rgba(137, 180, 250, 0.2);
    border-color: #89b4fa;
}
.btn-delete {
    background: rgba(243, 139, 168, 0.1);
    color: #f38ba8;
    border: 1px solid rgba(243, 139, 168, 0.3);
}
.btn-delete:hover {
    background: rgba(243, 139, 168, 0.2);
    border-color: #f38ba8;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--text-muted);
}
.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.empty-state p {
    margin: 0.5rem 0;
}
.empty-state .btn {
    margin-top: 1rem;
}
</style>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">✓ <?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">✗ <?= esc($error) ?></div>
<?php endif; ?>

<!-- Page Header -->
<div class="users-header">
    <div class="users-header-info">
        <h1>
            <span class="users-icon">👥</span>
            User Management
        </h1>
        <p class="page-subtitle">Manage admin accounts and access permissions</p>
    </div>
    <div class="users-header-actions">
        <a href="/admin/users/create" class="btn btn-primary">+ New User</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon total">👥</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon admin">👑</div>
        <div class="stat-info">
            <div class="stat-value"><?= $adminCount ?></div>
            <div class="stat-label">Administrators</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon editor">✏️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $editorCount ?></div>
            <div class="stat-label">Editors</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon viewer">👁️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $viewerCount ?></div>
            <div class="stat-label">Viewers</div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">All Users</h2>
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-icon">👤</div>
            <p><strong>No users found</strong></p>
            <p>Create your first admin user to get started.</p>
            <a href="/admin/users/create" class="btn btn-primary">+ Create User</a>
        </div>
    <?php else: ?>
        <table class="users-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>User</th>
                    <th>Role <span class="tip"><span class="tip-text">Determines what this user can do. Admin has full access.</span></span></th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $initials = strtoupper(substr($user['username'], 0, 2));
                    $roleClass = match($user['role'] ?? 'admin') {
                        'admin' => 'badge-admin',
                        'editor' => 'badge-editor',
                        'viewer' => 'badge-viewer',
                        default => 'badge-viewer'
                    };
                    $isCurrentUser = (int)$user['id'] === $currentUserId;
                    ?>
                    <tr>
                        <td style="color: var(--text-muted); font-size: 0.875rem;">#<?= (int)$user['id'] ?></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?= $initials ?></div>
                                <div class="user-info">
                                    <div class="user-name">
                                        <?= esc($user['username']) ?>
                                        <?php if ($isCurrentUser): ?>
                                            <span class="badge badge-you">You</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-email"><?= esc($user['email'] ?? 'No email') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $roleClass ?>"><?= esc(ucfirst($user['role'] ?? 'admin')) ?></span>
                        </td>
                        <td>
                            <?php if ($user['last_login']): ?>
                                <span class="login-time"><?= date('M j, Y', strtotime($user['last_login'])) ?></span>
                            <?php else: ?>
                                <span class="login-time never">Never</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.875rem;">
                            <?= date('M j, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="/admin/users/<?= (int)$user['id'] ?>/edit" class="btn-icon btn-edit" title="Edit">✏️</a>
                                <?php if (!$isCurrentUser): ?>
                                    <form method="post" action="/admin/users/<?= (int)$user['id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-icon btn-delete" title="Delete">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
