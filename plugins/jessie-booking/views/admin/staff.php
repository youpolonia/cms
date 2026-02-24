<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-staff.php';
$staff = \BookingStaff::getAll();
ob_start();
?>
<style>
.bk-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.staff-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.staff-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;text-align:center}
.staff-card .avatar{width:64px;height:64px;border-radius:50%;background:#334155;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem}
.staff-card h4{margin:0 0 4px;color:var(--text,#e2e8f0)}
.staff-card .email{font-size:.78rem;color:var(--muted,#94a3b8)}
.staff-card .services{font-size:.75rem;color:#a5b4fc;margin-top:8px}
</style>
<div class="bk-wrap">
    <div class="bk-header"><h1>👤 Staff</h1><div style="display:flex;gap:10px"><a href="/admin/booking" class="btn-secondary">← Dashboard</a><a href="/admin/booking/staff/create" class="btn-bk">➕ Add Staff</a></div></div>
    <?php if (empty($staff)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted,#94a3b8)"><p>No staff members yet.</p><a href="/admin/booking/staff/create" class="btn-bk" style="margin-top:12px">➕ Add Staff Member</a></div>
    <?php else: ?>
    <div class="staff-grid">
        <?php foreach ($staff as $s): ?>
        <div class="staff-card">
            <div class="avatar"><?= $s['avatar'] ? '<img src="'.h($s['avatar']).'" style="width:64px;height:64px;border-radius:50%;object-fit:cover">' : '👤' ?></div>
            <h4><?= h($s['name']) ?> <span style="font-size:.72rem;padding:2px 6px;border-radius:4px;<?= $s['status']==='active'?'background:rgba(16,185,129,.15);color:#34d399':'background:rgba(239,68,68,.15);color:#fca5a5' ?>"><?= $s['status'] ?></span></h4>
            <div class="email"><?= h($s['email'] ?? '') ?></div>
            <div class="services"><?= count($s['services']) ?> services assigned</div>
            <a href="/admin/booking/staff/<?= (int)$s['id'] ?>/edit" style="color:#a5b4fc;font-size:.82rem;text-decoration:none;display:inline-block;margin-top:10px">✏️ Edit</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Staff'; require CMS_APP . '/views/admin/layouts/topbar.php';
