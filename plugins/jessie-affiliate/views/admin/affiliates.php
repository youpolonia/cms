<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-affiliate-program.php';
require_once $pluginDir . '/includes/class-affiliate.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \Affiliate::getAll($_GET, $page);
$programs = \AffiliateProgram::getActive();
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
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-suspended{background:rgba(239,68,68,.15);color:#fca5a5}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.btn-sm{padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem;border:none;display:inline-block}
.code-box{font-family:monospace;background:rgba(124,58,237,.1);color:#c4b5fd;padding:2px 8px;border-radius:4px;font-size:.78rem;letter-spacing:.5px}
</style>
<div class="aff-wrap">
    <div class="aff-header"><h1>👥 Affiliates</h1><a href="/admin/affiliate" class="btn-secondary">← Dashboard</a></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Statuses</option><?php foreach (['active','pending','suspended'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?program_id='+this.value"><option value="">All Programs</option><?php foreach ($programs as $p): ?><option value="<?= $p['id'] ?>" <?= ($_GET['program_id']??'')==(string)$p['id']?'selected':'' ?>><?= h($p['name']) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search name/email..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> affiliates</span>
    </div>
    <table class="aff-table"><thead><tr><th>Affiliate</th><th>Program</th><th>Code</th><th>Clicks</th><th>Conv.</th><th>Earnings</th><th>Pending</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($result['affiliates'] as $a): ?>
        <tr>
            <td><strong><?= h($a['name']) ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= h($a['email']) ?></span><?php if ($a['website']): ?><br><span style="font-size:.7rem;color:var(--muted)"><?= h($a['website']) ?></span><?php endif; ?></td>
            <td style="font-size:.82rem"><?= h($a['program_name'] ?? '—') ?></td>
            <td><span class="code-box"><?= h($a['referral_code']) ?></span></td>
            <td style="font-size:.82rem;text-align:center"><?= number_format((int)$a['total_clicks']) ?></td>
            <td style="font-size:.82rem;text-align:center"><?= number_format((int)$a['total_conversions']) ?></td>
            <td style="font-size:.82rem">$<?= number_format((float)$a['total_earnings'], 2) ?></td>
            <td style="font-size:.82rem">$<?= number_format((float)$a['pending_payout'], 2) ?></td>
            <td><span class="status-badge status-<?= h($a['status']) ?>"><?= h($a['status']) ?></span></td>
            <td style="white-space:nowrap">
                <?php if ($a['status'] === 'pending'): ?>
                <form method="POST" action="/admin/affiliate/affiliates/<?= $a['id'] ?>/approve" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(16,185,129,.15);color:#34d399" title="Approve">✓</button></form>
                <?php endif; ?>
                <?php if ($a['status'] !== 'suspended'): ?>
                <form method="POST" action="/admin/affiliate/affiliates/<?= $a['id'] ?>/suspend" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(245,158,11,.15);color:#fbbf24" title="Suspend">⏸</button></form>
                <?php endif; ?>
                <?php if ($a['status'] === 'suspended'): ?>
                <form method="POST" action="/admin/affiliate/affiliates/<?= $a['id'] ?>/approve" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(16,185,129,.15);color:#34d399" title="Reactivate">▶</button></form>
                <?php endif; ?>
                <form method="POST" action="/admin/affiliate/affiliates/<?= $a['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this affiliate?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(239,68,68,.1);color:#fca5a5" title="Delete">🗑</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['affiliates'])): ?><tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">No affiliates found.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Affiliates'; require CMS_APP . '/views/admin/layouts/topbar.php';
