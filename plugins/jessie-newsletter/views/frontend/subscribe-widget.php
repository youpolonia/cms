<!-- Jessie Newsletter Subscribe Widget — embed anywhere -->
<div class="nl-subscribe-widget" id="nl-widget">
    <h3>📧 Stay Updated</h3>
    <p>Get the latest news delivered to your inbox.</p>
    <form id="nl-subscribe-form">
        <input type="text" name="name" placeholder="Your name" class="nl-input">
        <input type="email" name="email" placeholder="Email address *" class="nl-input" required>
        <input type="hidden" name="list_id" value="<?= (int)($_GET['list_id'] ?? 1) ?>">
        <button type="submit" class="nl-submit">Subscribe ✉️</button>
    </form>
    <div id="nl-msg" style="display:none;margin-top:10px;font-size:.85rem"></div>
</div>
<style>
.nl-subscribe-widget{max-width:400px;margin:20px auto;padding:24px;background:#f8fafc;border-radius:12px;font-family:-apple-system,sans-serif;text-align:center}
.nl-subscribe-widget h3{margin:0 0 6px;font-size:1.1rem;color:#0f172a}
.nl-subscribe-widget p{font-size:.85rem;color:#64748b;margin:0 0 16px}
.nl-input{display:block;width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:.9rem;margin-bottom:8px;box-sizing:border-box}
.nl-submit{width:100%;padding:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer}
</style>
<script>
document.getElementById('nl-subscribe-form').addEventListener('submit',function(e){
    e.preventDefault();var f=new FormData(this);var d={email:f.get('email'),name:f.get('name'),list_id:parseInt(f.get('list_id'))};
    fetch('/api/newsletter/subscribe',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)})
    .then(function(r){return r.json()}).then(function(r){
        var msg=document.getElementById('nl-msg');msg.style.display='block';
        if(r.ok){msg.style.color='#059669';msg.textContent=r.already_subscribed?'You\'re already subscribed! 👍':'Welcome aboard! 🎉';}
        else{msg.style.color='#dc2626';msg.textContent=r.error||'Something went wrong.';}
    });
});
</script>
