<?php
/**
 * A/B Testing Frontend Widget
 * Loads active tests, assigns variants, tracks views/conversions
 */
function cms_ab_testing_widget(): string
{
    return <<<'ABWIDGET'
<script>
(function(){
    var AB_KEY='cms_ab_';
    fetch('/api/ab-tests').then(function(r){return r.json()}).then(function(d){
        if(!d.tests||!d.tests.length)return;
        d.tests.forEach(function(t){
            // Check page filter
            if(t.page_id){
                var pid=document.querySelector('meta[name="page-id"]');
                if(pid&&pid.content!=t.page_id)return;
            }
            // Assign variant (persistent)
            var key=AB_KEY+t.id;
            var v=localStorage.getItem(key);
            if(!v){v=Math.random()<0.5?'a':'b';localStorage.setItem(key,v);}
            // Apply variant
            var el=document.querySelector(t.selector);
            if(!el)return;
            var content=v==='b'?t.variant_b:t.variant_a;
            if(t.type==='image'&&el.tagName==='IMG'){el.src=content;}
            else if(t.type==='layout'){if(v==='b')el.classList.add('ab-variant-b');}
            else{el.innerHTML=content;}
            // Track view
            fetch('/api/ab-track',{method:'POST',headers:{'Content-Type':'application/json'},
                body:JSON.stringify({test_id:t.id,variant:v,type:'view'})});
            // Track conversion
            if(t.goal==='click'&&t.goal_selector){
                var goal=document.querySelector(t.goal_selector);
                if(goal)goal.addEventListener('click',function(){
                    fetch('/api/ab-track',{method:'POST',headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({test_id:t.id,variant:v,type:'conversion'})});
                },{once:true});
            }
            if(t.goal==='form_submit'){
                var forms=document.querySelectorAll('form');
                forms.forEach(function(f){f.addEventListener('submit',function(){
                    fetch('/api/ab-track',{method:'POST',headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({test_id:t.id,variant:v,type:'conversion'})});
                },{once:true});});
            }
            if(t.goal==='time_on_page'){
                setTimeout(function(){
                    fetch('/api/ab-track',{method:'POST',headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({test_id:t.id,variant:v,type:'conversion'})});
                },30000);
            }
            if(t.goal==='scroll'&&t.goal_selector){
                var obs=new IntersectionObserver(function(entries){
                    entries.forEach(function(e){if(e.isIntersecting){
                        fetch('/api/ab-track',{method:'POST',headers:{'Content-Type':'application/json'},
                            body:JSON.stringify({test_id:t.id,variant:v,type:'conversion'})});
                        obs.disconnect();
                    }});
                });
                var scrollEl=document.querySelector(t.goal_selector);
                if(scrollEl)obs.observe(scrollEl);
            }
        });
    }).catch(function(){});
})();
</script>
ABWIDGET;
}
