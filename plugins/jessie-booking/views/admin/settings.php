<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-calendar.php';
$g = fn($k, $d = '') => h(\BookingCalendar::getSetting($k, $d));
$hours = json_decode(\BookingCalendar::getSetting('business_hours', '{}'), true) ?: [];
ob_start();
?>
<style>
.bk-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.bk-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.bk-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.hours-row{display:grid;grid-template-columns:80px 1fr 1fr;gap:10px;align-items:center;margin-bottom:8px;font-size:.82rem;color:var(--text,#e2e8f0)}
.hours-row input[type="time"]{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:6px 8px;border-radius:6px;font-size:.82rem}
.toggle-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.toggle-row input[type="checkbox"]{width:18px;height:18px;accent-color:#6366f1}
.toggle-row label{font-size:.82rem;color:var(--text,#e2e8f0);cursor:pointer}
</style>
<div class="bk-wrap">
    <div class="bk-header"><h1>⚙️ Booking Settings</h1><a href="/admin/booking" class="btn-secondary">← Dashboard</a></div>
    <form method="post" action="/admin/booking/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="bk-card">
            <h3>🏢 Business</h3>
            <div class="form-group"><label>Business Name</label><input type="text" name="business_name" value="<?= $g('business_name','My Business') ?>"></div>
            <div class="form-group"><label>Notification Email</label><input type="email" name="notification_email" value="<?= $g('notification_email') ?>" placeholder="admin@example.com"></div>
        </div>
        <div class="bk-card">
            <h3>🕐 Business Hours</h3>
            <?php foreach (['mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thu'=>'Thursday','fri'=>'Friday','sat'=>'Saturday','sun'=>'Sunday'] as $key => $label): $h = $hours[$key] ?? []; ?>
            <div class="hours-row">
                <span><?= $label ?></span>
                <input type="time" name="hours_<?= $key ?>_open" value="<?= h($h[0] ?? '') ?>" placeholder="Closed">
                <input type="time" name="hours_<?= $key ?>_close" value="<?= h($h[1] ?? '') ?>">
            </div>
            <?php endforeach; ?>
            <p style="font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px">Leave both empty = closed that day</p>
        </div>
        <div class="bk-card">
            <h3>📅 Scheduling</h3>
            <div class="form-row">
                <div class="form-group"><label>Slot Interval (minutes)</label><select name="slot_interval">
                    <?php foreach ([15,30,60] as $i): ?><option value="<?= $i ?>" <?= $g('slot_interval','30')==$i?'selected':'' ?>><?= $i ?> min</option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Min Advance (hours)</label><input type="number" name="min_advance_hours" value="<?= $g('min_advance_hours','2') ?>" min="0"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Max Advance (days)</label><input type="number" name="max_advance_days" value="<?= $g('max_advance_days','60') ?>" min="1"></div>
                <div class="form-group"><label>Reminder (hours before)</label><input type="number" name="reminder_hours" value="<?= $g('reminder_hours','24') ?>" min="0"></div>
            </div>
            <div class="toggle-row"><input type="checkbox" id="auto-confirm" name="auto_confirm" value="1" <?= $g('auto_confirm','0')==='1'?'checked':'' ?>><label for="auto-confirm">Auto-confirm bookings (no manual approval)</label></div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/booking" class="btn-secondary">Cancel</a><button type="submit" class="btn-bk">💾 Save Settings</button></div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = 'Booking Settings'; require CMS_APP . '/views/admin/layouts/topbar.php';
