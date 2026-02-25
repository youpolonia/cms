<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-directory-review.php';
$reviews = \DirectoryReview::getPending();
ob_start();
?>
<style>
.dir-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.dir-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.dir-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.review-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;margin-bottom:12px}
.review-card .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.review-card h4{margin:0;font-size:.9rem;color:var(--text,#e2e8f0)}
.review-card .stars{color:#f59e0b;font-size:.9rem}
.review-card .meta{font-size:.75rem;color:var(--muted,#94a3b8);margin-bottom:8px}
.review-card .content{font-size:.85rem;color:var(--text,#e2e8f0);line-height:1.6}
.review-card .actions{display:flex;gap:8px;margin-top:12px}
.review-card .actions button{padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:none;cursor:pointer}
</style>
<div class="dir-wrap">
    <div class="dir-header"><h1>⭐ Pending Reviews (<?= count($reviews) ?>)</h1><a href="/admin/directory" class="btn-secondary">← Dashboard</a></div>
    <?php if (empty($reviews)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)"><p style="font-size:1.2rem">🎉 No pending reviews!</p></div>
    <?php else: foreach ($reviews as $r): ?>
    <div class="review-card">
        <div class="header"><h4><?= h($r['listing_title']) ?></h4><span class="stars"><?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5 - (int)$r['rating']) ?></span></div>
        <div class="meta">By <?= h($r['reviewer_name']) ?> · <?= date('M j, Y', strtotime($r['created_at'])) ?></div>
        <?php if ($r['title']): ?><strong style="font-size:.85rem"><?= h($r['title']) ?></strong><br><?php endif; ?>
        <div class="content"><?= h($r['content']) ?></div>
        <div class="actions">
            <button onclick="fetch('/api/directory/approve-review/<?= $r['id'] ?>',{method:'POST',credentials:'same-origin'}).then(function(){location.reload()})" style="background:rgba(16,185,129,.15);color:#34d399">✓ Approve</button>
            <button onclick="fetch('/api/directory/reject-review/<?= $r['id'] ?>',{method:'POST',credentials:'same-origin'}).then(function(){location.reload()})" style="background:rgba(239,68,68,.1);color:#fca5a5">✕ Reject</button>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Pending Reviews'; require CMS_APP . '/views/admin/layouts/topbar.php';
