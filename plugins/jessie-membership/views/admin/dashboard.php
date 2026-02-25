<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-membership-plan.php';
require_once $pluginDir . '/includes/class-membership-member.php';
$stats = \MembershipMember::getStats();
$plans = \MembershipPlan::getAll('active');
$recentResult = \MembershipMember::getAll([], 1, 8);
ob_start();
?>
<style>
.mb-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.mb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.mb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.mb-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.mb-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.mb-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.mb-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.mb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.mb-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-mb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.member-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.member-row:last-child{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-trial{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-expired{background:rgba(239,68,68,.15);color:#fca5a5}
.status-cancelled{background:rgba(107,114,128,.15);color:#9ca3af}
</style>
<div class="mb-wrap">
    <div class="mb-header"><h1>🔑 Membership Dashboard</h1><a href="/admin/membership/plans/create" class="btn-mb">➕ New Plan</a></div>
    <div class="mb-stats">
        <div class="mb-stat"><div class="val" style="color:#6366f1"><?= $stats['active'] ?></div><div class="lbl">Active</div></div>
        <div class="mb-stat"><div class="val" style="color:#a5b4fc"><?= $stats['trial'] ?></div><div class="lbl">Trial</div></div>
        <div class="mb-stat"><div class="val" style="color:#10b981">$<?= number_format($stats['revenue_30d'], 0) ?></div><div class="lbl">Revenue (30d)</div></div>
        <div class="mb-stat"><div class="val" style="color:#f59e0b"><?= $stats['expired'] ?></div><div class="lbl">Expired</div></div>
        <div class="mb-stat"><div class="val" style="color:var(--text,#e2e8f0)"><?= $stats['total'] ?></div><div class="lbl">All Time</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/membership/plans" class="quick-link"><span class="icon">💎</span><div><div class="text">Plans</div><div class="desc"><?= count($plans) ?> active</div></div></a>
        <a href="/admin/membership/members" class="quick-link"><span class="icon">👥</span><div><div class="text">Members</div><div class="desc">Manage members</div></div></a>
        <a href="/admin/membership/content" class="quick-link"><span class="icon">🔒</span><div><div class="text">Content Rules</div><div class="desc">Gate content</div></div></a>
        <a href="/admin/membership/settings" class="quick-link"><span class="icon">⚙️</span><div><div class="text">Settings</div><div class="desc">Configuration</div></div></a>
    </div>
    <div class="mb-card">
        <h3>👥 Recent Members</h3>
        <?php if (empty($recentResult['members'])): ?>
            <p style="color:var(--muted);font-size:.85rem">No members yet. <a href="/admin/membership/plans" style="color:#a5b4fc">Create a plan to get started →</a></p>
        <?php else: foreach ($recentResult['members'] as $m): ?>
            <div class="member-row">
                <div style="flex:1;min-width:0"><strong style="font-size:.85rem;color:var(--text)"><?= h($m['name'] ?: $m['email']) ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= h($m['email']) ?></span></div>
                <div style="font-size:.78rem;color:var(--muted)"><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= h($m['plan_color'] ?? '#6366f1') ?>;margin-right:4px"></span><?= h($m['plan_name'] ?? '?') ?></div>
                <span class="status-badge status-<?= h($m['status']) ?>"><?= h($m['status']) ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Membership Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
