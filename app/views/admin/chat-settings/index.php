<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'AI Chatbot Settings';
$layout = 'admin';
ob_start();
$s = $settings;
?>
<style>
.chat-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
.chat-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:24px;margin-bottom:20px}
.chat-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin-bottom:16px}
.chat-field{margin-bottom:16px}
.chat-field label{display:block;font-size:.85rem;color:var(--text,#e2e8f0);margin-bottom:4px;font-weight:500}
.chat-field small{display:block;font-size:.75rem;color:var(--muted,#94a3b8);margin-top:2px}
.chat-field input,.chat-field textarea,.chat-field select{width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text,#e2e8f0);font-size:.9rem;font-family:inherit}
.chat-field textarea{min-height:80px;resize:vertical}
.chat-toggle{display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg,#0f172a);border-radius:8px;margin-bottom:20px}
.chat-toggle input[type=checkbox]{width:20px;height:20px;accent-color:#6366f1}
.chat-toggle label{font-size:1rem;font-weight:600;color:var(--text,#e2e8f0);cursor:pointer}
.chat-btn{padding:12px 24px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.95rem;font-weight:600;width:100%}
.chat-btn:hover{background:#4f46e5}
.stat-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.stat-card{text-align:center;padding:16px;background:var(--bg,#0f172a);border-radius:8px}
.stat-card .num{font-size:1.8rem;font-weight:700;color:var(--text,#e2e8f0)}
.stat-card .lbl{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
@media(max-width:768px){.chat-grid{grid-template-columns:1fr}.stat-grid{grid-template-columns:1fr}}
</style>

<h1 style="font-size:1.5rem;font-weight:700;margin-bottom:24px">🤖 AI Chatbot</h1>

<form method="post" action="/admin/chat-settings">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="chat-grid">
        <div>
            <div class="chat-card">
                <div class="chat-toggle">
                    <input type="hidden" name="chatbot_enabled" value="0">
                    <input type="checkbox" id="cbEnabled" name="chatbot_enabled" value="1" <?= $s['chatbot_enabled'] === '1' ? 'checked' : '' ?>>
                    <label for="cbEnabled">Enable chatbot on your website</label>
                </div>

                <h3>AI Provider</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="chat-field">
                        <label>Provider</label>
                        <select name="chatbot_provider" id="cbProvider">
                            <option value="">Auto-detect</option>
                            <?php foreach ($providers as $key => $p): ?>
                                <option value="<?= h($key) ?>" <?= $s['chatbot_provider'] === $key ? 'selected' : '' ?>><?= h($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chat-field">
                        <label>Model</label>
                        <input type="text" name="chatbot_model" value="<?= h($s['chatbot_model']) ?>" placeholder="Auto-detect">
                        <small>Leave empty for provider's default model</small>
                    </div>
                </div>
            </div>

            <div class="chat-card">
                <h3>Widget Settings</h3>
                <div class="chat-field">
                    <label>Welcome Message</label>
                    <input type="text" name="chatbot_welcome" value="<?= h($s['chatbot_welcome']) ?>" placeholder="Hi! 👋 How can I help you today?">
                </div>
                <div class="chat-field">
                    <label>Suggested Questions</label>
                    <textarea name="chatbot_suggestions" placeholder="What services do you offer?&#10;How can I contact you?&#10;Tell me about your pricing"><?= h($s['chatbot_suggestions']) ?></textarea>
                    <small>One question per line. Shown as quick-click buttons in the chat.</small>
                </div>
                <div class="chat-field">
                    <label>Custom Instructions</label>
                    <textarea name="chatbot_custom_instructions" placeholder="E.g.: Always mention our free consultation offer. Respond in Polish."><?= h($s['chatbot_custom_instructions']) ?></textarea>
                    <small>Additional instructions for the AI. The chatbot already knows your site content.</small>
                </div>
            </div>

            <button type="submit" class="chat-btn">💾 Save Settings</button>
        </div>

        <div>
            <div class="chat-card">
                <h3>Chat Stats</h3>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="num"><?= $stats['total_sessions'] ?></div>
                        <div class="lbl">Total Chats</div>
                    </div>
                    <div class="stat-card">
                        <div class="num"><?= $stats['this_week'] ?></div>
                        <div class="lbl">This Week</div>
                    </div>
                    <div class="stat-card">
                        <div class="num"><?= $stats['today'] ?></div>
                        <div class="lbl">Today</div>
                    </div>
                </div>
                <div style="margin-top:16px;text-align:center">
                    <a href="/admin/chat-settings/sessions" style="color:var(--primary,#6366f1);font-size:.85rem">View all chat sessions →</a>
                </div>
            </div>

            <div class="chat-card">
                <h3>How It Works</h3>
                <div style="font-size:.85rem;color:var(--muted,#94a3b8);line-height:1.7">
                    <p>The AI chatbot automatically learns from your website content:</p>
                    <ul style="margin:8px 0;padding-left:20px">
                        <li>📄 All published pages</li>
                        <li>📰 All published articles</li>
                        <li>📞 Contact information</li>
                        <li>ℹ️ Site name & description</li>
                    </ul>
                    <p style="margin-top:8px"><strong>Zero configuration needed</strong> — just enable it and the chatbot knows your site.</p>
                    <p style="margin-top:8px">Content is refreshed every hour automatically.</p>
                </div>
            </div>

            <?php if (empty($providers)): ?>
            <div class="chat-card" style="border-color:#ef4444">
                <h3 style="color:#ef4444">⚠️ No AI Provider</h3>
                <p style="font-size:.85rem;color:var(--muted)">
                    Configure an AI provider in <a href="/admin/ai-settings" style="color:var(--primary,#6366f1)">AI Settings</a> to enable the chatbot.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
