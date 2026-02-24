<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-list.php';
$lists = \NewsletterList::getAll('active');
$isEdit = isset($campaign) && $campaign !== null;
$v = fn($k, $d = '') => h($isEdit ? ($campaign[$k] ?? $d) : $d);
ob_start();
?>
<style>
.nl-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.nl-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.nl-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:100px;resize:vertical}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-ai{background:rgba(99,102,241,.15);color:#a5b4fc;padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:1px solid rgba(99,102,241,.3);cursor:pointer}
.ai-panel{background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:16px;margin-bottom:16px}
.ai-panel h4{margin:0 0 10px;font-size:.82rem;color:#a5b4fc}
.ai-suggestions{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.ai-suggestion{padding:6px 12px;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);border-radius:6px;font-size:.78rem;cursor:pointer;color:var(--text,#e2e8f0);transition:.2s}
.ai-suggestion:hover{border-color:#6366f1}
#content-editor{min-height:300px;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:14px;border-radius:8px;font-family:monospace;font-size:.85rem;resize:vertical;width:100%;box-sizing:border-box}
.preview-frame{background:#fff;border-radius:8px;padding:20px;max-height:400px;overflow:auto;margin-top:10px}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1><?= $isEdit ? '✏️ Edit Campaign' : '✉️ New Campaign' ?></h1><a href="/admin/newsletter/campaigns" class="btn-secondary">← Campaigns</a></div>
    <form method="post" action="<?= $isEdit ? '/admin/newsletter/campaigns/' . (int)$campaign['id'] . '/update' : '/admin/newsletter/campaigns/store' ?>" id="campForm">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="nl-card">
            <h3>📋 Campaign Details</h3>
            <div class="form-group"><label>Campaign Name</label><input type="text" name="name" value="<?= $v('name') ?>" required placeholder="e.g. February Newsletter"></div>
            <div class="form-row">
                <div class="form-group"><label>Send To (List)</label><select name="list_id"><option value="">— Select list —</option><?php foreach ($lists as $l): ?><option value="<?= $l['id'] ?>" <?= ($isEdit && ($campaign['list_id'] ?? '')==$l['id'])?'selected':'' ?>><?= h($l['name']) ?> (<?= $l['subscriber_count'] ?>)</option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Schedule (optional)</label><input type="datetime-local" name="scheduled_at" value="<?= $isEdit && $campaign['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '' ?>"></div>
            </div>
        </div>
        <div class="nl-card">
            <h3>✉️ Email Content</h3>
            <div class="form-group">
                <label>Subject Line</label>
                <input type="text" name="subject" id="subject" value="<?= $v('subject') ?>" required placeholder="Your email subject">
                <button type="button" class="btn-ai" onclick="aiSubjects()" style="margin-top:6px">✨ AI Suggest Subjects</button>
                <div id="ai-subjects" class="ai-suggestions" style="display:none"></div>
            </div>
            <div class="form-group"><label>Preview Text</label><input type="text" name="preview_text" value="<?= $v('preview_text') ?>" placeholder="Text shown in inbox preview"></div>
            <div class="form-row">
                <div class="form-group"><label>From Name</label><input type="text" name="from_name" value="<?= $v('from_name') ?>" placeholder="Your Business"></div>
                <div class="form-group"><label>From Email</label><input type="email" name="from_email" value="<?= $v('from_email') ?>" placeholder="hello@example.com"></div>
            </div>
        </div>
        <div class="nl-card">
            <h3>📝 Content</h3>
            <div class="ai-panel">
                <h4>✨ AI Content Assistant</h4>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <input type="text" id="ai-brief" placeholder="Describe your email content..." style="flex:1;min-width:200px;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:6px;font-size:.82rem">
                    <button type="button" class="btn-ai" onclick="aiContent()">✨ Generate</button>
                    <button type="button" class="btn-ai" onclick="aiImprove()">🔄 Improve Current</button>
                </div>
            </div>
            <div class="form-group"><label>HTML Content <button type="button" onclick="togglePreview()" style="background:none;border:none;color:#a5b4fc;cursor:pointer;font-size:.78rem;float:right">👁 Preview</button></label>
                <textarea id="content-editor" name="content_html"><?= h($isEdit ? ($campaign['content_html'] ?? '') : '') ?></textarea>
            </div>
            <div id="preview-container" style="display:none"><div class="preview-frame" id="preview-html"></div></div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap">
            <a href="/admin/newsletter/campaigns" class="btn-secondary">Cancel</a>
            <?php if ($isEdit): ?><button type="button" onclick="sendTest()" class="btn-secondary" style="cursor:pointer">📨 Send Test</button><?php endif; ?>
            <button type="submit" class="btn-nl">💾 <?= $isEdit ? 'Update' : 'Save Draft' ?></button>
        </div>
    </form>
</div>
<script>
function aiSubjects(){
    var topic=document.getElementById('subject').value||document.getElementById('ai-brief').value||'newsletter';
    fetch('/api/newsletter/ai-subjects',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({topic:topic}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(!d.ok)return;var el=document.getElementById('ai-subjects');el.style.display='flex';el.innerHTML='';
        (d.subjects||[]).forEach(function(s){var btn=document.createElement('div');btn.className='ai-suggestion';btn.textContent=s;btn.onclick=function(){document.getElementById('subject').value=s;};el.appendChild(btn);});
    });
}
function aiContent(){
    var brief=document.getElementById('ai-brief').value;if(!brief){alert('Enter a brief first');return;}
    fetch('/api/newsletter/ai-content',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({brief:brief}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok&&d.data){document.getElementById('content-editor').value=d.data.content_html||'';if(d.data.subject)document.getElementById('subject').value=d.data.subject;}
    });
}
function aiImprove(){
    var html=document.getElementById('content-editor').value;if(!html){alert('No content to improve');return;}
    fetch('/api/newsletter/ai-improve',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({content_html:html}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok&&d.data)document.getElementById('content-editor').value=d.data.improved_html||html;});
}
function togglePreview(){
    var c=document.getElementById('preview-container');var vis=c.style.display==='none';c.style.display=vis?'block':'none';
    if(vis)document.getElementById('preview-html').innerHTML=document.getElementById('content-editor').value;
}
<?php if ($isEdit): ?>
function sendTest(){var email=prompt('Send test email to:');if(email)fetch('/api/newsletter/send-test',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({campaign_id:<?= (int)$campaign['id'] ?>,email:email}),credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){alert(d.ok?'Test sent!':'Failed: '+(d.error||''))});}
<?php endif; ?>
</script>
<?php $content = ob_get_clean(); $title = $isEdit ? 'Edit Campaign' : 'New Campaign'; require CMS_APP . '/views/admin/layouts/topbar.php';
