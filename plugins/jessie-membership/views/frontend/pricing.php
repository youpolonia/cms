<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pricing</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;color:#1e293b;background:#f8fafc}
.pricing{max-width:1000px;margin:0 auto;padding:60px 20px;text-align:center}
.pricing h1{font-size:2rem;margin-bottom:8px}.pricing .sub{color:#64748b;margin-bottom:40px;font-size:1.1rem}
.plans{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;text-align:left}
.plan{background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:32px 24px;transition:.2s;position:relative}
.plan:hover{border-color:#6366f1;transform:translateY(-4px);box-shadow:0 12px 40px rgba(99,102,241,.15)}
.plan.popular{border-color:#6366f1}.plan.popular::after{content:"POPULAR";position:absolute;top:-12px;right:16px;background:#6366f1;color:#fff;padding:4px 12px;border-radius:6px;font-size:.7rem;font-weight:700}
.plan h3{font-size:1.2rem;margin-bottom:4px}
.plan .price{font-size:2.5rem;font-weight:800;color:#6366f1;margin:12px 0 4px}.plan .price span{font-size:1rem;font-weight:400;color:#64748b}
.plan .billing{font-size:.85rem;color:#94a3b8;margin-bottom:16px}
.plan .features{list-style:none;margin-bottom:24px}
.plan .features li{padding:6px 0;font-size:.9rem;color:#334155}.plan .features li::before{content:"✓ ";color:#10b981;font-weight:700}
.plan .trial{font-size:.82rem;color:#f59e0b;margin-bottom:12px}
.plan .cta{display:block;width:100%;padding:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;text-align:center;text-decoration:none}
.plan.free .cta{background:#e2e8f0;color:#334155}
</style></head><body>
<div class="pricing">
    <h1>Choose Your Plan</h1>
    <p class="sub">Start free, upgrade anytime.</p>
    <div class="plans" id="plans"></div>
</div>
<script>
fetch('/api/membership/plans').then(function(r){return r.json()}).then(function(d){
    if(!d.ok)return;var el=document.getElementById('plans');
    d.plans.forEach(function(p,i){
        var pop=i===Math.floor(d.plans.length/2);
        var h='<div class="plan'+(pop?' popular':'')+(parseFloat(p.price)===0?' free':'')+'" style="border-top:3px solid '+p.color+'">';
        h+='<h3>'+esc(p.name)+'</h3>';
        h+='<div class="price">'+(parseFloat(p.price)>0?'$'+parseFloat(p.price).toFixed(0)+'<span>/'+p.billing_period+'</span>':'Free')+'</div>';
        if(parseInt(p.trial_days)>0)h+='<div class="trial">'+p.trial_days+'-day free trial</div>';
        h+='<div class="billing">'+esc(p.description||'')+'</div>';
        if(p.features&&p.features.length){h+='<ul class="features">';p.features.forEach(function(f){h+='<li>'+esc(f)+'</li>';});h+='</ul>';}
        h+='<a href="/membership/signup?plan='+p.id+'" class="cta">Get Started</a></div>';
        el.innerHTML+=h;
    });
});
function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
</script></body></html>
