<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-affiliate-program.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \AffiliateProgram::getAll($_GET, $page);
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
.status-inactive{background:rgba(245,158,11,.15);color:#fbbf24}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
</style>
<div class="aff-wrap">
    <div class="aff-header"><h1>📋 Affiliate Programs</h1><div style="display:flex;gap:10px"><a href="/admin/affiliate" class="btn-secondary">← Dashboard</a><a href="/admin/affiliate/programs/create" class="btn-aff">➕ Add Program</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All</option><?php foreach (['active','inactive'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> programs</span>
    </div>
    <table class="aff-table"><thead><tr><th>Program</th><th>Commission</th><th>Cookie</th><th>Min Payout</th><th>Affiliates</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($result['programs'] as $p): ?>
        <tr>
            <td><strong><?= h($p['name']) ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= h($p['slug']) ?></span></td>
            <td style="font-size:.82rem"><?= $p['commission_type'] === 'percentage' ? h($p['commission_value']) . '%' : '$' . number_format((float)$p['commission_value'], 2) ?><br><span style="font-size:.7rem;color:var(--muted)"><?= h($p['commission_type']) ?></span></td>
            <td style="font-size:.82rem"><?= (int)$p['cookie_days'] ?> days</td>
            <td style="font-size:.82rem">$<?= number_format((float)$p['min_payout'], 2) ?></td>
            <td style="font-size:.82rem"><?= (int)$p['affiliate_count'] ?></td>
            <td><span class="status-badge status-<?= h($p['status']) ?>"><?= h($p['status']) ?></span></td>
            <td><a href="/admin/affiliate/programs/<?= $p['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️ Edit</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['programs'])): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No programs found. <a href="/admin/affiliate/programs/create" style="color:#a5b4fc">Create one</a></td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Affiliate Programs'; require CMS_APP . '/views/admin/layouts/topbar.php';
