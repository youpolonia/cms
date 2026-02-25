<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-realestate-agent.php';
$agents = \RealEstateAgent::getAll('active');
$isEdit = isset($property) && $property !== null;
$v = fn($k, $d = '') => h($isEdit ? ($property[$k] ?? $d) : $d);
ob_start();
?>
<style>
.re-wrap{max-width:850px;margin:0 auto;padding:24px 20px}
.re-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.re-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.re-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.re-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:80px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.form-row4{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px}
@media(max-width:600px){.form-row,.form-row3,.form-row4{grid-template-columns:1fr}}
.btn-re{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:600;border:1px solid rgba(239,68,68,.3);cursor:pointer}
</style>
<div class="re-wrap">
    <div class="re-header"><h1><?= $isEdit ? '✏️ Edit Property' : '➕ Add Property' ?></h1><a href="/admin/realestate/properties" class="btn-secondary">← Back</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/realestate/properties/' . (int)$property['id'] . '/update' : '/admin/realestate/properties/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="re-card">
            <h3>🏠 Property Details</h3>
            <div class="form-group"><label>Title *</label><input type="text" name="title" id="prop-title" value="<?= $v('title') ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label>Property Type</label><select name="property_type" id="prop-type"><?php foreach (['house','apartment','condo','townhouse','land','commercial','other'] as $t): ?><option value="<?= $t ?>" <?= ($isEdit&&($property['property_type']??'')===$t)?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Listing Type</label><select name="listing_type"><?php foreach (['sale'=>'For Sale','rent'=>'For Rent','lease'=>'For Lease'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($property['listing_type']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-row3">
                <div class="form-group"><label>Price *</label><input type="number" name="price" id="prop-price" value="<?= $v('price', '0') ?>" step="0.01" required></div>
                <div class="form-group"><label>Price Period</label><select name="price_period"><?php foreach (['total'=>'Total','monthly'=>'Monthly','weekly'=>'Weekly','yearly'=>'Yearly'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($property['price_period']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Currency</label><input type="text" name="currency" value="<?= $v('currency', 'GBP') ?>"></div>
            </div>
            <div class="form-group"><label>Short Description</label><input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="500"></div>
            <div class="form-group"><label>Full Description</label><textarea name="description" id="prop-desc" rows="5"><?= h($isEdit ? ($property['description'] ?? '') : '') ?></textarea><button type="button" class="btn-ai" onclick="aiDesc()" style="margin-top:6px">✨ AI Generate</button> <button type="button" class="btn-ai" onclick="aiValuation()" style="margin-top:6px">📊 AI Valuation</button></div>
        </div>
        <div class="re-card">
            <h3>📐 Specifications</h3>
            <div class="form-row4">
                <div class="form-group"><label>Bedrooms</label><input type="number" name="bedrooms" id="prop-beds" value="<?= $v('bedrooms') ?>" min="0"></div>
                <div class="form-group"><label>Bathrooms</label><input type="number" name="bathrooms" id="prop-baths" value="<?= $v('bathrooms') ?>" min="0"></div>
                <div class="form-group"><label>Area (sq ft)</label><input type="number" name="area_sqft" id="prop-area" value="<?= $v('area_sqft') ?>" min="0"></div>
                <div class="form-group"><label>Year Built</label><input type="number" name="year_built" value="<?= $v('year_built') ?>" min="1800" max="2030"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Lot Size (sq ft)</label><input type="number" name="lot_size" value="<?= $v('lot_size') ?>" min="0"></div>
                <div class="form-group"><label>Agent</label><select name="agent_id"><option value="">— No Agent —</option><?php foreach ($agents as $ag): ?><option value="<?= $ag['id'] ?>" <?= ($isEdit&&($property['agent_id']??'')==$ag['id'])?'selected':'' ?>><?= h($ag['name']) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="form-group"><label>Features (comma-separated)</label><input type="text" name="features" value="<?= $isEdit ? h(implode(', ', $property['features'] ?? [])) : '' ?>" placeholder="Garden, Parking, Pool, Garage, Central Heating"></div>
        </div>
        <div class="re-card">
            <h3>📍 Location</h3>
            <div class="form-group"><label>Address</label><input type="text" name="address" value="<?= $v('address') ?>"></div>
            <div class="form-row3">
                <div class="form-group"><label>City</label><input type="text" name="city" id="prop-city" value="<?= $v('city') ?>"></div>
                <div class="form-group"><label>State/County</label><input type="text" name="state" value="<?= $v('state') ?>"></div>
                <div class="form-group"><label>Postcode</label><input type="text" name="zip" value="<?= $v('zip') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Country</label><input type="text" name="country" value="<?= $v('country') ?>"></div>
                <div class="form-group"><label>Virtual Tour URL</label><input type="url" name="virtual_tour" value="<?= $v('virtual_tour') ?>"></div>
            </div>
        </div>
        <div class="re-card">
            <h3>🖼️ Images</h3>
            <div class="form-group"><label>Image URLs (one per line)</label><textarea name="images" rows="4"><?= $isEdit ? h(implode("\n", $property['images'] ?? [])) : '' ?></textarea></div>
            <div class="form-group"><label>Floor Plan URL</label><input type="text" name="floor_plan" value="<?= $v('floor_plan') ?>"></div>
        </div>
        <div class="re-card">
            <h3>⚙️ Settings</h3>
            <div class="form-row">
                <div class="form-group"><label>Status</label><select name="status"><?php foreach (['active'=>'Active','pending'=>'Pending','sold'=>'Sold','rented'=>'Rented','draft'=>'Draft'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($property['status']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Featured</label><select name="is_featured"><option value="0">No</option><option value="1" <?= ($isEdit&&($property['is_featured']??0))?'selected':'' ?>>Yes ⭐</option></select></div>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:space-between;align-items:center">
            <?php if ($isEdit): ?>
            <form method="post" action="/admin/realestate/properties/<?= (int)$property['id'] ?>/delete" onsubmit="return confirm('Delete this property?')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" class="btn-danger">🗑️ Delete</button></form>
            <?php else: ?><div></div><?php endif; ?>
            <div style="display:flex;gap:12px"><a href="/admin/realestate/properties" class="btn-secondary">Cancel</a><button type="submit" class="btn-re"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button></div>
        </div>
    </form>
</div>
<div id="valuation-result" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--bg-card,#1e293b);border:2px solid #6366f1;border-radius:12px;padding:24px;min-width:400px;z-index:9999;box-shadow:0 20px 60px rgba(0,0,0,.5)"><h3 style="margin:0 0 12px;color:#a5b4fc">📊 AI Valuation</h3><div id="val-body" style="font-size:.85rem;line-height:1.6"></div><button onclick="document.getElementById('valuation-result').style.display='none'" style="margin-top:12px;background:#6366f1;color:#fff;border:none;padding:6px 16px;border-radius:6px;cursor:pointer">Close</button></div>
<script>
function aiDesc(){
    var t=document.getElementById('prop-title').value,c=document.getElementById('prop-city').value,tp=document.getElementById('prop-type').value,b=document.getElementById('prop-beds').value,p=document.getElementById('prop-price').value;
    if(!t){alert('Enter title first');return;}
    fetch('/api/realestate/ai-description',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:t,property_type:tp,city:c,bedrooms:parseInt(b)||0,price:parseFloat(p)||0}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok&&d.data){document.getElementById('prop-desc').value=d.data.description||'';if(d.data.short_description){document.querySelector('[name=short_description]').value=d.data.short_description;}}else{alert(d.error||'Failed');}});
}
function aiValuation(){
    var t=document.getElementById('prop-title').value,c=document.getElementById('prop-city').value,tp=document.getElementById('prop-type').value,b=document.getElementById('prop-beds').value,ba=document.getElementById('prop-baths').value,a=document.getElementById('prop-area').value;
    if(!t||!c){alert('Enter title and city first');return;}
    fetch('/api/realestate/ai-valuation',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:t,property_type:tp,city:c,bedrooms:parseInt(b)||0,bathrooms:parseInt(ba)||0,area_sqft:parseInt(a)||0}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok&&d.data){
            var h='<strong>Estimated Value:</strong> £'+Number(d.data.estimated_value||0).toLocaleString()+'<br>';
            h+='<strong>Range:</strong> '+(d.data.price_range||'N/A')+'<br>';
            h+='<strong>Trend:</strong> '+(d.data.market_trend||'N/A')+'<br>';
            h+='<strong>Reasoning:</strong> '+(d.data.reasoning||'');
            document.getElementById('val-body').innerHTML=h;
            document.getElementById('valuation-result').style.display='block';
        }else{alert(d.error||'Failed');}
    });
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Property' : 'Add Property'; require CMS_APP . '/views/admin/layouts/topbar.php';
