<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-event-manager.php';
require_once $pluginDir . '/includes/class-event-order.php';
$stats = \EventManager::getStats();
$sym = \EventManager::getSetting('currency_symbol', '£');
$upcoming = \EventManager::getAll(['status' => 'upcoming'], 1, 5);
ob_start();
?>
<style>
.ew{max-width:1100px;margin:0 auto;padding:24px 20px}
.eh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.eh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.es{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.es .s{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.es .v{font-size:1.8rem;font-weight:800;line-height:1}
.es .l{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-p{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.ql{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px}
.ql a{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:.2s;display:flex;align-items:center;gap:12px}
.ql a:hover{border-color:#6366f1;transform:translateY(-2px)}
.ql .i{font-size:1.5rem}.ql .t{font-weight:600;font-size:.9rem}.ql .d{font-size:.75rem;color:var(--muted,#94a3b8)}
.ev-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem}
.ev-row:last-child{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.st-upcoming{background:rgba(59,130,246,.15);color:#60a5fa}
.st-ongoing{background:rgba(16,185,129,.15);color:#34d399}
.st-completed{background:rgba(100,116,139,.15);color:#94a3b8}
.st-cancelled{background:rgba(239,68,68,.15);color:#fca5a5}
</style>
<div class="ew">
    <div class="eh"><h1>🎫 Events Dashboard</h1><div style="display:flex;gap:10px"><a href="/admin/events/create" class="btn-p">➕ New Event</a><a href="/admin/events/orders" class="btn-s">📋 Orders</a></div></div>
    <div class="es">
        <div class="s"><div class="v" style="color:#10b981"><?= $sym ?><?= number_format($stats['revenue_today'], 2) ?></div><div class="l">Today Revenue</div></div>
        <div class="s"><div class="v" style="color:#6366f1"><?= $stats['orders_today'] ?></div><div class="l">Today Orders</div></div>
        <div class="s"><div class="v" style="color:#f59e0b"><?= $stats['events_upcoming'] ?></div><div class="l">Upcoming</div></div>
        <div class="s"><div class="v" style="color:#a855f7"><?= $stats['tickets_sold'] ?></div><div class="l">Tickets Sold</div></div>
        <div class="s"><div class="v" style="color:var(--text)"><?= $stats['events_total'] ?></div><div class="l">Total Events</div></div>
        <div class="s"><div class="v" style="color:#10b981"><?= $sym ?><?= number_format($stats['revenue_total'], 0) ?></div><div class="l">Total Revenue</div></div>
    </div>
    <div class="ql">
        <a href="/admin/events/list"><span class="i">🎪</span><div><div class="t">Events</div><div class="d"><?= $stats['events_total'] ?> total</div></div></a>
        <a href="/admin/events/orders"><span class="i">📋</span><div><div class="t">Orders</div><div class="d"><?= $stats['orders_total'] ?> total</div></div></a>
        <a href="/admin/events/orders"><span class="i">✅</span><div><div class="t">Check-in</div><div class="d"><?= $stats['checked_in'] ?> checked in</div></div></a>
        <a href="/admin/events/settings"><span class="i">⚙️</span><div><div class="t">Settings</div><div class="d">Currency, defaults</div></div></a>
    </div>
    <?php if (!empty($upcoming['events'])): ?>
    <div class="card">
        <h3>📅 Upcoming Events</h3>
        <?php foreach ($upcoming['events'] as $ev): ?>
        <div class="ev-row">
            <a href="/admin/events/<?= $ev['id'] ?>/edit" style="color:#a5b4fc;font-weight:700;text-decoration:none"><?= h($ev['title']) ?></a>
            <span class="status-badge st-<?= h($ev['status']) ?>"><?= h($ev['status']) ?></span>
            <span style="color:var(--muted);font-size:.78rem"><?= date('M j, Y H:i', strtotime($ev['start_date'])) ?></span>
            <span style="color:var(--muted);font-size:.78rem"><?= h($ev['venue_name'] ?: $ev['city']) ?></span>
            <?php if ($ev['is_featured']): ?><span style="color:#f59e0b;font-size:.7rem">⭐</span><?php endif; ?>
            <a href="/admin/events/<?= $ev['id'] ?>/tickets" style="margin-left:auto;color:#a5b4fc;font-size:.78rem;text-decoration:none">🎫 Tickets</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Events Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
