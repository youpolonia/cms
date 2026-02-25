<?php if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 4)); } ?>
<?php require_once CMS_ROOT . '/admin/includes/header.php'; ?>
<div class="admin-content" style="padding:24px">
    <h1 style="font-size:24px;font-weight:700;color:#e2e8f0;margin-bottom:24px">📋 SaaS Plans</h1>
    <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="background:#0f172a">
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Service</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Plan</th>
                <th style="text-align:right;padding:12px;color:#94a3b8;font-size:12px">Monthly</th>
                <th style="text-align:right;padding:12px;color:#94a3b8;font-size:12px">Yearly</th>
                <th style="text-align:right;padding:12px;color:#94a3b8;font-size:12px">Credits/mo</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Status</th>
            </tr></thead>
            <tbody>
            <?php foreach ($plans as $p): ?>
            <tr style="border-bottom:1px solid #334155">
                <td style="padding:12px;color:#8b5cf6;font-weight:600"><?= htmlspecialchars($p['service']) ?></td>
                <td style="padding:12px;color:#e2e8f0"><?= htmlspecialchars($p['name']) ?><?= $p['is_popular']?' ⭐':'' ?></td>
                <td style="text-align:right;padding:12px;color:#22c55e;font-weight:600">$<?= number_format($p['price_monthly'],2) ?></td>
                <td style="text-align:right;padding:12px;color:#22c55e">$<?= number_format($p['price_yearly'],2) ?></td>
                <td style="text-align:right;padding:12px;color:#f59e0b"><?= number_format($p['credits_monthly']) ?></td>
                <td style="padding:12px"><span style="color:<?= $p['status']==='active'?'#22c55e':'#64748b' ?>"><?= $p['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
