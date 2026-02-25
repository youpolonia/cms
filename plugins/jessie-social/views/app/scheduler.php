<?php /** Social Media Scheduler — User App */ ?>
<div class="saas-header"><h1>📱 Social Media Manager</h1></div>
<div class="saas-content">
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px">
<div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">Create Post</h3>
    <label class="saas-label">Platform</label>
    <select class="saas-input" id="sm-plat"><option value="instagram">Instagram</option><option value="facebook">Facebook</option><option value="twitter">Twitter/X</option><option value="linkedin">LinkedIn</option><option value="tiktok">TikTok</option></select>
    <label class="saas-label" style="margin-top:10px">Content</label>
    <textarea class="saas-input" id="sm-text" rows="4" placeholder="Write your post..."></textarea>
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
  <div class="saas-card"><h3 style="margin-bottom:12px">Accounts</h3><div id="sm-accts" style="font-size:13px;color:#64748b">No accounts connected</div><button class="saas-btn saas-btn-ghost" style="margin-top:8px;width:100%" onclick="alert('OAuth integration coming soon')">+ Connect Account</button></div>
</div>
</div></div>
<script>
const smA='/api/social',smH={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key'] ?? '') ?>'};
document.getElementById('sm-date').valueAsDate=new Date();
function smBody(st){return{platform:document.getElementById('sm-plat').value,content:document.getElementById('sm-text').value,hashtags:document.getElementById('sm-hash').value,status:st};}
async function smSched(){const b=smBody('scheduled');b.scheduled_at=document.getElementById('sm-date').value+'T'+document.getElementById('sm-time').value;const r=await(await fetch(smA+'/posts',{method:'POST',headers:smH,body:JSON.stringify(b)})).json();r.success?(alert('Scheduled!'),smLoad()):alert(r.error);}
async function smNow(){const r=await(await fetch(smA+'/posts',{method:'POST',headers:smH,body:JSON.stringify(smBody('published'))})).json();r.success?(alert('Posted!'),smLoad()):alert(r.error);}
async function smAI(){const t=document.getElementById('sm-topic').value;if(!t)return;document.getElementById('sm-ai-out').innerHTML='Generating...';const r=await(await fetch(smA+'/ai-generate',{method:'POST',headers:smH,body:JSON.stringify({topic:t,platform:document.getElementById('sm-plat').value,tone:document.getElementById('sm-tone').value})})).json();document.getElementById('sm-ai-out').innerHTML=r.success?'<div style="background:#0f172a;padding:12px;border-radius:8px;cursor:pointer;white-space:pre-wrap" onclick="document.getElementById(\'sm-text\').value=this.textContent" title="Click to use">'+r.post.content.replace(/</g,'&lt;')+'</div>':r.error;}
async function smLoad(){const r=await(await fetch(smA+'/posts?status=scheduled',{headers:smH})).json();if(!r.success)return;let h='';r.posts.forEach(function(p){h+='<div style="background:#0f172a;padding:10px;border-radius:8px;margin-bottom:8px"><div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="color:#8b5cf6;font-size:11px;font-weight:600">'+p.platform.toUpperCase()+'</span><span style="color:#64748b;font-size:11px">'+(p.scheduled_at||'').substring(0,16)+'</span></div><div>'+p.content.substring(0,100)+'</div></div>';});document.getElementById('sm-list').innerHTML=h||'No scheduled posts';}
smLoad();
</script>
