<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-affiliate-program.php';
require_once $pluginDir . '/includes/class-affiliate.php';
$stats = \AffiliateProgram::getStats();
$recentConversions = \Affiliate::getConversions([], 1, 5);
$pendingAffiliates = \Affiliate::getAll(['status' => 'pending'], 1, 5);
ob_start();
?>
<style>
.aff-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.aff-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.aff-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.aff-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:24px}
.aff-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.aff-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.aff-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.aff-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.aff-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-aff{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#7c3aed;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.conv-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.conv-row:last-child{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-approved{background:rgba(16,185,129,.15);color:#34d399}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-paid{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
.status-suspended{background:rgba(239,68,68,.15);color:#fca5a5}
.btn-sm{padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem;border:none}
</style>
<div class="aff-wrap">
    <div class="aff-header"><h1>🤝 Affiliate Dashboard</h1><a href="/admin/affiliate/programs/create" class="btn-aff">➕ New Program</a></div>
    <div class="aff-stats">
        <div class="aff-stat"><div class="val" style="color:#7c3aed"><?= $stats['programs'] ?></div><div class="lbl">Programs</div></div>
        <div class="aff-stat"><div class="val" style="color:#10b981"><?= $stats['active_affiliates'] ?></div><div class="lbl">Active Affiliates</div></div>
        <div class="aff-stat"><div class="val" style="color:#f59e0b"><?= $stats['pending_affiliates'] ?></div><div class="lbl">Pending</div></div>
        <div class="aff-stat"><div class="val" style="color:#a5b4fc"><?= number_format($stats['total_clicks']) ?></div><div class="lbl">Total Clicks</div></div>
        <div class="aff-stat"><div class="val" style="color:#34d399"><?= number_format($stats['total_conversions']) ?></div><div class="lbl">Conversions</div></div>
        <div class="aff-stat"><div class="val" style="color:#fbbf24">$<?= number_format($stats['total_earnings'], 2) ?></div><div class="lbl">Total Earnings</div></div>
        <div class="aff-stat"><div class="val" style="color:#ef4444">$<?= number_format($stats['pending_payouts'], 2) ?></div><div class="lbl">Pending Payouts</div></div>
        <div class="aff-stat"><div class="val" style="color:#818cf8">$<?= number_format($stats['total_paid'], 2) ?></div><div class="lbl">Total Paid</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/affiliate/programs" class="quick-link"><span class="icon">📋</span><div><div class="text">Programs</div><div class="desc"><?= $stats['programs'] ?> active</div></div></a>
        <a href="/admin/affiliate/affiliates" class="quick-link"><span class="icon">👥</span><div><div class="text">Affiliates</div><div class="desc"><?= $stats['affiliates'] ?> total</div></div></a>
        <a href="/admin/affiliate/conversions" class="quick-link"><span class="icon">🎯</span><div><div class="text">Conversions</div><div class="desc"><?= $stats['pending_conversions'] ?> pending</div></div></a>
        <a href="/admin/affiliate/payouts" class="quick-link"><span class="icon">💰</span><div><div class="text">Payouts</div><div class="desc">$<?= number_format($stats['pending_payouts'], 2) ?> pending</div></div></a>
    </div>

    <?php if (!empty($pendingAffiliates['affiliates'])): ?>
    <div class="aff-card">
        <h3>⏳ Pending Affiliate Applications</h3>
        <?php foreach ($pendingAffiliates['affiliates'] as $a): ?>
        <div class="conv-row">
            <div style="flex:1">
                <strong style="font-size:.85rem;color:var(--text)"><?= h($a['name']) ?></strong>
                <br><span style="font-size:.75rem;color:var(--muted)"><?= h($a['email']) ?> — <?= h($a['program_name'] ?? '—') ?></span>
            </div>
            <div style="display:flex;gap:6px">
                <form method="POST" action="/admin/affiliate/affiliates/<?= $a['id'] ?>/approve" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(16,185,129,.15);color:#34d399">✓ Approve</button></form>
                <form method="POST" action="/admin/affiliate/affiliates/<?= $a['id'] ?>/suspend" style="display:inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button type="submit" class="btn-sm" style="background:rgba(239,68,68,.1);color:#fca5a5">✕ Reject</button></form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($recentConversions['conversions'])): ?>
    <div class="aff-card">
        <h3>🎯 Recent Conversions</h3>
        <?php foreach ($recentConversions['conversions'] as $c): ?>
        <div class="conv-row">
            <div style="flex:1">
                <strong style="font-size:.85rem;color:var(--text)"><?= h($c['affiliate_name'] ?? 'Unknown') ?></strong>
                <span style="font-size:.72rem;color:var(--muted);margin-left:8px"><?= h($c['program_name'] ?? '') ?></span>
                <br><span style="font-size:.75rem;color:var(--muted)">Order: <?= h($c['order_id'] ?: '—') ?> · $<?= number_format((float)$c['order_total'], 2) ?> → Commission: $<?= number_format((float)$c['commission'], 2) ?></span>
            </div>
            <span class="status-badge status-<?= h($c['status']) ?>"><?= h($c['status']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Affiliate Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
