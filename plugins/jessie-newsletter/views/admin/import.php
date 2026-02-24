<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-list.php';
$lists = \NewsletterList::getAll('active');
ob_start();
?>
<style>
.nl-wrap{max-width:600px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.nl-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.nl-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group textarea,.form-group select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:monospace}
.form-group textarea{min-height:150px;resize:vertical}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>📥 Import Subscribers</h1><a href="/admin/newsletter/subscribers" class="btn-secondary">← Back</a></div>
    <div class="nl-card">
        <h3>CSV Import</h3>
        <p style="font-size:.82rem;color:var(--muted,#94a3b8);margin-bottom:16px">Paste CSV data with headers. Required: <strong>email</strong>. Optional: <strong>name</strong>.</p>
        <div class="form-group"><label>Add to List</label><select id="import-list"><?php foreach ($lists as $l): ?><option value="<?= $l['id'] ?>"><?= h($l['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>CSV Data</label><textarea id="import-csv" placeholder="email,name&#10;john@example.com,John Doe&#10;jane@example.com,Jane Smith"></textarea></div>
        <button type="button" class="btn-nl" onclick="importCSV()" id="import-btn">📥 Import</button>
        <div id="import-result" style="margin-top:12px;font-size:.85rem;display:none"></div>
    </div>
</div>
<script>
function importCSV(){
    var csv=document.getElementById('import-csv').value;var lid=document.getElementById('import-list').value;
    if(!csv){alert('Paste CSV data first');return;}
    var btn=document.getElementById('import-btn');btn.disabled=true;btn.textContent='⏳ Importing...';
    fetch('/api/newsletter/subscribers/import',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csv:csv,list_id:parseInt(lid)}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        btn.disabled=false;btn.textContent='📥 Import';
        var el=document.getElementById('import-result');el.style.display='block';
        if(d.ok)el.innerHTML='<span style="color:#34d399">✅ Imported: '+d.imported+' | Skipped: '+d.skipped+' | Errors: '+d.errors+'</span>';
        else el.innerHTML='<span style="color:#ef4444">❌ '+d.error+'</span>';
    });
}
</script>
<?php $content = ob_get_clean(); $title = 'Import Subscribers'; require CMS_APP . '/views/admin/layouts/topbar.php';
