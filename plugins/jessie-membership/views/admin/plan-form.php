<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = isset($plan) && $plan !== null;
$v = fn($k, $d = '') => h($isEdit ? ($plan[$k] ?? $d) : $d);
$features = $isEdit ? ($plan['features'] ?? []) : [];
ob_start();
?>
<style>
.mb-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.mb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.mb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.mb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.mb-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:80px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn-mb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.feature-list{margin:0;padding:0;list-style:none}
.feature-item{display:flex;gap:8px;align-items:center;margin-bottom:6px}
.feature-item input{flex:1;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.82rem}
.feature-item button{background:rgba(239,68,68,.15);color:#fca5a5;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:.78rem}
</style>
<div class="mb-wrap">
    <div class="mb-header"><h1><?= $isEdit ? '✏️ Edit Plan' : '➕ New Plan' ?></h1><a href="/admin/membership/plans" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/membership/plans/' . (int)$plan['id'] . '/update' : '/admin/membership/plans/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="mb-card">
            <h3>💎 Plan Details</h3>
            <div class="form-group"><label>Name *</label><input type="text" name="name" id="plan-name" value="<?= $v('name') ?>" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" id="plan-desc"><?= h($isEdit ? ($plan['description'] ?? '') : '') ?></textarea><button type="button" class="btn-ai" onclick="aiPlan()" style="margin-top:6px">✨ AI Generate</button></div>
        </div>
        <div class="mb-card">
            <h3>💰 Pricing</h3>
            <div class="form-row">
                <div class="form-group"><label>Price ($)</label><input type="number" name="price" id="plan-price" step="0.01" value="<?= $v('price', '0') ?>" min="0"></div>
                <div class="form-group"><label>Billing Period</label><select name="billing_period" id="plan-billing"><?php foreach (['free'=>'Free','monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly','lifetime'=>'Lifetime'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit && ($plan['billing_period']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Trial Days</label><input type="number" name="trial_days" value="<?= $v('trial_days', '0') ?>" min="0"></div>
                <div class="form-group"><label>Color</label><input type="color" name="color" value="<?= $v('color', '#6366f1') ?>" style="height:42px"></div>
            </div>
        </div>
        <div class="mb-card">
            <h3>✅ Features</h3>
            <ul class="feature-list" id="features">
                <?php foreach ($features as $i => $f): ?>
                <li class="feature-item"><input type="text" name="features[]" value="<?= h($f) ?>"><button type="button" onclick="this.parentElement.remove()">✕</button></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" onclick="addFeature()" class="btn-ai" style="margin-top:8px">➕ Add Feature</button>
        </div>
        <div class="mb-card">
            <h3>🎨 Display</h3>
            <div class="form-row">
                <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= $v('sort_order', '0') ?>" min="0"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($isEdit && ($plan['status']??'')==='active')?'selected':'' ?>>Active</option><option value="inactive" <?= ($isEdit && ($plan['status']??'')==='inactive')?'selected':'' ?>>Inactive</option></select></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/membership/plans" class="btn-secondary">Cancel</a><button type="submit" class="btn-mb"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
    </form>
</div>
<script>
function addFeature(val){
    var li=document.createElement('li');li.className='feature-item';
    li.innerHTML='<input type="text" name="features[]" value="'+(val||'')+'" placeholder="e.g. Unlimited access"><button type="button" onclick="this.parentElement.remove()">✕</button>';
    document.getElementById('features').appendChild(li);if(!val)li.querySelector('input').focus();
}
function aiPlan(){
    var name=document.getElementById('plan-name').value,price=document.getElementById('plan-price').value,billing=document.getElementById('plan-billing').value;
    if(!name){alert('Enter plan name first');return;}
    fetch('/api/membership/ai-plan',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:name,price:parseFloat(price)||0,billing:billing}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(!d.ok||!d.data)return;
        document.getElementById('plan-desc').value=d.data.description||'';
        var fl=document.getElementById('features');fl.innerHTML='';
        (d.data.features||[]).forEach(function(f){addFeature(f);});
    });
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Plan' : 'New Plan'; require CMS_APP . '/views/admin/layouts/topbar.php';
