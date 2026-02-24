<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$f = $forward;
ob_start();
?>
<style>
.ds-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.4);font-size:.85rem}
.info-row:last-child{border-bottom:none}
.info-row .label{color:var(--muted,#94a3b8)}
.info-row .value{color:var(--text,#e2e8f0);font-weight:600}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.status-badge{padding:4px 14px;border-radius:6px;font-size:.78rem;font-weight:700;text-transform:uppercase}
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
        <h1>🚚 Order Forward #<?= (int)$f['id'] ?></h1>
        <a href="/admin/dropshipping/orders" class="btn-secondary">← Back</a>
    </div>

    <div class="ds-card">
        <h3>📋 Forward Details</h3>
        <div class="info-row"><span class="label">Order</span><span class="value"><a href="/admin/shop/orders/<?= (int)$f['order_id'] ?>" style="color:#a5b4fc">#<?= (int)$f['order_id'] ?></a></span></div>
        <div class="info-row"><span class="label">Customer</span><span class="value"><?= h($f['customer_name'] ?? '') ?> (<?= h($f['customer_email'] ?? '') ?>)</span></div>
        <div class="info-row"><span class="label">Supplier</span><span class="value"><?= h($f['supplier_name'] ?? '?') ?> (<?= h($f['supplier_type'] ?? '') ?>)</span></div>
        <div class="info-row"><span class="label">Status</span><span class="value"><span class="status-badge status-<?= h($f['status']) ?>"><?= h($f['status']) ?></span></span></div>
        <div class="info-row"><span class="label">Supplier Order ID</span><span class="value"><?= h($f['supplier_order_id'] ?? '—') ?></span></div>
        <div class="info-row"><span class="label">Cost Total</span><span class="value">$<?= number_format((float)($f['cost_total'] ?? 0), 2) ?></span></div>
        <div class="info-row"><span class="label">Order Total</span><span class="value">$<?= number_format((float)($f['order_total'] ?? 0), 2) ?></span></div>
        <div class="info-row"><span class="label">Forwarded At</span><span class="value"><?= $f['forwarded_at'] ? h(date('M j, Y H:i', strtotime($f['forwarded_at']))) : '—' ?></span></div>
        <div class="info-row"><span class="label">Created</span><span class="value"><?= h(date('M j, Y H:i', strtotime($f['created_at']))) ?></span></div>
        <?php if ($f['tracking_number']): ?>
        <div class="info-row"><span class="label">Tracking</span><span class="value"><?= h($f['tracking_number']) ?> <?php if ($f['tracking_url']): ?><a href="<?= h($f['tracking_url']) ?>" target="_blank" style="color:#a5b4fc;font-size:.78rem">Track →</a><?php endif; ?></span></div>
        <?php endif; ?>
        <?php if ($f['notes']): ?>
        <div class="info-row"><span class="label">Notes</span><span class="value" style="font-weight:400;max-width:60%;text-align:right"><?= h($f['notes']) ?></span></div>
        <?php endif; ?>
    </div>

    <!-- Update Form -->
    <div class="ds-card">
        <h3>✏️ Update Status / Tracking</h3>
        <form method="post" action="/admin/dropshipping/orders/<?= (int)$f['id'] ?>/update">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['pending','sent','confirmed','shipped','delivered','failed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $f['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tracking Number</label>
                    <input type="text" name="tracking_number" value="<?= h($f['tracking_number'] ?? '') ?>" placeholder="e.g. 1Z999AA10123456784">
                </div>
            </div>
            <div class="form-group">
                <label>Tracking URL</label>
                <input type="url" name="tracking_url" value="<?= h($f['tracking_url'] ?? '') ?>" placeholder="https://tracking.example.com/...">
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="2" style="min-height:60px;resize:vertical;font-family:inherit"><?= h($f['notes'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end">
                <a href="/admin/dropshipping/orders" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-ds">💾 Update</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Order Forward #' . (int)$f['id'];
require CMS_APP . '/views/admin/layouts/topbar.php';
