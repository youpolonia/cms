<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-directory-listing.php';
require_once $pluginDir . '/includes/class-directory-category.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \DirectoryListing::getAll($_GET, $page);
$categories = \DirectoryCategory::getAll('active');
ob_start();
?>
<style>
.dir-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.dir-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.dir-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-dir{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.dir-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.dir-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.dir-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.dir-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.stars{color:#f59e0b}
</style>
<div class="dir-wrap">
    <div class="dir-header"><h1>🏢 Listings</h1><div style="display:flex;gap:10px"><a href="/admin/directory" class="btn-secondary">← Dashboard</a><a href="/admin/directory/listings/create" class="btn-dir">➕ Add</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All</option><?php foreach (['active','pending','rejected','expired'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?category_id='+this.value"><option value="">All Categories</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['category_id']??'')==(string)$c['id']?'selected':'' ?>><?= h($c['name']) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> listings</span>
    </div>
    <table class="dir-table"><thead><tr><th>Listing</th><th>Category</th><th>City</th><th>Rating</th><th>Views</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($result['listings'] as $l): ?>
        <tr>
            <td><strong><?= h($l['title']) ?></strong><?= $l['is_featured']?'<span style="font-size:.65rem;color:#f59e0b;margin-left:6px">⭐ FEATURED</span>':'' ?><?= $l['is_verified']?'<span style="font-size:.65rem;color:#10b981;margin-left:6px">✓ VERIFIED</span>':'' ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($l['owner_name']?:$l['owner_email']) ?></span></td>
            <td style="font-size:.82rem"><?= h($l['category_name']??'—') ?></td>
            <td style="font-size:.82rem"><?= h($l['city']?:$l['country']) ?></td>
            <td><span class="stars"><?= $l['avg_rating']>0?str_repeat('★',round((float)$l['avg_rating'])):'—' ?></span><br><span style="font-size:.7rem;color:var(--muted)"><?= $l['review_count'] ?> reviews</span></td>
            <td style="font-size:.82rem;color:var(--muted)"><?= number_format($l['view_count']) ?></td>
            <td><span class="status-badge status-<?= h($l['status']) ?>"><?= h($l['status']) ?></span></td>
            <td><a href="/admin/directory/listings/<?= $l['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['listings'])): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No listings found.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Directory Listings'; require CMS_APP . '/views/admin/layouts/topbar.php';
