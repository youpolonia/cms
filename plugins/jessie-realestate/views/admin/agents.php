<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-realestate-agent.php';
$agents = \RealEstateAgent::getAll();
ob_start();
?>
<style>
.re-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.re-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.re-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-re{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.re-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.re-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:12px}.form-group label{display:block;font-size:.78rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:8px;font-size:.82rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:60px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
@media(max-width:600px){.form-row,.form-row3{grid-template-columns:1fr}}
.agents-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px}
.agent-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;position:relative}
.agent-card .name{font-size:1rem;font-weight:700;margin-bottom:4px}
.agent-card .meta{font-size:.78rem;color:var(--muted,#94a3b8);margin-bottom:8px}
.agent-card .meta a{color:#a5b4fc}
.agent-card .bio{font-size:.82rem;color:var(--muted,#94a3b8);margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.agent-card .badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase}
.agent-card .badge-active{background:rgba(16,185,129,.15);color:#34d399}
.agent-card .badge-inactive{background:rgba(239,68,68,.15);color:#fca5a5}
.agent-actions{display:flex;gap:6px;margin-top:10px}
.agent-actions form{display:inline}
.agent-actions button{background:none;border:none;cursor:pointer;font-size:.78rem;padding:4px 10px;border-radius:4px}
.btn-edit{background:rgba(99,102,241,.1);color:#a5b4fc}
.btn-del{background:rgba(239,68,68,.1);color:#fca5a5}
</style>
<div class="re-wrap">
    <div class="re-header"><h1>👤 Agents</h1><a href="/admin/realestate" class="btn-secondary">← Dashboard</a></div>

    <div class="re-card">
        <h3>➕ Add Agent</h3>
        <form method="post" action="/admin/realestate/agents/store">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-row">
                <div class="form-group"><label>Name *</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email"></div>
            </div>
            <div class="form-row3">
                <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
                <div class="form-group"><label>License #</label><input type="text" name="license_number"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            </div>
            <div class="form-group"><label>Photo URL</label><input type="text" name="photo"></div>
            <div class="form-group"><label>Specialties</label><input type="text" name="specialties" placeholder="Residential, Commercial, Luxury"></div>
            <div class="form-group"><label>Bio</label><textarea name="bio" rows="2"></textarea></div>
            <button type="submit" class="btn-re" style="margin-top:8px">➕ Add Agent</button>
        </form>
    </div>

    <div class="agents-grid">
        <?php foreach ($agents as $ag): ?>
        <div class="agent-card">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
                <?php if ($ag['photo']): ?><img src="<?= h($ag['photo']) ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border)"><?php else: ?><div style="width:48px;height:48px;border-radius:50%;background:rgba(99,102,241,.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem">👤</div><?php endif; ?>
                <div>
                    <div class="name"><?= h($ag['name']) ?> <span class="badge badge-<?= h($ag['status']) ?>"><?= h($ag['status']) ?></span></div>
                    <div class="meta"><?= h($ag['email'] ?: '—') ?> · <?= h($ag['phone'] ?: '—') ?></div>
                </div>
            </div>
            <?php if ($ag['license_number']): ?><div style="font-size:.72rem;color:var(--muted);margin-bottom:4px">License: <?= h($ag['license_number']) ?></div><?php endif; ?>
            <?php if ($ag['bio']): ?><div class="bio"><?= h($ag['bio']) ?></div><?php endif; ?>
            <div style="font-size:.78rem;color:#a5b4fc"><?= (int)($ag['property_count'] ?? 0) ?> active properties</div>
            <div class="agent-actions">
                <form method="post" action="/admin/realestate/agents/<?= $ag['id'] ?>/update"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="status" value="<?= $ag['status'] === 'active' ? 'inactive' : 'active' ?>"><button type="submit" class="btn-edit"><?= $ag['status'] === 'active' ? '⏸ Deactivate' : '▶ Activate' ?></button></form>
                <form method="post" action="/admin/realestate/agents/<?= $ag['id'] ?>/delete" onsubmit="return confirm('Delete this agent?')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" class="btn-del">🗑️ Delete</button></form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($agents)): ?><div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--muted)">No agents yet. Add one above.</div><?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Agents'; require CMS_APP . '/views/admin/layouts/topbar.php';
