<?php /** SEO Writer — User App */ ?>
<div class="saas-header"><h1>🔍 SEO Writer</h1></div>
<div class="saas-content">
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px">
<div>
  <div class="saas-card" style="margin-bottom:16px">
    <h3 style="margin-bottom:12px">Article Generator</h3>
    <label class="saas-label">Target Keyword</label>
    <input class="saas-input" id="sw-keyword" placeholder="e.g. best coffee machines 2026">
    <label class="saas-label" style="margin-top:12px">Language</label>
    <select class="saas-input" id="sw-lang"><option value="en">English</option><option value="pl">Polski</option><option value="de">Deutsch</option><option value="es">Español</option><option value="fr">Français</option></select>
    <label class="saas-label" style="margin-top:12px">Tone</label>
    <select class="saas-input" id="sw-tone"><option>professional</option><option>casual</option><option>technical</option><option>friendly</option></select>
    <label class="saas-label" style="margin-top:12px">Outline (optional)</label>
    <textarea class="saas-input" id="sw-outline" rows="3" placeholder="H2 sections to cover..."></textarea>
    <button class="saas-btn saas-btn-primary" style="margin-top:16px;width:100%" onclick="swGenerate()" id="sw-btn">✨ Generate Article (5 credits)</button>
  </div>
  <div class="saas-card" id="sw-result" style="display:none">
    <h3 style="margin-bottom:8px" id="sw-title"></h3>
    <p style="color:#94a3b8;font-size:13px;margin-bottom:16px" id="sw-meta"></p>
    <div id="sw-body" style="line-height:1.8;font-size:14px"></div>
  </div>
</div>
<div>
  <div class="saas-card" style="position:sticky;top:20px">
    <h3 style="margin-bottom:12px">Live SEO Score</h3>
    <div style="text-align:center;margin-bottom:16px">
      <div id="sw-score" style="font-size:48px;font-weight:700;color:#8b5cf6">—</div>
      <div id="sw-summary" style="color:#94a3b8;font-size:13px">Enter content to score</div>
    </div>
    <div id="sw-checks"></div>
    <hr style="border-color:#334155;margin:16px 0">
    <h4 style="margin-bottom:8px;font-size:13px;color:#94a3b8">KEYWORD RESEARCH</h4>
    <input class="saas-input" id="sw-kw-seed" placeholder="Seed keyword">
    <button class="saas-btn saas-btn-ghost" style="margin-top:8px;width:100%" onclick="swKeywords()">🔬 Research (2 credits)</button>
    <div id="sw-keywords" style="margin-top:12px;font-size:12px"></div>
  </div>
</div>
</div>
</div>
<script>
const API='/api/seowriter';
const hdr={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key']??'') ?>'};
async function swGenerate(){
  const btn=document.getElementById('sw-btn');btn.disabled=true;btn.textContent='Generating...';
  try{const r=await fetch(API+'/generate',{method:'POST',headers:hdr,body:JSON.stringify({keyword:document.getElementById('sw-keyword').value,language:document.getElementById('sw-lang').value,tone:document.getElementById('sw-tone').value,outline:document.getElementById('sw-outline').value})});
  const d=await r.json();
  if(d.success){document.getElementById('sw-result').style.display='block';document.getElementById('sw-title').textContent=d.title;document.getElementById('sw-meta').textContent=d.meta_description;document.getElementById('sw-body').innerHTML=d.body.replace(/\n/g,'<br>');document.getElementById('sw-score').textContent=d.seo_score+'%';document.getElementById('sw-score').style.color=d.seo_score>=70?'#22c55e':d.seo_score>=40?'#f59e0b':'#ef4444';
  let checks='';d.checks.forEach(c=>{const col=c.status==='good'?'#22c55e':c.status==='warning'?'#f59e0b':'#ef4444';checks+=`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px"><span>${c.name}</span><span style="color:${col}">${c.score}/${c.max}</span></div>`;});document.getElementById('sw-checks').innerHTML=checks;
  }else alert(d.error);}catch(e){alert('Error: '+e.message)}
  btn.disabled=false;btn.textContent='✨ Generate Article (5 credits)';
}
async function swKeywords(){
  const seed=document.getElementById('sw-kw-seed').value;if(!seed)return;
  document.getElementById('sw-keywords').innerHTML='<div style="color:#94a3b8">Researching...</div>';
  const r=await fetch(API+'/keywords',{method:'POST',headers:hdr,body:JSON.stringify({keyword:seed,language:document.getElementById('sw-lang').value})});
  const d=await r.json();
  if(d.success){let h='';['primary','long_tail','questions'].forEach(t=>{if(d.keywords[t]&&d.keywords[t].length){h+=`<div style="margin-top:8px;font-weight:600;color:#8b5cf6;text-transform:uppercase">${t.replace('_',' ')}</div>`;d.keywords[t].forEach(k=>{h+=`<div style="padding:2px 0;color:#e2e8f0">${k.keyword}</div>`});}});document.getElementById('sw-keywords').innerHTML=h;}
  else document.getElementById('sw-keywords').innerHTML=`<div style="color:#ef4444">${d.error}</div>`;
}
</script>
