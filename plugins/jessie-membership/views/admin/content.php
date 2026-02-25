<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-membership-access.php';
require_once $pluginDir . '/includes/class-membership-plan.php';
$rules = \MembershipAccess::getAllRules();
$plans = \MembershipPlan::getAll('active');
ob_start();
?>
<style>
.mb-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.mb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.mb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-mb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.mb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.mb-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.rule-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.rule-row:last-child{border-bottom:none}
.form-group{margin-bottom:12px}.form-group label{display:block;font-size:.78rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.form-group input,.form-group select{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.82rem}
.check-row{display:flex;align-items:center;gap:6px;margin-bottom:4px}
.check-row input{width:16px;height:16px;accent-color:#6366f1}
</style>
<div class="mb-wrap">
    <div class="mb-header"><h1>🔒 Content Rules</h1><a href="/admin/membership" class="btn-secondary">← Dashboard</a></div>
    <div class="mb-card">
        <h3>➕ Add Rule</h3>
        <form method="post" action="/admin/membership/content/store" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-group"><label>Content Type</label><select name="content_type"><option value="page">Page</option><option value="article">Article</option><option value="category">Category</option></select></div>
            <div class="form-group"><label>Content ID</label><input type="number" name="content_id" placeholder="Page/article ID" style="width:100px"></div>
            <div class="form-group"><label>Rule</label><select name="rule_type"><option value="require_any">Require any plan</option><option value="exclude">Exclude plans</option></select></div>
            <div class="form-group"><label>Plans</label><div><?php foreach ($plans as $p): ?><div class="check-row"><input type="checkbox" name="plan_ids[]" value="<?= $p['id'] ?>"><label style="font-size:.78rem"><?= h($p['name']) ?></label></div><?php endforeach; ?></div></div>
            <button type="submit" class="btn-mb" style="height:38px">➕ Add</button>
        </form>
    </div>
    <div class="mb-card">
        <h3>📋 Active Rules</h3>
        <?php if (empty($rules)): ?>
            <p style="color:var(--muted);font-size:.85rem">No content rules yet. All content is public.</p>
        <?php else: foreach ($rules as $r): ?>
            <div class="rule-row">
                <div style="flex:1">
                    <strong style="font-size:.85rem"><?= h(ucfirst($r['content_type'])) ?> #<?= (int)$r['content_id'] ?></strong>
                    <span style="font-size:.72rem;color:var(--muted)"> — <?= h($r['rule_type']) ?></span><br>
                    <span style="font-size:.75rem;color:#a5b4fc">Plans: <?= h($r['plan_names'] ?: 'none') ?></span>
                </div>
                <form method="post" action="/admin/membership/content/<?= $r['id'] ?>/delete" style="margin:0"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">🗑</button></form>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Content Rules'; require CMS_APP . '/views/admin/layouts/topbar.php';
