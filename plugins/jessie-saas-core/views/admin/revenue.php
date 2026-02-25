<?php if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 4)); } ?>
<?php require_once CMS_ROOT . '/admin/includes/header.php'; ?>
<div class="admin-content" style="padding:24px">
    <h1 style="font-size:24px;font-weight:700;color:#e2e8f0;margin-bottom:24px">💰 Revenue</h1>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
        <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px">
            <h2 style="font-size:16px;font-weight:600;color:#e2e8f0;margin-bottom:16px">Monthly Revenue</h2>
            <?php foreach ($monthlyRevenue as $m): ?>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #334155">
                <span style="color:#94a3b8"><?= $m['month'] ?></span>
                <span style="color:#22c55e;font-weight:700">$<?= number_format($m['revenue'],2) ?></span>
                <span style="color:#64748b;font-size:12px"><?= $m['txns'] ?> txns</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($monthlyRevenue)): ?><p style="color:#64748b;text-align:center;padding:16px">No revenue yet</p><?php endif; ?>
        </div>
        <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px">
            <h2 style="font-size:16px;font-weight:600;color:#e2e8f0;margin-bottom:16px">Recent Transactions</h2>
            <?php foreach (array_slice($transactions, 0, 15) as $t): ?>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #334155;font-size:13px">
                <span style="color:#e2e8f0"><?= htmlspecialchars($t['email']) ?></span>
                <span style="color:<?= $t['type']==='charge'?'#22c55e':'#f59e0b' ?>"><?= $t['type'] === 'charge' ? '+$'.number_format($t['amount'],2) : $t['credits'].' cr' ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?><p style="color:#64748b;text-align:center;padding:16px">No transactions yet</p><?php endif; ?>
        </div>
    </div>
</div>
<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
