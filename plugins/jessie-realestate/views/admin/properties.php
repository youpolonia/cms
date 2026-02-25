<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-realestate-property.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \RealEstateProperty::getAll($_GET, $page, 20);
$symbol = \RealEstateProperty::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.re-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.re-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.re-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-re{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.re-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.re-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.re-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.re-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-sold{background:rgba(239,68,68,.15);color:#fca5a5}
.status-rented{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-draft{background:rgba(148,163,184,.15);color:#94a3b8}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:16px}
.pagination a,.pagination span{padding:6px 12px;border-radius:6px;font-size:.82rem;border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);text-decoration:none}
.pagination a:hover{background:rgba(99,102,241,.1);border-color:#6366f1}
.pagination .current{background:#6366f1;color:#fff;border-color:#6366f1}
</style>
<div class="re-wrap">
    <div class="re-header"><h1>🏘️ Properties</h1><div style="display:flex;gap:10px"><a href="/admin/realestate" class="btn-secondary">← Dashboard</a><a href="/admin/realestate/properties/create" class="btn-re">➕ Add</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Statuses</option><?php foreach (['active','pending','sold','rented','draft'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?property_type='+this.value"><option value="">All Types</option><?php foreach (['house','apartment','condo','townhouse','land','commercial','other'] as $t): ?><option value="<?= $t ?>" <?= ($_GET['property_type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?listing_type='+this.value"><option value="">Sale/Rent</option><option value="sale" <?= ($_GET['listing_type']??'')==='sale'?'selected':'' ?>>For Sale</option><option value="rent" <?= ($_GET['listing_type']??'')==='rent'?'selected':'' ?>>For Rent</option><option value="lease" <?= ($_GET['listing_type']??'')==='lease'?'selected':'' ?>>For Lease</option></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> properties</span>
    </div>
    <table class="re-table"><thead><tr><th>Property</th><th>Type</th><th>Price</th><th>Beds/Baths</th><th>City</th><th>Agent</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($result['properties'] as $p): ?>
        <tr>
            <td><strong><?= h($p['title']) ?></strong><?= $p['is_featured']?'<span style="font-size:.65rem;color:#f59e0b;margin-left:6px">⭐</span>':'' ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($p['address'] ?: $p['slug']) ?></span></td>
            <td style="font-size:.82rem"><?= ucfirst(h($p['property_type'])) ?><br><span style="font-size:.72rem;color:var(--muted)">For <?= h($p['listing_type']) ?></span></td>
            <td style="font-weight:700;color:#10b981"><?= $symbol ?><?= number_format((float)$p['price']) ?><?= $p['listing_type']==='rent'?'<span style="font-size:.7rem;color:var(--muted)">/mo</span>':'' ?></td>
            <td style="font-size:.85rem"><?= $p['bedrooms'] !== null ? $p['bedrooms'] . '🛏' : '—' ?> <?= $p['bathrooms'] !== null ? $p['bathrooms'] . '🚿' : '' ?></td>
            <td style="font-size:.82rem"><?= h($p['city'] ?: '—') ?></td>
            <td style="font-size:.82rem"><?= h($p['agent_name'] ?? '—') ?></td>
            <td><span class="status-badge status-<?= h($p['status']) ?>"><?= h($p['status']) ?></span></td>
            <td><a href="/admin/realestate/properties/<?= $p['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['properties'])): ?><tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No properties found.</td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($result['pages'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
            <?php if ($i === $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="?page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Properties'; require CMS_APP . '/views/admin/layouts/topbar.php';
