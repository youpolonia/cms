<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-subscriber.php';
require_once $pluginDir . '/includes/class-newsletter-list.php';
$filters = ['status' => $_GET['status'] ?? '', 'search' => $_GET['q'] ?? '', 'list_id' => $_GET['list_id'] ?? ''];
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \NewsletterSubscriber::getAll($filters, $page);
$lists = \NewsletterList::getAll('active');
ob_start();
?>
<style>
.nl-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.nl-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.nl-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.nl-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.nl-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-unsubscribed{background:rgba(107,114,128,.15);color:#9ca3af}
.status-bounced{background:rgba(239,68,68,.15);color:#fca5a5}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.pagination{display:flex;gap:6px;justify-content:center;margin-top:16px}
.pagination a{padding:6px 12px;border-radius:6px;font-size:.82rem;text-decoration:none;color:var(--text,#e2e8f0);border:1px solid var(--border,#334155)}
.pagination a.active{background:#6366f1;border-color:#6366f1;color:#fff}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>👥 Subscribers</h1><div style="display:flex;gap:10px"><a href="/admin/newsletter" class="btn-secondary">← Dashboard</a><a href="/admin/newsletter/subscribers/import" class="btn-secondary">📥 Import CSV</a><a href="/admin/newsletter/subscribers/add" class="btn-nl">➕ Add</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All</option><?php foreach (['active','unsubscribed','bounced','pending'] as $s): ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?list_id='+this.value"><option value="">All Lists</option><?php foreach ($lists as $l): ?><option value="<?= $l['id'] ?>" <?= $filters['list_id']==(string)$l['id']?'selected':'' ?>><?= h($l['name']) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($filters['search']) ?>" onchange="location.href='?q='+encodeURIComponent(this.value)">
        <span style="color:var(--muted,#94a3b8);font-size:.82rem;padding:8px"><?= $result['total'] ?> subscribers</span>
    </div>
    <table class="nl-table"><thead><tr><th>Email</th><th>Name</th><th>Lists</th><th>Status</th><th>Subscribed</th><th></th></tr></thead><tbody>
        <?php foreach ($result['subscribers'] as $s): ?>
        <tr>
            <td><strong><?= h($s['email']) ?></strong></td>
            <td><?= h($s['name'] ?: '—') ?></td>
            <td style="font-size:.75rem"><?= count($s['lists']) ?> list(s)</td>
            <td><span class="status-badge status-<?= h($s['status']) ?>"><?= h($s['status']) ?></span></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= $s['confirmed_at'] ? date('M j, Y', strtotime($s['confirmed_at'])) : '—' ?></td>
            <td><button onclick="if(confirm('Delete?'))fetch('/api/newsletter/subscribers/delete/<?= $s['id'] ?>',{method:'POST',credentials:'same-origin'}).then(function(){location.reload()})" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.78rem">🗑</button></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['subscribers'])): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No subscribers found.</td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($result['pages'] > 1): ?>
    <div class="pagination"><?php for ($p = 1; $p <= $result['pages']; $p++): ?><a href="?page=<?= $p ?>&status=<?= h($filters['status']) ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a><?php endfor; ?></div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Subscribers'; require CMS_APP . '/views/admin/layouts/topbar.php';
