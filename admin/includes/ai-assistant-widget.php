<?php
/**
 * AI Assistant — Floating Context Widget v2.0
 * Full markdown tutorials per page + AI chat
 * Include in topbar.php layout
 */
require_once (defined('CMS_ROOT') ? CMS_ROOT : dirname(dirname(__DIR__))) . '/core/ai-assistant-context.php';

$_widgetCtx = getAssistantContext($_SERVER['REQUEST_URI'] ?? '/admin');
$_widgetTitle = $_widgetCtx['title'] ?? 'Help';
$_widgetIcon = $_widgetCtx['icon'] ?? '💡';
$_widgetTutorial = $_widgetCtx['tutorial'] ?? '';
?>

<style>
/* ═══════════════════════════════════════
   Floating AI Assistant Widget v2.0
   Full tutorial support
   ═══════════════════════════════════════ */
#ai-assist-fab{
    position:fixed;bottom:24px;right:24px;z-index:99998;
    width:56px;height:56px;border-radius:50%;border:none;
    background:linear-gradient(135deg,#8b5cf6 0%,#6366f1 100%);
    color:#fff;font-size:24px;cursor:pointer;
    box-shadow:0 4px 16px rgba(99,102,241,.4),0 2px 6px rgba(0,0,0,.2);
    transition:all .25s cubic-bezier(.4,0,.2,1);
    display:flex;align-items:center;justify-content:center;
}
#ai-assist-fab:hover{transform:scale(1.1) translateY(-2px);box-shadow:0 6px 24px rgba(99,102,241,.5)}
#ai-assist-fab.open{transform:rotate(45deg) scale(1.1);background:#ef4444}

/* Panel */
#ai-assist-panel{
    position:fixed;bottom:90px;right:24px;z-index:99997;
    width:420px;max-height:calc(100vh - 140px);
    background:var(--bg-primary,#1e1e2e);
    border:1px solid var(--border,#313244);
    border-radius:16px;
    box-shadow:0 8px 32px rgba(0,0,0,.35),0 2px 8px rgba(0,0,0,.2);
    display:none;flex-direction:column;
    overflow:hidden;
    animation:panelIn .25s ease;
    font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
}
#ai-assist-panel.visible{display:flex}
@keyframes panelIn{from{opacity:0;transform:translateY(12px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}

/* Header */
.aw-header{
    padding:14px 18px;display:flex;align-items:center;gap:10px;
    border-bottom:1px solid var(--border,#313244);
    background:linear-gradient(135deg,rgba(139,92,246,.08),rgba(99,102,241,.05));
    flex-shrink:0;
}
.aw-header-icon{font-size:22px}
.aw-header-text h3{font-size:14px;font-weight:700;color:var(--text-primary,#cdd6f4);margin:0}
.aw-header-text p{font-size:11px;color:var(--text-muted,#6c7086);margin:2px 0 0}

/* Tabs */
.aw-tabs{display:flex;border-bottom:1px solid var(--border,#313244);flex-shrink:0}
.aw-tab{flex:1;padding:10px;text-align:center;font-size:12px;font-weight:600;color:var(--text-muted,#6c7086);
    cursor:pointer;border:none;background:none;font-family:inherit;transition:all .15s;position:relative}
.aw-tab:hover{color:var(--text-secondary,#a6adc8)}
.aw-tab.active{color:var(--accent,#89b4fa)}
.aw-tab.active::after{content:'';position:absolute;bottom:0;left:20%;right:20%;height:2px;
    background:var(--accent,#89b4fa);border-radius:2px}

/* Body */
.aw-body{flex:1;overflow-y:auto;max-height:calc(100vh - 300px)}
.aw-body::-webkit-scrollbar{width:5px}
.aw-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}

/* Tutorial content */
.aw-tutorial{padding:18px;font-size:13px;line-height:1.75;color:var(--text-secondary,#a6adc8)}

.aw-tutorial h2{font-size:15px;font-weight:700;color:var(--text-primary,#cdd6f4);margin:20px 0 8px;
    padding-bottom:6px;border-bottom:1px solid var(--border,#313244)}
.aw-tutorial h2:first-child{margin-top:0}
.aw-tutorial h3{font-size:13px;font-weight:700;color:var(--text-primary,#cdd6f4);margin:16px 0 6px}
.aw-tutorial h4{font-size:12px;font-weight:700;color:var(--accent,#89b4fa);margin:12px 0 4px;text-transform:uppercase;letter-spacing:.5px}

.aw-tutorial p{margin:0 0 10px}
.aw-tutorial strong{color:var(--text-primary,#cdd6f4);font-weight:600}
.aw-tutorial em{font-style:italic;color:var(--text-muted,#6c7086)}

.aw-tutorial ul,.aw-tutorial ol{margin:6px 0 12px;padding-left:20px}
.aw-tutorial li{margin-bottom:4px}
.aw-tutorial li::marker{color:var(--accent,#89b4fa)}

.aw-tutorial code{background:rgba(0,0,0,.2);padding:1px 5px;border-radius:3px;font-size:12px;font-family:'JetBrains Mono',monospace;color:#f38ba8}
.aw-tutorial pre{background:rgba(0,0,0,.25);padding:10px;border-radius:8px;overflow-x:auto;margin:8px 0 12px;font-size:12px}
.aw-tutorial pre code{background:none;padding:0;color:var(--text-secondary,#a6adc8)}

.aw-tutorial a{color:#89b4fa;text-decoration:underline}
.aw-tutorial a:hover{color:#b4befe}

.aw-tutorial blockquote{border-left:3px solid var(--accent,#89b4fa);padding:8px 14px;margin:10px 0;
    background:rgba(137,180,250,.05);border-radius:0 8px 8px 0;font-size:12px}

.aw-tutorial hr{border:none;height:1px;background:var(--border,#313244);margin:16px 0}

/* Info boxes in tutorial */
.aw-tutorial .tip-box{padding:10px 14px;border-radius:8px;margin:10px 0;font-size:12px;line-height:1.6;
    border:1px solid rgba(166,227,161,.2);background:rgba(166,227,161,.05)}
.aw-tutorial .warn-box{padding:10px 14px;border-radius:8px;margin:10px 0;font-size:12px;line-height:1.6;
    border:1px solid rgba(250,179,135,.2);background:rgba(250,179,135,.05)}

/* Tab content */
.aw-tab-content{display:none}.aw-tab-content.active{display:block}

/* Chat tab (same as v1) */
.aw-chat-messages{min-height:200px;max-height:300px;overflow-y:auto;padding:16px 18px;display:flex;flex-direction:column;gap:8px}
.aw-chat-msg{display:flex;gap:8px;max-width:95%}
.aw-chat-msg.user{align-self:flex-end;flex-direction:row-reverse}
.aw-chat-msg.assistant{align-self:flex-start}
.aw-chat-bubble{padding:8px 12px;border-radius:12px;font-size:13px;line-height:1.6}
.aw-chat-msg.user .aw-chat-bubble{background:var(--accent,#6366f1);color:#fff;border-bottom-right-radius:4px}
.aw-chat-msg.assistant .aw-chat-bubble{background:var(--bg-secondary,#181825);border:1px solid var(--border,#313244);color:var(--text-primary,#cdd6f4);border-bottom-left-radius:4px}
.aw-chat-bubble p{margin:0 0 .3rem}.aw-chat-bubble p:last-child{margin:0}
.aw-chat-bubble strong{font-weight:600}.aw-chat-bubble code{background:rgba(0,0,0,.2);padding:1px 4px;border-radius:3px;font-size:12px}
.aw-chat-bubble ul,.aw-chat-bubble ol{margin:.3rem 0;padding-left:1.25rem}
.aw-chat-bubble li{margin-bottom:.15rem}
.aw-chat-bubble a{color:#93c5fd;text-decoration:underline}
.aw-chat-input{display:flex;gap:6px;padding:0 18px 14px}
.aw-chat-input textarea{flex:1;padding:8px 12px;border:1px solid var(--border,#313244);border-radius:10px;
    background:var(--bg-secondary,#181825);color:var(--text-primary,#cdd6f4);
    font-size:13px;font-family:inherit;resize:none;max-height:60px;line-height:1.4}
.aw-chat-input textarea:focus{outline:none;border-color:var(--accent,#89b4fa)}
.aw-chat-input textarea::placeholder{color:var(--text-muted,#6c7086)}
.aw-chat-input button{padding:8px 14px;border:none;border-radius:10px;
    background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;font-weight:600;font-size:12px;
    cursor:pointer;font-family:inherit;white-space:nowrap;transition:filter .15s}
.aw-chat-input button:hover{filter:brightness(1.15)}
.aw-chat-input button:disabled{opacity:.5;cursor:not-allowed}
.aw-typing{display:flex;gap:3px;padding:4px 0}
.aw-typing span{width:5px;height:5px;background:var(--text-muted);border-radius:50%;animation:awBounce .6s infinite}
.aw-typing span:nth-child(2){animation-delay:.15s}.aw-typing span:nth-child(3){animation-delay:.3s}
@keyframes awBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}

/* Expand button */
.aw-expand{position:absolute;top:12px;right:44px;width:28px;height:28px;border-radius:8px;border:1px solid var(--border,#313244);
    background:var(--bg-secondary,#181825);display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:14px;color:var(--text-muted);transition:all .15s;z-index:1}
.aw-expand:hover{border-color:var(--accent);color:var(--accent)}
#ai-assist-panel.expanded{width:600px;max-height:calc(100vh - 100px)}

@media(max-width:480px){
    #ai-assist-panel{width:calc(100vw - 32px);right:16px;bottom:82px}
    #ai-assist-panel.expanded{width:calc(100vw - 32px)}
    #ai-assist-fab{bottom:16px;right:16px;width:48px;height:48px;font-size:20px}
}
</style>

<button id="ai-assist-fab" onclick="toggleAssistPanel()" title="Help & Guide">💡</button>

<div id="ai-assist-panel">
    <div class="aw-header" style="position:relative">
        <span class="aw-header-icon"><?= $_widgetIcon ?></span>
        <div class="aw-header-text">
            <h3><?= htmlspecialchars($_widgetTitle, ENT_QUOTES, 'UTF-8') ?></h3>
            <p>Tutorial & Help</p>
        </div>
        <button class="aw-expand" onclick="toggleExpand()" title="Expand/Collapse">⤢</button>
    </div>

    <div class="aw-tabs">
        <button class="aw-tab active" onclick="switchAwTab('guide',this)">📖 Tutorial</button>
        <button class="aw-tab" onclick="switchAwTab('chat',this)">💬 Ask AI</button>
    </div>

    <div class="aw-body">
        <!-- Tutorial Tab -->
        <div class="aw-tab-content active" id="aw-tab-guide">
            <div class="aw-tutorial" id="aw-tutorial-content">
                <?php if ($_widgetTutorial): ?>
                    <?= _awRenderMarkdown($_widgetTutorial) ?>
                <?php else: ?>
                    <p style="text-align:center;padding:20px 0;color:var(--text-muted)">
                        No tutorial available for this page yet.<br>
                        Try the <strong>Ask AI</strong> tab for help!
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Tab -->
        <div class="aw-tab-content" id="aw-tab-chat">
            <div class="aw-chat-messages" id="aw-chat-messages">
                <div class="aw-chat-msg assistant">
                    <div class="aw-chat-bubble">
                        <p>👋 Hi! I can answer questions about <strong><?= htmlspecialchars($_widgetTitle, ENT_QUOTES, 'UTF-8') ?></strong>. What would you like to know?</p>
                    </div>
                </div>
            </div>
            <div class="aw-chat-input">
                <textarea id="aw-chat-input" rows="1" placeholder="Ask about this page..."
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendAwChat()}"></textarea>
                <button id="aw-chat-send" onclick="sendAwChat()">Send</button>
            </div>
        </div>
    </div>
</div>

<?php
function _awRenderMarkdown(string $md): string {
    $html = htmlspecialchars($md, ENT_QUOTES, 'UTF-8');

    // Code blocks
    $html = preg_replace('/```(\w*)\n(.*?)```/s', '<pre><code>$2</code></pre>', $html);
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

    // Tip/Warning boxes
    $html = preg_replace('/^&gt; 💡 (.+)$/m', '<div class="tip-box">💡 $1</div>', $html);
    $html = preg_replace('/^&gt; ⚠️ (.+)$/m', '<div class="warn-box">⚠️ $1</div>', $html);
    $html = preg_replace('/^&gt; (.+)$/m', '<blockquote>$1</blockquote>', $html);

    // Headings
    $html = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $html);
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);

    // Bold, italic
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);

    // Lists
    $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $html);
    // Wrap consecutive <li> in <ul>
    $html = preg_replace('/(<li>.*?<\/li>\n?)+/s', '<ul>$0</ul>', $html);

    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);

    // Horizontal rule
    $html = preg_replace('/^---$/m', '<hr>', $html);

    // Paragraphs
    $html = preg_replace('/\n{2,}/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';

    // Clean up empty paragraphs around block elements
    $html = preg_replace('/<p>\s*(<(?:h[1-4]|ul|ol|pre|blockquote|div|hr))/s', '$1', $html);
    $html = preg_replace('/(<\/(?:h[1-4]|ul|ol|pre|blockquote|div|hr)>)\s*<\/p>/s', '$1', $html);
    $html = str_replace('<p></p>', '', $html);

    return $html;
}
?>

<script>
(function(){
    const PAGE_CONTEXT = <?= json_encode($_widgetTitle) ?>;
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';

    window.toggleAssistPanel = function() {
        const panel = document.getElementById('ai-assist-panel');
        const fab = document.getElementById('ai-assist-fab');
        panel.classList.toggle('visible');
        fab.classList.toggle('open');
        fab.textContent = panel.classList.contains('visible') ? '✕' : '💡';
    };

    window.toggleExpand = function() {
        document.getElementById('ai-assist-panel').classList.toggle('expanded');
    };

    window.switchAwTab = function(tab, btn) {
        document.querySelectorAll('.aw-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.aw-tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('aw-tab-' + tab).classList.add('active');
    };

    // ─── Chat ───
    const chatMsgs = document.getElementById('aw-chat-messages');
    const chatInput = document.getElementById('aw-chat-input');
    const chatSend = document.getElementById('aw-chat-send');

    function addChatMsg(role, html) {
        const div = document.createElement('div');
        div.className = 'aw-chat-msg ' + role;
        div.innerHTML = '<div class="aw-chat-bubble">' + html + '</div>';
        chatMsgs.appendChild(div);
        chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }

    function addTyping() {
        const div = document.createElement('div');
        div.className = 'aw-chat-msg assistant'; div.id = 'aw-typing';
        div.innerHTML = '<div class="aw-chat-bubble"><div class="aw-typing"><span></span><span></span><span></span></div></div>';
        chatMsgs.appendChild(div); chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }

    window.sendAwChat = async function() {
        const msg = chatInput.value.trim();
        if (!msg) return;
        const escaped = msg.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        addChatMsg('user', '<p>' + escaped + '</p>');
        chatInput.value = '';
        chatSend.disabled = true;
        addTyping();

        try {
            const res = await fetch('/api/ai-tutor/ask', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN': CSRF_TOKEN},
                body: JSON.stringify({ message: msg, context: PAGE_CONTEXT })
            });
            const data = await res.json();
            const typing = document.getElementById('aw-typing');
            if (typing) typing.remove();

            if (data.error) {
                addChatMsg('assistant', '<p style="color:#f87171">❌ ' + data.error + '</p>');
            } else {
                addChatMsg('assistant', parseMd(data.response));
            }
        } catch(e) {
            const typing = document.getElementById('aw-typing');
            if (typing) typing.remove();
            addChatMsg('assistant', '<p style="color:#f87171">❌ Network error</p>');
        }
        chatSend.disabled = false;
        chatInput.focus();
    };

    function parseMd(t) {
        let h = t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        h = h.replace(/```(\w*)\n([\s\S]*?)```/g,'<pre><code>$2</code></pre>');
        h = h.replace(/`([^`]+)`/g,'<code>$1</code>');
        h = h.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>');
        h = h.replace(/\*(.+?)\*/g,'<em>$1</em>');
        h = h.replace(/^### (.+)$/gm,'<h3>$1</h3>');
        h = h.replace(/^## (.+)$/gm,'<h2>$1</h2>');
        h = h.replace(/^- (.+)$/gm,'<li>$1</li>');
        h = h.replace(/^(\d+)\. (.+)$/gm,'<li>$2</li>');
        h = h.replace(/\[([^\]]+)\]\(([^)]+)\)/g,'<a href="$2">$1</a>');
        h = h.replace(/\n{2,}/g,'</p><p>');
        return '<p>' + h + '</p>';
    }
})();
</script>
