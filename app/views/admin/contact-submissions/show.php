<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Submission #' . $submission['id'];
$layout = 'admin';
$statusLabels = ['new' => '🔵 New', 'read' => '👁 Read', 'replied' => '✅ Replied', 'spam' => '🚫 Spam', 'archived' => '📦 Archived'];
$statusColors = ['new' => '#3b82f6', 'read' => '#94a3b8', 'replied' => '#22c55e', 'spam' => '#ef4444', 'archived' => '#64748b'];
ob_start();
$s = $submission;
?>
<style>
.cs-detail { max-width: 700px; }
.cs-detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.cs-back { color: var(--primary, #6366f1); text-decoration: none; font-size: 0.85rem; }
.cs-card { background: var(--bg-card, #1e293b); border: 1px solid var(--border, #334155); border-radius: 8px; padding: 24px; margin-bottom: 20px; }
.cs-field { margin-bottom: 16px; }
.cs-field-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted, #94a3b8); margin-bottom: 4px; }
.cs-field-value { color: var(--text, #e2e8f0); font-size: 0.95rem; line-height: 1.6; }
.cs-field-value a { color: var(--primary, #6366f1); }
.cs-message-full { background: var(--bg, #0f172a); border: 1px solid var(--border, #334155); border-radius: 6px; padding: 16px; white-space: pre-wrap; line-height: 1.7; }
.cs-status-bar { display: flex; gap: 8px; margin-bottom: 20px; }
.cs-status-btn { padding: 6px 14px; border-radius: 6px; border: 1px solid var(--border, #334155); background: transparent; color: var(--text, #e2e8f0); cursor: pointer; font-size: 0.8rem; transition: all 0.2s; }
.cs-status-btn:hover, .cs-status-btn.active { background: var(--primary, #6366f1); border-color: var(--primary, #6366f1); color: #fff; }
.cs-meta { font-size: 0.75rem; color: var(--muted, #94a3b8); display: flex; gap: 16px; flex-wrap: wrap; }
</style>

<div class="cs-detail">
    <div class="cs-detail-header">
        <a href="/admin/contact-submissions" class="cs-back">← Back to submissions</a>
        <span class="cs-status" style="background: <?= $statusColors[$s['status']] ?? '#64748b' ?>20; color: <?= $statusColors[$s['status']] ?? '#64748b' ?>; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem;">
            <?= $statusLabels[$s['status']] ?? $s['status'] ?>
        </span>
    </div>

    <div class="cs-card">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="cs-field">
                <div class="cs-field-label">Name</div>
                <div class="cs-field-value"><?= h($s['name']) ?></div>
            </div>
            <div class="cs-field">
                <div class="cs-field-label">Email</div>
                <div class="cs-field-value"><a href="mailto:<?= h($s['email']) ?>"><?= h($s['email']) ?></a></div>
            </div>
            <?php if ($s['phone']): ?>
            <div class="cs-field">
                <div class="cs-field-label">Phone</div>
                <div class="cs-field-value"><a href="tel:<?= h($s['phone']) ?>"><?= h($s['phone']) ?></a></div>
            </div>
            <?php endif; ?>
            <div class="cs-field">
                <div class="cs-field-label">Subject</div>
                <div class="cs-field-value"><?= h($s['subject'] ?: '—') ?></div>
            </div>
        </div>

        <div class="cs-field">
            <div class="cs-field-label">Message</div>
            <div class="cs-message-full"><?= h($s['message']) ?></div>
        </div>
    </div>

    <div class="cs-status-bar">
        <?php foreach ($statusLabels as $key => $label): ?>
            <form method="post" action="/admin/contact-submissions/<?= $s['id'] ?>/status" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="status" value="<?= $key ?>">
                <button type="submit" class="cs-status-btn <?= $s['status'] === $key ? 'active' : '' ?>"><?= $label ?></button>
            </form>
        <?php endforeach; ?>
    </div>

    <div class="cs-meta">
        <span>📅 <?= date('M j, Y H:i:s', strtotime($s['created_at'])) ?></span>
        <?php if ($s['page_slug']): ?><span>📄 <?= h($s['page_slug']) ?></span><?php endif; ?>
        <span>🌐 <?= h($s['ip_address'] ?? '—') ?></span>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
