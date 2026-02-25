<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-job-company.php';
$companies = \JobCompany::getAll();
ob_start();
?>
<style>
.jb-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.jb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.jb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-jb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.jb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.jb-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:60px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
@media(max-width:600px){.form-row,.form-row3{grid-template-columns:1fr}}
.company-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px}
.company-item{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;display:flex;align-items:center;gap:14px}
.company-logo{width:48px;height:48px;border-radius:10px;background:rgba(99,102,241,.1);display:flex;align-items:center;justify-content:center;font-size:1.5rem;overflow:hidden;flex-shrink:0}
.company-logo img{width:100%;height:100%;object-fit:cover}
.company-info{flex:1;min-width:0}
.company-info h4{margin:0;font-size:.9rem;color:var(--text,#e2e8f0)}
.company-info .meta{font-size:.72rem;color:var(--muted,#94a3b8)}
.company-actions{display:flex;gap:6px}
.company-actions button{background:none;border:none;cursor:pointer;font-size:.8rem;padding:4px 6px;border-radius:4px}
</style>
<div class="jb-wrap">
    <div class="jb-header"><h1>🏢 Companies</h1><a href="/admin/jobs" class="btn-secondary">← Dashboard</a></div>

    <!-- Add Company Form -->
    <div class="jb-card">
        <h3>➕ Add Company</h3>
        <form method="post" action="/admin/jobs/companies/store">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-row">
                <div class="form-group"><label>Company Name *</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Industry</label><input type="text" name="industry" placeholder="e.g. Technology, Finance"></div>
            </div>
            <div class="form-row3">
                <div class="form-group"><label>Website</label><input type="url" name="website" placeholder="https://"></div>
                <div class="form-group"><label>Size</label><select name="size"><option value="">—</option><option value="1-10">1-10</option><option value="11-50">11-50</option><option value="51-200">51-200</option><option value="201-500">201-500</option><option value="501-1000">501-1000</option><option value="1000+">1000+</option></select></div>
                <div class="form-group"><label>Location</label><input type="text" name="location" placeholder="e.g. London, UK"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Logo URL</label><input type="url" name="logo" placeholder="https://"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="active">Active</option><option value="hidden">Hidden</option></select></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
            <button type="submit" class="btn-jb">➕ Add Company</button>
        </form>
    </div>

    <!-- Companies List -->
    <div class="company-grid">
        <?php foreach ($companies as $c): ?>
        <div class="company-item">
            <div class="company-logo">
                <?php if ($c['logo']): ?><img src="<?= h($c['logo']) ?>" alt="<?= h($c['name']) ?>"><?php else: ?>🏢<?php endif; ?>
            </div>
            <div class="company-info">
                <h4><?= h($c['name']) ?></h4>
                <div class="meta">
                    <?= h($c['industry'] ?: '') ?><?= $c['industry']&&$c['location']?' · ':'' ?><?= h($c['location'] ?: '') ?>
                    <?php if (!empty($c['job_count'])): ?> · <?= $c['job_count'] ?> jobs<?php endif; ?>
                    <?php if ($c['website']): ?> · <a href="<?= h($c['website']) ?>" target="_blank" style="color:#a5b4fc">🌐</a><?php endif; ?>
                </div>
            </div>
            <div class="company-actions">
                <button onclick="if(confirm('Delete?')){var f=document.createElement('form');f.method='POST';f.action='/admin/jobs/companies/<?= $c['id'] ?>/delete';var t=document.createElement('input');t.type='hidden';t.name='csrf_token';t.value='<?= h(csrf_token()) ?>';f.appendChild(t);document.body.appendChild(f);f.submit();}" style="color:#fca5a5">🗑️</button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($companies)): ?>
        <div style="text-align:center;padding:40px;color:var(--muted);grid-column:1/-1">No companies yet. Add one above.</div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Companies'; require CMS_APP . '/views/admin/layouts/topbar.php';
