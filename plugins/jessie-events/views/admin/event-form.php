<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-event-manager.php';
$isEdit = isset($event) && $event !== null;
$v = fn($k, $d = '') => h($isEdit ? (($event[$k] ?? null) ?: $d) : $d);
ob_start();
?>
<style>
.ew{max-width:800px;margin:0 auto;padding:24px 20px}
.eh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.eh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.fg{margin-bottom:14px}.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:5px}
.fg input,.fg select,.fg textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.fg textarea{min-height:80px;resize:vertical}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:600px){.fr{grid-template-columns:1fr}}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.check-row{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:14px}
.check-row label{display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--text,#e2e8f0);cursor:pointer}
.check-row input[type=checkbox]{width:16px;height:16px}
</style>
<div class="ew">
    <div class="eh"><h1><?= $isEdit ? '✏️ Edit Event' : '➕ Create Event' ?></h1><div style="display:flex;gap:10px"><?php if ($isEdit): ?><a href="/admin/events/<?= (int)$event['id'] ?>/tickets" class="btn-s">🎫 Tickets</a><?php endif; ?><a href="/admin/events/list" class="btn-s">← Back</a></div></div>
    <form method="post" action="<?= $isEdit ? '/admin/events/'.(int)$event['id'].'/update' : '/admin/events/store' ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="card">
            <h3>🎪 Event Details</h3>
            <div class="fg"><label>Title *</label><input type="text" name="title" id="ev-title" value="<?= $v('title') ?>" required></div>
            <div class="fg"><label>Short Description</label><input type="text" name="short_description" value="<?= $v('short_description') ?>" maxlength="500"></div>
            <div class="fg"><label>Description</label><textarea name="description" id="ev-desc"><?= h($isEdit ? (($event['description'] ?? null) ?: '') : '') ?></textarea><button type="button" class="btn-ai" onclick="aiDesc()" style="margin-top:6px">✨ AI Description</button></div>
            <div class="fr">
                <div class="fg"><label>Category</label><input type="text" name="category" value="<?= $v('category') ?>" placeholder="Music, Tech, Sports..."></div>
                <div class="fg"><label>Status</label><select name="status"><?php foreach (['upcoming'=>'Upcoming','ongoing'=>'Ongoing','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$l): ?><option value="<?= $k ?>" <?= ($isEdit&&($event['status']??'')===$k)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="fg"><label>Image URL</label><input type="text" name="image" value="<?= $v('image') ?>" placeholder="https://..."></div>
        </div>
        <div class="card">
            <h3>📅 Date & Time</h3>
            <div class="fr">
                <div class="fg"><label>Start Date *</label><input type="datetime-local" name="start_date" value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($event['start_date'])) : '' ?>" required></div>
                <div class="fg"><label>End Date</label><input type="datetime-local" name="end_date" value="<?= $isEdit && $event['end_date'] ? date('Y-m-d\TH:i', strtotime($event['end_date'])) : '' ?>"></div>
            </div>
        </div>
        <div class="card">
            <h3>📍 Venue</h3>
            <div class="fr">
                <div class="fg"><label>Venue Name</label><input type="text" name="venue_name" value="<?= $v('venue_name') ?>"></div>
                <div class="fg"><label>Venue Address</label><input type="text" name="venue_address" value="<?= $v('venue_address') ?>"></div>
            </div>
            <div class="fr">
                <div class="fg"><label>City</label><input type="text" name="city" value="<?= $v('city') ?>"></div>
                <div class="fg"><label>Country</label><input type="text" name="country" value="<?= $v('country') ?>"></div>
            </div>
        </div>
        <div class="card">
            <h3>👤 Organizer</h3>
            <div class="fr">
                <div class="fg"><label>Organizer Name</label><input type="text" name="organizer_name" value="<?= $v('organizer_name') ?>"></div>
                <div class="fg"><label>Organizer Email</label><input type="email" name="organizer_email" value="<?= $v('organizer_email') ?>"></div>
            </div>
            <div class="fg"><label>Max Capacity</label><input type="number" name="max_capacity" min="0" value="<?= $v('max_capacity') ?>"></div>
        </div>
        <div class="card">
            <h3>🏷️ Options</h3>
            <div class="check-row">
                <label><input type="checkbox" name="is_featured" value="1" <?= ($isEdit && ($event['is_featured'] ?? 0)) ? 'checked' : '' ?>> ⭐ Featured</label>
                <label><input type="checkbox" name="is_free" value="1" <?= ($isEdit && ($event['is_free'] ?? 0)) ? 'checked' : '' ?>> 🆓 Free Event</label>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end">
            <?php if ($isEdit): ?><form method="post" action="/admin/events/<?= (int)$event['id'] ?>/delete" style="display:inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" onclick="return confirm('Delete this event and all tickets/orders?')" class="btn-s" style="color:#fca5a5">🗑 Delete</button></form><?php endif; ?>
            <a href="/admin/events/list" class="btn-s">Cancel</a>
            <button type="submit" class="btn-p"><?= $isEdit ? '💾 Update' : '➕ Create' ?></button>
        </div>
    </form>
</div>
<script>
function aiDesc(){
    var title=document.getElementById('ev-title').value;
    if(!title){alert('Enter title first');return;}
    var btn=event.target;btn.textContent='⏳ Generating...';btn.disabled=true;
    fetch('/api/events/ai-description',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title:title}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        btn.textContent='✨ AI Description';btn.disabled=false;
        if(d.ok&&d.data){document.getElementById('ev-desc').value=d.data.description||'';}else{alert(d.error||'Failed');}
    }).catch(function(){btn.textContent='✨ AI Description';btn.disabled=false;alert('Error');});
}
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Event' : 'Create Event'; require CMS_APP . '/views/admin/layouts/topbar.php';
