<?php /** Analytics — User App */ ?>
<div class="saas-header"><h1>📊 Analytics Dashboard</h1></div>
<div class="saas-content">
<div style="display:flex;gap:8px;margin-bottom:20px">
  <select class="saas-input" id="an-range" style="width:auto" onchange="anLoad()">
    <option value="7">Last 7 days</option><option value="30" selected>Last 30 days</option><option value="90">Last 90 days</option><option value="365">Last year</option>
  </select>
  <button class="saas-btn saas-btn-ghost" onclick="anInsights()">🤖 AI Insights (5cr)</button>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#8b5cf6" id="an-pv">—</div><div style="color:#94a3b8;font-size:12px">Pageviews</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#06b6d4" id="an-sess">—</div><div style="color:#94a3b8;font-size:12px">Sessions</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#22c55e" id="an-conv">—</div><div style="color:#94a3b8;font-size:12px">Conversions</div></div>
  <div class="saas-card" style="text-align:center"><div style="font-size:28px;font-weight:700;color:#f59e0b" id="an-total">—</div><div style="color:#94a3b8;font-size:12px">Total Events</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="saas-card">
    <h3 style="margin-bottom:12px">Top Pages</h3>
    <div id="an-pages" style="font-size:13px">Loading...</div>
  </div>
  <div class="saas-card">
    <h3 style="margin-bottom:12px">Top Referrers</h3>
    <div id="an-refs" style="font-size:13px">Loading...</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="saas-card">
    <h3 style="margin-bottom:12px">Devices</h3>
    <div id="an-dev" style="font-size:13px">Loading...</div>
  </div>
  <div class="saas-card">
    <h3 style="margin-bottom:12px">Daily Trend</h3>
    <div id="an-trend" style="font-size:13px;max-height:250px;overflow-y:auto">Loading...</div>
  </div>
</div>

<div class="saas-card" id="an-insights-box" style="display:none">
  <h3 style="margin-bottom:12px">🤖 AI Insights</h3>
  <div id="an-insights"></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px">
  <div class="saas-card">
    <h3 style="margin-bottom:12px">Goals</h3>
    <div id="an-goals" style="font-size:13px;margin-bottom:12px">Loading...</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
      <input class="saas-input" id="an-goal-name" placeholder="Goal name">
      <input class="saas-input" id="an-goal-target" type="number" placeholder="Target value">
    </div>
    <button class="saas-btn saas-btn-ghost" style="margin-top:8px;width:100%" onclick="anCreateGoal()">+ Add Goal</button>
  </div>
  <div class="saas-card">
    <h3 style="margin-bottom:12px">Tracking Snippet</h3>
    <p style="color:#94a3b8;font-size:12px;margin-bottom:8px">Add this to your website to start tracking:</p>
    <pre style="background:#0f172a;padding:12px;border-radius:8px;font-size:11px;color:#22c55e;overflow-x:auto;cursor:pointer" onclick="navigator.clipboard.writeText(this.textContent)" title="Click to copy">&lt;script&gt;
(function(k){
  var s=Math.random().toString(36).substr(2);
  function t(e,m){
    fetch('/api/analytics/track',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-API-Key':k},
      body:JSON.stringify({event_type:e,page_url:location.href,
        referrer:document.referrer,session_id:s,
        user_agent:navigator.userAgent,
        device:(/Mobi/.test(navigator.userAgent)?'mobile':'desktop'),
        metadata:m||{}})
    });
  }
  t('pageview');
  window.jTrack=function(e,m){t(e,m)};
})('<?= htmlspecialchars($user['api_key'] ?? 'YOUR_API_KEY') ?>');
&lt;/script&gt;</pre>
  </div>
</div>
</div>

<script>
const anA='/api/analytics',anH={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key'] ?? '') ?>'};
function anDates(){const d=parseInt(document.getElementById('an-range').value);const e=new Date(),s=new Date();s.setDate(s.getDate()-d);return{start:s.toISOString().substring(0,10),end:e.toISOString().substring(0,10)+' 23:59:59'};}
async function anLoad(){
  const{start,end}=anDates();
  const ov=await(await fetch(anA+'/overview?start='+start+'&end='+end,{headers:anH})).json();
  if(ov.success){const o=ov.overview;document.getElementById('an-pv').textContent=o.pageviews.toLocaleString();document.getElementById('an-sess').textContent=o.sessions.toLocaleString();document.getElementById('an-conv').textContent=o.conversions.toLocaleString();document.getElementById('an-total').textContent=o.total_events.toLocaleString();}
  const pg=await(await fetch(anA+'/top-pages?start='+start+'&end='+end,{headers:anH})).json();
  if(pg.success){let h='';pg.pages.forEach(function(p){h+='<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e293b"><span style="color:#e2e8f0;max-width:70%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+p.page_url+'</span><span style="color:#8b5cf6;font-weight:600">'+p.views+'</span></div>';});document.getElementById('an-pages').innerHTML=h||'No data yet';}
  const rf=await(await fetch(anA+'/top-referrers?start='+start+'&end='+end,{headers:anH})).json();
  if(rf.success){let h='';rf.referrers.forEach(function(r){h+='<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e293b"><span style="color:#e2e8f0;max-width:70%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+r.referrer+'</span><span style="color:#06b6d4;font-weight:600">'+r.visits+'</span></div>';});document.getElementById('an-refs').innerHTML=h||'No referrer data';}
  const dv=await(await fetch(anA+'/devices?start='+start+'&end='+end,{headers:anH})).json();
  if(dv.success){let h='';const total=dv.devices.reduce(function(s,d){return s+parseInt(d.cnt)},0)||1;dv.devices.forEach(function(d){const pct=Math.round(d.cnt/total*100);h+='<div style="margin-bottom:8px"><div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px"><span>'+d.device+'</span><span style="color:#8b5cf6">'+pct+'%</span></div><div style="background:#0f172a;border-radius:4px;height:6px"><div style="background:#8b5cf6;height:100%;border-radius:4px;width:'+pct+'%"></div></div></div>';});document.getElementById('an-dev').innerHTML=h||'No device data';}
  const tr=await(await fetch(anA+'/trend?start='+start+'&end='+end,{headers:anH})).json();
  if(tr.success){let h='';tr.trend.forEach(function(t){h+='<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #1e293b;font-size:12px"><span>'+t.day+'</span><span style="color:#94a3b8">'+t.events+' events</span><span style="color:#8b5cf6">'+t.pageviews+' pv</span></div>';});document.getElementById('an-trend').innerHTML=h||'No trend data';}
  anLoadGoals();
}
async function anLoadGoals(){const r=await(await fetch(anA+'/goals',{headers:anH})).json();if(!r.success)return;let h='';r.goals.forEach(function(g){const pct=g.target_value?Math.min(100,Math.round(g.current_value/g.target_value*100)):0;h+='<div style="background:#0f172a;padding:10px;border-radius:8px;margin-bottom:8px"><div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="font-weight:600">'+g.name+'</span><span style="color:#8b5cf6">'+g.current_value+'/'+g.target_value+'</span></div><div style="background:#1e293b;border-radius:4px;height:6px"><div style="background:linear-gradient(90deg,#8b5cf6,#06b6d4);height:100%;border-radius:4px;width:'+pct+'%"></div></div></div>';});document.getElementById('an-goals').innerHTML=h||'No goals set';}
async function anCreateGoal(){const n=document.getElementById('an-goal-name').value,t=parseInt(document.getElementById('an-goal-target').value);if(!n||!t)return;const r=await(await fetch(anA+'/goals',{method:'POST',headers:anH,body:JSON.stringify({name:n,target_value:t,event_type:'conversion'})})).json();r.success?(alert('Goal created!'),anLoadGoals()):alert(r.error);}
async function anInsights(){document.getElementById('an-insights-box').style.display='block';document.getElementById('an-insights').innerHTML='<div style="color:#8b5cf6">Analyzing your data...</div>';const{start,end}=anDates();const r=await(await fetch(anA+'/insights',{method:'POST',headers:anH,body:JSON.stringify({start:start,end:end})})).json();if(r.success){let h='';r.insights.forEach(function(i){const col=i.priority==='high'?'#ef4444':i.priority==='medium'?'#f59e0b':'#22c55e';h+='<div style="background:#0f172a;padding:12px;border-radius:8px;margin-bottom:8px;border-left:3px solid '+col+'"><div style="font-weight:600;margin-bottom:4px">'+i.title+'</div><div style="color:#94a3b8;font-size:13px;margin-bottom:4px">'+i.description+'</div><div style="color:#8b5cf6;font-size:12px">Action: '+i.action+'</div></div>';});document.getElementById('an-insights').innerHTML=h||'No insights available';}else document.getElementById('an-insights').innerHTML='<div style="color:#ef4444">'+r.error+'</div>';}
anLoad();
</script>
