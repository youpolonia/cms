<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$stats = $stats ?? [];
ob_start();
?>
<style>
.ds-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px}
.ds-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.ds-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.ds-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-ds:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,.4)}
.btn-sm{padding:6px 14px;font-size:.78rem}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px);box-shadow:0 4px 12px rgba(99,102,241,.15)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.import-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.import-item:last-child{border-bottom:none}
.import-status{padding:2px 8px;border-radius:4px;font-size:.72rem;font-weight:600;text-transform:uppercase}
.status-completed{background:rgba(16,185,129,.15);color:#34d399}
.status-failed{background:rgba(239,68,68,.15);color:#fca5a5}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-processing{background:rgba(99,102,241,.15);color:#a5b4fc}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>🚚 Dropshipping Dashboard</h1>
        <a href="/admin/dropshipping/import" class="btn-ds">📥 Import Products</a>
    </div>

    <!-- Stats -->
    <div class="ds-stats">
        <div class="ds-stat"><div class="val" style="color:#6366f1"><?= (int)($stats['suppliers'] ?? 0) ?></div><div class="lbl">Suppliers</div></div>
        <div class="ds-stat"><div class="val" style="color:#a5b4fc"><?= (int)($stats['linked_products'] ?? 0) ?></div><div class="lbl">Linked Products</div></div>
        <div class="ds-stat"><div class="val" style="color:#10b981">$<?= number_format((float)($stats['total_profit'] ?? 0), 2) ?></div><div class="lbl">Total Margin</div></div>
        <div class="ds-stat"><div class="val" style="color:#f59e0b"><?= (int)($stats['pending_orders'] ?? 0) ?></div><div class="lbl">Pending Orders</div></div>
        <div class="ds-stat"><div class="val" style="color:var(--text,#e2e8f0)"><?= (int)($stats['total_imports'] ?? 0) ?></div><div class="lbl">Total Imports</div></div>
        <?php if (($stats['sync_errors'] ?? 0) > 0): ?>
        <div class="ds-stat"><div class="val" style="color:#ef4444"><?= (int)$stats['sync_errors'] ?></div><div class="lbl">Sync Errors</div></div>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="/admin/dropshipping/import" class="quick-link">
            <span class="icon">📥</span>
            <div><div class="text">Import Products</div><div class="desc">URL, CSV, or batch import with AI</div></div>
        </a>
        <a href="/admin/dropshipping/suppliers" class="quick-link">
            <span class="icon">🏭</span>
            <div><div class="text">Manage Suppliers</div><div class="desc"><?= (int)($stats['suppliers'] ?? 0) ?> active suppliers</div></div>
        </a>
        <a href="/admin/dropshipping/products" class="quick-link">
            <span class="icon">📦</span>
            <div><div class="text">Dropship Products</div><div class="desc"><?= (int)($stats['linked_products'] ?? 0) ?> products linked</div></div>
        </a>
        <a href="/admin/dropshipping/price-rules" class="quick-link">
            <span class="icon">💰</span>
            <div><div class="text">Price Rules</div><div class="desc"><?= (int)($stats['price_rules'] ?? 0) ?> active rules</div></div>
        </a>
    </div>

    <!-- Recent Imports -->
    <div class="ds-card">
        <h3>📋 Recent Imports</h3>
        <?php if (empty($stats['recent_imports'])): ?>
            <p style="color:var(--muted,#94a3b8);font-size:.85rem">No imports yet. <a href="/admin/dropshipping/import" style="color:#a5b4fc">Start importing →</a></p>
        <?php else: ?>
            <?php foreach ($stats['recent_imports'] as $imp): ?>
            <div class="import-item">
                <span class="import-status status-<?= h($imp['status']) ?>"><?= h($imp['status']) ?></span>
                <div style="flex:1;min-width:0">
                    <?php if ($imp['product_name']): ?>
                        <div style="font-weight:600;font-size:.85rem;color:var(--text,#e2e8f0)"><?= h($imp['product_name']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:.75rem;color:var(--muted,#94a3b8);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($imp['source_url'] ?? $imp['source_type']) ?></div>
                </div>
                <div style="font-size:.72rem;color:var(--muted,#94a3b8);white-space:nowrap"><?= h(date('M j, H:i', strtotime($imp['created_at']))) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dropshipping Dashboard';
require CMS_APP . '/views/admin/layouts/topbar.php';
