<?php $title = 'AI Tutor'; ob_start(); ?>
<style>
/* ═══════════════════════════════════════════════
   AI Tutor — Interactive Learning Assistant
   ═══════════════════════════════════════════════ */
.tutor-wrap{max-width:1200px;margin:0 auto;padding:1rem}

/* ─── Header ─── */
.tutor-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.tutor-title{display:flex;align-items:center;gap:.75rem}
.tutor-title h1{font-size:1.5rem;font-weight:700;color:var(--text-primary)}
.tutor-title .tutor-badge{padding:4px 12px;font-size:11px;font-weight:700;border-radius:20px;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;letter-spacing:.5px}
.tutor-actions{display:flex;align-items:center;gap:.5rem}
.tutor-actions select{padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--bg-tertiary);color:var(--text-primary);font-size:12px;font-family:inherit}
.tutor-actions .btn-xs{padding:6px 12px;font-size:12px;border-radius:8px;border:1px solid var(--border);background:var(--bg-tertiary);color:var(--text-secondary);cursor:pointer;font-family:inherit;transition:all .15s}
.tutor-actions .btn-xs:hover{border-color:var(--accent);color:var(--accent)}

/* ─── Two-panel layout ─── */
.tutor-layout{display:grid;grid-template-columns:340px 1fr;gap:1.5rem;min-height:calc(100vh - 200px)}
@media(max-width:900px){.tutor-layout{grid-template-columns:1fr;min-height:auto}}

/* ─── Left panel: Topics ─── */
.tutor-topics{display:flex;flex-direction:column;gap:.75rem;overflow-y:auto;max-height:calc(100vh - 200px);padding-right:4px}
.tutor-topics::-webkit-scrollbar{width:5px}
.tutor-topics::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}

.topic-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:12px;padding:14px 16px;cursor:pointer;transition:all .2s;position:relative;overflow:hidden}
.topic-card:hover{border-color:var(--accent);transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.15)}
.topic-card.active{border-color:var(--accent);background:var(--accent-muted)}
.topic-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:3px 0 0 3px;transition:background .2s}
.topic-card:hover::before,.topic-card.active::before{background:var(--topic-color,var(--accent))}

.topic-header{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.topic-icon{font-size:20px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:10px;flex-shrink:0}
.topic-info h3{font-size:14px;font-weight:600;color:var(--text-primary);margin:0}
.topic-info p{font-size:12px;color:var(--text-muted);margin:2px 0 0}

.topic-questions{display:none;margin-top:10px;padding-top:10px;border-top:1px solid var(--border)}
.topic-card.active .topic-questions{display:block}
.topic-q{display:block;padding:8px 10px;font-size:13px;color:var(--text-secondary);border-radius:8px;cursor:pointer;transition:all .15s;text-decoration:none;border:none;background:none;width:100%;text-align:left;font-family:inherit;line-height:1.4}
.topic-q:hover{background:var(--accent-muted);color:var(--accent)}
.topic-q::before{content:'💬 ';font-size:12px}

/* ─── Right panel: Chat ─── */
.tutor-chat{display:flex;flex-direction:column;background:var(--bg-primary);border:1px solid var(--border);border-radius:16px;overflow:hidden;min-height:500px}

/* Welcome state */
.tutor-welcome{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem;text-align:center;gap:1rem}
.tutor-welcome-icon{font-size:4rem;filter:grayscale(.3)}
.tutor-welcome h2{font-size:1.25rem;font-weight:700;color:var(--text-primary)}
.tutor-welcome p{font-size:.875rem;color:var(--text-muted);max-width:400px;line-height:1.6}
.tutor-welcome-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.5rem;width:100%;max-width:440px}
.tutor-welcome-hint{padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border);border-radius:10px;font-size:13px;color:var(--text-secondary);cursor:pointer;transition:all .15s;text-align:left;line-height:1.4}
.tutor-welcome-hint:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-muted)}

/* Messages */
.tutor-messages{flex:1;overflow-y:auto;padding:1.25rem;display:flex;flex-direction:column;gap:.75rem}
.tutor-messages::-webkit-scrollbar{width:6px}
.tutor-messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:3px}

.t-msg{display:flex;gap:.75rem;max-width:90%;animation:msgIn .3s ease}
.t-msg.user{align-self:flex-end;flex-direction:row-reverse}
.t-msg.assistant{align-self:flex-start}
@keyframes msgIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

.t-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.t-msg.user .t-avatar{background:rgba(99,102,241,.15)}
.t-msg.assistant .t-avatar{background:rgba(16,185,129,.15)}

.t-bubble{padding:12px 16px;border-radius:14px;font-size:.875rem;line-height:1.7;color:var(--text-primary);word-wrap:break-word}
.t-msg.user .t-bubble{background:var(--accent,#6366f1);color:white;border-bottom-right-radius:4px}
.t-msg.assistant .t-bubble{background:var(--bg-secondary);border:1px solid var(--border);border-bottom-left-radius:4px}

/* Markdown inside bubbles */
.t-bubble p{margin:0 0 .5rem}.t-bubble p:last-child{margin:0}
.t-bubble code{background:rgba(0,0,0,.15);padding:1px 5px;border-radius:3px;font-size:.8rem}
.t-bubble pre{background:rgba(0,0,0,.2);padding:10px;border-radius:8px;overflow-x:auto;margin:.5rem 0;font-size:.8rem}
.t-bubble pre code{background:none;padding:0}
.t-bubble ul,.t-bubble ol{margin:.5rem 0;padding-left:1.25rem}
.t-bubble li{margin-bottom:.25rem}
.t-bubble strong{font-weight:600}
.t-bubble h1,.t-bubble h2,.t-bubble h3{font-size:1rem;font-weight:700;margin:.75rem 0 .25rem;color:var(--text-primary)}
.t-bubble a{color:#93c5fd;text-decoration:underline}

.t-meta{font-size:.65rem;color:var(--text-muted);margin-top:3px}
.t-msg.user .t-meta{text-align:right}

/* Typing indicator */
.typing-dots{display:flex;gap:4px;padding:4px 0}
.typing-dots span{width:6px;height:6px;background:var(--text-muted);border-radius:50%;animation:dotBounce .6s ease-in-out infinite}
.typing-dots span:nth-child(2){animation-delay:.15s}
.typing-dots span:nth-child(3){animation-delay:.3s}
@keyframes dotBounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

/* Input */
.tutor-input-wrap{border-top:1px solid var(--border);padding:12px 16px;display:flex;gap:.5rem;align-items:flex-end;background:var(--bg-primary)}
.tutor-input{flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:12px;background:var(--bg-secondary);color:var(--text-primary);font-size:.875rem;font-family:inherit;resize:none;max-height:100px;line-height:1.5}
.tutor-input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-muted)}
.tutor-input::placeholder{color:var(--text-muted)}
.tutor-send{padding:10px 20px;border:none;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:white;font-weight:600;font-size:.875rem;cursor:pointer;transition:all .15s;white-space:nowrap;font-family:inherit}
.tutor-send:hover{filter:brightness(1.15);transform:translateY(-1px)}
.tutor-send:disabled{opacity:.5;cursor:not-allowed;transform:none}

/* ─── Quick stats bar ─── */
.tutor-stats{display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap}
.tutor-stat{display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--bg-primary);border:1px solid var(--border);border-radius:10px;font-size:13px;color:var(--text-secondary)}
.tutor-stat-icon{font-size:16px}
.tutor-stat strong{color:var(--text-primary);font-weight:600}
</style>

<div class="tutor-wrap">
    <!-- Header -->
    <div class="tutor-header">
        <div class="tutor-title">
            <h1>🎓 AI Tutor</h1>
            <span class="tutor-badge">INTERACTIVE</span>
        </div>
        <div class="tutor-actions">
            <select id="tutor-model">
                <?php foreach ($models as $m): ?>
                <option value="<?= htmlspecialchars($m['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($m['provider'] . ' — ' . $m['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
                <?php if (empty($models)): ?>
                <option value="" disabled>No AI models — configure in AI Settings</option>
                <?php endif; ?>
            </select>
            <button class="btn-xs" onclick="clearTutor()">🗑 Clear Chat</button>
        </div>
    </div>

    <!-- Quick stats -->
    <div class="tutor-stats">
        <div class="tutor-stat"><span class="tutor-stat-icon">🧩</span> <strong><?= count($plugins) ?></strong> plugins enabled</div>
        <div class="tutor-stat"><span class="tutor-stat-icon">📄</span> <strong>8</strong> topic areas</div>
        <div class="tutor-stat"><span class="tutor-stat-icon">💡</span> <strong><?= array_sum(array_map(fn($t) => count($t['questions']), $topics)) ?></strong> guided questions</div>
        <div class="tutor-stat"><span class="tutor-stat-icon">🤖</span> <strong><?= count($models) ?></strong> AI models available</div>
    </div>

    <!-- Two-panel layout -->
    <div class="tutor-layout">
        <!-- Left: Topics -->
        <div class="tutor-topics">
            <?php foreach ($topics as $topic): ?>
            <div class="topic-card" style="--topic-color:<?= $topic['color'] ?>" data-topic="<?= $topic['id'] ?>" onclick="toggleTopic(this)">
                <div class="topic-header">
                    <div class="topic-icon" style="background:<?= $topic['color'] ?>20"><?= $topic['icon'] ?></div>
                    <div class="topic-info">
                        <h3><?= htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars($topic['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
                <div class="topic-questions">
                    <?php foreach ($topic['questions'] as $q): ?>
                    <button class="topic-q" onclick="event.stopPropagation(); askQuestion(this.textContent.replace('💬 ',''))"><?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Right: Chat -->
        <div class="tutor-chat">
            <div class="tutor-messages" id="tutor-messages">
                <?php if (empty($history)): ?>
                <div class="tutor-welcome" id="tutor-welcome">
                    <div class="tutor-welcome-icon">🎓</div>
                    <h2>Welcome to the AI Tutor!</h2>
                    <p>I'm here to help you learn Jessie AI-CMS. Pick a topic on the left, or ask me anything below. No question is too basic!</p>
                    <div class="tutor-welcome-grid">
                        <div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">🚀 What should I do first after installing?</div>
                        <div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">📄 How do I create my first page?</div>
                        <div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">🎨 How do I change my website's theme?</div>
                        <div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">🤖 How do I set up AI features?</div>
                    </div>
                </div>
                <?php else: ?>
                    <?php foreach ($history as $msg): ?>
                    <div class="t-msg <?= $msg['role'] ?>">
                        <div class="t-avatar"><?= $msg['role'] === 'user' ? '👤' : '🎓' ?></div>
                        <div>
                            <div class="t-bubble"><?= $msg['role'] === 'assistant' ? tutorParseMd($msg['content']) : htmlspecialchars($msg['content'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="t-meta"><?= date('H:i', $msg['time'] ?? time()) ?><?= isset($msg['model']) ? ' · ' . htmlspecialchars($msg['model'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="tutor-input-wrap">
                <textarea class="tutor-input" id="tutor-input" placeholder="Ask me anything about the CMS..." rows="1"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendTutor()}"></textarea>
                <button class="tutor-send" id="tutor-send" onclick="sendTutor()">Ask 🎓</button>
            </div>
        </div>
    </div>
</div>

<?php
function tutorParseMd(string $text): string {
    $t = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $t = preg_replace('/```(\w*)\n(.*?)```/s', '<pre><code>$2</code></pre>', $t);
    $t = preg_replace('/`([^`]+)`/', '<code>$1</code>', $t);
    $t = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $t);
    $t = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $t);
    $t = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $t);
    $t = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $t);
    $t = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $t);
    $t = preg_replace('/^- (.+)$/m', '<li>$1</li>', $t);
    $t = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $t);
    $t = preg_replace('/(<li>.*<\/li>\n?)+/', '<ul>$0</ul>', $t);
    $t = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $t);
    $t = preg_replace('/\n{2,}/', '</p><p>', $t);
    return '<p>' . $t . '</p>';
}
?>

<script>
const CSRF = '<?= $csrfToken ?>';
const messagesEl = document.getElementById('tutor-messages');
const inputEl = document.getElementById('tutor-input');
const sendBtn = document.getElementById('tutor-send');

// Auto-resize input
inputEl.addEventListener('input', () => {
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 100) + 'px';
});

function scrollBottom() { messagesEl.scrollTop = messagesEl.scrollHeight; }
scrollBottom();

// Toggle topic cards
function toggleTopic(card) {
    const wasActive = card.classList.contains('active');
    document.querySelectorAll('.topic-card').forEach(c => c.classList.remove('active'));
    if (!wasActive) card.classList.add('active');
}

// Ask a pre-defined question
function askQuestion(text) {
    text = text.replace(/^[^\w\s]*\s*/, '').trim(); // strip leading emoji
    inputEl.value = text;
    sendTutor();
}

function addMsg(role, content, model) {
    const welcome = document.getElementById('tutor-welcome');
    if (welcome) welcome.remove();

    const div = document.createElement('div');
    div.className = 't-msg ' + role;
    const time = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
    const meta = model ? time + ' · ' + model : time;

    if (role === 'assistant') {
        div.innerHTML = '<div class="t-avatar">🎓</div><div><div class="t-bubble">' + content + '</div><div class="t-meta">' + meta + '</div></div>';
    } else {
        const escaped = content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        div.innerHTML = '<div class="t-avatar">👤</div><div><div class="t-bubble">' + escaped + '</div><div class="t-meta">' + meta + '</div></div>';
    }
    messagesEl.appendChild(div);
    scrollBottom();
}

function addTyping() {
    const div = document.createElement('div');
    div.className = 't-msg assistant'; div.id = 'typing-ind';
    div.innerHTML = '<div class="t-avatar">🎓</div><div><div class="t-bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div></div>';
    messagesEl.appendChild(div); scrollBottom();
}
function removeTyping() { const t = document.getElementById('typing-ind'); if (t) t.remove(); }

async function sendTutor() {
    const msg = inputEl.value.trim();
    if (!msg) return;
    const model = document.getElementById('tutor-model').value;
    if (!model) { alert('No AI model selected. Configure AI in Settings first.'); return; }

    addMsg('user', msg);
    inputEl.value = ''; inputEl.style.height = 'auto';
    sendBtn.disabled = true;
    addTyping();

    try {
        const res = await fetch('/api/ai-tutor/ask', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({ message: msg, model: model })
        });
        const data = await res.json();
        removeTyping();

        if (data.error) {
            addMsg('assistant', '<p style="color:#f87171">❌ ' + data.error + '</p>', model);
        } else {
            addMsg('assistant', parseMd(data.response), data.model || model);
        }
    } catch(e) {
        removeTyping();
        addMsg('assistant', '<p style="color:#f87171">❌ Network error. Please try again.</p>');
    }
    sendBtn.disabled = false;
    inputEl.focus();
}

async function clearTutor() {
    if (!confirm('Clear tutor chat history?')) return;
    await fetch('/api/ai-tutor/clear', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}});
    messagesEl.innerHTML = '<div class="tutor-welcome" id="tutor-welcome">' +
        '<div class="tutor-welcome-icon">🎓</div>' +
        '<h2>Welcome to the AI Tutor!</h2>' +
        '<p>I\'m here to help you learn Jessie AI-CMS. Pick a topic on the left, or ask me anything below.</p>' +
        '<div class="tutor-welcome-grid">' +
        '<div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">🚀 What should I do first after installing?</div>' +
        '<div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">📄 How do I create my first page?</div>' +
        '<div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">🎨 How do I change my website\'s theme?</div>' +
        '<div class="tutor-welcome-hint" onclick="askQuestion(this.textContent)">🤖 How do I set up AI features?</div>' +
        '</div></div>';
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
    h = h.replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>');
    h = h.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
    h = h.replace(/\n{2,}/g, '</p><p>');
    return '<p>' + h + '</p>';
}
</script>
<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
