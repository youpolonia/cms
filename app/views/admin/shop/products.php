<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Products';
ob_start();
$currency = get_setting('shop_currency', 'USD');
$symbols = ['USD'=>'$','EUR'=>'€','GBP'=>'£','PLN'=>'zł'];
$sym = $symbols[$currency] ?? $currency . ' ';
?>
<style>
.shop-wrap{max-width:1200px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px}
.shop-toolbar input,.shop-toolbar select{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.85rem}
.shop-toolbar input{min-width:200px}
.shop-toolbar select{min-width:140px}
.btn-primary{background:#6366f1;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:.85rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
.btn-primary:hover{background:#4f46e5}
.btn-sm{padding:4px 10px;font-size:.75rem;border-radius:6px;text-decoration:none;border:none;cursor:pointer;font-weight:600}
.btn-edit{background:#3b82f622;color:#3b82f6}
.btn-edit:hover{background:#3b82f644}
.btn-del{background:#ef444422;color:#ef4444}
.btn-del:hover{background:#ef444444}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.shop-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.shop-tbl th,.shop-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.shop-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase;background:var(--bg,#0f172a)}
.shop-tbl tr:hover{background:rgba(99,102,241,.04)}
.shop-tbl img.thumb{width:50px;height:50px;object-fit:cover;border-radius:6px;background:var(--bg,#0f172a)}
.shop-badge{display:inline-block;padding:2px 10px;border-radius:10px;font-size:.7rem;font-weight:600}
.shop-badge.active{background:#10b98122;color:#10b981}
.shop-badge.draft{background:#94a3b822;color:#94a3b8}
.shop-badge.archived{background:#ef444422;color:#ef4444}
.shop-pagination{display:flex;justify-content:center;gap:6px;margin-top:20px}
.shop-pagination a,.shop-pagination span{padding:6px 12px;border-radius:6px;font-size:.85rem;text-decoration:none;border:1px solid var(--border,#334155);color:var(--text,#e2e8f0)}
.shop-pagination span.current{background:#6366f1;color:#fff;border-color:#6366f1}
a.shop-link{color:#6366f1;text-decoration:none}
a.shop-link:hover{text-decoration:underline}
.no-img{width:50px;height:50px;background:var(--bg,#0f172a);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
.text-muted{color:var(--muted,#94a3b8)}
.price-sale{text-decoration:line-through;color:var(--muted,#94a3b8);font-size:.8rem}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>📦 Products <span class="text-muted" style="font-size:.9rem;font-weight:400">(<?= (int)$total ?>)</span></h1>
        <a href="/admin/shop/products/create" class="btn-primary">+ Add Product</a>
    </div>

    <form method="get" action="/admin/shop/products" class="shop-toolbar">
        <input type="text" name="q" placeholder="Search products..." value="<?= h($filters['search'] ?? '') ?>">
        <select name="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ($filters['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
        </select>
        <button type="submit" class="btn-primary" style="padding:8px 14px">🔍 Search</button>
    </form>

    <div class="shop-card">
        <?php if (empty($products)): ?>
            <div style="padding:40px;text-align:center;color:var(--muted,#94a3b8)">
                <div style="font-size:2rem;margin-bottom:8px">📦</div>
                <p>No products found.</p>
                <a href="/admin/shop/products/create" class="btn-primary" style="margin-top:12px;display:inline-flex">+ Add First Product</a>
            </div>
        <?php else: ?>
            <table class="shop-tbl">
                <thead>
                    <tr>
                        <th style="width:60px">Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= h($p['image']) ?>" alt="" class="thumb">
                            <?php else: ?>
                                <div class="no-img">📷</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/shop/products/<?= (int)$p['id'] ?>/edit" class="shop-link" style="font-weight:600"><?= h($p['name']) ?></a>
                            <?php if (!empty($p['sku'])): ?>
                                <div class="text-muted" style="font-size:.75rem">SKU: <?= h($p['sku']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['sale_price'] !== null && (float)$p['sale_price'] > 0): ?>
                                <span class="price-sale"><?= h($sym) ?><?= number_format((float)$p['price'], 2) ?></span><br>
                                <span style="color:#10b981;font-weight:600"><?= h($sym) ?><?= number_format((float)$p['sale_price'], 2) ?></span>
                            <?php else: ?>
                                <span style="font-weight:600"><?= h($sym) ?><?= number_format((float)$p['price'], 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int)$p['stock'] === -1): ?>
                                <span title="Unlimited">∞</span>
                            <?php elseif ((int)$p['stock'] <= 5): ?>
                                <span style="color:#ef4444;font-weight:600"><?= (int)$p['stock'] ?></span>
                            <?php else: ?>
                                <?= (int)$p['stock'] ?>
                            <?php endif; ?>
                        </td>
                        <td><span class="shop-badge <?= h($p['status'] ?? 'draft') ?>"><?= h(ucfirst($p['status'] ?? 'draft')) ?></span></td>
                        <td class="text-muted"><?= h($p['category_name'] ?? '—') ?></td>
                        <td>
                            <a href="/admin/shop/products/<?= (int)$p['id'] ?>/edit" class="btn-sm btn-edit">✏️ Edit</a>
                            <form method="post" action="/admin/shop/products/<?= (int)$p['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <button type="submit" class="btn-sm btn-del">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="shop-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                    $qs = $_GET;
                    $qs['page'] = $i;
                    $url = '/admin/shop/products?' . http_build_query($qs);
                ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= h($url) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
