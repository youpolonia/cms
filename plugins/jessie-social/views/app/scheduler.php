<?php /** Social Media Scheduler — User App (Enhanced) */ ?>
<div class="saas-header"><h1>📱 Social Media Manager</h1></div>
<div class="saas-content">

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid #334155;padding-bottom:12px">
  <button class="saas-btn saas-btn-primary sm-tab active" data-tab="compose" onclick="smTab(this,'compose')" style="font-size:.82rem;padding:8px 16px">✍️ Compose</button>
  <button class="saas-btn saas-btn-ghost sm-tab" data-tab="calendar" onclick="smTab(this,'calendar')" style="font-size:.82rem;padding:8px 16px">📅 Calendar</button>
  <button class="saas-btn saas-btn-ghost sm-tab" data-tab="analytics" onclick="smTab(this,'analytics')" style="font-size:.82rem;padding:8px 16px">📊 Analytics</button>
  <button class="saas-btn saas-btn-ghost sm-tab" data-tab="hashtags" onclick="smTab(this,'hashtags')" style="font-size:.82rem;padding:8px 16px">#️⃣ Hashtags</button>
</div>

<!-- TAB: Compose -->
<div id="tab-compose">
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px">
<div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">Create Post</h3>
    <label class="saas-label">Platform</label>
    <select class="saas-input" id="sm-plat"><option value="instagram">Instagram</option><option value="facebook">Facebook</option><option value="twitter">Twitter/X</option><option value="linkedin">LinkedIn</option><option value="tiktok">TikTok</option></select>
    <label class="saas-label" style="margin-top:10px">Content</label>
    <textarea class="saas-input" id="sm-text" rows="4" placeholder="Write your post..." oninput="smCharCount()"></textarea>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-top:4px"><span id="sm-chars">0 characters</span><span id="sm-limit"></span></div>
    <label class="saas-label" style="margin-top:10px">Hashtags</label>
    <input class="saas-input" id="sm-hash" placeholder="#marketing #ai">
    <label class="saas-label" style="margin-top:10px">Schedule</label>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
      <input type="date" class="saas-input" id="sm-date"><input type="time" class="saas-input" id="sm-time" value="09:00">
    </div>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="saas-btn saas-btn-primary" style="flex:1" onclick="smSched()">📅 Schedule</button>
      <button class="saas-btn saas-btn-ghost" onclick="smNow()">⚡ Now</button>
    </div>
  </div>
  <div class="saas-card"><h3 style="margin-bottom:12px">Scheduled Posts</h3><div id="sm-list" style="font-size:13px;color:#64748b">Loading...</div></div>
</div>
<div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">AI Generate</h3>
    <input class="saas-input" id="sm-topic" placeholder="Topic to promote">
    <select class="saas-input" id="sm-tone" style="margin-top:8px"><option>professional</option><option>casual</option><option>funny</option><option>inspirational</option></select>
    <button class="saas-btn saas-btn-primary" style="margin-top:8px;width:100%" onclick="smAI()">✨ Generate (2cr)</button>
    <div id="sm-ai-out" style="margin-top:12px;font-size:13px"></div>
  </div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">Quick Templates</h3>
    <div id="sm-templates" style="font-size:13px">
      <?php $templates = [
        ['🚀 Product Launch', "🚀 Excited to announce {product}!\n\n{key_benefit}\n\n👉 Link in bio\n\n#launch #new"],
        ['📢 Promotion', "🔥 Limited time offer!\n\n{offer_details}\n\nDon't miss out ⏰\n\n#sale #deal"],
        ['💡 Tips', "💡 Pro tip: {tip}\n\nSave this for later! 🔖\n\n#tips #growth"],
        ['🎉 Milestone', "🎉 We just hit {milestone}!\n\nThank you for your support 🙏\n\n#milestone #thankyou"],
      ]; foreach ($templates as $t): ?>
      <div style="background:#0f172a;padding:10px;border-radius:8px;margin-bottom:6px;cursor:pointer;font-size:12px" onclick="document.getElementById('sm-text').value=this.dataset.tpl;smCharCount()" data-tpl="<?= htmlspecialchars($t[1]) ?>" title="Click to use">
        <strong><?= $t[0] ?></strong>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="saas-card"><h3 style="margin-bottom:12px">Accounts</h3><div id="sm-accts" style="font-size:13px;color:#64748b">No accounts connected</div><button class="saas-btn saas-btn-ghost" style="margin-top:8px;width:100%" onclick="alert('OAuth integration coming soon')">+ Connect Account</button></div>
</div>
</div>
</div>

<!-- TAB: Calendar -->
<div id="tab-calendar" style="display:none">
<div class="saas-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <button class="saas-btn saas-btn-ghost" style="font-size:.8rem;padding:6px 12px" onclick="smCalNav(-1)">← Prev</button>
    <h3 id="sm-cal-title" style="font-size:1rem"></h3>
    <button class="saas-btn saas-btn-ghost" style="font-size:.8rem;padding:6px 12px" onclick="smCalNav(1)">Next →</button>
  </div>
  <div id="sm-cal-grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;font-size:.82rem"></div>
</div>
</div>

<!-- TAB: Analytics -->
<div id="tab-analytics" style="display:none">
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#8b5cf6" id="sm-st-total">—</div><div style="color:#94a3b8;font-size:12px">Total Posts</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#22c55e" id="sm-st-pub">—</div><div style="color:#94a3b8;font-size:12px">Published</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#06b6d4" id="sm-st-sched">—</div><div style="color:#94a3b8;font-size:12px">Scheduled</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#f59e0b" id="sm-st-engage">—</div><div style="color:#94a3b8;font-size:12px">Engagement</div></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <div class="saas-card"><h3 style="margin-bottom:12px">Posts by Platform</h3><div id="sm-platform-stats" style="font-size:13px">Loading...</div></div>
  <div class="saas-card"><h3 style="margin-bottom:12px">Best Posting Times</h3><div id="sm-best-times" style="font-size:13px">Loading...</div></div>
</div>
</div>

<!-- TAB: Hashtags -->
<div id="tab-hashtags" style="display:none">
<div class="saas-card">
  <h3 style="margin-bottom:12px">Hashtag Research</h3>
  <div style="display:flex;gap:8px;margin-bottom:16px">
    <input class="saas-input" id="sm-hash-topic" placeholder="Enter a topic..." style="flex:1">
    <button class="saas-btn saas-btn-primary" onclick="smHashResearch()">🔍 Research (1cr)</button>
  </div>
  <div id="sm-hash-results" style="font-size:13px;color:#64748b">Enter a topic to find trending hashtags</div>
</div>
</div>

</div>
<script>
const smA='/api/social',smH={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key'] ?? '') ?>'};
const charLimits={twitter:280,instagram:2200,facebook:63206,linkedin:3000,tiktok:2200};
document.getElementById('sm-date').valueAsDate=new Date();

function smTab(btn,tab){document.querySelectorAll('.sm-tab').forEach(function(b){b.classList.remove('saas-btn-primary');b.classList.add('saas-btn-ghost')});btn.classList.remove('saas-btn-ghost');btn.classList.add('saas-btn-primary');
['compose','calendar','analytics','hashtags'].forEach(function(t){document.getElementById('tab-'+t).style.display=t===tab?'':'none'});
if(tab==='calendar')smCalRender();if(tab==='analytics')smLoadStats();}

function smCharCount(){var t=document.getElementById('sm-text').value.length,p=document.getElementById('sm-plat').value;
document.getElementById('sm-chars').textContent=t+' characters';document.getElementById('sm-limit').textContent='Limit: '+(charLimits[p]||'∞');}
function smBody(st){return{platform:document.getElementById('sm-plat').value,content:document.getElementById('sm-text').value,hashtags:document.getElementById('sm-hash').value,status:st};}
async function smSched(){const b=smBody('scheduled');b.scheduled_at=document.getElementById('sm-date').value+'T'+document.getElementById('sm-time').value;const r=await(await fetch(smA+'/posts',{method:'POST',headers:smH,body:JSON.stringify(b)})).json();r.success?(alert('Scheduled!'),smLoad()):alert(r.error);}
async function smNow(){const r=await(await fetch(smA+'/posts',{method:'POST',headers:smH,body:JSON.stringify(smBody('published'))})).json();r.success?(alert('Posted!'),smLoad()):alert(r.error);}
async function smAI(){const t=document.getElementById('sm-topic').value;if(!t)return;document.getElementById('sm-ai-out').innerHTML='Generating...';const r=await(await fetch(smA+'/ai-generate',{method:'POST',headers:smH,body:JSON.stringify({topic:t,platform:document.getElementById('sm-plat').value,tone:document.getElementById('sm-tone').value})})).json();document.getElementById('sm-ai-out').innerHTML=r.success?'<div style="background:#0f172a;padding:12px;border-radius:8px;cursor:pointer;white-space:pre-wrap" onclick="document.getElementById(\'sm-text\').value=this.textContent;smCharCount()" title="Click to use">'+r.post.content.replace(/</g,'&lt;')+'</div>':r.error;}
async function smLoad(){const r=await(await fetch(smA+'/posts?status=scheduled',{headers:smH})).json();if(!r.success)return;let h='';r.posts.forEach(function(p){h+='<div style="background:#0f172a;padding:10px;border-radius:8px;margin-bottom:8px"><div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="color:#8b5cf6;font-size:11px;font-weight:600">'+p.platform.toUpperCase()+'</span><span style="color:#64748b;font-size:11px">'+(p.scheduled_at||'').substring(0,16)+'</span></div><div style="font-size:12px">'+p.content.substring(0,120)+'</div></div>';});document.getElementById('sm-list').innerHTML=h||'No scheduled posts';}

// Calendar
var smCalMonth=new Date().getMonth(),smCalYear=new Date().getFullYear();
function smCalNav(d){smCalMonth+=d;if(smCalMonth>11){smCalMonth=0;smCalYear++;}if(smCalMonth<0){smCalMonth=11;smCalYear--;}smCalRender();}
async function smCalRender(){var M=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
document.getElementById('sm-cal-title').textContent=M[smCalMonth]+' '+smCalYear;
var grid=document.getElementById('sm-cal-grid'),html='';
['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(function(d){html+='<div style="font-size:11px;color:#64748b;padding:6px;font-weight:600">'+d+'</div>';});
var fd=new Date(smCalYear,smCalMonth,1).getDay(),dim=new Date(smCalYear,smCalMonth+1,0).getDate();
for(var i=0;i<fd;i++)html+='<div></div>';
var r=await(await fetch(smA+'/posts?month='+smCalYear+'-'+String(smCalMonth+1).padStart(2,'0'),{headers:smH})).json();
var postsByDay={};if(r.success)r.posts.forEach(function(p){var d=p.scheduled_at?p.scheduled_at.substring(8,10):p.created_at?p.created_at.substring(8,10):'';if(d){var dn=parseInt(d);if(!postsByDay[dn])postsByDay[dn]=[];postsByDay[dn].push(p);}});
for(var d=1;d<=dim;d++){var posts=postsByDay[d]||[];var bg=posts.length?'rgba(99,102,241,.15)':'transparent';var dots=posts.map(function(p){var c={instagram:'#e4405f',facebook:'#1877f2',twitter:'#1da1f2',linkedin:'#0a66c2',tiktok:'#fe2c55'};return'<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:'+(c[p.platform]||'#8b5cf6')+'"></span>'}).join(' ');
html+='<div style="padding:8px 4px;border-radius:6px;background:'+bg+';min-height:40px"><div style="font-weight:600;font-size:.82rem">'+d+'</div>'+(dots?'<div style="margin-top:2px">'+dots+'</div>':'')+'</div>';}
grid.innerHTML=html;}

// Analytics
async function smLoadStats(){
var r=await(await fetch(smA+'/posts?per_page=999',{headers:smH})).json();if(!r.success)return;
var total=r.posts.length,pub=0,sched=0,platforms={};
r.posts.forEach(function(p){if(p.status==='published')pub++;if(p.status==='scheduled')sched++;if(!platforms[p.platform])platforms[p.platform]=0;platforms[p.platform]++;});
document.getElementById('sm-st-total').textContent=total;document.getElementById('sm-st-pub').textContent=pub;
document.getElementById('sm-st-sched').textContent=sched;document.getElementById('sm-st-engage').textContent='—';
var ph='';Object.keys(platforms).forEach(function(k){ph+='<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e293b"><span>'+k+'</span><span style="color:#8b5cf6;font-weight:600">'+platforms[k]+'</span></div>';});
document.getElementById('sm-platform-stats').innerHTML=ph||'No data';
document.getElementById('sm-best-times').innerHTML='<div style="color:#64748b">Post analytics will appear as you publish more content.</div>';}

// Hashtag research
async function smHashResearch(){var t=document.getElementById('sm-hash-topic').value;if(!t)return;
document.getElementById('sm-hash-results').innerHTML='<div style="color:#8b5cf6">Researching...</div>';
var r=await(await fetch(smA+'/hashtag-research',{method:'POST',headers:smH,body:JSON.stringify({topic:t})})).json();
if(r.success&&r.hashtags){var h='<div style="display:flex;flex-wrap:wrap;gap:6px">';r.hashtags.forEach(function(tag){h+='<span style="background:#0f172a;padding:4px 10px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid #334155" onclick="var el=document.getElementById(\'sm-hash\');el.value+=(el.value?\' \':\'\')+this.textContent">#'+tag+'</span>';});h+='</div>';document.getElementById('sm-hash-results').innerHTML=h;}
else document.getElementById('sm-hash-results').innerHTML='<div style="color:#ef4444">'+(r.error||'No results')+'</div>';}

smLoad();
</script>
