<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = isset($service) && $service !== null;
$v = fn($k, $d = '') => h($isEdit ? ($service[$k] ?? $d) : $d);
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
.form-group textarea{min-height:80px;resize:vertical;font-family:inherit}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-bk{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
</style>
<div class="bk-wrap">
    <div class="bk-header"><h1><?= $isEdit ? '✏️ Edit Service' : '➕ Add Service' ?></h1><a href="/admin/booking/services" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/booking/services/' . (int)$service['id'] . '/update' : '/admin/booking/services/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="bk-card">
            <h3>📋 Service Details</h3>
            <div class="form-group"><label>Name *</label><input type="text" name="name" value="<?= $v('name') ?>" required id="svc-name"><button type="button" class="btn-ai" onclick="aiDesc()" style="margin-top:6px">✨ AI Generate Description</button></div>
            <div class="form-group"><label>Description</label><textarea name="description" id="svc-desc"><?= h($isEdit ? ($service['description'] ?? '') : '') ?></textarea></div>
            <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= $v('category') ?>" placeholder="e.g. Hair, Massage, Consultation"></div>
        </div>
        <div class="bk-card">
            <h3>⏱ Scheduling</h3>
            <div class="form-row">
                <div class="form-group"><label>Duration (minutes)</label><input type="number" name="duration_minutes" value="<?= $v('duration_minutes', '60') ?>" min="5" max="480"></div>
                <div class="form-group"><label>Buffer (minutes between)</label><input type="number" name="buffer_minutes" value="<?= $v('buffer_minutes', '15') ?>" min="0" max="120"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Price ($)</label><input type="number" name="price" step="0.01" value="<?= $v('price', '0') ?>" min="0"></div>
                <div class="form-group"><label>Max Bookings Per Slot</label><input type="number" name="max_bookings_per_slot" value="<?= $v('max_bookings_per_slot', '1') ?>" min="1" max="100"></div>
            </div>
        </div>
        <div class="bk-card">
            <h3>🎨 Display</h3>
            <div class="form-row">
                <div class="form-group"><label>Color</label><input type="color" name="color" value="<?= $v('color', '#6366f1') ?>" style="height:42px"></div>
                <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($isEdit && $service['status']==='active') ? 'selected' : '' ?>>Active</option><option value="inactive" <?= ($isEdit && $service['status']==='inactive') ? 'selected' : '' ?>>Inactive</option></select></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/booking/services" class="btn-secondary">Cancel</a><button type="submit" class="btn-bk"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
    </form>
</div>
<script>
function aiDesc(){
    var name=document.getElementById('svc-name').value;
    if(!name){alert('Enter service name first');return;}
    fetch('/api/booking/ai-description',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:name}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok&&d.data){document.getElementById('svc-desc').value=d.data.description||'';}else{alert('AI failed');}
    });
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Service' : 'Add Service'; require CMS_APP . '/views/admin/layouts/topbar.php';
