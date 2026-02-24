<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-service.php';
require_once $pluginDir . '/includes/class-booking-staff.php';
require_once $pluginDir . '/includes/class-booking-appointment.php';
require_once $pluginDir . '/includes/class-booking-calendar.php';
$stats = \BookingAppointment::getStats();
$upcoming = \BookingAppointment::getUpcoming(8);
$services = \BookingService::getAll('active');
ob_start();
?>
<style>
.bk-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.bk-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.bk-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.bk-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.bk-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.bk-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.bk-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.appt-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.appt-row:last-child{border-bottom:none}
.appt-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.appt-time{font-size:.78rem;color:var(--muted,#94a3b8);white-space:nowrap;min-width:80px}
.appt-name{font-weight:600;font-size:.85rem;color:var(--text,#e2e8f0)}
.appt-service{font-size:.75rem;color:var(--muted,#94a3b8)}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-confirmed{background:rgba(16,185,129,.15);color:#34d399}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
</style>
<div class="bk-wrap">
    <div class="bk-header">
        <h1>📅 Booking Dashboard</h1>
        <a href="/admin/booking/calendar" class="btn-bk">📅 Calendar View</a>
    </div>
    <div class="bk-stats">
        <div class="bk-stat"><div class="val" style="color:#6366f1"><?= $stats['today'] ?></div><div class="lbl">Today</div></div>
        <div class="bk-stat"><div class="val" style="color:#a5b4fc"><?= $stats['this_week'] ?></div><div class="lbl">This Week</div></div>
        <div class="bk-stat"><div class="val" style="color:#f59e0b"><?= $stats['pending'] ?></div><div class="lbl">Pending</div></div>
        <div class="bk-stat"><div class="val" style="color:#10b981">$<?= number_format($stats['revenue'], 0) ?></div><div class="lbl">Revenue</div></div>
        <div class="bk-stat"><div class="val" style="color:var(--text,#e2e8f0)"><?= $stats['total'] ?></div><div class="lbl">All Time</div></div>
        <?php if ($stats['no_shows'] > 0): ?><div class="bk-stat"><div class="val" style="color:#ef4444"><?= $stats['no_shows'] ?></div><div class="lbl">No Shows (30d)</div></div><?php endif; ?>
    </div>
    <div class="quick-links">
        <a href="/admin/booking/services" class="quick-link"><span class="icon">📋</span><div><div class="text">Services</div><div class="desc"><?= count($services) ?> active</div></div></a>
        <a href="/admin/booking/appointments" class="quick-link"><span class="icon">📅</span><div><div class="text">Appointments</div><div class="desc">View all bookings</div></div></a>
        <a href="/admin/booking/staff" class="quick-link"><span class="icon">👤</span><div><div class="text">Staff</div><div class="desc">Manage team</div></div></a>
        <a href="/admin/booking/settings" class="quick-link"><span class="icon">⚙️</span><div><div class="text">Settings</div><div class="desc">Hours & notifications</div></div></a>
    </div>
    <div class="bk-card">
        <h3>📅 Upcoming Appointments</h3>
        <?php if (empty($upcoming)): ?>
            <p style="color:var(--muted,#94a3b8);font-size:.85rem">No upcoming appointments.</p>
        <?php else: foreach ($upcoming as $a): ?>
            <div class="appt-row">
                <div class="appt-dot" style="background:<?= h($a['service_color'] ?? '#6366f1') ?>"></div>
                <div class="appt-time"><?= h(date('M j', strtotime($a['date']))) ?><br><?= h(date('g:i A', strtotime($a['start_time']))) ?></div>
                <div style="flex:1;min-width:0">
                    <div class="appt-name"><?= h($a['customer_name']) ?></div>
                    <div class="appt-service"><?= h($a['service_name']) ?><?= $a['staff_name'] ? ' — ' . h($a['staff_name']) : '' ?></div>
                </div>
                <span class="status-badge status-<?= h($a['status']) ?>"><?= h($a['status']) ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Booking Dashboard';
require CMS_APP . '/views/admin/layouts/topbar.php';
