<?php
if (!function_exists('h')) { function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
ob_start();
$stats = $stats ?? [];
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
.btn-sm{padding:4px 10px;font-size:.75rem;border-radius:6px;text-decoration:none;border:none;cursor:pointer;font-weight:600;display:inline-block}
.btn-edit{background:#3b82f622;color:#3b82f6}
.btn-edit:hover{background:#3b82f644}
.btn-del{background:#ef444422;color:#ef4444}
.btn-del:hover{background:#ef444444}
.btn-toggle{background:#f59e0b22;color:#f59e0b}
.btn-toggle:hover{background:#f59e0b44}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.shop-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.shop-tbl th,.shop-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.shop-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase;background:var(--bg,#0f172a)}
.shop-tbl tr:hover{background:rgba(99,102,241,.04)}
.shop-badge{display:inline-block;padding:2px 10px;border-radius:10px;font-size:.7rem;font-weight:600}
.shop-badge.active{background:#10b98122;color:#10b981}
.shop-badge.inactive{background:#94a3b822;color:#94a3b8}
.shop-pagination{display:flex;justify-content:center;gap:6px;margin-top:20px}
.shop-pagination a,.shop-pagination span{padding:6px 12px;border-radius:6px;font-size:.85rem;text-decoration:none;border:1px solid var(--border,#334155);color:var(--text,#e2e8f0)}
.shop-pagination span.current{background:#6366f1;color:#fff;border-color:#6366f1}
.text-muted{color:var(--muted,#94a3b8)}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:24px}
.stat-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-align:center}
.stat-card .val{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0)}
.stat-card .lbl{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.code-badge{font-family:monospace;background:#6366f122;color:#a5b4fc;padding:3px 8px;border-radius:6px;font-size:.8rem;font-weight:600;letter-spacing:.5px}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>🏷️ Coupons <span class="text-muted" style="font-size:.9rem;font-weight:400">(<?= (int)($total ?? 0) ?>)</span></h1>
        <a href="/admin/shop/coupons/create" class="btn-primary">+ New Coupon</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="val"><?= (int)($stats['total_coupons'] ?? 0) ?></div><div class="lbl">Total Coupons</div></div>
        <div class="stat-card"><div class="val"><?= (int)($stats['active_coupons'] ?? 0) ?></div><div class="lbl">Active</div></div>
        <div class="stat-card"><div class="val"><?= (int)($stats['total_used'] ?? 0) ?></div><div class="lbl">Times Used</div></div>
        <div class="stat-card"><div class="val"><?= h($stats['most_used']['code'] ?? '—') ?></div><div class="lbl">Most Used</div></div>
    </div>

    <form method="get" action="/admin/shop/coupons" class="shop-toolbar">
        <input type="text" name="q" placeholder="Search codes..." value="<?= h($filters['search'] ?? '') ?>">
        <select name="status">
            <option value="">All Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <select name="type">
            <option value="">All Types</option>
            <option value="percentage" <?= ($filters['type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
            <option value="fixed" <?= ($filters['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed</option>
            <option value="free_shipping" <?= ($filters['type'] ?? '') === 'free_shipping' ? 'selected' : '' ?>>Free Shipping</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
    </form>

    <?php if (empty($coupons)): ?>
        <div class="shop-card" style="padding:40px;text-align:center">
            <div style="font-size:2.5rem;margin-bottom:10px">🏷️</div>
            <p class="text-muted">No coupons found. Create your first coupon!</p>
        </div>
    <?php else: ?>
        <div class="shop-card">
            <table class="shop-tbl">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Used / Max</th>
                        <th>Valid Period</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($coupons as $c): ?>
                    <tr>
                        <td><span class="code-badge"><?= h($c['code']) ?></span></td>
                        <td>
                            <?php if ($c['type'] === 'percentage'): ?>
                                <span style="color:#a78bfa">% Percentage</span>
                            <?php elseif ($c['type'] === 'fixed'): ?>
                                <span style="color:#34d399">$ Fixed</span>
                            <?php else: ?>
                                <span style="color:#60a5fa">🚚 Free Ship</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['type'] === 'percentage'): ?>
                                <?= h($c['value']) ?>%
                            <?php elseif ($c['type'] === 'fixed'): ?>
                                <?= h(number_format((float)$c['value'], 2)) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= (int)$c['used_count'] ?> / <?= $c['max_uses'] !== null ? (int)$c['max_uses'] : '∞' ?>
                        </td>
                        <td class="text-muted" style="font-size:.8rem">
                            <?php if ($c['valid_from'] || $c['valid_until']): ?>
                                <?= $c['valid_from'] ? date('M j, Y', strtotime($c['valid_from'])) : '—' ?>
                                →
                                <?= $c['valid_until'] ? date('M j, Y', strtotime($c['valid_until'])) : '—' ?>
                            <?php else: ?>
                                No limit
                            <?php endif; ?>
                        </td>
                        <td><span class="shop-badge <?= $c['status'] ?>"><?= h($c['status']) ?></span></td>
                        <td style="white-space:nowrap">
                            <a href="/admin/shop/coupons/<?= (int)$c['id'] ?>/edit" class="btn-sm btn-edit">Edit</a>
                            <form method="post" action="/admin/shop/coupons/<?= (int)$c['id'] ?>/toggle" style="display:inline">
                                <?= csrf_token_html() ?>
                                <button type="submit" class="btn-sm btn-toggle"><?= $c['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
                            </form>
                            <form method="post" action="/admin/shop/coupons/<?= (int)$c['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this coupon?')">
                                <?= csrf_token_html() ?>
                                <button type="submit" class="btn-sm btn-del">Del</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="shop-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                $qs = $_GET;
                $qs['page'] = $i;
                $url = '/admin/shop/coupons?' . http_build_query($qs);
                ?>
                <?php if ($i === ($page ?? 1)): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= h($url) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Coupons — Shop';
require CMS_ROOT . '/app/views/admin/layouts/topbar.php';
