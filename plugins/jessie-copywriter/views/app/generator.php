<?php /** AI Copywriter — User App */ ?>
<div class="saas-header"><h1>✍️ AI Copywriter</h1></div>
<div class="saas-content">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div class="saas-card">
  <h3 style="margin-bottom:12px">Generate Product Copy</h3>
  <label class="saas-label">Product Name *</label>
  <input class="saas-input" id="cw-name" placeholder="e.g. Premium Wireless Headphones">
  <label class="saas-label" style="margin-top:10px">Features & Details</label>
  <textarea class="saas-input" id="cw-features" rows="4" placeholder="Active noise cancellation, 40hr battery, Bluetooth 5.3..."></textarea>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
    <div><label class="saas-label">Platform</label><select class="saas-input" id="cw-platform"><option value="general">General</option><option value="amazon">Amazon</option><option value="shopify">Shopify</option><option value="ebay">eBay</option><option value="etsy">Etsy</option><option value="google_ads">Google Ads</option><option value="facebook_ads">Facebook Ads</option></select></div>
    <div><label class="saas-label">Category</label><select class="saas-input" id="cw-cat"><option value="electronics">Electronics</option><option value="clothing">Clothing</option><option value="home">Home & Garden</option><option value="beauty">Beauty</option><option value="sports">Sports</option><option value="food">Food</option><option value="health">Health</option><option value="jewelry">Jewelry</option><option value="other">Other</option></select></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px">
    <div><label class="saas-label">Tone</label><select class="saas-input" id="cw-tone"><option>professional</option><option>casual</option><option>luxury</option><option>friendly</option><option>technical</option><option>seo</option><option>persuasive</option><option>minimal</option></select></div>
    <div><label class="saas-label">Brand Voice</label><select class="saas-input" id="cw-brand"><option value="0">None</option></select></div>
  </div>
  <button class="saas-btn saas-btn-primary" style="margin-top:16px;width:100%" onclick="cwGenerate()" id="cw-btn">✨ Generate Copy (3 credits)</button>
</div>
<div>
  <div class="saas-card" id="cw-result" style="display:none">
    <h3 style="margin-bottom:12px">Generated Copy</h3>
    <div id="cw-sections"></div>
  </div>
  <div class="saas-card" style="margin-top:16px" id="cw-rewrite-box" style="display:none">
    <h3 style="margin-bottom:12px">Rewrite Existing Copy</h3>
    <textarea class="saas-input" id="cw-rewrite-text" rows="4" placeholder="Paste existing product copy here..."></textarea>
    <select class="saas-input" id="cw-rewrite-mode" style="margin-top:8px"><option>professional</option><option>casual</option><option>luxury</option><option>friendly</option><option>seo</option><option>persuasive</option><option>minimal</option></select>
    <button class="saas-btn saas-btn-ghost" style="margin-top:8px;width:100%" onclick="cwRewrite()">🔄 Rewrite (2 credits)</button>
    <div id="cw-rewrite-result" style="margin-top:12px;font-size:13px;white-space:pre-wrap"></div>
  </div>
</div>
</div>
</div>
<script>
const API='/api/copywriter';
const hdr={'Content-Type':'application/json','X-API-Key':'<?= htmlspecialchars($user['api_key']??'') ?>'};
// Load brands
fetch(API+'/brands',{headers:hdr}).then(r=>r.json()).then(d=>{if(d.success&&d.brands.length){const s=document.getElementById('cw-brand');d.brands.forEach(b=>{const o=document.createElement('option');o.value=b.id;o.textContent=b.name;s.appendChild(o)});}});
async function cwGenerate(){
  const btn=document.getElementById('cw-btn');btn.disabled=true;btn.textContent='Generating...';
  try{const r=await fetch(API+'/generate',{method:'POST',headers:hdr,body:JSON.stringify({name:document.getElementById('cw-name').value,features:document.getElementById('cw-features').value,platform:document.getElementById('cw-platform').value,category:document.getElementById('cw-cat').value,tone:document.getElementById('cw-tone').value,brand_id:parseInt(document.getElementById('cw-brand').value)})});
  const d=await r.json();
  if(d.success){document.getElementById('cw-result').style.display='block';
    let html='';
    const secs=[['Title',d.title],['Description',d.description],['Bullet Points',d.bullet_points],['Meta Title',d.meta_title],['Meta Description',d.meta_description],['Tags',d.tags]];
    secs.forEach(([l,v])=>{if(v){html+=`<div style="margin-bottom:16px"><div style="font-size:11px;color:#8b5cf6;font-weight:600;text-transform:uppercase;margin-bottom:4px">${l}</div><div style="background:#0f172a;padding:12px;border-radius:8px;font-size:13px;white-space:pre-wrap;cursor:pointer" onclick="navigator.clipboard.writeText(this.textContent)" title="Click to copy">${v.replace(/</g,'&lt;')}</div></div>`}});
    document.getElementById('cw-sections').innerHTML=html;
  }else alert(d.error);}catch(e){alert(e.message)}
  btn.disabled=false;btn.textContent='✨ Generate Copy (3 credits)';
}
async function cwRewrite(){
  const text=document.getElementById('cw-rewrite-text').value;if(!text)return;
  document.getElementById('cw-rewrite-result').textContent='Rewriting...';
  const r=await fetch(API+'/rewrite',{method:'POST',headers:hdr,body:JSON.stringify({text,mode:document.getElementById('cw-rewrite-mode').value})});
  const d=await r.json();document.getElementById('cw-rewrite-result').textContent=d.success?d.rewritten:d.error;
}
</script>
