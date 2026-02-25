<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-portfolio-category.php';
$categories = \PortfolioCategory::getAll('active');
$isEdit = isset($project) && $project !== null;
$v = fn($k, $d = '') => h($isEdit ? ($project[$k] ?? $d) : $d);
ob_start();
?>
<style>
.pf-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.pf-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.pf-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.pf-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.pf-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:100px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
@media(max-width:600px){.form-row,.form-row3{grid-template-columns:1fr}}
.btn-pf{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(124,58,237,.15);color:#c4b5fd;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(124,58,237,.3);cursor:pointer}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid rgba(239,68,68,.3);cursor:pointer}
.help-text{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px}
</style>
<div class="pf-wrap">
    <div class="pf-header"><h1><?= $isEdit ? '✏️ Edit Project' : '➕ New Project' ?></h1><a href="/admin/portfolio/projects" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/portfolio/projects/' . (int)$project['id'] . '/update' : '/admin/portfolio/projects/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="pf-card">
            <h3>💼 Project Info</h3>
            <div class="form-group"><label>Title *</label><input type="text" name="title" id="proj-title" value="<?= $v('title') ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label>Category</label><select name="category_id"><option value="">— Select —</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($isEdit&&($project['category_id']??'')==$c['id'])?'selected':'' ?>><?= h(($c['icon'] ? $c['icon'].' ' : '') . $c['name']) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Client Name</label><input type="text" name="client_name" value="<?= $v('client_name') ?>"></div>
            </div>
            <div class="form-group"><label>Short Description</label><input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="500"></div>
            <div class="form-group">
                <label>Full Description</label>
                <textarea name="description" id="proj-desc"><?= h($isEdit ? ($project['description'] ?? '') : '') ?></textarea>
                <button type="button" class="btn-ai" onclick="aiCaseStudy()" style="margin-top:6px">✨ AI Case Study</button>
            </div>
        </div>
        <div class="pf-card">
            <h3>🖼️ Media</h3>
            <div class="form-group"><label>Cover Image URL</label><input type="text" name="cover_image" value="<?= $v('cover_image') ?>" placeholder="https://..."></div>
            <div class="form-group">
                <label>Gallery Images</label>
                <textarea name="images" rows="3" placeholder="One URL per line"><?= $isEdit ? h(implode("\n", $project['images'] ?? [])) : '' ?></textarea>
                <div class="help-text">One image URL per line</div>
            </div>
        </div>
        <div class="pf-card">
            <h3>🛠️ Technical Details</h3>
            <div class="form-group">
                <label>Technologies</label>
                <input type="text" name="technologies" value="<?= $isEdit ? h(implode(', ', $project['technologies'] ?? [])) : '' ?>" placeholder="React, Node.js, PostgreSQL">
                <div class="help-text">Comma-separated list</div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Project URL</label><input type="url" name="project_url" value="<?= $v('project_url') ?>" placeholder="https://..."></div>
                <div class="form-group"><label>Completion Date</label><input type="date" name="completion_date" value="<?= $v('completion_date') ?>"></div>
            </div>
        </div>
        <div class="pf-card">
            <h3>⚙️ Settings</h3>
            <div class="form-row3">
                <div class="form-group"><label>Status</label><select name="status"><option value="draft" <?= ($isEdit&&($project['status']??'')==='draft')?'selected':'' ?>>Draft</option><option value="published" <?= ($isEdit&&($project['status']??'')==='published')?'selected':'' ?>>Published</option></select></div>
                <div class="form-group"><label>Featured</label><select name="is_featured"><option value="0">No</option><option value="1" <?= ($isEdit&&($project['is_featured']??0))?'selected':'' ?>>Yes ⭐</option></select></div>
                <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $v('sort_order', '0') ?>" min="0"></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:space-between;flex-wrap:wrap">
            <div>
                <?php if ($isEdit): ?>
                <form method="post" action="/admin/portfolio/projects/<?= (int)$project['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this project?')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" class="btn-danger">🗑 Delete</button></form>
                <?php endif; ?>
            </div>
            <div style="display:flex;gap:12px"><a href="/admin/portfolio/projects" class="btn-secondary">Cancel</a><button type="submit" class="btn-pf"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
        </div>
    </form>
</div>
<script>
function aiCaseStudy(){
    var title=document.getElementById('proj-title').value;
    if(!title){alert('Enter project title first');return;}
    var btn=event.target;btn.disabled=true;btn.textContent='⏳ Generating...';
    fetch('/api/portfolio/ai-case-study',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:title,description:document.getElementById('proj-desc').value}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        btn.disabled=false;btn.textContent='✨ AI Case Study';
        if(d.ok&&d.data){
            if(d.data.case_study)document.getElementById('proj-desc').value=d.data.case_study;
            if(d.data.short_description){var sd=document.querySelector('[name=short_description]');if(sd)sd.value=d.data.short_description;}
        }else{alert(d.error||'AI generation failed');}
    }).catch(function(){btn.disabled=false;btn.textContent='✨ AI Case Study';alert('Request failed');});
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Project' : 'New Project'; require CMS_APP . '/views/admin/layouts/topbar.php';
