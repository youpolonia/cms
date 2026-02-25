<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-calendar.php';
require_once $pluginDir . '/includes/class-booking-appointment.php';
$weekStart = $_GET['week'] ?? date('Y-m-d', strtotime('monday this week'));
$weekData = \BookingCalendar::getWeekData($weekStart);
$prevWeek = date('Y-m-d', strtotime($weekStart . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($weekStart . ' +7 days'));
ob_start();
?>
<style>
.bk-wrap{max-width:1200px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.cal-nav{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.cal-nav .week-label{font-size:1rem;font-weight:600;color:var(--text,#e2e8f0)}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:var(--border,#334155);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.cal-day-header{background:rgba(99,102,241,.1);padding:10px;text-align:center;font-size:.78rem;font-weight:700;color:var(--muted,#94a3b8);text-transform:uppercase}
.cal-day{background:var(--bg-card,#1e293b);padding:10px;min-height:120px;vertical-align:top}
.cal-day .date{font-size:.78rem;font-weight:700;color:var(--muted,#94a3b8);margin-bottom:8px}
.cal-day.today .date{color:#6366f1;background:rgba(99,102,241,.15);padding:2px 8px;border-radius:4px;display:inline-block}
.cal-appt{background:var(--bg,#0f172a);border-left:3px solid #6366f1;border-radius:4px;padding:6px 8px;margin-bottom:4px;font-size:.75rem;cursor:default}
.cal-appt .time{color:var(--muted,#94a3b8);font-size:.7rem}
.cal-appt .name{color:var(--text,#e2e8f0);font-weight:600}
.cal-appt .service{color:var(--muted,#94a3b8)}
</style>
<div class="bk-wrap">
    <div class="bk-header"><h1>📅 Calendar</h1><div style="display:flex;gap:10px"><a href="/admin/booking" class="btn-secondary">← Dashboard</a><a href="/admin/booking/appointments" class="btn-secondary">📋 List View</a></div></div>
    <div class="cal-nav">
        <a href="?week=<?= h($prevWeek) ?>" class="btn-secondary">← Prev</a>
        <span class="week-label"><?= date('M j', strtotime($weekStart)) ?> – <?= date('M j, Y', strtotime($weekStart . ' +6 days')) ?></span>
        <a href="?week=<?= h($nextWeek) ?>" class="btn-secondary">Next →</a>
        <a href="?week=<?= date('Y-m-d', strtotime('monday this week')) ?>" class="btn-secondary">Today</a>
    </div>
    <div class="cal-grid">
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?><div class="cal-day-header"><?= $d ?></div><?php endforeach; ?>
        <?php foreach ($weekData as $date => $appointments):
            $isToday = $date === date('Y-m-d');
        ?>
        <div class="cal-day <?= $isToday ? 'today' : '' ?>">
            <div class="date"><?= date('j', strtotime($date)) ?></div>
            <?php foreach ($appointments as $a): ?>
            <div class="cal-appt" style="border-left-color:<?= h($a['service_color'] ?? '#6366f1') ?>">
                <div class="time"><?= h(date('g:i A', strtotime($a['start_time']))) ?></div>
                <div class="name"><?= h($a['customer_name']) ?></div>
                <div class="service"><?= h($a['service_name'] ?? '') ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Calendar'; require CMS_APP . '/views/admin/layouts/topbar.php';
