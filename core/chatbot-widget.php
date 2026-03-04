<?php
/**
 * Jessie CMS — Chat Widget (frontend)
 * Lightweight file — only the widget HTML/CSS/JS
 * The heavy CmsChatbot class is in core/chatbot.php
 */

if (!function_exists('ai_theme_chat_widget')) {
function ai_theme_chat_widget(): string
{
    return <<<'CHATWIDGET'
<style>
.jc-wrap{position:fixed;bottom:20px;right:20px;z-index:99998;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.jc-btn{width:56px;height:56px;border-radius:50%;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;box-shadow:0 4px 20px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:24px;transition:transform .2s}
.jc-btn:hover{transform:scale(1.1)}
.jc-btn.open{background:#ef4444}
.jc-box{display:none;position:fixed;bottom:88px;right:20px;width:380px;max-width:calc(100vw - 40px);height:500px;max-height:calc(100vh - 120px);background:#1e293b;border:1px solid #334155;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.4);flex-direction:column;overflow:hidden;z-index:99999}
.jc-box.visible{display:flex}
.jc-head{padding:16px;background:#0f172a;border-bottom:1px solid #334155;display:flex;align-items:center;gap:10px}
.jc-head .dot{width:10px;height:10px;border-radius:50%;background:#22c55e}
.jc-head h4{margin:0;font-size:.95rem;color:#e2e8f0;font-weight:600}
.jc-msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px}
.jc-msg{max-width:85%;padding:10px 14px;border-radius:12px;font-size:.875rem;line-height:1.5;word-wrap:break-word}
.jc-msg.bot{background:#334155;color:#e2e8f0;border-bottom-left-radius:4px;align-self:flex-start}
.jc-msg.user{background:var(--primary,#6366f1);color:#fff;border-bottom-right-radius:4px;align-self:flex-end}
.jc-msg.typing{color:#94a3b8;font-style:italic}
.jc-sugg{display:flex;flex-wrap:wrap;gap:6px;padding:0 16px 8px}
.jc-sugg button{padding:6px 12px;border-radius:20px;border:1px solid #334155;background:transparent;color:#94a3b8;font-size:.75rem;cursor:pointer;transition:all .2s}
.jc-sugg button:hover{border-color:var(--primary,#6366f1);color:#e2e8f0}
.jc-input{display:flex;padding:12px;border-top:1px solid #334155;gap:8px}
.jc-input input{flex:1;padding:10px 14px;border-radius:8px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;font-size:.875rem;outline:none}
.jc-input input:focus{border-color:var(--primary,#6366f1)}
.jc-input button{padding:10px 16px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.875rem;font-weight:600}
.jc-input button:disabled{opacity:.5;cursor:default}
@media(max-width:480px){.jc-box{bottom:0;right:0;width:100%;height:100vh;max-height:100vh;border-radius:0}}
</style>
<div class="jc-wrap" id="jcWrap" style="display:none">
    <div class="jc-box" id="jcBox">
        <div class="jc-head"><span class="dot"></span><h4 id="jcTitle">Chat</h4></div>
        <div class="jc-msgs" id="jcMsgs"></div>
        <div class="jc-sugg" id="jcSugg"></div>
        <div class="jc-input">
            <input type="text" id="jcIn" placeholder="Type a message..." maxlength="1000" autocomplete="off">
            <button id="jcSend">Send</button>
        </div>
    </div>
    <button class="jc-btn" id="jcToggle">💬</button>
</div>
<script>
(function(){
    var wrap=document.getElementById('jcWrap'),box=document.getElementById('jcBox'),
        msgs=document.getElementById('jcMsgs'),input=document.getElementById('jcIn'),
        sendBtn=document.getElementById('jcSend'),toggle=document.getElementById('jcToggle'),
        sugg=document.getElementById('jcSugg'),titleEl=document.getElementById('jcTitle');
    var sid=localStorage.getItem('jc_sid')||'',cfg=null,busy=false;

    fetch('/api/chat/config').then(function(r){return r.json()}).then(function(c){
        cfg=c;
        if(!c.enabled)return;
        wrap.style.display='block';
        if(c.welcome){addMsg(c.welcome,'bot');}
        if(c.suggestions&&c.suggestions.length){
            c.suggestions.forEach(function(s){
                if(!s)return;
                var b=document.createElement('button');
                b.textContent=s;
                b.onclick=function(){input.value=s;send()};
                sugg.appendChild(b);
            });
        }
    }).catch(function(){});

    toggle.onclick=function(){
        var open=box.classList.toggle('visible');
        toggle.textContent=open?'✕':'💬';
        toggle.classList.toggle('open',open);
        if(open)input.focus();
    };

    function addMsg(text,type){
        var d=document.createElement('div');
        d.className='jc-msg '+type;
        var html=text.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
                      .replace(/\[([^\]]+)\]\(([^)]+)\)/g,'<a href="$2" style="color:inherit;text-decoration:underline">$1</a>')
                      .replace(/\n/g,'<br>');
        d.innerHTML=html;
        msgs.appendChild(d);
        msgs.scrollTop=msgs.scrollHeight;
        return d;
    }

    function send(){
        var text=input.value.trim();
        if(!text||busy)return;
        input.value='';
        sugg.innerHTML='';
        addMsg(text,'user');
        busy=true;
        sendBtn.disabled=true;
        var typing=addMsg('Thinking...','bot typing');

        fetch('/api/chat',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({message:text,session_id:sid,page_url:location.pathname})
        })
        .then(function(r){return r.json()})
        .then(function(d){
            typing.remove();
            if(d.ok){
                addMsg(d.reply,'bot');
                if(d.session_id){sid=d.session_id;localStorage.setItem('jc_sid',sid);}
            }else{
                addMsg(d.error||'Something went wrong.','bot');
            }
        })
        .catch(function(){typing.remove();addMsg('Connection error. Please try again.','bot');})
        .finally(function(){busy=false;sendBtn.disabled=false;input.focus();});
    }

    sendBtn.onclick=send;
    input.onkeydown=function(e){if(e.key==='Enter')send()};
})();
</script>
CHATWIDGET;
}
} // end function_exists guard
