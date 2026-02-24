<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$orders = $orders ?? [];
ob_start();
?>
<style>
.ds-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.ds-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.ds-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.ds-table td{padding:12px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.ds-table tr:last-child td{border-bottom:none}
.status-badge{padding:3px 10px;border-radius:4px;font-size:.72rem;font-weight:600}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-sent{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-confirmed{background:rgba(16,185,129,.15);color:#34d399}
.status-shipped{background:rgba(59,130,246,.15);color:#93c5fd}
.status-delivered{background:rgba(16,185,129,.2);color:#10b981}
.status-failed{background:rgba(239,68,68,.15);color:#fca5a5}
.status-cancelled{background:rgba(107,114,128,.15);color:#9ca3af}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>🚚 Dropship Orders</h1>
        <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
    </div>

    <?php if (empty($orders)): ?>
        <div style="text-align:center;padding:60px 20px;color:var(--muted,#94a3b8)">
            <p style="font-size:1.2rem">No dropship orders yet.</p>
            <p style="font-size:.85rem">Orders will appear here when customers purchase dropship products.</p>
        </div>
    <?php else: ?>
        <table class="ds-table">
            <thead><tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Tracking</th>
                <th>Cost</th>
                <th>Date</th>
            </tr></thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="/admin/shop/orders/<?= (int)$o['order_id'] ?>" style="color:#a5b4fc">#<?= (int)$o['order_id'] ?></a></td>
                    <td style="font-size:.82rem"><?= h($o['customer_name'] ?? '') ?></td>
                    <td style="font-size:.82rem"><?= h($o['supplier_name'] ?? '?') ?></td>
                    <td><span class="status-badge status-<?= h($o['status']) ?>"><?= h($o['status']) ?></span></td>
                    <td style="font-size:.8rem"><?= $o['tracking_number'] ? h($o['tracking_number']) : '<span style="color:var(--muted,#94a3b8)">—</span>' ?></td>
                    <td style="font-size:.85rem">$<?= number_format((float)($o['cost_total'] ?? 0), 2) ?></td>
                    <td style="font-size:.78rem;color:var(--muted,#94a3b8)"><?= h(date('M j, H:i', strtotime($o['created_at']))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Dropship Orders';
require CMS_APP . '/views/admin/layouts/topbar.php';
