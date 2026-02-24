<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-menu.php';
$categories = \RestaurantMenu::getCategories();
$isEdit = isset($item) && $item !== null;
$v = fn($k, $d = '') => h($isEdit ? ($item[$k] ?? $d) : $d);
$sym = \RestaurantMenu::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.rw{max-width:800px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.fg{margin-bottom:14px}.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:5px}
.fg input,.fg select,.fg textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.fg textarea{min-height:80px;resize:vertical}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fr3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
@media(max-width:600px){.fr,.fr3{grid-template-columns:1fr}}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.check-row{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:14px}
.check-row label{display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--text,#e2e8f0);cursor:pointer}
.check-row input[type=checkbox]{width:16px;height:16px}
</style>
<div class="rw">
    <div class="rh"><h1><?= $isEdit ? '✏️ Edit Item' : '➕ Add Menu Item' ?></h1><a href="/admin/restaurant/menu" class="btn-s">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/restaurant/menu/'.(int)$item['id'].'/update' : '/admin/restaurant/menu/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="card">
            <h3>🍽️ Item Details</h3>
            <div class="fg"><label>Name *</label><input type="text" name="name" id="item-name" value="<?= $v('name') ?>" required></div>
            <div class="fr">
                <div class="fg"><label>Category</label><select name="category_id"><option value="">—</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($isEdit&&($item['category_id']??'')==$c['id'])?'selected':'' ?>><?= h($c['icon'].' '.$c['name']) ?></option><?php endforeach; ?></select></div>
                <div class="fg"><label>Status</label><select name="status"><?php foreach (['active'=>'Active','hidden'=>'Hidden','soldout'=>'Sold Out'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($item['status']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="fg"><label>Short Description</label><input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="500"></div>
            <div class="fg"><label>Description</label><textarea name="description" id="item-desc"><?= h($isEdit?($item['description']??''):'') ?></textarea><button type="button" class="btn-ai" onclick="aiDesc()" style="margin-top:6px">✨ AI Description</button></div>
        </div>
        <div class="card">
            <h3>💰 Pricing & Details</h3>
            <div class="fr3">
                <div class="fg"><label>Price (<?= $sym ?>) *</label><input type="number" name="price" step="0.01" min="0" value="<?= $v('price','0.00') ?>" required></div>
                <div class="fg"><label>Sale Price</label><input type="number" name="sale_price" step="0.01" min="0" value="<?= $v('sale_price') ?>"></div>
                <div class="fg"><label>Prep Time (min)</label><input type="number" name="prep_time_min" min="0" value="<?= $v('prep_time_min') ?>"></div>
            </div>
            <div class="fr">
                <div class="fg"><label>Allergens</label><input type="text" name="allergens" value="<?= $v('allergens') ?>" placeholder="gluten, dairy, nuts"></div>
                <div class="fg"><label>Calories</label><input type="number" name="calories" min="0" value="<?= $v('calories') ?>"></div>
            </div>
        </div>
        <div class="card">
            <h3>🏷️ Dietary & Features</h3>
            <div class="check-row">
                <label><input type="checkbox" name="is_vegetarian" value="1" <?= ($isEdit&&($item['is_vegetarian']??0))?'checked':'' ?>> 🥬 Vegetarian</label>
                <label><input type="checkbox" name="is_vegan" value="1" <?= ($isEdit&&($item['is_vegan']??0))?'checked':'' ?>> 🌱 Vegan</label>
                <label><input type="checkbox" name="is_gluten_free" value="1" <?= ($isEdit&&($item['is_gluten_free']??0))?'checked':'' ?>> 🌾 Gluten Free</label>
                <label><input type="checkbox" name="is_spicy" value="1" <?= ($isEdit&&($item['is_spicy']??0))?'checked':'' ?>> 🌶️ Spicy</label>
                <label><input type="checkbox" name="is_featured" value="1" <?= ($isEdit&&($item['is_featured']??0))?'checked':'' ?>> ⭐ Featured</label>
                <label><input type="checkbox" name="is_available" value="1" <?= ($isEdit?($item['is_available']??1):1)?'checked':'' ?>> ✅ Available</label>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end">
            <?php if ($isEdit): ?><form method="post" action="/admin/restaurant/menu/<?= (int)$item['id'] ?>/delete" style="display:inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" onclick="return confirm('Delete?')" class="btn-s" style="color:#fca5a5">🗑 Delete</button></form><?php endif; ?>
            <a href="/admin/restaurant/menu" class="btn-s">Cancel</a>
            <button type="submit" class="btn-p"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button>
        </div>
    </form>
</div>
<script>
function aiDesc(){
    var name=document.getElementById('item-name').value;
    if(!name){alert('Enter name first');return;}
    var btn=event.target;btn.textContent='⏳ Generating...';btn.disabled=true;
    fetch('/api/restaurant/ai-description',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:name}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        btn.textContent='✨ AI Description';btn.disabled=false;
        if(d.ok&&d.data){document.getElementById('item-desc').value=d.data.description||'';}else{alert(d.error||'Failed');}
    }).catch(function(){btn.textContent='✨ AI Description';btn.disabled=false;alert('Error');});
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Item' : 'Add Item'; require CMS_APP . '/views/admin/layouts/topbar.php';
