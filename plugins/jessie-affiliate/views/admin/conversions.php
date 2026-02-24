<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-affiliate-program.php';
require_once $pluginDir . '/includes/class-affiliate.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \Affiliate::getConversions($_GET, $page);
ob_start();
?>
<style>
.aff-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.aff-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.aff-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.aff-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.aff-table th{background:rgba(124,58,237,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.aff-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.aff-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-approved{background:rgba(16,185,129,.15);color:#34d399}
.status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
.status-paid{background:rgba(124,58,237,.15);color:#c4b5fd}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.btn-sm{padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem;border:none;display:inline-block}
</style>
<div class="aff-wrap">
    <div class="aff-header"><h1>🎯 Conversions</h1><a href="/admin/affiliate" class="btn-secondary">← Dashboard</a></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Statuses</option><?php foreach (['pending','approved','rejected','paid'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> conversions</span>
    </div>
    <table class="aff-table"><thead><tr><th>Affiliate</th><th>Program</th><th>Order</th><th>Order Total</th><th>Commission</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($result['conversions'] as $c): ?>
        <tr>
            <td><strong><?= h($c['affiliate_name'] ?? '—') ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= h($c['affiliate_email'] ?? '') ?></span></td>
            <td style="font-size:.82rem"><?= h($c['program_name'] ?? '—') ?></td>
            <td style="font-size:.82rem;font-family:monospace"><?= h($c['order_id'] ?: '—') ?></td>
            <td style="font-size:.82rem">$<?= number_format((float)$c['order_total'], 2) ?></td>
            <td style="font-size:.82rem;font-weight:700;color:#34d399">$<?= number_format((float)$c['commission'], 2) ?></td>
            <td><span class="status-badge status-<?= h($c['status']) ?>"><?= h($c['status']) ?></span></td>
            <td style="font-size:.75rem;color:var(--muted)"><?= date('M j, Y H:i', strtotime($c['created_at'])) ?></td>
            <td style="white-space:nowrap">
                <?php if ($c['status'] === 'pending'): ?>
                <form method="POST" action="/admin/affiliate/conversions/<?= $c['id'] ?>/approve" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(16,185,129,.15);color:#34d399" title="Approve">✓</button></form>
                <form method="POST" action="/admin/affiliate/conversions/<?= $c['id'] ?>/reject" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(239,68,68,.1);color:#fca5a5" title="Reject">✕</button></form>
                <?php else: ?>
                <span style="font-size:.72rem;color:var(--muted)">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['conversions'])): ?><tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No conversions found.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Affiliate Conversions'; require CMS_APP . '/views/admin/layouts/topbar.php';
