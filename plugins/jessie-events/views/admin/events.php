<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-event-manager.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \EventManager::getAll($_GET, $page);
$categories = \EventManager::getCategories();
$cities = \EventManager::getCities();
ob_start();
?>
<style>
.ew{max-width:1100px;margin:0 auto;padding:24px 20px}
.eh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.eh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.tb{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.tb th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.tb td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.tb tr:last-child td{border-bottom:none}
.fb{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.fb select,.fb input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.sb{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.st-upcoming{background:rgba(59,130,246,.15);color:#60a5fa}.st-ongoing{background:rgba(16,185,129,.15);color:#34d399}
.st-completed{background:rgba(100,116,139,.15);color:#94a3b8}.st-cancelled{background:rgba(239,68,68,.15);color:#fca5a5}
.pag{display:flex;gap:6px;margin-top:16px;justify-content:center}
.pag a,.pag span{padding:6px 12px;border-radius:6px;font-size:.82rem;text-decoration:none}
.pag a{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0)}
.pag a:hover{border-color:#6366f1}.pag span{background:#6366f1;color:#fff}
</style>
<div class="ew">
    <div class="eh"><h1>🎪 Events</h1><div style="display:flex;gap:10px"><a href="/admin/events" class="btn-s">← Dashboard</a><a href="/admin/events/create" class="btn-p">➕ New Event</a></div></div>
    <div class="fb">
        <select onchange="location.href='?status='+this.value"><option value="">All Status</option><?php foreach (['upcoming','ongoing','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?category='+encodeURIComponent(this.value)"><option value="">All Categories</option><?php foreach ($categories as $c): ?><option value="<?= h($c) ?>" <?= ($_GET['category']??'')===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?city='+encodeURIComponent(this.value)"><option value="">All Cities</option><?php foreach ($cities as $c): ?><option value="<?= h($c) ?>" <?= ($_GET['city']??'')===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="padding:8px;color:var(--muted);font-size:.82rem"><?= $result['total'] ?> events</span>
    </div>
    <table class="tb"><thead><tr><th>Event</th><th>Date</th><th>Venue</th><th>Category</th><th>Status</th><th>Views</th><th></th></tr></thead><tbody>
        <?php foreach ($result['events'] as $ev): ?>
        <tr>
            <td><strong><?= h($ev['title']) ?></strong><?= $ev['is_featured']?'<span style="color:#f59e0b;margin-left:4px;font-size:.7rem">⭐</span>':'' ?><?= $ev['is_free']?'<span style="color:#34d399;margin-left:4px;font-size:.65rem;font-weight:700">FREE</span>':'' ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h(mb_substr($ev['short_description'] ?: strip_tags($ev['description'] ?? ''), 0, 60)) ?></span></td>
            <td style="font-size:.82rem;white-space:nowrap"><?= date('M j, Y', strtotime($ev['start_date'])) ?><br><span style="color:var(--muted)"><?= date('H:i', strtotime($ev['start_date'])) ?></span></td>
            <td style="font-size:.82rem"><?= h($ev['venue_name'] ?: '—') ?><br><span style="color:var(--muted);font-size:.72rem"><?= h($ev['city'] ?: '') ?></span></td>
            <td style="font-size:.82rem"><?= h($ev['category'] ?: '—') ?></td>
            <td><span class="sb st-<?= h($ev['status']) ?>"><?= h($ev['status']) ?></span></td>
            <td style="font-size:.82rem;color:var(--muted)"><?= number_format((int)$ev['view_count']) ?></td>
            <td style="white-space:nowrap"><a href="/admin/events/<?= $ev['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none;margin-right:8px">✏️</a><a href="/admin/events/<?= $ev['id'] ?>/tickets" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">🎫</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['events'])): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No events. <a href="/admin/events/create">Create first event</a></td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($result['pages'] > 1): ?>
    <div class="pag">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
            <?php if ($p == $page): ?><span><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Events'; require CMS_APP . '/views/admin/layouts/topbar.php';
