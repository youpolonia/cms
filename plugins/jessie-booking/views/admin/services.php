<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-service.php';
$services = \BookingService::getAll();
ob_start();
?>
<style>
.bk-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.svc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.svc-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;transition:all .2s}
.svc-card:hover{border-color:#6366f1;transform:translateY(-2px)}
.svc-card .dot{width:12px;height:12px;border-radius:50%;display:inline-block;margin-right:8px}
.svc-card h4{margin:0 0 6px;font-size:1rem;color:var(--text,#e2e8f0)}
.svc-card .meta{font-size:.78rem;color:var(--muted,#94a3b8);display:flex;gap:12px;margin-bottom:8px}
.svc-card .desc{font-size:.82rem;color:var(--muted,#94a3b8);margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.svc-card .price{font-weight:700;font-size:1.1rem;color:#10b981}
.svc-card .actions{display:flex;gap:8px;margin-top:12px}
.svc-card .actions a{font-size:.78rem;color:#a5b4fc;text-decoration:none}
.badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600}
.badge-active{background:rgba(16,185,129,.15);color:#34d399}
.badge-inactive{background:rgba(239,68,68,.15);color:#fca5a5}
</style>
<div class="bk-wrap">
    <div class="bk-header">
        <h1>📋 Services</h1>
        <div style="display:flex;gap:10px"><a href="/admin/booking" class="btn-secondary">← Dashboard</a><a href="/admin/booking/services/create" class="btn-bk">➕ Add Service</a></div>
    </div>
    <?php if (empty($services)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted,#94a3b8)"><p style="font-size:1.2rem">No services yet.</p><a href="/admin/booking/services/create" class="btn-bk" style="margin-top:12px">➕ Create Your First Service</a></div>
    <?php else: ?>
    <div class="svc-grid">
        <?php foreach ($services as $s): ?>
        <div class="svc-card">
            <h4><span class="dot" style="background:<?= h($s['color']) ?>"></span><?= h($s['name']) ?> <span class="badge badge-<?= $s['status'] ?>"><?= $s['status'] ?></span></h4>
            <div class="meta"><span>⏱ <?= (int)$s['duration_minutes'] ?>min</span><span>🔄 <?= (int)$s['buffer_minutes'] ?>min buffer</span><?php if ($s['category']): ?><span>📁 <?= h($s['category']) ?></span><?php endif; ?></div>
            <?php if ($s['description']): ?><div class="desc"><?= h($s['description']) ?></div><?php endif; ?>
            <div class="price"><?= (float)$s['price'] > 0 ? '$' . number_format((float)$s['price'], 2) : 'Free' ?></div>
            <div class="actions"><a href="/admin/booking/services/<?= (int)$s['id'] ?>/edit">✏️ Edit</a></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Services'; require CMS_APP . '/views/admin/layouts/topbar.php';
