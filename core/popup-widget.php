<?php
/**
 * Popup Widget — Frontend JS+CSS for displaying popups
 * Self-contained, no external dependencies
 *
 * Usage: echo cms_popup_widget();
 */

if (!function_exists('cms_popup_widget')) {
    function cms_popup_widget(): string
    {
        return <<<'WIDGET'
<!-- CMS Popup Widget -->
<style>
.cms-pu-overlay{position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .3s,visibility .3s}
.cms-pu-overlay.active{opacity:1;visibility:visible}
.cms-pu-overlay.has-bg{background:rgba(0,0,0,.55)}
.cms-pu-box{border-radius:12px;padding:28px;text-align:center;transform:translateY(30px);transition:transform .35s cubic-bezier(.22,1,.36,1);box-shadow:0 20px 60px rgba(0,0,0,.4);position:relative;max-height:90vh;overflow-y:auto}
.cms-pu-overlay.active .cms-pu-box{transform:translateY(0)}
.cms-pu-close{position:absolute;top:10px;right:14px;background:none;border:none;font-size:1.2rem;cursor:pointer;opacity:.6;color:inherit;line-height:1}
.cms-pu-close:hover{opacity:1}
.cms-pu-box img{max-width:100%;max-height:160px;border-radius:8px;margin-bottom:12px;object-fit:cover}
.cms-pu-box h2{margin:0 0 8px;font-size:1.3rem;font-weight:700}
.cms-pu-box p{margin:0 0 16px;font-size:.85rem;opacity:.85;line-height:1.5}
.cms-pu-btn{display:inline-block;padding:10px 24px;border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;border:none;color:#fff;transition:transform .15s}
.cms-pu-btn:hover{transform:scale(1.03)}
.cms-pu-form{display:flex;flex-direction:column;gap:8px;margin-bottom:12px}
.cms-pu-form input{padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.1);color:inherit;font-size:.85rem}
.cms-pu-success{font-size:.9rem;font-weight:600;padding:20px}

/* Modal: centered, overlay */
.cms-pu-modal .cms-pu-box{max-width:500px;width:90%}
.cms-pu-modal.has-bg{background:rgba(0,0,0,.55)}

/* Slide-in: bottom-right */
.cms-pu-slide_in{align-items:flex-end;justify-content:flex-end;padding:16px;pointer-events:none}
.cms-pu-slide_in .cms-pu-box{max-width:350px;text-align:left;pointer-events:all;transform:translateX(120%)}
.cms-pu-slide_in.active .cms-pu-box{transform:translateX(0)}

/* Bar: full width top */
.cms-pu-bar{align-items:flex-start;padding:0;pointer-events:none}
.cms-pu-bar .cms-pu-box{max-width:100%;width:100%;border-radius:0;padding:12px 24px;display:flex;align-items:center;gap:16px;text-align:left;pointer-events:all;transform:translateY(-100%)}
.cms-pu-bar.active .cms-pu-box{transform:translateY(0)}
.cms-pu-bar .cms-pu-box h2{font-size:.95rem;margin:0;white-space:nowrap}
.cms-pu-bar .cms-pu-box p{margin:0;font-size:.8rem;flex:1}
.cms-pu-bar .cms-pu-form{flex-direction:row;margin:0}
.cms-pu-bar .cms-pu-form input{width:140px}
.cms-pu-bar .cms-pu-box img{max-height:40px;margin:0}

/* Bar bottom position */
.cms-pu-bar.cms-pu-pos-bottom{align-items:flex-end}
.cms-pu-bar.cms-pu-pos-bottom .cms-pu-box{transform:translateY(100%)}
.cms-pu-bar.cms-pu-pos-bottom.active .cms-pu-box{transform:translateY(0)}

/* Fullscreen */
.cms-pu-fullscreen{background:rgba(0,0,0,.85)}
.cms-pu-fullscreen .cms-pu-box{max-width:560px;width:90%}
</style>
<script>
(function(){
    var CMS_PU={popups:[],shown:{}};

    function getShown(){try{return JSON.parse(localStorage.getItem('cms_pu_shown')||'{}')}catch(e){return{}}}
    function setShown(id){var s=getShown();s[id]=Date.now();localStorage.setItem('cms_pu_shown',JSON.stringify(s))}

    function matchUrl(patterns, url){
        if(!patterns||patterns.trim()==='*'||patterns.trim()==='') return true;
        var lines=patterns.split('\n');
        for(var i=0;i<lines.length;i++){
            var p=lines[i].trim();
            if(!p) continue;
            if(p==='*') return true;
            // Simple wildcard match
            var re=new RegExp('^'+p.replace(/[.*+?^${}()|[\]\\]/g,'\\$&').replace(/\\\*/g,'.*')+'$','i');
            if(re.test(url)) return true;
            // Also check if pattern is contained in URL
            if(url.indexOf(p)!==-1) return true;
        }
        return false;
    }

    function track(popupId, type){
        try{
            fetch('/api/popup-track',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({popup_id:popupId,type:type})});
        }catch(e){}
    }

    function showPopup(p){
        if(CMS_PU.shown[p.id]) return;
        CMS_PU.shown[p.id]=true;

        var overlay=document.createElement('div');
        overlay.className='cms-pu-overlay cms-pu-'+p.type;
        if(p.type==='modal'||p.type==='fullscreen') overlay.classList.add('has-bg');
        if(p.position) overlay.classList.add('cms-pu-pos-'+p.position);

        var box=document.createElement('div');
        box.className='cms-pu-box';
        box.style.background=p.bg_color||'#1e293b';
        box.style.color=p.text_color||'#e2e8f0';

        var html='<button class="cms-pu-close" data-close>&times;</button>';
        if(p.image) html+='<img src="'+esc(p.image)+'" alt="">';
        html+='<h2>'+esc(p.name)+'</h2>';
        if(p.content) html+='<p>'+esc(p.content)+'</p>';

        // Form fields
        var fields=p.form_fields||[];
        if(p.cta_action==='form'&&fields.length>0){
            html+='<div class="cms-pu-form" data-form>';
            for(var i=0;i<fields.length;i++){
                var f=fields[i];
                var type=f==='email'?'email':f==='phone'?'tel':'text';
                html+='<input type="'+type+'" name="'+esc(f)+'" placeholder="'+esc(f.charAt(0).toUpperCase()+f.slice(1))+'" required>';
            }
            html+='<button class="cms-pu-btn" style="background:'+(p.btn_color||'#6366f1')+'" data-submit>'+esc(p.cta_text||'Submit')+'</button>';
            html+='</div>';
        }else{
            html+='<button class="cms-pu-btn" style="background:'+(p.btn_color||'#6366f1')+'" data-cta>'+esc(p.cta_text||'Subscribe')+'</button>';
        }

        box.innerHTML=html;
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        // Animate in
        requestAnimationFrame(function(){requestAnimationFrame(function(){overlay.classList.add('active')})});

        // Track view
        track(p.id,'view');
        if(p.show_once) setShown(p.id);

        // Close handlers
        function close(){overlay.classList.remove('active');setTimeout(function(){overlay.remove()},350)}
        overlay.querySelector('[data-close]').addEventListener('click',close);
        if(p.type==='modal'||p.type==='fullscreen'){
            overlay.addEventListener('click',function(e){if(e.target===overlay)close()});
        }

        // CTA click
        var ctaBtn=overlay.querySelector('[data-cta]');
        if(ctaBtn){
            ctaBtn.addEventListener('click',function(){
                track(p.id,'click');
                if(p.cta_action==='url'&&p.cta_url){window.location.href=p.cta_url}
                else{close()}
            });
        }

        // Form submit
        var submitBtn=overlay.querySelector('[data-submit]');
        if(submitBtn){
            submitBtn.addEventListener('click',function(){
                var form=overlay.querySelector('[data-form]');
                var inputs=form.querySelectorAll('input');
                var data={popup_id:p.id,page_url:window.location.href};
                var valid=true;
                inputs.forEach(function(inp){
                    if(inp.required&&!inp.value.trim()){valid=false;inp.style.borderColor='#f38ba8'}
                    else{inp.style.borderColor='';data[inp.name]=inp.value.trim()}
                });
                if(!valid)return;
                track(p.id,'click');
                fetch('/api/popup-submit',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
                .then(function(r){return r.json()})
                .then(function(resp){
                    if(resp.coupon_code){
                        box.innerHTML='<div class="cms-pu-success" style="color:'+(p.text_color||'#e2e8f0')+';padding:24px;">'
                            +'<div style="font-size:1.3rem;margin-bottom:12px">🎉 Thank you!</div>'
                            +'<div style="font-size:.85rem;opacity:.8;margin-bottom:12px">Your discount code:</div>'
                            +'<div style="background:rgba(255,255,255,.15);border:2px dashed rgba(255,255,255,.3);border-radius:8px;padding:12px;font-size:1.4rem;font-weight:700;letter-spacing:.1em;margin-bottom:12px;user-select:all" id="cms-pu-coupon">'+resp.coupon_code+'</div>'
                            +'<button onclick="navigator.clipboard.writeText(document.getElementById(\'cms-pu-coupon\').textContent.trim());this.textContent=\'Copied! ✅\'" style="background:'+(p.btn_color||'#6366f1')+';color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:.8rem;font-weight:600">📋 Copy Code</button>'
                            +'</div>';
                        setTimeout(close,8000);
                    }else{
                        box.innerHTML='<div class="cms-pu-success" style="color:'+(p.text_color||'#e2e8f0')+'">✅ Thank you!</div>';
                        setTimeout(close,2000);
                    }
                }).catch(function(){close()});
            });
        }
    }

    function esc(s){if(!s)return'';var d=document.createElement('div');d.textContent=s;return d.innerHTML}

    function setupTrigger(p){
        var url=window.location.pathname;

        // Check targeting
        if(!matchUrl(p.show_on||'*',url)) return;
        if(p.hide_on&&matchUrl(p.hide_on,url)) return;

        // Check show_once
        if(p.show_once){var shown=getShown();if(shown[p.id])return}

        // Check schedule (client-side double check)
        var now=new Date();
        if(p.start_date&&new Date(p.start_date)>now) return;
        if(p.end_date&&new Date(p.end_date)<now) return;

        var trigger=p.trigger_type||'delay';
        var val=parseFloat(p.trigger_value)||3;

        if(trigger==='delay'){
            setTimeout(function(){showPopup(p)},val*1000);
        }else if(trigger==='scroll'){
            var triggered=false;
            window.addEventListener('scroll',function(){
                if(triggered)return;
                var pct=(window.scrollY/(document.documentElement.scrollHeight-window.innerHeight))*100;
                if(pct>=val){triggered=true;showPopup(p)}
            });
        }else if(trigger==='exit_intent'){
            var triggered=false;
            document.addEventListener('mouseleave',function(e){
                if(triggered)return;
                if(e.clientY<0){triggered=true;showPopup(p)}
            });
        }else if(trigger==='click'){
            document.querySelectorAll('.popup-trigger').forEach(function(el){
                el.addEventListener('click',function(e){e.preventDefault();showPopup(p)});
            });
        }
    }

    // Fetch and initialize
    fetch('/api/popups').then(function(r){return r.json()}).then(function(d){
        CMS_PU.popups=d.popups||[];
        CMS_PU.popups.forEach(setupTrigger);
    }).catch(function(){});
})();
</script>
WIDGET;
    }
}
