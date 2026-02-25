<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-directory-category.php';
$categories = \DirectoryCategory::getAll('active');
$isEdit = isset($listing) && $listing !== null;
$v = fn($k, $d = '') => h($isEdit ? ($listing[$k] ?? $d) : $d);
ob_start();
?>
<style>
.dir-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.dir-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.dir-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.dir-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.dir-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:80px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
@media(max-width:600px){.form-row,.form-row3{grid-template-columns:1fr}}
.btn-dir{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
</style>
<div class="dir-wrap">
    <div class="dir-header"><h1><?= $isEdit ? '✏️ Edit Listing' : '➕ Add Listing' ?></h1><a href="/admin/directory/listings" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/directory/listings/' . (int)$listing['id'] . '/update' : '/admin/directory/listings/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="dir-card">
            <h3>🏢 Business Info</h3>
            <div class="form-group"><label>Business Name *</label><input type="text" name="title" id="biz-name" value="<?= $v('title') ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label>Category</label><select name="category_id"><option value="">— Select —</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($isEdit&&($listing['category_id']??'')==$c['id'])?'selected':'' ?>><?= h($c['icon'].' '.$c['name']) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Price Range</label><select name="price_range"><option value="">—</option><?php foreach (['$'=>'$ Budget','$$'=>'$$ Moderate','$$$'=>'$$$ Upscale','$$$$'=>'$$$$ Premium'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($listing['price_range']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-group"><label>Short Description</label><input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="500"></div>
            <div class="form-group"><label>Full Description</label><textarea name="description" id="biz-desc"><?= h($isEdit ? ($listing['description'] ?? '') : '') ?></textarea><button type="button" class="btn-ai" onclick="aiDesc()" style="margin-top:6px">✨ AI Generate</button></div>
            <div class="form-group"><label>Tags</label><input type="text" name="tags" value="<?= $v('tags') ?>" placeholder="restaurant, italian, pizza"></div>
        </div>
        <div class="dir-card">
            <h3>📍 Location & Contact</h3>
            <div class="form-group"><label>Address</label><input type="text" name="address" value="<?= $v('address') ?>"></div>
            <div class="form-row3">
                <div class="form-group"><label>City</label><input type="text" name="city" id="biz-city" value="<?= $v('city') ?>"></div>
                <div class="form-group"><label>State/Province</label><input type="text" name="state" value="<?= $v('state') ?>"></div>
                <div class="form-group"><label>ZIP</label><input type="text" name="zip" value="<?= $v('zip') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= $v('phone') ?>"></div>
                <div class="form-group"><label>Website</label><input type="url" name="website" value="<?= $v('website') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Owner Name</label><input type="text" name="owner_name" value="<?= $v('owner_name') ?>"></div>
                <div class="form-group"><label>Owner Email</label><input type="email" name="owner_email" value="<?= $v('owner_email') ?>"></div>
            </div>
        </div>
        <div class="dir-card">
            <h3>⚙️ Settings</h3>
            <div class="form-row3">
                <div class="form-group"><label>Status</label><select name="status"><option value="active" <?= ($isEdit&&($listing['status']??'')==='active')?'selected':'' ?>>Active</option><option value="pending" <?= ($isEdit&&($listing['status']??'')==='pending')?'selected':'' ?>>Pending</option></select></div>
                <div class="form-group"><label>Featured</label><select name="is_featured"><option value="0">No</option><option value="1" <?= ($isEdit&&($listing['is_featured']??0))?'selected':'' ?>>Yes ⭐</option></select></div>
                <div class="form-group"><label>Verified</label><select name="is_verified"><option value="0">No</option><option value="1" <?= ($isEdit&&($listing['is_verified']??0))?'selected':'' ?>>Yes ✓</option></select></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/directory/listings" class="btn-secondary">Cancel</a><button type="submit" class="btn-dir"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
    </form>
</div>
<script>
function aiDesc(){
    var name=document.getElementById('biz-name').value,city=document.getElementById('biz-city').value;
    if(!name){alert('Enter business name first');return;}
    fetch('/api/directory/ai-description',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:name,city:city}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok&&d.data)document.getElementById('biz-desc').value=d.data.description||'';});
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Listing' : 'Add Listing'; require CMS_APP . '/views/admin/layouts/topbar.php';
