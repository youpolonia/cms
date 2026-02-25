<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-job-company.php';
$companies = \JobCompany::getAll('active');
$isEdit = isset($job) && $job !== null;
$v = fn($k, $d = '') => h($isEdit ? ($job[$k] ?? $d) : $d);
$skillsStr = '';
if ($isEdit && !empty($job['skills'])) {
    $skillsStr = is_array($job['skills']) ? implode(', ', $job['skills']) : $job['skills'];
}
ob_start();
?>
<style>
.jb-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.jb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.jb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.jb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.jb-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:100px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
@media(max-width:600px){.form-row,.form-row3{grid-template-columns:1fr}}
.btn-jb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:600;border:1px solid rgba(239,68,68,.3);cursor:pointer}
</style>
<div class="jb-wrap">
    <div class="jb-header"><h1><?= $isEdit ? '✏️ Edit Job' : '➕ Post Job' ?></h1><a href="/admin/jobs/listings" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/jobs/listings/' . (int)$job['id'] . '/update' : '/admin/jobs/listings/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="jb-card">
            <h3>💼 Job Info</h3>
            <div class="form-group"><label>Job Title *</label><input type="text" name="title" id="job-title" value="<?= $v('title') ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label>Company Name *</label><input type="text" name="company_name" id="job-company" value="<?= $v('company_name') ?>" list="company-list" required>
                    <datalist id="company-list"><?php foreach ($companies as $c): ?><option value="<?= h($c['name']) ?>"><?php endforeach; ?></datalist>
                </div>
                <div class="form-group"><label>Company Logo URL</label><input type="url" name="company_logo" value="<?= $v('company_logo') ?>"></div>
            </div>
            <div class="form-row3">
                <div class="form-group"><label>Category</label><input type="text" name="category" id="job-category" value="<?= $v('category') ?>" placeholder="e.g. Engineering, Marketing"></div>
                <div class="form-group"><label>Job Type</label><select name="job_type"><?php foreach (['full-time'=>'Full-time','part-time'=>'Part-time','contract'=>'Contract','freelance'=>'Freelance'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($job['job_type']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Remote Type</label><select name="remote_type"><?php foreach (['onsite'=>'On-site','remote'=>'Remote','hybrid'=>'Hybrid'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($job['remote_type']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Location</label><input type="text" name="location" id="job-location" value="<?= $v('location') ?>" placeholder="e.g. London, UK"></div>
                <div class="form-group"><label>Experience Level</label><select name="experience_level"><?php foreach (['entry'=>'Entry Level','mid'=>'Mid Level','senior'=>'Senior','lead'=>'Lead / Manager'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($job['experience_level']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
        </div>
        <div class="jb-card">
            <h3>💰 Salary</h3>
            <div class="form-row3">
                <div class="form-group"><label>Min Salary</label><input type="number" name="salary_min" value="<?= $isEdit?($job['salary_min']??''):'' ?>" step="0.01" placeholder="e.g. 50000"></div>
                <div class="form-group"><label>Max Salary</label><input type="number" name="salary_max" value="<?= $isEdit?($job['salary_max']??''):'' ?>" step="0.01" placeholder="e.g. 80000"></div>
                <div class="form-group"><label>Currency</label><select name="salary_currency"><?php foreach (['USD','EUR','GBP','PLN','CHF','CAD','AUD'] as $c): ?><option value="<?= $c ?>" <?= ($isEdit&&($job['salary_currency']??'USD')===$c)?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
            </div>
        </div>
        <div class="jb-card">
            <h3>📝 Description</h3>
            <div class="form-group"><label>Job Description</label><textarea name="description" id="job-desc" rows="6"><?= h($isEdit ? ($job['description'] ?? '') : '') ?></textarea><button type="button" class="btn-ai" onclick="aiDesc()" style="margin-top:6px">✨ AI Generate Description</button></div>
            <div class="form-group"><label>Requirements</label><textarea name="requirements" id="job-req" rows="5"><?= h($isEdit ? ($job['requirements'] ?? '') : '') ?></textarea><button type="button" class="btn-ai" onclick="aiReq()" style="margin-top:6px">✨ AI Generate Requirements</button></div>
            <div class="form-group"><label>Benefits</label><textarea name="benefits" id="job-benefits" rows="4"><?= h($isEdit ? ($job['benefits'] ?? '') : '') ?></textarea></div>
            <div class="form-group"><label>Skills (comma-separated)</label><input type="text" name="skills" id="job-skills" value="<?= h($skillsStr) ?>" placeholder="JavaScript, React, Node.js, SQL"></div>
        </div>
        <div class="jb-card">
            <h3>📩 Application</h3>
            <div class="form-row">
                <div class="form-group"><label>Application URL</label><input type="url" name="application_url" value="<?= $v('application_url') ?>" placeholder="https://careers.example.com/apply"></div>
                <div class="form-group"><label>Application Email</label><input type="email" name="application_email" value="<?= $v('application_email') ?>" placeholder="jobs@example.com"></div>
            </div>
        </div>
        <div class="jb-card">
            <h3>⚙️ Settings</h3>
            <div class="form-row3">
                <div class="form-group"><label>Status</label><select name="status"><option value="draft" <?= ($isEdit&&($job['status']??'')==='draft')?'selected':'' ?>>Draft</option><option value="active" <?= ($isEdit&&($job['status']??'')==='active')?'selected':'' ?>>Active</option><option value="expired" <?= ($isEdit&&($job['status']??'')==='expired')?'selected':'' ?>>Expired</option></select></div>
                <div class="form-group"><label>Featured</label><select name="is_featured"><option value="0">No</option><option value="1" <?= ($isEdit&&($job['is_featured']??0))?'selected':'' ?>>Yes ⭐</option></select></div>
                <div class="form-group"><label>Expires At</label><input type="date" name="expires_at" value="<?= $isEdit&&!empty($job['expires_at'])?date('Y-m-d',strtotime($job['expires_at'])):'' ?>"></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:space-between">
            <div>
            <?php if ($isEdit): ?>
                <button type="button" class="btn-danger" onclick="if(confirm('Delete this job?')){var f=document.createElement('form');f.method='POST';f.action='/admin/jobs/listings/<?= (int)$job['id'] ?>/delete';var t=document.createElement('input');t.type='hidden';t.name='csrf_token';t.value='<?= h(csrf_token()) ?>';f.appendChild(t);document.body.appendChild(f);f.submit();}">🗑️ Delete</button>
            <?php endif; ?>
            </div>
            <div style="display:flex;gap:12px"><a href="/admin/jobs/listings" class="btn-secondary">Cancel</a><button type="submit" class="btn-jb"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
        </div>
    </form>
</div>
<script>
function aiDesc(){
    var title=document.getElementById('job-title').value,company=document.getElementById('job-company').value,loc=document.getElementById('job-location').value;
    if(!title){alert('Enter job title first');return;}
    fetch('/api/jobs/ai-description',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:title,company:company,location:loc,job_type:document.querySelector('[name=job_type]').value}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok&&d.data){if(d.data.description)document.getElementById('job-desc').value=d.data.description;if(d.data.benefits&&!document.getElementById('job-benefits').value)document.getElementById('job-benefits').value=d.data.benefits;}else{alert('AI generation failed');}});
}
function aiReq(){
    var title=document.getElementById('job-title').value,cat=document.getElementById('job-category').value;
    if(!title){alert('Enter job title first');return;}
    fetch('/api/jobs/ai-requirements',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:title,category:cat,experience_level:document.querySelector('[name=experience_level]').value}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok&&d.data){if(d.data.requirements)document.getElementById('job-req').value=d.data.requirements;if(d.data.skills&&!document.getElementById('job-skills').value)document.getElementById('job-skills').value=d.data.skills.join(', ');}else{alert('AI generation failed');}});
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Job' : 'Post Job'; require CMS_APP . '/views/admin/layouts/topbar.php';
