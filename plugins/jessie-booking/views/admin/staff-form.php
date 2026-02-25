<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-booking-service.php';
$allServices = \BookingService::getAll('active');
$isEdit = isset($staffMember) && $staffMember !== null;
$assignedServices = $isEdit ? ($staffMember['services'] ?? []) : [];
if (is_string($assignedServices)) $assignedServices = json_decode($assignedServices, true) ?: [];
$v = fn($k, $d = '') => h($isEdit ? ($staffMember[$k] ?? $d) : $d);
ob_start();
?>
<style>
.bk-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.bk-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.bk-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.bk-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.bk-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.check-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.check-row input{width:18px;height:18px;accent-color:#6366f1}
.check-row label{font-size:.82rem;color:var(--text,#e2e8f0);cursor:pointer}
</style>
<div class="bk-wrap">
    <div class="bk-header"><h1><?= $isEdit ? '✏️ Edit Staff' : '➕ Add Staff' ?></h1><a href="/admin/booking/staff" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/booking/staff/' . (int)$staffMember['id'] . '/update' : '/admin/booking/staff/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="bk-card">
            <h3>👤 Information</h3>
            <div class="form-row">
                <div class="form-group"><label>Name *</label><input type="text" name="name" value="<?= $v('name') ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= $v('email') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= $v('phone') ?>"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($isEdit && ($staffMember['status']??'')==='active')?'selected':'' ?>>Active</option><option value="inactive" <?= ($isEdit && ($staffMember['status']??'')==='inactive')?'selected':'' ?>>Inactive</option></select></div>
            </div>
            <div class="form-group"><label>Bio</label><textarea name="bio" rows="3"><?= h($isEdit ? ($staffMember['bio'] ?? '') : '') ?></textarea></div>
        </div>
        <div class="bk-card">
            <h3>📋 Assigned Services</h3>
            <?php foreach ($allServices as $svc): ?>
            <div class="check-row">
                <input type="checkbox" name="services[]" value="<?= (int)$svc['id'] ?>" id="svc-<?= $svc['id'] ?>" <?= in_array((int)$svc['id'], array_map('intval', $assignedServices)) ? 'checked' : '' ?>>
                <label for="svc-<?= $svc['id'] ?>"><?= h($svc['name']) ?> (<?= (int)$svc['duration_minutes'] ?>min)</label>
            </div>
            <?php endforeach; ?>
            <?php if (empty($allServices)): ?><p style="color:var(--muted);font-size:.85rem">No services yet. <a href="/admin/booking/services/create" style="color:#a5b4fc">Create one first →</a></p><?php endif; ?>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/booking/staff" class="btn-secondary">Cancel</a><button type="submit" class="btn-bk"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Staff' : 'Add Staff'; require CMS_APP . '/views/admin/layouts/topbar.php';
