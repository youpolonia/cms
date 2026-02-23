<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = $title ?? 'Social Media Manager';
ob_start();
?>

<style>
.sm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.sm-stat-card {
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
}
.sm-stat-card .stat-value { font-size: 2rem; font-weight: 700; color: var(--accent); }
.sm-stat-card .stat-label { font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem; }

.sm-accounts-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; }
.sm-account-chip {
    display: flex; align-items: center; gap: 0.5rem;
    background: var(--bg-tertiary); border: 1px solid var(--border);
    border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.9rem;
}
.sm-account-chip .dot { width: 8px; height: 8px; border-radius: 50%; }
.sm-account-chip .dot.active { background: var(--success); }
.sm-account-chip .dot.inactive { background: var(--text-muted); }

.sm-section { margin-bottom: 2rem; }
.sm-section h2 { font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }

.sm-table { width: 100%; border-collapse: collapse; }
.sm-table th { text-align: left; padding: 0.75rem; border-bottom: 2px solid var(--border); color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.sm-table td { padding: 0.75rem; border-bottom: 1px solid var(--border); color: var(--text-primary); font-size: 0.9rem; }
.sm-table tr:hover td { background: var(--bg-tertiary); }

.sm-badge {
    display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;
}
.sm-badge-twitter { background: rgba(29,155,240,0.15); color: #1d9bf0; }
.sm-badge-linkedin { background: rgba(0,119,181,0.15); color: #0077b5; }
.sm-badge-facebook { background: rgba(24,119,242,0.15); color: #1877f2; }
.sm-badge-instagram { background: rgba(225,48,108,0.15); color: #e1306c; }

.sm-badge-draft { background: rgba(148,163,184,0.15); color: var(--text-muted); }
.sm-badge-scheduled { background: rgba(245,158,11,0.15); color: var(--warning); }
.sm-badge-published { background: rgba(16,185,129,0.15); color: var(--success); }
.sm-badge-failed { background: rgba(239,68,68,0.15); color: var(--danger); }

.sm-content-preview { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.sm-gen-box {
    background: var(--bg-tertiary); border: 1px solid var(--border); border-radius: 12px;
    padding: 1.5rem; margin-bottom: 2rem;
}
.sm-gen-box h2 { margin-top: 0; }
.sm-gen-form { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
.sm-gen-form select, .sm-gen-form button {
    padding: 0.6rem 1rem; border-radius: 8px; border: 1px solid var(--border);
    background: var(--bg-secondary); color: var(--text-primary); font-size: 0.9rem;
}
.sm-gen-form select { min-width: 250px; }
.sm-gen-form button {
    background: var(--accent); color: #fff; border: none; cursor: pointer; font-weight: 600;
    transition: background 0.2s;
}
.sm-gen-form button:hover { background: var(--accent-hover); }
.sm-gen-form button:disabled { opacity: 0.5; cursor: not-allowed; }

.sm-gen-results { margin-top: 1.5rem; display: none; }
.sm-gen-card {
    background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 8px;
    padding: 1rem; margin-bottom: 0.75rem;
}
.sm-gen-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.sm-gen-card textarea {
    width: 100%; min-height: 80px; background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: 6px; color: var(--text-primary); padding: 0.5rem; font-size: 0.9rem;
    resize: vertical; font-family: inherit;
}
.sm-gen-card-actions { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
.sm-gen-card-actions button {
    padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--border);
    background: var(--bg-tertiary); color: var(--text-primary); font-size: 0.8rem;
    cursor: pointer; transition: background 0.2s;
}
.sm-gen-card-actions button:hover { background: var(--accent); color: #fff; }

.sm-actions { display: flex; gap: 0.5rem; }
.sm-btn {
    padding: 0.3rem 0.6rem; border-radius: 6px; border: 1px solid var(--border);
    background: var(--bg-tertiary); color: var(--text-primary); font-size: 0.8rem;
    cursor: pointer; text-decoration: none; transition: background 0.2s;
}
.sm-btn:hover { background: var(--accent); color: #fff; }
.sm-btn-danger:hover { background: var(--danger); color: #fff; }

.sm-empty { text-align: center; padding: 2rem; color: var(--text-muted); }

/* Schedule modal */
.sm-modal-overlay {
    display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center;
}
.sm-modal-overlay.active { display: flex; }
.sm-modal {
    background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px;
    padding: 1.5rem; min-width: 400px; max-width: 90vw;
}
.sm-modal h3 { margin-top: 0; color: var(--text-primary); }
.sm-modal label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); font-size: 0.85rem; }
.sm-modal input[type="datetime-local"] {
    width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border);
    background: var(--bg-primary); color: var(--text-primary); font-size: 0.9rem;
    margin-bottom: 1rem;
}
.sm-modal-actions { display: flex; gap: 0.5rem; justify-content: flex-end; }
</style>

<div style="max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 style="margin:0; color:var(--text-primary);">📱 Social Media Manager</h1>
        <div style="display:flex; gap:0.5rem;">
            <a href="/admin/social-media/calendar" class="sm-btn" style="padding:0.5rem 1rem;">📅 Calendar</a>
            <a href="/admin/social-media/accounts" class="sm-btn" style="padding:0.5rem 1rem;">🔗 Accounts</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="sm-grid">
        <div class="sm-stat-card">
            <div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
            <div class="stat-label">Total Posts</div>
        </div>
        <div class="sm-stat-card">
            <div class="stat-value" style="color:var(--success)"><?= (int)($stats['published'] ?? 0) ?></div>
            <div class="stat-label">Published</div>
        </div>
        <div class="sm-stat-card">
            <div class="stat-value" style="color:var(--warning)"><?= (int)($stats['scheduled'] ?? 0) ?></div>
            <div class="stat-label">Scheduled</div>
        </div>
        <div class="sm-stat-card">
            <div class="stat-value" style="color:var(--danger)"><?= (int)($stats['failed'] ?? 0) ?></div>
            <div class="stat-label">Failed</div>
        </div>
        <div class="sm-stat-card">
            <div class="stat-value"><?= (int)($stats['this_week'] ?? 0) ?></div>
            <div class="stat-label">This Week</div>
        </div>
    </div>

    <!-- Connected accounts -->
    <div class="sm-accounts-row">
        <?php
        $platformNames = ['twitter' => '𝕏 Twitter', 'linkedin' => '💼 LinkedIn', 'facebook' => '📘 Facebook', 'instagram' => '📸 Instagram'];
        $connectedPlatforms = [];
        foreach ($accounts ?? [] as $acc) { $connectedPlatforms[$acc['platform']] = $acc; }
        foreach ($platformNames as $p => $name): ?>
            <div class="sm-account-chip">
                <span class="dot <?= isset($connectedPlatforms[$p]) && $connectedPlatforms[$p]['active'] ? 'active' : 'inactive' ?>"></span>
                <?= $name ?>
                <?php if (isset($connectedPlatforms[$p])): ?>
                    <span style="color:var(--text-muted); font-size:0.8rem;"><?= h($connectedPlatforms[$p]['account_name'] ?? '') ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- AI Generate -->
    <div class="sm-gen-box">
        <h2 style="margin-top:0; color:var(--text-primary);">✨ AI Post Generator</h2>
        <p style="color:var(--text-secondary); margin-bottom:1rem; font-size:0.9rem;">Select an article to auto-generate optimized posts for all platforms.</p>
        <div class="sm-gen-form">
            <select id="genArticle">
                <option value="">— Select Article —</option>
                <?php foreach ($articles ?? [] as $art): ?>
                    <option value="<?= (int)$art['id'] ?>"><?= h($art['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" id="genBtn" onclick="generatePosts()">🚀 Generate Posts</button>
        </div>
        <div class="sm-gen-results" id="genResults"></div>
    </div>

    <!-- Upcoming Scheduled Posts -->
    <div class="sm-section">
        <h2>⏰ Upcoming Scheduled</h2>
        <?php if (empty($scheduled)): ?>
            <div class="sm-empty">No scheduled posts yet. Generate and schedule some!</div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="sm-table">
                    <thead>
                        <tr><th>Platform</th><th>Content</th><th>Scheduled</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scheduled as $post): ?>
                        <tr>
                            <td><span class="sm-badge sm-badge-<?= h($post['platform']) ?>"><?= h(ucfirst($post['platform'])) ?></span></td>
                            <td class="sm-content-preview"><?= h($post['content']) ?></td>
                            <td><?= h($post['scheduled_at'] ?? '-') ?></td>
                            <td><span class="sm-badge sm-badge-<?= h($post['status']) ?>"><?= h(ucfirst($post['status'])) ?></span></td>
                            <td class="sm-actions">
                                <button class="sm-btn" onclick="publishNow(<?= (int)$post['id'] ?>)">📤 Publish</button>
                                <button class="sm-btn sm-btn-danger" onclick="deletePost(<?= (int)$post['id'] ?>)">🗑</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Published -->
    <div class="sm-section">
        <h2>📊 Recently Published</h2>
        <?php if (empty($recent)): ?>
            <div class="sm-empty">No published posts yet.</div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="sm-table">
                    <thead>
                        <tr><th>Platform</th><th>Content</th><th>Published</th><th>External ID</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $post): ?>
                        <tr>
                            <td><span class="sm-badge sm-badge-<?= h($post['platform']) ?>"><?= h(ucfirst($post['platform'])) ?></span></td>
                            <td class="sm-content-preview"><?= h($post['content']) ?></td>
                            <td><?= h($post['published_at'] ?? '-') ?></td>
                            <td style="font-size:0.8rem; color:var(--text-muted);"><?= h($post['external_id'] ?? '-') ?></td>
                            <td class="sm-actions">
                                <button class="sm-btn sm-btn-danger" onclick="deletePost(<?= (int)$post['id'] ?>)">🗑</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Schedule Modal -->
<div class="sm-modal-overlay" id="scheduleModal">
    <div class="sm-modal">
        <h3>📅 Schedule Post</h3>
        <input type="hidden" id="schedulePostId">
        <label for="scheduleDateTime">Date & Time</label>
        <input type="datetime-local" id="scheduleDateTime">
        <div class="sm-modal-actions">
            <button class="sm-btn" onclick="closeScheduleModal()">Cancel</button>
            <button class="sm-btn" style="background:var(--accent);color:#fff;" onclick="confirmSchedule()">Schedule</button>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?= function_exists("csrf_token") ? csrf_token() : "" ?>';

function generatePosts() {
    const articleId = document.getElementById('genArticle').value;
    if (!articleId) { alert('Please select an article'); return; }

    const btn = document.getElementById('genBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Generating...';

    const form = new FormData();
    form.append('article_id', articleId);
    form.append('csrf_token', csrfToken);

    fetch('/admin/social-media/generate', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = '🚀 Generate Posts';

            const results = document.getElementById('genResults');
            results.style.display = 'block';

            if (!data.ok) {
                results.innerHTML = '<div style="color:var(--danger);padding:1rem;">Error: ' + (data.error || 'Generation failed') + '</div>';
                return;
            }

            let html = '';
            if (data.error) {
                html += '<div style="color:var(--warning);padding:0.5rem;margin-bottom:0.5rem;font-size:0.85rem;">Note: ' + data.error + '</div>';
            }

            (data.posts || []).forEach((post, i) => {
                const id = data.saved_ids ? data.saved_ids[i] : 0;
                html += `
                <div class="sm-gen-card">
                    <div class="sm-gen-card-header">
                        <span class="sm-badge sm-badge-${post.platform}">${post.platform.charAt(0).toUpperCase() + post.platform.slice(1)}</span>
                        <span style="color:var(--text-muted);font-size:0.8rem;">${post.content.length} chars</span>
                    </div>
                    <textarea id="genContent_${id}">${escHtml(post.content)}</textarea>
                    <div class="sm-gen-card-actions">
                        <button onclick="openScheduleModal(${id})">📅 Schedule</button>
                        <button onclick="publishNow(${id})">📤 Publish Now</button>
                    </div>
                </div>`;
            });

            results.innerHTML = html;
        })
        .catch(e => {
            btn.disabled = false;
            btn.textContent = '🚀 Generate Posts';
            alert('Error: ' + e.message);
        });
}

function escHtml(s) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(s));
    return d.innerHTML;
}

function publishNow(id) {
    if (!confirm('Publish this post now?')) return;

    const form = new FormData();
    form.append('csrf_token', csrfToken);

    fetch('/admin/social-media/publish/' + id, { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('Published successfully!');
                location.reload();
            } else {
                alert('Publish failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(e => alert('Error: ' + e.message));
}

function deletePost(id) {
    if (!confirm('Delete this post?')) return;

    const form = new FormData();
    form.append('csrf_token', csrfToken);

    fetch('/admin/social-media/delete/' + id, { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.ok) { location.reload(); }
            else { alert('Delete failed'); }
        })
        .catch(e => alert('Error: ' + e.message));
}

function openScheduleModal(postId) {
    document.getElementById('schedulePostId').value = postId;
    const now = new Date();
    now.setHours(now.getHours() + 1, 0, 0, 0);
    document.getElementById('scheduleDateTime').value = now.toISOString().slice(0, 16);
    document.getElementById('scheduleModal').classList.add('active');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.remove('active');
}

function confirmSchedule() {
    const postId = document.getElementById('schedulePostId').value;
    const dt = document.getElementById('scheduleDateTime').value;
    if (!dt) { alert('Pick a date/time'); return; }

    // Also grab updated content if the textarea exists
    const textarea = document.getElementById('genContent_' + postId);
    const content = textarea ? textarea.value : '';

    const form = new FormData();
    form.append('csrf_token', csrfToken);
    form.append('post_id', postId);
    form.append('scheduled_at', dt.replace('T', ' ') + ':00');
    if (content) form.append('content', content);

    fetch('/admin/social-media/schedule', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            closeScheduleModal();
            if (data.ok) {
                alert('Scheduled!');
                location.reload();
            } else {
                alert('Schedule failed: ' + (data.error || ''));
            }
        })
        .catch(e => alert('Error: ' + e.message));
}
</script>

<?php
$content = ob_get_clean();
require_once CMS_APP . '/views/admin/layouts/topbar.php';
