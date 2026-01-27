<?php
$title = 'Database Migrations';
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>🗄️ Database Migrations</h1>
        <p class="page-subtitle">Manage database schema changes safely</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">✓ <?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">✕ <?= esc($error) ?></div>
<?php endif; ?>

<div class="migrations-layout">
    <div class="migrations-main">
        <?php if (!empty($pending)): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <span class="status-dot status-pending"></span>
                    <h2 class="card-title">Pending Migrations</h2>
                    <span class="count-badge"><?= count($pending) ?></span>
                </div>
                <form method="post" action="/admin/migrations/run" onsubmit="return confirm('Run all pending migrations?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-icon">▶</span> Run All
                    </button>
                </form>
            </div>
            <div class="card-body no-padding">
                <div class="migration-list">
                    <?php foreach ($pending as $migration): ?>
                    <div class="migration-item pending">
                        <div class="migration-info">
                            <span class="migration-icon">📄</span>
                            <code class="migration-name"><?= esc($migration) ?></code>
                        </div>
                        <form method="post" action="/admin/migrations/run-single">
                            <?= csrf_field() ?>
                            <input type="hidden" name="migration" value="<?= esc($migration) ?>">
                            <button type="submit" class="btn btn-sm btn-accent">Run</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div class="card-header-left">
                    <span class="status-dot status-success"></span>
                    <h2 class="card-title">Executed Migrations</h2>
                </div>
                <?php if ($lastBatch > 0): ?>
                <form method="post" action="/admin/migrations/rollback" onsubmit="return confirm('Rollback last batch (<?= $lastBatch ?>)?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-sm">
                        ↩ Rollback Batch <?= $lastBatch ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <?php if (empty($executed)): ?>
            <div class="card-body">
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>No migrations have been executed yet.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="card-body no-padding">
                <div class="migration-list">
                    <?php foreach ($executed as $migration): ?>
                    <div class="migration-item executed">
                        <div class="migration-info">
                            <span class="migration-icon">✓</span>
                            <code class="migration-name"><?= esc($migration['migration']) ?></code>
                        </div>
                        <div class="migration-meta">
                            <span class="batch-badge">Batch <?= (int)$migration['batch'] ?></span>
                            <span class="migration-date">
                                <?= $migration['executed_at'] ? date('M j, Y H:i', strtotime($migration['executed_at'])) : '-' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="migrations-sidebar">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">➕ Create Migration</h3>
            </div>
            <div class="card-body">
                <form method="post" action="/admin/migrations/create">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="name">Migration Name</label>
                        <input type="text" id="name" name="name" class="form-control" required 
                               placeholder="e.g., add_users_table" 
                               pattern="[a-z0-9_]+" 
                               title="Lowercase letters, numbers, underscores only">
                        <p class="form-hint">Lowercase, underscores only</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Create Migration</button>
                </form>
            </div>
        </div>

        <div class="card info-card">
            <div class="card-body">
                <h4>📁 Migration Files</h4>
                <div class="info-row">
                    <span class="info-label">Location:</span>
                    <code>/database/migrations/</code>
                </div>
                <div class="info-row">
                    <span class="info-label">Format:</span>
                    <code>YYYY_MM_DD_HHMMSS_name.php</code>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-header {
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    padding: 2rem 2rem 1.5rem 2rem;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
    border-bottom: 1px solid var(--border-color);
}
.page-header h1 {
    margin: 0 0 0.25rem 0;
    font-size: 1.75rem;
    font-weight: 600;
}
.page-subtitle {
    margin: 0;
    color: var(--text-muted);
}

/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}
.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

/* Layout */
.migrations-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
}
@media (max-width: 900px) {
    .migrations-layout {
        grid-template-columns: 1fr;
    }
}
.migrations-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Cards */
.card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}
.card-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}
.card-body {
    padding: 1.25rem;
}
.card-body.no-padding {
    padding: 0;
}

/* Status Dots */
.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.status-pending {
    background: #f59e0b;
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.5);
}
.status-success {
    background: #22c55e;
    box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
}

/* Count Badge */
.count-badge {
    background: var(--accent-color);
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Migration List */
.migration-list {
    display: flex;
    flex-direction: column;
}
.migration-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
}
.migration-item:last-child {
    border-bottom: none;
}
.migration-item:hover {
    background: var(--bg-secondary);
}
.migration-item.pending {
    background: rgba(245, 158, 11, 0.05);
}
.migration-item.pending:hover {
    background: rgba(245, 158, 11, 0.1);
}
.migration-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.migration-icon {
    font-size: 1.1rem;
}
.migration-item.executed .migration-icon {
    color: #22c55e;
}
.migration-name {
    font-size: 0.875rem;
    background: var(--bg-secondary);
    padding: 0.3rem 0.6rem;
    border-radius: 6px;
    color: var(--text-primary);
}
.migration-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.batch-badge {
    background: rgba(99, 102, 241, 0.2);
    color: var(--accent-color);
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}
.migration-date {
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
}
.empty-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.75rem;
}
.empty-state p {
    margin: 0;
    color: var(--text-muted);
}

/* Sidebar */
.migrations-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Form */
.form-group {
    margin-bottom: 1rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-size: 0.9rem;
}
.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.9rem;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.form-hint {
    margin: 0.4rem 0 0 0;
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Info Card */
.info-card .card-body {
    padding: 1rem;
}
.info-card h4 {
    font-size: 0.9rem;
    margin: 0 0 0.75rem 0;
    font-weight: 600;
}
.info-row {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
}
.info-row:last-child {
    margin-bottom: 0;
}
.info-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.info-row code {
    font-size: 0.8rem;
    background: var(--bg-secondary);
    padding: 0.3rem 0.5rem;
    border-radius: 4px;
    color: var(--accent-color);
}

/* Buttons */
.btn {
    padding: 0.6rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    border: none;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.btn-primary {
    background: var(--accent-color);
    color: white;
}
.btn-primary:hover {
    background: var(--accent-hover, #4f46e5);
}
.btn-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.btn-danger:hover {
    background: rgba(239, 68, 68, 0.25);
}
.btn-accent {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.btn-accent:hover {
    background: rgba(245, 158, 11, 0.25);
}
.btn-sm {
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
}
.btn-block {
    width: 100%;
    justify-content: center;
}
.btn-icon {
    font-size: 0.75rem;
}
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
