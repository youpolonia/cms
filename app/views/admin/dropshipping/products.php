<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
ob_start();
?>
<style>
.ds-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.ds-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.ds-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.ds-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0);vertical-align:middle}
.ds-table tr:last-child td{border-bottom:none}
.ds-table tr:hover td{background:rgba(99,102,241,.04)}
.product-row{display:flex;align-items:center;gap:10px}
.product-row img{width:40px;height:40px;border-radius:6px;object-fit:cover;border:1px solid var(--border,#334155)}
.product-row .name{font-weight:600}
.product-row .name a{color:#a5b4fc;text-decoration:none}
.product-row .name a:hover{text-decoration:underline}
.price-cell{text-align:right}
.price-cost{color:#f59e0b;font-size:.8rem}
.price-sell{color:#10b981;font-weight:700}
.price-margin{color:#a5b4fc;font-size:.75rem}
.sync-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600}
.sync-synced{background:rgba(16,185,129,.15);color:#34d399}
.sync-never{background:rgba(99,102,241,.15);color:#a5b4fc}
.sync-error{background:rgba(239,68,68,.15);color:#fca5a5}
.sync-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.supplier-tag{background:rgba(139,92,246,.12);color:#c4b5fd;padding:2px 8px;border-radius:4px;font-size:.72rem}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>📦 Dropship Products</h1>
        <div style="display:flex;gap:10px">
            <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
            <a href="/admin/dropshipping/import" class="btn-ds">📥 Import</a>
        </div>
    </div>

    <div class="filter-bar">
        <select onchange="location.href='?supplier='+this.value">
            <option value="">All Suppliers</option>
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (($filters['supplier_id'] ?? '') == $s['id']) ? 'selected' : '' ?>><?= h($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" placeholder="Search products..." value="<?= h($filters['search'] ?? '') ?>" onchange="location.href='?q='+encodeURIComponent(this.value)">
    </div>

    <?php if (empty($links)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--muted,#94a3b8)">
            <p style="font-size:1.2rem">No dropship products linked yet.</p>
            <a href="/admin/dropshipping/import" class="btn-ds" style="margin-top:12px;text-decoration:none">📥 Import Your First Product</a>
        </div>
    <?php else: ?>
        <table class="ds-table">
            <thead><tr>
                <th>Product</th>
                <th>Supplier</th>
                <th style="text-align:right">Cost</th>
                <th style="text-align:right">Sell Price</th>
                <th style="text-align:right">Margin</th>
                <th>Sync</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
                <?php foreach ($links as $l): ?>
                <tr>
                    <td>
                        <div class="product-row">
                            <?php if ($l['product_image']): ?><img src="<?= h($l['product_image']) ?>" alt=""><?php endif; ?>
                            <div class="name"><a href="/admin/shop/products/<?= (int)$l['product_id'] ?>/edit"><?= h($l['product_name'] ?? 'Product #' . $l['product_id']) ?></a></div>
                        </div>
                    </td>
                    <td><span class="supplier-tag"><?= h($l['supplier_name'] ?? '?') ?></span></td>
                    <td class="price-cell"><span class="price-cost">$<?= number_format((float)($l['supplier_price'] ?? 0), 2) ?></span></td>
                    <td class="price-cell"><span class="price-sell">$<?= number_format((float)($l['our_price'] ?? $l['product_price'] ?? 0), 2) ?></span></td>
                    <td class="price-cell"><span class="price-margin">$<?= number_format((float)($l['profit_margin'] ?? 0), 2) ?></span></td>
                    <td><span class="sync-badge sync-<?= h($l['sync_status'] ?? 'never') ?>"><?= h($l['sync_status'] ?? 'never') ?></span></td>
                    <td>
                        <?php if ($l['supplier_product_url']): ?>
                        <a href="<?= h($l['supplier_product_url']) ?>" target="_blank" style="color:#a5b4fc;font-size:.78rem" title="Open supplier page">🔗 Source</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Dropship Products';
require CMS_APP . '/views/admin/layouts/topbar.php';
