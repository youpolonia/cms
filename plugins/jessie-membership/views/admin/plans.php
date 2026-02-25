<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-membership-plan.php';
$plans = \MembershipPlan::getAll();
ob_start();
?>
<style>
.mb-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.mb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.mb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-mb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.plan-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.plan-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;text-align:center;transition:.2s;position:relative}
.plan-card:hover{border-color:#6366f1;transform:translateY(-2px)}
.plan-card .ribbon{position:absolute;top:12px;right:12px;padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase}
.plan-card h4{margin:0 0 6px;font-size:1.15rem;color:var(--text,#e2e8f0)}
.plan-card .price{font-size:2rem;font-weight:800;color:#6366f1;margin:12px 0 4px}
.plan-card .billing{font-size:.78rem;color:var(--muted,#94a3b8)}
.plan-card .features{text-align:left;margin:16px 0;font-size:.82rem;color:var(--muted,#94a3b8)}
.plan-card .features li{padding:4px 0;list-style:none}
.plan-card .features li::before{content:"✓ ";color:#10b981}
.plan-card .actions{margin-top:16px;display:flex;gap:8px;justify-content:center}
.plan-card .actions a{font-size:.78rem;color:#a5b4fc;text-decoration:none}
</style>
<div class="mb-wrap">
    <div class="mb-header"><h1>💎 Plans</h1><div style="display:flex;gap:10px"><a href="/admin/membership" class="btn-secondary">← Dashboard</a><a href="/admin/membership/plans/create" class="btn-mb">➕ New Plan</a></div></div>
    <?php if (empty($plans)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)"><p style="font-size:1.2rem">No plans yet.</p><a href="/admin/membership/plans/create" class="btn-mb" style="margin-top:12px">➕ Create First Plan</a></div>
    <?php else: ?>
    <div class="plan-grid">
        <?php foreach ($plans as $p): ?>
        <div class="plan-card" style="border-top:3px solid <?= h($p['color']) ?>">
            <span class="ribbon" style="background:<?= $p['status']==='active'?'rgba(16,185,129,.15)':'rgba(239,68,68,.15)' ?>;color:<?= $p['status']==='active'?'#34d399':'#fca5a5' ?>"><?= $p['status'] ?></span>
            <h4><?= h($p['name']) ?></h4>
            <div class="price"><?= (float)$p['price'] > 0 ? '$' . number_format((float)$p['price'], 2) : 'Free' ?></div>
            <div class="billing"><?= h(ucfirst($p['billing_period'])) ?><?= (int)$p['trial_days'] > 0 ? ' · ' . $p['trial_days'] . ' day trial' : '' ?></div>
            <?php if ($p['features']): ?><ul class="features"><?php foreach (array_slice($p['features'], 0, 5) as $f): ?><li><?= h($f) ?></li><?php endforeach; ?></ul><?php endif; ?>
            <div class="actions"><a href="/admin/membership/plans/<?= $p['id'] ?>/edit">✏️ Edit</a></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Membership Plans'; require CMS_APP . '/views/admin/layouts/topbar.php';
