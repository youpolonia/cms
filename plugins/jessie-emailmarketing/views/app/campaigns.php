<?php /** Email Marketing — User App */ ?>
<div class="saas-header"><h1>📧 Email Marketing</h1></div>
<div class="saas-content">
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  <div class="saas-card" style="text-align:center;cursor:pointer" onclick="emTab('campaigns')"><div style="font-size:24px">📨</div><div style="font-size:12px;color:#94a3b8;margin-top:4px">Campaigns</div></div>
  <div class="saas-card" style="text-align:center;cursor:pointer" onclick="emTab('lists')"><div style="font-size:24px">📋</div><div style="font-size:12px;color:#94a3b8;margin-top:4px">Lists</div></div>
  <div class="saas-card" style="text-align:center;cursor:pointer" onclick="emTab('templates')"><div style="font-size:24px">📝</div><div style="font-size:12px;color:#94a3b8;margin-top:4px">Templates</div></div>
  <div class="saas-card" style="text-align:center;cursor:pointer" onclick="emTab('compose')"><div style="font-size:24px">✨</div><div style="font-size:12px;color:#94a3b8;margin-top:4px">AI Compose</div></div>
</div>

<div id="em-campaigns" class="em-panel">
  <div class="saas-card"><h3 style="margin-bottom:12px">Your Campaigns</h3><div id="em-camp-list" style="font-size:13px">Loading...</div></div>
</div>

<div id="em-lists" class="em-panel" style="display:none">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="saas-card">
      <h3 style="margin-bottom:12px">Subscriber Lists</h3>
      <div id="em-list-data" style="font-size:13px">Loading...</div>
    </div>
    <div class="saas-card">
      <h3 style="margin-bottom:12px">Create List</h3>
      <input class="saas-input" id="em-list-name" placeholder="List name">
      <textarea class="saas-input" id="em-list-desc" rows="2" placeholder="Description" style="margin-top:8px"></textarea>
      <button class="saas-btn saas-btn-primary" style="margin-top:8px;width:100%" onclick="emCreateList()">Create List</button>
    </div>
  </div>
</div>

<div id="em-templates" class="em-panel" style="display:none">
  <div class="saas-card"><h3 style="margin-bottom:12px">Email Templates</h3><div id="em-tpl-list" style="font-size:13px">Loading...</div></div>
</div>

<div id="em-compose" class="em-panel" style="display:none">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="saas-card">
      <h3 style="margin-bottom:12px">AI Email Composer</h3>
      <label class="saas-label">Topic / Product</label>
      <input class="saas-input" id="em-ai-topic" placeholder="Product launch, newsletter, promo...">
      <label class="saas-label" style="margin-top:10px">Audience</label>
      <input class="saas-input" id="em-ai-audience" placeholder="e.g. existing customers, new leads">
      <label class="saas-label" style="margin-top:10px">Tone</label>
      <select class="saas-input" id="em-ai-tone"><option>professional</option><option>casual</option><option>friendly</option><option>urgent</option><option>promotional</option></select>
      <label class="saas-label" style="margin-top:10px">Type</label>
      <select class="saas-input" id="em-ai-type"><option value="newsletter">Newsletter</option><option value="promo">Promotion</option><option value="welcome">Welcome</option><option value="followup">Follow-up</option><option value="announcement">Announcement</option></select>
      <button class="saas-btn saas-btn-primary" style="margin-top:16px;width:100%" onclick="emGenerate()">✨ Generate Email (3cr)</button>
    </div>
    <div class="saas-card" id="em-ai-result">
      <h3 style="margin-bottom:12px">Preview</h3>
      <div id="em-ai-preview" style="font-size:13px;color:#64748b">Generate an email to see preview</div>
    </div>
  </div>
</div>
</div>

<script>
const emA='/api/emailmarketing',emH={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key'] ?? '') ?>'};
function emTab(t){document.querySelectorAll('.em-panel').forEach(function(p){p.style.display='none';});document.getElementById('em-'+t).style.display='block';}

async function emLoadCampaigns(){const r=await(await fetch(emA+'/campaigns',{headers:emH})).json();if(!r.success)return;let h='';r.campaigns.forEach(function(c){const col=c.status==='sent'?'#22c55e':c.status==='scheduled'?'#3b82f6':'#94a3b8';h+='<div style="background:#0f172a;padding:12px;border-radius:8px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center"><div><div style="font-weight:600">'+c.name+'</div><div style="color:#94a3b8;font-size:12px">'+c.subject+'</div></div><div style="text-align:right"><span style="color:'+col+';font-size:11px;font-weight:600">'+c.status.toUpperCase()+'</span><div style="color:#64748b;font-size:11px">Sent: '+(c.total_sent||0)+'</div></div></div>';});document.getElementById('em-camp-list').innerHTML=h||'No campaigns yet. Use AI Compose to create one!';}

async function emLoadLists(){const r=await(await fetch(emA+'/lists',{headers:emH})).json();if(!r.success)return;let h='';r.lists.forEach(function(l){h+='<div style="background:#0f172a;padding:10px;border-radius:8px;margin-bottom:8px;display:flex;justify-content:space-between"><span>'+l.name+'</span><span style="color:#8b5cf6">'+(l.subscriber_count||0)+' subs</span></div>';});document.getElementById('em-list-data').innerHTML=h||'No lists yet';}

async function emCreateList(){const n=document.getElementById('em-list-name').value;if(!n)return;const r=await(await fetch(emA+'/lists',{method:'POST',headers:emH,body:JSON.stringify({name:n,description:document.getElementById('em-list-desc').value})})).json();r.success?(alert('List created!'),emLoadLists()):alert(r.error);}

async function emLoadTemplates(){const r=await(await fetch(emA+'/templates',{headers:emH})).json();if(!r.success)return;let h='';r.templates.forEach(function(t){h+='<div style="background:#0f172a;padding:10px;border-radius:8px;margin-bottom:8px"><div style="font-weight:600">'+t.name+'</div><div style="color:#64748b;font-size:12px">'+t.category+'</div></div>';});document.getElementById('em-tpl-list').innerHTML=h||'No templates yet';}

async function emGenerate(){const topic=document.getElementById('em-ai-topic').value;if(!topic)return;document.getElementById('em-ai-preview').innerHTML='<div style="color:#8b5cf6">Generating...</div>';const r=await(await fetch(emA+'/ai-generate',{method:'POST',headers:emH,body:JSON.stringify({topic:topic,audience:document.getElementById('em-ai-audience').value,tone:document.getElementById('em-ai-tone').value,type:document.getElementById('em-ai-type').value})})).json();if(r.success){document.getElementById('em-ai-preview').innerHTML='<div style="margin-bottom:8px"><span style="color:#8b5cf6;font-size:11px;font-weight:600">SUBJECT</span><div style="background:#0f172a;padding:8px;border-radius:6px;margin-top:4px">'+r.subject+'</div></div><div><span style="color:#8b5cf6;font-size:11px;font-weight:600">BODY</span><div style="background:#0f172a;padding:12px;border-radius:6px;margin-top:4px;white-space:pre-wrap;max-height:400px;overflow-y:auto">'+r.body.replace(/</g,'&lt;')+'</div></div><button class="saas-btn saas-btn-primary" style="margin-top:12px;width:100%" onclick="alert(\'Save as campaign coming soon\')">💾 Save as Campaign</button>';}else{document.getElementById('em-ai-preview').innerHTML='<div style="color:#ef4444">'+r.error+'</div>';}}

emLoadCampaigns();emLoadLists();emLoadTemplates();
</script>
