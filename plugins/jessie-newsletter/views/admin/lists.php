<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-list.php';
$lists = \NewsletterList::getAll();
ob_start();
?>
<style>
.nl-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.list-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.list-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;transition:.2s}
.list-card:hover{border-color:#6366f1}
.list-card h4{margin:0 0 6px;font-size:1rem;color:var(--text,#e2e8f0);display:flex;align-items:center;gap:8px}
.list-card .dot{width:12px;height:12px;border-radius:50%}
.list-card .count{font-size:1.5rem;font-weight:800;color:#6366f1;margin:8px 0}
.list-card .desc{font-size:.78rem;color:var(--muted,#94a3b8);margin-bottom:10px}
.list-card .actions a{font-size:.78rem;color:#a5b4fc;text-decoration:none;margin-right:10px}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>📋 Lists</h1><div style="display:flex;gap:10px"><a href="/admin/newsletter" class="btn-secondary">← Dashboard</a><a href="/admin/newsletter/lists/create" class="btn-nl">➕ New List</a></div></div>
    <?php if (empty($lists)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)"><p style="font-size:1.2rem">No lists yet.</p><a href="/admin/newsletter/lists/create" class="btn-nl" style="margin-top:12px">➕ Create Your First List</a></div>
    <?php else: ?>
    <div class="list-grid">
        <?php foreach ($lists as $l): ?>
        <div class="list-card">
            <h4><span class="dot" style="background:<?= h($l['color']) ?>"></span><?= h($l['name']) ?></h4>
            <div class="count"><?= (int)$l['subscriber_count'] ?> <span style="font-size:.7rem;font-weight:400;color:var(--muted)">subscribers</span></div>
            <?php if ($l['description']): ?><div class="desc"><?= h($l['description']) ?></div><?php endif; ?>
            <div class="actions"><a href="/admin/newsletter/lists/<?= $l['id'] ?>/edit">✏️ Edit</a><a href="/admin/newsletter/subscribers?list_id=<?= $l['id'] ?>">👥 View</a></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Newsletter Lists'; require CMS_APP . '/views/admin/layouts/topbar.php';
