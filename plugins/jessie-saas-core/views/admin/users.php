<?php if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 4)); } ?>
<?php require_once CMS_ROOT . '/admin/includes/header.php'; ?>
<div class="admin-content" style="padding:24px">
    <h1 style="font-size:24px;font-weight:700;color:#e2e8f0;margin-bottom:24px">👥 SaaS Users</h1>
    <div style="background:#1e293b;border:1px solid #334155;border-radius:12px;overflow:hidden">
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="background:#0f172a">
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">ID</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Email</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Name</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Plan</th>
                <th style="text-align:right;padding:12px;color:#94a3b8;font-size:12px">Credits</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Status</th>
                <th style="text-align:left;padding:12px;color:#94a3b8;font-size:12px">Joined</th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr style="border-bottom:1px solid #334155">
                <td style="padding:12px;color:#64748b"><?= $u['id'] ?></td>
                <td style="padding:12px;color:#e2e8f0"><?= htmlspecialchars($u['email']) ?></td>
                <td style="padding:12px;color:#cbd5e1"><?= htmlspecialchars($u['name'] ?: '-') ?></td>
                <td style="padding:12px"><span style="background:#8b5cf620;color:#8b5cf6;padding:2px 8px;border-radius:4px;font-size:12px"><?= htmlspecialchars($u['plan']) ?></span></td>
                <td style="text-align:right;padding:12px;color:#22c55e;font-weight:600"><?= $u['credits_remaining'] ?></td>
                <td style="padding:12px"><span style="color:<?= $u['status']==='active'?'#22c55e':'#ef4444' ?>"><?= $u['status'] ?></span></td>
                <td style="padding:12px;color:#64748b;font-size:13px"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total > $perPage): ?>
    <div style="margin-top:16px;display:flex;gap:8px;justify-content:center">
        <?php for ($i=1;$i<=ceil($total/$perPage);$i++): ?>
        <a href="?page=<?= $i ?>" style="padding:8px 12px;border-radius:6px;background:<?= $i===$page?'#8b5cf6':'#1e293b' ?>;color:#e2e8f0;text-decoration:none;font-size:13px"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
