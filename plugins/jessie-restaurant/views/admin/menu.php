<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-menu.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \RestaurantMenu::getItems($_GET, $page);
$categories = \RestaurantMenu::getCategories();
$sym = \RestaurantMenu::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.rw{max-width:1100px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.tb{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.tb th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.tb td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.tb tr:last-child td{border-bottom:none}
.fb{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.fb select,.fb input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.diet{display:inline-block;font-size:.6rem;padding:1px 5px;border-radius:3px;margin-left:2px;font-weight:700}
.d-v{background:rgba(16,185,129,.15);color:#34d399}.d-vg{background:rgba(52,211,153,.15);color:#6ee7b7}
.d-gf{background:rgba(245,158,11,.15);color:#fbbf24}.d-sp{background:rgba(239,68,68,.15);color:#fca5a5}
.price{font-weight:700;color:#10b981}.price-sale{text-decoration:line-through;color:var(--muted);font-size:.75rem;margin-right:4px}
.sb{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.sb-active{background:rgba(16,185,129,.15);color:#34d399}.sb-hidden{background:rgba(100,116,139,.15);color:#94a3b8}.sb-soldout{background:rgba(239,68,68,.15);color:#fca5a5}
</style>
<div class="rw">
    <div class="rh"><h1>🍽️ Menu Items</h1><div style="display:flex;gap:10px"><a href="/admin/restaurant" class="btn-s">← Dashboard</a><a href="/admin/restaurant/menu/create" class="btn-p">➕ Add Item</a></div></div>
    <div class="fb">
        <select onchange="location.href='?category_id='+this.value"><option value="">All Categories</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['category_id']??'')==(string)$c['id']?'selected':'' ?>><?= h($c['icon'].' '.$c['name']) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?status='+this.value"><option value="">All Status</option><?php foreach (['active','hidden','soldout'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="padding:8px;color:var(--muted);font-size:.82rem"><?= $result['total'] ?> items</span>
    </div>
    <table class="tb"><thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Diet</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($result['items'] as $i): ?>
        <tr>
            <td><strong><?= h($i['name']) ?></strong><?= $i['is_featured']?'<span style="color:#f59e0b;margin-left:4px;font-size:.7rem">⭐</span>':'' ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h(mb_substr($i['short_description'] ?: $i['description'], 0, 60)) ?></span></td>
            <td style="font-size:.82rem"><?= h($i['category_name']??'—') ?></td>
            <td><?php if($i['sale_price']): ?><span class="price-sale"><?= $sym ?><?= number_format((float)$i['price'],2) ?></span><?php endif; ?><span class="price"><?= $sym ?><?= number_format((float)($i['sale_price']?:$i['price']),2) ?></span></td>
            <td><?= $i['is_vegetarian']?'<span class="diet d-v">V</span>':'' ?><?= $i['is_vegan']?'<span class="diet d-vg">VG</span>':'' ?><?= $i['is_gluten_free']?'<span class="diet d-gf">GF</span>':'' ?><?= $i['is_spicy']?'<span class="diet d-sp">🌶️</span>':'' ?></td>
            <td><span class="sb sb-<?= h($i['status']) ?>"><?= h($i['status']) ?></span></td>
            <td><a href="/admin/restaurant/menu/<?= $i['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['items'])): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No items. <a href="/admin/restaurant/menu/create">Add first item</a></td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Menu Items'; require CMS_APP . '/views/admin/layouts/topbar.php';
