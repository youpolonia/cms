<?php $title = 'AI Assistant'; ob_start(); ?>
<style>
*{box-sizing:border-box}
.chat-wrap{display:flex;flex-direction:column;height:calc(100vh - 60px);max-width:900px;margin:0 auto;padding:1rem}
.chat-header{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;border-bottom:1px solid var(--border);margin-bottom:.5rem;flex-shrink:0}
.chat-header h1{font-size:1.25rem;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:.5rem}
.chat-header-actions{display:flex;gap:.5rem;align-items:center}
.chat-header select{padding:4px 8px;border:1px solid var(--border);border-radius:6px;background:var(--bg-secondary);color:var(--text-primary);font-size:.75rem}

.chat-messages{flex:1;overflow-y:auto;padding:1rem 0;display:flex;flex-direction:column;gap:.75rem}
.chat-msg{display:flex;gap:.75rem;max-width:85%}
.chat-msg.user{align-self:flex-end;flex-direction:row-reverse}
.chat-msg.assistant{align-self:flex-start}
.chat-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.chat-msg.user .chat-avatar{background:rgba(99,102,241,.15)}
.chat-msg.assistant .chat-avatar{background:rgba(16,185,129,.15)}
.chat-bubble{padding:10px 14px;border-radius:12px;font-size:.875rem;line-height:1.65;color:var(--text-primary);word-wrap:break-word}
.chat-msg.user .chat-bubble{background:var(--accent,#6366f1);color:white;border-bottom-right-radius:4px}
.chat-msg.assistant .chat-bubble{background:var(--bg-secondary);border:1px solid var(--border);border-bottom-left-radius:4px}
.chat-bubble p{margin:0 0 .5rem}
.chat-bubble p:last-child{margin:0}
.chat-bubble code{background:rgba(0,0,0,.15);padding:1px 5px;border-radius:3px;font-size:.8rem}
.chat-bubble pre{background:rgba(0,0,0,.2);padding:10px;border-radius:6px;overflow-x:auto;margin:.5rem 0;font-size:.8rem}
.chat-bubble pre code{background:none;padding:0}
.chat-bubble ul,.chat-bubble ol{margin:.5rem 0;padding-left:1.25rem}
.chat-bubble li{margin-bottom:.25rem}
.chat-bubble strong{color:inherit;font-weight:600}
.chat-bubble h1,.chat-bubble h2,.chat-bubble h3{font-size:1rem;font-weight:600;margin:.75rem 0 .25rem}
.chat-bubble a{color:#93c5fd;text-decoration:underline}

.chat-meta{font-size:.65rem;color:var(--text-muted);margin-top:2px;text-align:right}
.chat-msg.assistant .chat-meta{text-align:left}

.chat-input-wrap{flex-shrink:0;border-top:1px solid var(--border);padding-top:.75rem;display:flex;gap:.5rem}
.chat-input{flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:10px;background:var(--bg-secondary);color:var(--text-primary);font-size:.875rem;font-family:inherit;resize:none;max-height:120px;line-height:1.5}
.chat-input:focus{outline:none;border-color:var(--accent)}
.chat-input::placeholder{color:var(--text-muted)}
.chat-send{padding:10px 18px;border:none;border-radius:10px;background:var(--accent,#6366f1);color:white;font-weight:600;font-size:.875rem;cursor:pointer;transition:filter .15s;white-space:nowrap}
.chat-send:hover{filter:brightness(1.1)}
.chat-send:disabled{opacity:.5;cursor:not-allowed}

.chat-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);gap:.75rem;text-align:center}
.chat-empty-icon{font-size:3rem}
.chat-empty-title{font-size:1.1rem;font-weight:600;color:var(--text-secondary)}
.chat-empty-hints{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.5rem;max-width:500px}
.chat-empty-hint{background:var(--bg-secondary);border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-size:.75rem;cursor:pointer;transition:border-color .15s;text-align:left}
.chat-empty-hint:hover{border-color:var(--accent)}

.typing{display:flex;gap:4px;padding:4px 0}
.typing span{width:6px;height:6px;background:var(--text-muted);border-radius:50%;animation:bounce .6s ease-in-out infinite}
.typing span:nth-child(2){animation-delay:.15s}
.typing span:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

.btn-xs{padding:4px 10px;font-size:.7rem;border-radius:5px;border:none;cursor:pointer;font-weight:500;background:var(--bg-tertiary);color:var(--text-secondary)}
.btn-xs:hover{background:var(--border)}
</style>

<div class="chat-wrap">
    <div class="chat-header">
        <h1>🤖 AI Assistant</h1>
        <div class="chat-header-actions">
            <select id="chat-model">
                <?php foreach ($models as $m): ?>
                <option value="<?= esc($m['id']) ?>"><?= esc($m['provider'] . ' — ' . $m['name']) ?></option>
                <?php endforeach; ?>
                <?php if (empty($models)): ?>
                <option value="" disabled>No AI models configured</option>
                <?php endif; ?>
            </select>
            <button class="btn-xs" onclick="clearChat()">🗑 Clear</button>
        </div>
    </div>

    <div class="chat-messages" id="chat-messages">
        <?php if (empty($history)): ?>
        <div class="chat-empty" id="chat-empty">
            <div class="chat-empty-icon">💬</div>
            <div class="chat-empty-title">How can I help you today?</div>
            <p style="font-size:.8rem;max-width:400px">Ask me about your CMS — content, themes, SEO, or anything else. I know your site's context.</p>
            <div class="chat-empty-hints">
                <div class="chat-empty-hint" onclick="sendHint(this)">How do I improve my SEO score?</div>
                <div class="chat-empty-hint" onclick="sendHint(this)">Write a blog post about our services</div>
                <div class="chat-empty-hint" onclick="sendHint(this)">How do I customize my theme colors?</div>
                <div class="chat-empty-hint" onclick="sendHint(this)">What content should I create next?</div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($history as $msg): ?>
        <div class="chat-msg <?= $msg['role'] ?>">
            <div class="chat-avatar"><?= $msg['role'] === 'user' ? '👤' : '🤖' ?></div>
            <div>
                <div class="chat-bubble"><?= $msg['role'] === 'assistant' ? parseMarkdown($msg['content']) : esc($msg['content']) ?></div>
                <div class="chat-meta"><?= date('H:i', $msg['time'] ?? time()) ?><?= isset($msg['model']) ? ' · ' . esc($msg['model']) : '' ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="chat-input-wrap">
        <textarea class="chat-input" id="chat-input" placeholder="Ask me anything about your CMS..." rows="1" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage()}"></textarea>
        <button class="chat-send" id="chat-send" onclick="sendMessage()">Send ↑</button>
    </div>
</div>

<?php
// Simple markdown to HTML
function parseMarkdown(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    // Code blocks
    $text = preg_replace('/```(\w*)\n(.*?)```/s', '<pre><code>$2</code></pre>', $text);
    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    // Italic
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    // Headers
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    // Lists
    $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $text);
    // Links
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
    // Paragraphs
    $text = preg_replace('/\n{2,}/', '</p><p>', $text);
    $text = '<p>' . $text . '</p>';
    $text = str_replace('<p></p>', '', $text);
    return $text;
}
?>

<script>
const CSRF = '<?= $csrfToken ?>';
const messagesEl = document.getElementById('chat-messages');
const inputEl = document.getElementById('chat-input');
const sendBtn = document.getElementById('chat-send');

// Auto-resize input
inputEl.addEventListener('input', () => {
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
});

function scrollBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}
scrollBottom();

function addMessage(role, content, model) {
    const empty = document.getElementById('chat-empty');
    if (empty) empty.remove();

    const div = document.createElement('div');
    div.className = 'chat-msg ' + role;

    const time = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
    const meta = model ? `${time} · ${model}` : time;

    if (role === 'assistant') {
        div.innerHTML = `<div class="chat-avatar">🤖</div><div><div class="chat-bubble">${content}</div><div class="chat-meta">${meta}</div></div>`;
    } else {
        const escaped = content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        div.innerHTML = `<div class="chat-avatar">👤</div><div><div class="chat-bubble">${escaped}</div><div class="chat-meta">${meta}</div></div>`;
    }
    messagesEl.appendChild(div);
    scrollBottom();
    return div;
}

function addTyping() {
    const div = document.createElement('div');
    div.className = 'chat-msg assistant';
    div.id = 'typing';
    div.innerHTML = '<div class="chat-avatar">🤖</div><div><div class="chat-bubble"><div class="typing"><span></span><span></span><span></span></div></div></div>';
    messagesEl.appendChild(div);
    scrollBottom();
}

function removeTyping() {
    const t = document.getElementById('typing');
    if (t) t.remove();
}

async function sendMessage() {
    const msg = inputEl.value.trim();
    if (!msg) return;

    const model = document.getElementById('chat-model').value;
    if (!model) { alert('No AI model selected. Configure AI in Settings first.'); return; }

    addMessage('user', msg);
    inputEl.value = '';
    inputEl.style.height = 'auto';
    sendBtn.disabled = true;
    addTyping();

    try {
        const res = await fetch('/api/ai-chat/send', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({message: msg, model})
        });
        const data = await res.json();
        removeTyping();

        if (data.error) {
            addMessage('assistant', '<p style="color:#f87171">❌ ' + data.error + '</p>', model);
        } else {
            // Parse markdown client-side for fresh messages
            addMessage('assistant', parseMd(data.response), data.model);
        }
    } catch(e) {
        removeTyping();
        addMessage('assistant', '<p style="color:#f87171">❌ Network error. Please try again.</p>');
    }
    sendBtn.disabled = false;
    inputEl.focus();
}

async function clearChat() {
    if (!confirm('Clear all chat history?')) return;
    await fetch('/api/ai-chat/clear', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}});
    messagesEl.innerHTML = `<div class="chat-empty" id="chat-empty">
        <div class="chat-empty-icon">💬</div>
        <div class="chat-empty-title">How can I help you today?</div>
        <p style="font-size:.8rem;max-width:400px">Ask me about your CMS — content, themes, SEO, or anything else.</p>
        <div class="chat-empty-hints">
            <div class="chat-empty-hint" onclick="sendHint(this)">How do I improve my SEO score?</div>
            <div class="chat-empty-hint" onclick="sendHint(this)">Write a blog post about our services</div>
            <div class="chat-empty-hint" onclick="sendHint(this)">How do I customize my theme colors?</div>
            <div class="chat-empty-hint" onclick="sendHint(this)">What content should I create next?</div>
        </div>
    </div>`;
}

function sendHint(el) {
    inputEl.value = el.textContent;
    sendMessage();
}

function parseMd(text) {
    let h = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    h = h.replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>');
    h = h.replace(/`([^`]+)`/g, '<code>$1</code>');
    h = h.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    h = h.replace(/\*(.+?)\*/g, '<em>$1</em>');
    h = h.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    h = h.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    h = h.replace(/^- (.+)$/gm, '<li>$1</li>');
    h = h.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');
    h = h.replace(/\n{2,}/g, '</p><p>');
    return '<p>' + h + '</p>';
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
