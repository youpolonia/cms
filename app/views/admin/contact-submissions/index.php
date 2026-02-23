<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Contact Submissions';
$layout = 'admin';
ob_start();
$statusLabels = ['new' => '🔵 New', 'read' => '👁 Read', 'replied' => '✅ Replied', 'spam' => '🚫 Spam', 'archived' => '📦 Archived'];
$statusColors = ['new' => '#3b82f6', 'read' => '#94a3b8', 'replied' => '#22c55e', 'spam' => '#ef4444', 'archived' => '#64748b'];
?>
<style>
.cs-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
.cs-title { font-size: 1.5rem; font-weight: 700; color: var(--text, #e2e8f0); }
.cs-title .count { font-size: 0.875rem; color: var(--muted, #94a3b8); font-weight: 400; margin-left: 8px; }
.cs-filters { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.cs-filter-btn { padding: 6px 14px; border-radius: 6px; border: 1px solid var(--border, #334155); background: transparent; color: var(--text, #e2e8f0); cursor: pointer; font-size: 0.8rem; transition: all 0.2s; text-decoration: none; }
.cs-filter-btn:hover, .cs-filter-btn.active { background: var(--primary, #6366f1); border-color: var(--primary, #6366f1); color: #fff; }
.cs-filter-btn .badge { background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 10px; font-size: 0.7rem; margin-left: 4px; }
.cs-search { padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border, #334155); background: var(--bg-card, #1e293b); color: var(--text, #e2e8f0); font-size: 0.85rem; width: 200px; }
.cs-table { width: 100%; border-collapse: collapse; }
.cs-table th { text-align: left; padding: 10px 12px; border-bottom: 2px solid var(--border, #334155); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted, #94a3b8); }
.cs-table td { padding: 12px; border-bottom: 1px solid var(--border, #334155); font-size: 0.85rem; color: var(--text, #e2e8f0); }
.cs-table tr:hover { background: rgba(99, 102, 241, 0.05); }
.cs-table tr.is-new { font-weight: 600; }
.cs-status { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 500; }
.cs-message-preview { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--muted, #94a3b8); }
.cs-actions { display: flex; gap: 6px; }
.cs-actions a, .cs-actions button { padding: 4px 10px; border-radius: 4px; border: 1px solid var(--border, #334155); background: transparent; color: var(--text, #e2e8f0); cursor: pointer; font-size: 0.75rem; text-decoration: none; transition: all 0.2s; }
.cs-actions a:hover { background: var(--primary, #6366f1); border-color: var(--primary, #6366f1); }
.cs-actions button.delete:hover { background: #ef4444; border-color: #ef4444; }
.cs-empty { text-align: center; padding: 60px 20px; color: var(--muted, #94a3b8); }
.cs-empty .icon { font-size: 3rem; margin-bottom: 12px; }
.cs-pagination { display: flex; justify-content: center; gap: 6px; margin-top: 20px; }
.cs-pagination a { padding: 6px 12px; border-radius: 4px; border: 1px solid var(--border, #334155); color: var(--text, #e2e8f0); text-decoration: none; font-size: 0.85rem; }
.cs-pagination a.active { background: var(--primary, #6366f1); border-color: var(--primary, #6366f1); }
.cs-bulk { display: flex; gap: 8px; align-items: center; margin-bottom: 16px; }
.cs-bulk select { padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border, #334155); background: var(--bg-card, #1e293b); color: var(--text, #e2e8f0); font-size: 0.8rem; }
</style>

<div class="cs-header">
    <div class="cs-title">
        📬 Contact Submissions
        <span class="count"><?= $total ?> total<?php if ($newCount > 0): ?>, <strong style="color:#3b82f6"><?= $newCount ?> new</strong><?php endif; ?></span>
    </div>
    <div class="cs-filters">
        <a href="/admin/contact-submissions" class="cs-filter-btn <?= !$status ? 'active' : '' ?>">All</a>
        <?php foreach ($statusLabels as $key => $label): ?>
            <?php $cnt = $statusCounts[$key] ?? 0; if ($cnt === 0 && $key !== 'new') continue; ?>
            <a href="/admin/contact-submissions?status=<?= $key ?>" class="cs-filter-btn <?= $status === $key ? 'active' : '' ?>">
                <?= $label ?><?php if ($cnt > 0): ?><span class="badge"><?= $cnt ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
        <form method="get" action="/admin/contact-submissions" style="display:inline">
            <?php if ($status): ?><input type="hidden" name="status" value="<?= h($status) ?>"><?php endif; ?>
            <input type="search" name="q" value="<?= h($search) ?>" placeholder="Search..." class="cs-search">
        </form>
    </div>
</div>

<?php if (empty($submissions)): ?>
    <div class="cs-empty">
        <div class="icon">📭</div>
        <p>No submissions<?= $status ? " with status \"{$status}\"" : '' ?><?= $search ? " matching \"{$search}\"" : '' ?>.</p>
    </div>
<?php else: ?>
    <form id="bulkForm" method="post" action="/admin/contact-submissions/bulk">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="cs-bulk">
            <label><input type="checkbox" id="selectAll"> Select all</label>
            <select name="action" id="bulkAction">
                <option value="">Bulk action...</option>
                <option value="mark_read">Mark as read</option>
                <option value="mark_spam">Mark as spam</option>
                <option value="archive">Archive</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="cs-filter-btn" onclick="return confirm('Apply bulk action?')">Apply</button>
        </div>

        <table class="cs-table">
            <thead>
                <tr>
                    <th style="width:30px"></th>
                    <th>Status</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $s): ?>
                <tr class="<?= $s['status'] === 'new' ? 'is-new' : '' ?>">
                    <td><input type="checkbox" name="ids[]" value="<?= $s['id'] ?>"></td>
                    <td>
                        <span class="cs-status" style="background: <?= $statusColors[$s['status']] ?? '#64748b' ?>20; color: <?= $statusColors[$s['status']] ?? '#64748b' ?>">
                            <?= $statusLabels[$s['status']] ?? $s['status'] ?>
                        </span>
                    </td>
                    <td>
                        <strong><?= h($s['name']) ?></strong><br>
                        <small style="color:var(--muted)"><?= h($s['email']) ?></small>
                    </td>
                    <td><?= h($s['subject'] ?: '—') ?></td>
                    <td class="cs-message-preview"><?= h(mb_substr($s['message'], 0, 100)) ?></td>
                    <td style="white-space:nowrap; font-size:0.8rem; color:var(--muted)">
                        <?= date('M j, H:i', strtotime($s['created_at'])) ?>
                    </td>
                    <td class="cs-actions">
                        <a href="/admin/contact-submissions/<?= $s['id'] ?>">View</a>
                        <form method="post" action="/admin/contact-submissions/<?= $s['id'] ?>/delete" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button type="submit" class="delete" onclick="return confirm('Delete?')">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>

    <?php if ($totalPages > 1): ?>
    <div class="cs-pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="/admin/contact-submissions?page=<?= $i ?>&status=<?= h($status) ?>&q=<?= urlencode($search) ?>" 
               class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
