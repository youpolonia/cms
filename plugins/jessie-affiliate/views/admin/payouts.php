<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-affiliate-program.php';
require_once $pluginDir . '/includes/class-affiliate.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \Affiliate::getPayouts($_GET, $page);
// Get affiliates eligible for payout (active with pending_payout > 0)
$eligibleAffiliates = db()->query("SELECT a.id, a.name, a.email, a.pending_payout, a.payment_method, p.min_payout FROM affiliates a LEFT JOIN affiliate_programs p ON a.program_id = p.id WHERE a.status = 'active' AND a.pending_payout > 0 ORDER BY a.pending_payout DESC")->fetchAll(\PDO::FETCH_ASSOC);
ob_start();
?>
<style>
.aff-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.aff-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.aff-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-aff{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.aff-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.aff-table th{background:rgba(124,58,237,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.aff-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.aff-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-completed{background:rgba(16,185,129,.15);color:#34d399}
.status-failed{background:rgba(239,68,68,.15);color:#fca5a5}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.btn-sm{padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem;border:none;display:inline-block}
.payout-form{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:24px}
.payout-form h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.payout-form .row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;align-items:end}
@media(max-width:768px){.payout-form .row{grid-template-columns:1fr}}
.payout-form label{display:block;font-size:.78rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.payout-form input,.payout-form select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
</style>
<div class="aff-wrap">
    <div class="aff-header"><h1>💰 Payouts</h1><a href="/admin/affiliate" class="btn-secondary">← Dashboard</a></div>

    <?php if (!empty($eligibleAffiliates)): ?>
    <div class="payout-form">
        <h3>➕ Create Payout</h3>
        <form method="POST" action="/admin/affiliate/payouts/create">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div>
                    <label>Affiliate</label>
                    <select name="affiliate_id" required>
                        <option value="">Select affiliate...</option>
                        <?php foreach ($eligibleAffiliates as $ea): ?>
                        <option value="<?= $ea['id'] ?>"><?= h($ea['name']) ?> (<?= h($ea['email']) ?>) — $<?= number_format((float)$ea['pending_payout'], 2) ?> pending</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                </div>
                <div>
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="crypto">Crypto</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label>Reference</label>
                    <input type="text" name="payment_reference" placeholder="Transaction ID...">
                </div>
                <div><button type="submit" class="btn-aff" style="white-space:nowrap">💸 Create</button></div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Statuses</option><?php foreach (['pending','completed','failed'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> payouts</span>
    </div>
    <table class="aff-table"><thead><tr><th>Affiliate</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($result['payouts'] as $po): ?>
        <tr>
            <td><strong><?= h($po['affiliate_name'] ?? '—') ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= h($po['affiliate_email'] ?? '') ?></span></td>
            <td style="font-size:.9rem;font-weight:700;color:#34d399">$<?= number_format((float)$po['amount'], 2) ?></td>
            <td style="font-size:.82rem"><?= h($po['payment_method'] ?: '—') ?></td>
            <td style="font-size:.78rem;font-family:monospace;color:var(--muted)"><?= h($po['payment_reference'] ?: '—') ?></td>
            <td><span class="status-badge status-<?= h($po['status']) ?>"><?= h($po['status']) ?></span></td>
            <td style="font-size:.75rem;color:var(--muted)"><?= date('M j, Y H:i', strtotime($po['created_at'])) ?></td>
            <td style="white-space:nowrap">
                <?php if ($po['status'] === 'pending'): ?>
                <form method="POST" action="/admin/affiliate/payouts/<?= $po['id'] ?>/complete" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(16,185,129,.15);color:#34d399" title="Mark Completed">✓</button></form>
                <form method="POST" action="/admin/affiliate/payouts/<?= $po['id'] ?>/fail" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(239,68,68,.1);color:#fca5a5" title="Mark Failed">✕</button></form>
                <?php else: ?>
                <span style="font-size:.72rem;color:var(--muted)">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['payouts'])): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No payouts yet.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Affiliate Payouts'; require CMS_APP . '/views/admin/layouts/topbar.php';
