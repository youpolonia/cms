/**
<?php
/**
 * Comments Management - Modern Dark Theme
 */
$title = 'Comments';
ob_start();

$statusIcons = [
    'pending' => '⏳',
    'approved' => '✅',
    'spam' => '🚫',
    'trash' => '🗑️'
];
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">💬 Comments</h2>
        <div class="status-tabs">
            <a href="/admin/comments" class="status-tab <?= empty($status) ? 'active' : '' ?>">
                All <span class="badge"><?= $counts['all'] ?></span>
            </a>
            <a href="/admin/comments?status=pending" class="status-tab pending <?= $status === 'pending' ? 'active' : '' ?>">
                ⏳ Pending <span class="badge"><?= $counts['pending'] ?></span>
            </a>
            <a href="/admin/comments?status=approved" class="status-tab approved <?= $status === 'approved' ? 'active' : '' ?>">
                ✅ Approved <span class="badge"><?= $counts['approved'] ?></span>
            </a>
            <a href="/admin/comments?status=spam" class="status-tab spam <?= $status === 'spam' ? 'active' : '' ?>">
                🚫 Spam <span class="badge"><?= $counts['spam'] ?></span>
            </a>
            <a href="/admin/comments?status=trash" class="status-tab trash <?= $status === 'trash' ? 'active' : '' ?>">
                🗑️ Trash <span class="badge"><?= $counts['trash'] ?></span>
            </a>
        </div>
    </div>

    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <div class="empty-icon">💬</div>
            <h3>No comments found</h3>
            <p><?= $status ? "No {$status} comments" : "Comments will appear here when visitors leave them on your content" ?></p>
        </div>
    <?php else: ?>
        <form method="post" action="/admin/comments/bulk" id="commentsForm">
            <?= csrf_field() ?>
            
            <!-- Bulk Actions Bar -->
            <div class="bulk-actions-bar">
                <label class="checkbox-wrapper">
                    <input type="checkbox" id="selectAll">
                    <span class="checkmark"></span>
                </label>
                <select name="bulk_action" class="bulk-select">
                    <option value="">Bulk Actions</option>
                    <option value="approve">✅ Approve</option>
                    <option value="spam">🚫 Mark as Spam</option>
                    <option value="trash">🗑️ Move to Trash</option>
                    <?php if ($status === 'trash'): ?>
                        <option value="delete">❌ Delete Permanently</option>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
                <span class="selected-count" id="selectedCount"></span>
            </div>

            <!-- Comments List -->
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item <?= $comment['status'] ?>">
                        <div class="comment-checkbox">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="comment_ids[]" value="<?= (int)$comment['id'] ?>" class="comment-cb">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        
                        <div class="comment-avatar">
                            <?= strtoupper(substr($comment['author_name'], 0, 1)) ?>
                        </div>
                        
                        <div class="comment-body">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <strong><?= esc($comment['author_name']) ?></strong>
                                    <span class="comment-email"><?= esc($comment['author_email']) ?></span>
                                </div>
                                <div class="comment-meta">
                                    <span class="status-badge <?= $comment['status'] ?>">
                                        <?= $statusIcons[$comment['status']] ?? '' ?> <?= ucfirst($comment['status']) ?>
                                    </span>
                                    <span class="comment-date" title="<?= date('Y-m-d H:i:s', strtotime($comment['created_at'])) ?>">
                                        <?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="comment-content">
                                <?= nl2br(esc($comment['content'])) ?>
                            </div>
                            
                            <div class="comment-footer">
                                <div class="comment-source">
                                    <?php if ($comment['article_title']): ?>
                                        📄 <a href="/admin/articles/<?= (int)$comment['article_id'] ?>/edit"><?= esc($comment['article_title']) ?></a>
                                    <?php elseif ($comment['page_title']): ?>
                                        📑 <a href="/admin/pages/<?= (int)$comment['page_id'] ?>/edit"><?= esc($comment['page_title']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">No linked content</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="comment-actions">
                                    <?php if ($comment['status'] !== 'approved'): ?>
                                        <form method="post" action="/admin/comments/<?= (int)$comment['id'] ?>/approve" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="action-btn approve" title="Approve">✅ Approve</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($comment['status'] !== 'spam'): ?>
                                        <form method="post" action="/admin/comments/<?= (int)$comment['id'] ?>/spam" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="action-btn spam" title="Mark as Spam">🚫 Spam</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($comment['status'] === 'trash'): ?>
                                        <form method="post" action="/admin/comments/<?= (int)$comment['id'] ?>/restore" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="action-btn restore" title="Restore">♻️ Restore</button>
                                        </form>
                                        <form method="post" action="/admin/comments/<?= (int)$comment['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Permanently delete this comment? This cannot be undone.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="action-btn delete" title="Delete Permanently">❌ Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="/admin/comments/<?= (int)$comment['id'] ?>/trash" class="inline-form">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="action-btn trash" title="Move to Trash">🗑️ Trash</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
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

/* Card */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

/* Status Tabs */
.status-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.status-tab {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.status-tab:hover {
    background: var(--bg-primary);
    color: var(--text-primary);
}
.status-tab.active {
    background: var(--accent-color);
    color: white;
}
.status-tab .badge {
    background: rgba(255,255,255,0.2);
    padding: 0.125rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
}
.status-tab:not(.active) .badge {
    background: var(--bg-primary);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
.empty-state h3 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.empty-state p {
    color: var(--text-muted);
}

/* Bulk Actions Bar */
.bulk-actions-bar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
}
.bulk-select {
    padding: 0.5rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 0.875rem;
}
.selected-count {
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Checkbox */
.checkbox-wrapper {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}
.checkbox-wrapper input {
    display: none;
}
.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color);
    border-radius: 4px;
    transition: all 0.2s;
}
.checkbox-wrapper input:checked + .checkmark {
    background: var(--accent-color);
    border-color: var(--accent-color);
}
.checkbox-wrapper input:checked + .checkmark::after {
    content: '✓';
    color: white;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}

/* Comments List */
.comments-list {
    display: flex;
    flex-direction: column;
}
.comment-item {
    display: flex;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
}
.comment-item:hover {
    background: var(--bg-primary);
}
.comment-item:last-child {
    border-bottom: none;
}
.comment-item.pending {
    border-left: 3px solid #f59e0b;
}
.comment-item.approved {
    border-left: 3px solid #22c55e;
}
.comment-item.spam {
    border-left: 3px solid #ef4444;
    opacity: 0.7;
}
.comment-item.trash {
    border-left: 3px solid #6b7280;
    opacity: 0.6;
}

.comment-checkbox {
    padding-top: 0.25rem;
}

.comment-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-color), #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
}

.comment-body {
    flex: 1;
    min-width: 0;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.comment-author strong {
    display: block;
    color: var(--text-primary);
}
.comment-email {
    font-size: 0.8125rem;
    color: var(--text-muted);
}

.comment-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-badge.pending {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}
.status-badge.approved {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
}
.status-badge.spam {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}
.status-badge.trash {
    background: rgba(107, 114, 128, 0.15);
    color: #9ca3af;
}

.comment-date {
    font-size: 0.8125rem;
    color: var(--text-muted);
}

.comment-content {
    color: var(--text-primary);
    line-height: 1.6;
    margin-bottom: 1rem;
    padding: 1rem;
    background: var(--bg-primary);
    border-radius: 8px;
    border-left: 3px solid var(--border-color);
}

.comment-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.comment-source {
    font-size: 0.875rem;
    color: var(--text-muted);
}
.comment-source a {
    color: var(--accent-color);
    text-decoration: none;
}
.comment-source a:hover {
    text-decoration: underline;
}

.comment-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.inline-form {
    display: inline;
}

.action-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
}
.action-btn:hover {
    background: var(--bg-primary);
}
.action-btn.approve:hover {
    background: rgba(34, 197, 94, 0.15);
    border-color: #22c55e;
    color: #22c55e;
}
.action-btn.spam:hover,
.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    color: #ef4444;
}
.action-btn.trash:hover {
    background: rgba(245, 158, 11, 0.15);
    border-color: #f59e0b;
    color: #f59e0b;
}
.action-btn.restore:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
    color: #3b82f6;
}

.text-muted {
    color: var(--text-muted);
}

/* Responsive */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .comment-item {
        flex-direction: column;
    }
    .comment-header {
        flex-direction: column;
    }
    .comment-footer {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// Select All
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.comment-cb').forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

// Individual checkboxes
document.querySelectorAll('.comment-cb').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const count = document.querySelectorAll('.comment-cb:checked').length;
    const countEl = document.getElementById('selectedCount');
    if (countEl) {
        countEl.textContent = count > 0 ? `${count} selected` : '';
    }
}

// Confirm bulk delete
document.getElementById('commentsForm')?.addEventListener('submit', function(e) {
    const action = this.querySelector('[name="bulk_action"]').value;
    const checked = document.querySelectorAll('.comment-cb:checked').length;
    
    if (!action) {
        e.preventDefault();
        alert('Please select an action');
        return;
    }
    
    if (checked === 0) {
        e.preventDefault();
        alert('Please select at least one comment');
        return;
    }
    
    if (action === 'delete' && !confirm(`Permanently delete ${checked} comment(s)? This cannot be undone.`)) {
        e.preventDefault();
    }
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
