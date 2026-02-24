<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-service.php';
require_once $pluginDir . '/includes/class-booking-appointment.php';
require_once $pluginDir . '/includes/class-booking-calendar.php';
$filters = ['status' => $_GET['status'] ?? '', 'date' => $_GET['date'] ?? '', 'search' => $_GET['q'] ?? ''];
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \BookingAppointment::getAll($filters, $page, 20);
ob_start();
?>
<style>
.bk-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.bk-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.bk-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.bk-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.bk-table tr:last-child td{border-bottom:none}
.bk-table tr:hover td{background:rgba(99,102,241,.04)}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-confirmed{background:rgba(16,185,129,.15);color:#34d399}
.status-completed{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-cancelled{background:rgba(107,114,128,.15);color:#9ca3af}
.status-no_show{background:rgba(239,68,68,.15);color:#fca5a5}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
</style>
<div class="bk-wrap">
    <div class="bk-header"><h1>📋 Appointments</h1><a href="/admin/booking" class="btn-secondary">← Dashboard</a></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Statuses</option>
            <?php foreach (['pending','confirmed','completed','cancelled','no_show'] as $s): ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?>
        </select>
        <input type="date" value="<?= h($filters['date']) ?>" onchange="location.href='?date='+this.value">
        <input type="text" placeholder="Search..." value="<?= h($filters['search']) ?>" onchange="location.href='?q='+encodeURIComponent(this.value)">
    </div>
    <?php if (empty($result['appointments'])): ?>
        <div style="text-align:center;padding:60px;color:var(--muted,#94a3b8)"><p>No appointments found.</p></div>
    <?php else: ?>
    <table class="bk-table"><thead><tr><th>Date/Time</th><th>Customer</th><th>Service</th><th>Staff</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($result['appointments'] as $a): ?>
        <tr>
            <td style="white-space:nowrap"><?= h(date('M j', strtotime($a['date']))) ?><br><span style="font-size:.78rem;color:var(--muted)"><?= h(date('g:i A', strtotime($a['start_time']))) ?></span></td>
            <td><strong><?= h($a['customer_name']) ?></strong><br><span style="font-size:.75rem;color:var(--muted)"><?= h($a['customer_email']) ?></span></td>
            <td><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= h($a['service_color'] ?? '#6366f1') ?>;margin-right:6px"></span><?= h($a['service_name'] ?? '?') ?></td>
            <td style="font-size:.82rem"><?= h($a['staff_name'] ?? '—') ?></td>
            <td><span class="status-badge status-<?= h($a['status']) ?>"><?= h(str_replace('_',' ',$a['status'])) ?></span></td>
            <td>
                <?php if ($a['status'] === 'pending'): ?><button onclick="updateStatus(<?= $a['id'] ?>,'confirmed')" style="background:rgba(16,185,129,.15);color:#34d399;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✓ Confirm</button><?php endif; ?>
                <?php if (in_array($a['status'],['pending','confirmed'])): ?><button onclick="updateStatus(<?= $a['id'] ?>,'cancelled')" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✕ Cancel</button><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
</div>
<script>
function updateStatus(id,status){
    fetch('/api/booking/appointments/'+id,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({status:status}),credentials:'same-origin'})
    .then(function(){location.reload();});
}
</script>
<?php $content = ob_get_clean(); $title = 'Appointments'; require CMS_APP . '/views/admin/layouts/topbar.php';
