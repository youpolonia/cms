<?php /** Email Marketing — User App (Enhanced) */ ?>
<div class="saas-header"><h1>📧 Email Marketing</h1></div>
<div class="saas-content">

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid #334155;padding-bottom:12px">
  <button class="saas-btn saas-btn-primary em-tab" data-tab="campaigns" onclick="emTab(this,'campaigns')" style="font-size:.82rem;padding:8px 16px">📨 Campaigns</button>
  <button class="saas-btn saas-btn-ghost em-tab" data-tab="create" onclick="emTab(this,'create')" style="font-size:.82rem;padding:8px 16px">✍️ Create</button>
  <button class="saas-btn saas-btn-ghost em-tab" data-tab="lists" onclick="emTab(this,'lists')" style="font-size:.82rem;padding:8px 16px">👥 Lists</button>
  <button class="saas-btn saas-btn-ghost em-tab" data-tab="templates" onclick="emTab(this,'templates')" style="font-size:.82rem;padding:8px 16px">🎨 Templates</button>
  <button class="saas-btn saas-btn-ghost em-tab" data-tab="automations" onclick="emTab(this,'automations')" style="font-size:.82rem;padding:8px 16px">⚡ Automations</button>
</div>

<!-- TAB: Campaigns List -->
<div id="tab-campaigns">
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#8b5cf6" id="em-st-total">—</div><div style="color:#94a3b8;font-size:12px">Campaigns</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#22c55e" id="em-st-sent">—</div><div style="color:#94a3b8;font-size:12px">Sent</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#06b6d4" id="em-st-open">—</div><div style="color:#94a3b8;font-size:12px">Avg Open Rate</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#f59e0b" id="em-st-click">—</div><div style="color:#94a3b8;font-size:12px">Avg Click Rate</div></div>
</div>
<div class="saas-card"><h3 style="margin-bottom:12px">Recent Campaigns</h3><div id="em-list" style="font-size:13px">Loading...</div></div>
</div>

<!-- TAB: Create Campaign -->
<div id="tab-create" style="display:none">
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px">
<div>
  <div class="saas-card">
    <h3 style="margin-bottom:12px">New Campaign</h3>
    <div class="saas-form-group"><label class="saas-label">Campaign Name *</label><input class="saas-input" id="em-name" placeholder="Monthly Newsletter"></div>
    <div class="saas-form-group"><label class="saas-label">Subject Line *</label><input class="saas-input" id="em-subject" placeholder="Your weekly update 🚀"></div>
    <div class="saas-form-group"><label class="saas-label">Preview Text</label><input class="saas-input" id="em-preview" placeholder="Brief preview shown in inbox..."></div>
    <div class="saas-form-group"><label class="saas-label">Recipient List *</label><select class="saas-input" id="em-list-select"><option value="">Select a list...</option></select></div>
    <div class="saas-form-group"><label class="saas-label">Email Content</label><textarea class="saas-input" id="em-content" rows="10" placeholder="Write your email HTML or plain text..."></textarea></div>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="saas-btn saas-btn-primary" style="flex:1" onclick="emCreateCampaign('draft')">💾 Save Draft</button>
      <button class="saas-btn saas-btn-ghost" onclick="emCreateCampaign('scheduled')">📅 Schedule</button>
      <button class="saas-btn" style="background:#22c55e" onclick="emCreateCampaign('sending')">🚀 Send Now</button>
    </div>
  </div>
</div>
<div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">✨ AI Writer</h3>
    <input class="saas-input" id="em-ai-topic" placeholder="Newsletter topic...">
    <select class="saas-input" id="em-ai-tone" style="margin-top:8px"><option>professional</option><option>casual</option><option>promotional</option><option>educational</option></select>
    <button class="saas-btn saas-btn-primary" style="margin-top:8px;width:100%" onclick="emAI()">✨ Generate (3cr)</button>
    <div id="em-ai-out" style="margin-top:12px;font-size:13px"></div>
  </div>
  <div class="saas-card">
    <h3 style="margin-bottom:12px">A/B Testing</h3>
    <div class="saas-form-group"><label class="saas-label">Variant B Subject</label><input class="saas-input" id="em-ab-subject" placeholder="Alternative subject line"></div>
    <p style="font-size:11px;color:#64748b;margin-top:4px">Send variant B to 20% of recipients first, then winning subject to the rest.</p>
  </div>
</div>
</div>
</div>

<!-- TAB: Lists -->
<div id="tab-lists" style="display:none">
<div class="saas-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <h3>Subscriber Lists</h3>
    <button class="saas-btn saas-btn-primary" style="font-size:.82rem;padding:8px 16px" onclick="emCreateList()">+ New List</button>
  </div>
  <div id="em-lists-content" style="font-size:13px">Loading...</div>
</div>
</div>

<!-- TAB: Templates -->
<div id="tab-templates" style="display:none">
<div class="saas-card">
  <h3 style="margin-bottom:16px">Email Templates</h3>
  <div id="em-tpl-gallery" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
    <?php
    $templates = [
        ['📰 Newsletter','Clean layout for weekly/monthly updates','newsletter'],
        ['🛍️ Promotion','Bold design for sales and offers','promotion'],
        ['🎉 Announcement','Simple announcement template','announcement'],
        ['🙏 Welcome','Welcome email for new subscribers','welcome'],
        ['📊 Digest','Content digest with article cards','digest'],
        ['🎁 Holiday','Seasonal/holiday themed email','holiday'],
    ];
    foreach ($templates as $t): ?>
    <div style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;cursor:pointer;text-align:center;transition:.15s" onclick="emUseTemplate('<?= $t[2] ?>')" onmouseover="this.style.borderColor='#8b5cf6'" onmouseout="this.style.borderColor='#334155'">
      <div style="font-size:2rem;margin-bottom:8px"><?= explode(' ', $t[0])[0] ?></div>
      <div style="font-weight:600;font-size:.9rem;margin-bottom:4px"><?= substr($t[0], strpos($t[0], ' ') + 1) ?></div>
      <div style="font-size:.75rem;color:#64748b"><?= $t[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>

<!-- TAB: Automations -->
<div id="tab-automations" style="display:none">
<div class="saas-card">
  <h3 style="margin-bottom:16px">Email Automations</h3>
  <div id="em-automations" style="font-size:13px">Loading...</div>
  <div style="margin-top:16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
    <?php $autos = [
        ['🙏 Welcome Series','Send welcome emails to new subscribers','welcome_series'],
        ['🛒 Abandoned Cart','Remind customers about items left behind','abandoned_cart'],
        ['🎂 Birthday','Send birthday greetings with offers','birthday'],
        ['📧 Re-engagement','Win back inactive subscribers','re_engagement'],
    ]; foreach ($autos as $a): ?>
    <div style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:16px;cursor:pointer;transition:.15s" onclick="emCreateAutomation('<?= $a[2] ?>')" onmouseover="this.style.borderColor='#8b5cf6'" onmouseout="this.style.borderColor='#334155'">
      <div style="font-size:1.2rem;margin-bottom:6px"><?= explode(' ', $a[0])[0] ?></div>
      <div style="font-weight:600;font-size:.85rem;margin-bottom:2px"><?= substr($a[0], strpos($a[0], ' ') + 1) ?></div>
      <div style="font-size:.72rem;color:#64748b"><?= $a[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>

</div>
<script>
const emA='/api/email',emH={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key'] ?? '') ?>'};

function emTab(btn,tab){document.querySelectorAll('.em-tab').forEach(function(b){b.classList.remove('saas-btn-primary');b.classList.add('saas-btn-ghost')});
btn.classList.remove('saas-btn-ghost');btn.classList.add('saas-btn-primary');
['campaigns','create','lists','templates','automations'].forEach(function(t){document.getElementById('tab-'+t).style.display=t===tab?'':'none'});
if(tab==='lists')emLoadLists();if(tab==='automations')emLoadAutomations();}

async function emLoadCampaigns(){
const r=await(await fetch(emA+'/campaigns',{headers:emH})).json();
if(!r.success)return;var c=r.campaigns||[];
document.getElementById('em-st-total').textContent=c.length;
var sent=c.filter(function(x){return x.status==='sent'}).length;
document.getElementById('em-st-sent').textContent=sent;
var openRates=c.filter(function(x){return x.open_rate}).map(function(x){return parseFloat(x.open_rate)});
document.getElementById('em-st-open').textContent=openRates.length?Math.round(openRates.reduce(function(a,b){return a+b},0)/openRates.length)+'%':'—';
var clickRates=c.filter(function(x){return x.click_rate}).map(function(x){return parseFloat(x.click_rate)});
document.getElementById('em-st-click').textContent=clickRates.length?Math.round(clickRates.reduce(function(a,b){return a+b},0)/clickRates.length)+'%':'—';
var h='';c.slice(0,10).forEach(function(x){var sc={draft:'#64748b',scheduled:'#f59e0b',sending:'#8b5cf6',sent:'#22c55e',failed:'#ef4444'};
h+='<div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid #1e293b"><div><div style="font-weight:600;font-size:.9rem">'+x.name+'</div><div style="font-size:11px;color:#64748b">'+x.subject+'</div></div><span style="background:'+(sc[x.status]||'#64748b')+'20;color:'+(sc[x.status]||'#64748b')+';padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600">'+x.status+'</span></div>';});
document.getElementById('em-list').innerHTML=h||'No campaigns yet. Create one!';}

async function emCreateCampaign(status){
var data={name:document.getElementById('em-name').value,subject:document.getElementById('em-subject').value,
preview_text:document.getElementById('em-preview').value,list_id:document.getElementById('em-list-select').value,
content_html:document.getElementById('em-content').value,status:status};
if(!data.name||!data.subject){alert('Name and subject required');return;}
var r=await(await fetch(emA+'/campaigns',{method:'POST',headers:emH,body:JSON.stringify(data)})).json();
r.success?(alert(status==='sending'?'Campaign sent!':'Campaign saved!'),emLoadCampaigns(),emTab(document.querySelector('[data-tab="campaigns"]'),'campaigns')):alert(r.error);}

async function emAI(){var t=document.getElementById('em-ai-topic').value;if(!t)return;
document.getElementById('em-ai-out').innerHTML='<div style="color:#8b5cf6">Generating...</div>';
var r=await(await fetch(emA+'/ai-generate',{method:'POST',headers:emH,body:JSON.stringify({topic:t,tone:document.getElementById('em-ai-tone').value,format:'email'})})).json();
if(r.success){document.getElementById('em-subject').value=r.subject||'';document.getElementById('em-content').value=r.content||'';
document.getElementById('em-ai-out').innerHTML='<div style="color:#22c55e">✅ Generated! Subject and content filled.</div>';}
else document.getElementById('em-ai-out').innerHTML='<div style="color:#ef4444">'+r.error+'</div>';}

async function emLoadLists(){var r=await(await fetch(emA+'/lists',{headers:emH})).json();if(!r.success)return;
var h='',opts='<option value="">All subscribers</option>';
(r.lists||[]).forEach(function(l){h+='<div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid #1e293b"><div><strong>'+l.name+'</strong><span style="color:#64748b;font-size:11px;margin-left:8px">'+l.subscriber_count+' subscribers</span></div><span style="font-size:11px;color:#64748b">'+l.created_at.substring(0,10)+'</span></div>';
opts+='<option value="'+l.id+'">'+l.name+' ('+l.subscriber_count+')</option>';});
document.getElementById('em-lists-content').innerHTML=h||'No lists. Create one!';
document.getElementById('em-list-select').innerHTML=opts;}

function emCreateList(){var n=prompt('List name:');if(!n)return;
fetch(emA+'/lists',{method:'POST',headers:emH,body:JSON.stringify({name:n})}).then(function(r){return r.json()}).then(function(d){d.success?(alert('List created!'),emLoadLists()):alert(d.error)});}

function emUseTemplate(type){emTab(document.querySelector('[data-tab="create"]'),'create');
var templates={newsletter:'<h1>Weekly Newsletter</h1><p>Here\'s what happened this week...</p>',promotion:'<h1>🔥 Special Offer!</h1><p>Don\'t miss our limited-time deal.</p>',
announcement:'<h1>📢 Big News!</h1><p>We\'re excited to share...</p>',welcome:'<h1>Welcome aboard! 🎉</h1><p>Thanks for subscribing.</p>',
digest:'<h1>📊 Content Digest</h1><p>Top stories this week:</p>',holiday:'<h1>🎁 Happy Holidays!</h1><p>Season\'s greetings from our team.</p>'};
document.getElementById('em-content').value=templates[type]||'';}

async function emLoadAutomations(){var r=await(await fetch(emA+'/automations',{headers:emH})).json();
var h='';if(r.success&&r.automations){r.automations.forEach(function(a){var sc={active:'#22c55e',paused:'#f59e0b',draft:'#64748b'};
h+='<div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid #1e293b"><div><strong>'+a.name+'</strong><div style="font-size:11px;color:#64748b">Trigger: '+a.trigger_type+'</div></div><span style="background:'+(sc[a.status]||'#64748b')+'20;color:'+(sc[a.status]||'#64748b')+';padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600">'+a.status+'</span></div>';});}
document.getElementById('em-automations').innerHTML=h||'No automations set up yet.';}

function emCreateAutomation(type){alert('Automation "'+type+'" setup wizard coming soon!');}

emLoadCampaigns();emLoadLists();
</script>
