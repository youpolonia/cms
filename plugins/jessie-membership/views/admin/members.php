<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-membership-member.php';
require_once $pluginDir . '/includes/class-membership-plan.php';
$filters = ['status' => $_GET['status'] ?? '', 'plan_id' => $_GET['plan_id'] ?? '', 'search' => $_GET['q'] ?? ''];
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \MembershipMember::getAll($filters, $page);
$plans = \MembershipPlan::getAll();
ob_start();
?>
<style>
.mb-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.mb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.mb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-mb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.mb-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.mb-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.mb-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.mb-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-trial{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-expired{background:rgba(239,68,68,.15);color:#fca5a5}
.status-cancelled{background:rgba(107,114,128,.15);color:#9ca3af}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
</style>
<div class="mb-wrap">
    <div class="mb-header"><h1>👥 Members</h1><div style="display:flex;gap:10px"><a href="/admin/membership" class="btn-secondary">← Dashboard</a><a href="/admin/membership/members/add" class="btn-mb">➕ Add</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All</option><?php foreach (['active','trial','expired','cancelled','paused'] as $s): ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?plan_id='+this.value"><option value="">All Plans</option><?php foreach ($plans as $p): ?><option value="<?= $p['id'] ?>" <?= $filters['plan_id']==(string)$p['id']?'selected':'' ?>><?= h($p['name']) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($filters['search']) ?>" onchange="location.href='?q='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> members</span>
    </div>
    <table class="mb-table"><thead><tr><th>Member</th><th>Plan</th><th>Status</th><th>Expires</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($result['members'] as $m): ?>
        <tr>
            <td><strong><?= h($m['name'] ?: $m['email']) ?></strong><br><span style="font-size:.75rem;color:var(--muted)"><?= h($m['email']) ?></span></td>
            <td><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= h($m['plan_color'] ?? '#6366f1') ?>;margin-right:4px"></span><?= h($m['plan_name'] ?? '—') ?><br><span style="font-size:.72rem;color:var(--muted)">$<?= number_format((float)($m['plan_price'] ?? 0), 2) ?></span></td>
            <td><span class="status-badge status-<?= h($m['status']) ?>"><?= h($m['status']) ?></span></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= $m['expires_at'] ? date('M j, Y', strtotime($m['expires_at'])) : 'Lifetime' ?></td>
            <td>
                <?php if (in_array($m['status'], ['active','trial'])): ?><button onclick="if(confirm('Cancel?'))fetch('/admin/membership/members/<?= $m['id'] ?>/cancel',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'csrf_token=<?= h(csrf_token()) ?>',credentials:'same-origin'}).then(function(){location.reload()})" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">Cancel</button><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['members'])): ?><tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No members found.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Members'; require CMS_APP . '/views/admin/layouts/topbar.php';
