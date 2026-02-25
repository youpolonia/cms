<?php if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 4)); } ?>
<?php require_once CMS_ROOT . '/admin/includes/header.php'; ?>
<div class="admin-content" style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h1 style="font-size:24px;font-weight:700;color:#e2e8f0">🚀 SaaS Platform Dashboard</h1>
    </div>
    
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px">
        <?php foreach ([
            ['👥','Total Users',$totalUsers,'#8b5cf6'],
            ['🟢','Active (30d)',$activeUsers,'#22c55e'],
            ['💰','Revenue','$'.number_format($totalRevenue,2),'#f59e0b'],
            ['📊','API Calls Today',number_format($todayUsage),'#3b82f6']
        ] as [$icon,$label,$value,$color]): ?>
        <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px">
            <div style="font-size:24px;margin-bottom:8px"><?= $icon ?></div>
            <div style="font-size:24px;font-weight:700;color:<?= $color ?>"><?= $value ?></div>
            <div style="font-size:13px;color:#94a3b8;margin-top:4px"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
        <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px">
            <h2 style="font-size:16px;font-weight:600;color:#e2e8f0;margin-bottom:16px">📈 Usage by Service (30d)</h2>
            <table style="width:100%;border-collapse:collapse">
                <thead><tr style="border-bottom:1px solid #334155">
                    <th style="text-align:left;padding:8px;color:#94a3b8;font-size:12px">Service</th>
                    <th style="text-align:right;padding:8px;color:#94a3b8;font-size:12px">Requests</th>
                    <th style="text-align:right;padding:8px;color:#94a3b8;font-size:12px">Credits</th>
                </tr></thead>
                <tbody>
                <?php foreach ($usageByService as $s): ?>
                <tr style="border-bottom:1px solid #1e293b"><td style="padding:8px;color:#e2e8f0"><?= htmlspecialchars($s['service']) ?></td><td style="text-align:right;padding:8px;color:#8b5cf6;font-weight:600"><?= number_format($s['requests']) ?></td><td style="text-align:right;padding:8px;color:#f59e0b"><?= number_format($s['credits']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($usageByService)): ?><tr><td colspan="3" style="padding:16px;text-align:center;color:#64748b">No usage yet</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px">
            <h2 style="font-size:16px;font-weight:600;color:#e2e8f0;margin-bottom:16px">👥 Recent Users</h2>
            <table style="width:100%;border-collapse:collapse">
                <thead><tr style="border-bottom:1px solid #334155">
                    <th style="text-align:left;padding:8px;color:#94a3b8;font-size:12px">Email</th>
                    <th style="text-align:left;padding:8px;color:#94a3b8;font-size:12px">Plan</th>
                    <th style="text-align:right;padding:8px;color:#94a3b8;font-size:12px">Credits</th>
                </tr></thead>
                <tbody>
                <?php foreach ($recentUsers as $u): ?>
                <tr style="border-bottom:1px solid #1e293b"><td style="padding:8px;color:#e2e8f0;font-size:13px"><?= htmlspecialchars($u['email']) ?></td><td style="padding:8px"><span style="background:#8b5cf620;color:#8b5cf6;padding:2px 8px;border-radius:4px;font-size:12px"><?= htmlspecialchars($u['plan']) ?></span></td><td style="text-align:right;padding:8px;color:#22c55e"><?= $u['credits_remaining'] ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($recentUsers)): ?><tr><td colspan="3" style="padding:16px;text-align:center;color:#64748b">No users yet</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
