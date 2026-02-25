<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-membership-plan.php';
$plans = \MembershipPlan::getAll('active');
ob_start();
?>
<style>
.mb-wrap{max-width:600px;margin:0 auto;padding:24px 20px}
.mb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.mb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.mb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.btn-mb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
</style>
<div class="mb-wrap">
    <div class="mb-header"><h1>➕ Add Member</h1><a href="/admin/membership/members" class="btn-secondary">← Back</a></div>
    <form method="post" action="/admin/membership/members/store">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="mb-card">
            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Name</label><input type="text" name="name"></div>
            <div class="form-group"><label>Plan *</label><select name="plan_id" required><option value="">— Select plan —</option><?php foreach ($plans as $p): ?><option value="<?= $p['id'] ?>"><?= h($p['name']) ?> ($<?= number_format((float)$p['price'], 2) ?>/<?= $p['billing_period'] ?>)</option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/membership/members" class="btn-secondary">Cancel</a><button type="submit" class="btn-mb">➕ Add Member</button></div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = 'Add Member'; require CMS_APP . '/views/admin/layouts/topbar.php';
