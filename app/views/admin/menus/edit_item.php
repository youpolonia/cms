<?php
/**
 * Edit Menu Item - Catppuccin Dark UI
 */
$title = 'Edit: ' . esc($item['title']);
ob_start();

$emojis = ['🏠','📄','📁','📂','🔗','📰','📝','📋','📑','📚','📖','✏️','📓','🗒️','📃','🗂️',
'📞','☎️','📱','✉️','📧','📩','💬','💭','🗨️','📢','📣','🔔','👤','👥','👨','👩','👨‍💼','👩‍💼','🧑‍💻','🤝','👋',
'💼','🏢','🏪','📊','📈','📉','💰','💵','🏦','💳','🧾','🛒','🛍️','🏷️','💎','🎁','📦','✨','⭐','💫',
'💻','🖥️','⌨️','🔌','📡','📶','💡','🔧','⚙️','🔩','🎨','📷','🎬','🎥','🎵','🎤','🎧','📻','🖼️',
'📍','🗺️','🌍','🌎','✈️','🚗','🚀','🧭','⏰','📅','🗓️','⏳','📆','🌅','🌙',
'🔒','🔓','🔑','🗝️','🛡️','🔐','🚫','⛔','✅','❌','⚠️','❤️','💖','🧡','💛','💚','💙','💜','❓','❗','💯','🔥'];

$linkType = 'none';
if ($item['page_id']) $linkType = 'page';
elseif ($item['url']) $linkType = 'url';

// Get articles
$pdo = db();
$articlesStmt = $pdo->query("SELECT id, title, slug FROM articles WHERE status = 'published' ORDER BY title ASC");
$articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

// Determine if URL is an article link
$isArticleLink = false;
$articleSlug = '';
if ($linkType === 'url' && preg_match('#^/article/(.+)$#', $item['url'] ?? '', $m)) {
    $isArticleLink = true;
    $articleSlug = $m[1];
    $linkType = 'article';
}
?>

<style>
.page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);
}
.back-link { color: var(--text-muted); text-decoration: none; font-size: 0.875rem; display: inline-block; margin-bottom: 0.25rem; }
.back-link:hover { color: var(--accent); }
.page-header h1 { margin: 0.25rem 0 0; font-size: 1.5rem; }

.edit-layout { display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start; max-width: 900px; }
@media (max-width: 800px) { .edit-layout { grid-template-columns: 1fr; } }

.card { background: var(--bg-primary); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: var(--bg-secondary);
}
.card-title { margin: 0; font-size: 1rem; font-weight: 600; }
.card-body { padding: 1.25rem; }

.form-group { margin-bottom: 1.25rem; }
.form-group:last-child { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.875rem; font-weight: 500; color: var(--text-primary); }
.required { color: var(--danger); }
.form-control {
    width: 100%; padding: 0.6rem 0.75rem; background: var(--bg-secondary);
    border: 1px solid var(--border); border-radius: var(--radius);
    color: var(--text-primary); font-size: 0.9rem; transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-muted); }
.form-control::placeholder { color: var(--text-muted); }
select.form-control { cursor: pointer; }
.form-hint { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.35rem; }

.icon-input-row { display: flex; gap: 0.5rem; }
.icon-input-row .form-control { flex: 1; cursor: pointer; }
.clear-btn {
    background: var(--bg-tertiary); border: 1px solid var(--border); color: var(--text-muted);
    padding: 0.4rem 0.6rem; border-radius: var(--radius); cursor: pointer; font-size: 0.9rem;
}
.clear-btn:hover { border-color: var(--danger); color: var(--danger); }

.icon-picker {
    display: grid; grid-template-columns: repeat(8, 1fr); gap: 0.2rem;
    margin-top: 0.5rem; max-height: 200px; overflow-y: auto;
    padding: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius);
}
.emoji-btn {
    background: transparent; border: none; padding: 0.35rem;
    font-size: 1.15rem; cursor: pointer; border-radius: 4px; transition: background 0.1s, transform 0.1s;
}
.emoji-btn:hover { background: var(--bg-tertiary); transform: scale(1.15); }

.link-tabs {
    display: flex; gap: 0.2rem; background: var(--bg-secondary);
    padding: 0.25rem; border-radius: var(--radius); border: 1px solid var(--border);
}
.tab-btn {
    flex: 1; padding: 0.5rem; border: none; background: transparent;
    border-radius: 6px; cursor: pointer; font-size: 0.8rem;
    color: var(--text-secondary); transition: all 0.15s;
}
.tab-btn:hover { background: var(--bg-tertiary); color: var(--text-primary); }
.tab-btn.active { background: var(--accent); color: #1e1e2e; font-weight: 600; }
.link-tab-content { margin-top: 0.75rem; }

.checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem; }

.btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 1rem; font-size: 0.875rem; font-weight: 500;
    border-radius: var(--radius); border: none; cursor: pointer;
    text-decoration: none; transition: all 0.15s;
}
.btn-primary { background: var(--accent); color: #1e1e2e; }
.btn-primary:hover { background: var(--accent-hover); color: #1e1e2e; }
.btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
.btn-secondary:hover { border-color: var(--accent); }
.btn-danger { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(243,139,168,0.3); }
.btn-danger:hover { background: var(--danger); color: #1e1e2e; }
.btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }

.form-actions {
    display: flex; gap: 0.75rem; padding-top: 1.25rem;
    margin-top: 1.25rem; border-top: 1px solid var(--border);
}

.sidebar-card .card-body { display: flex; flex-direction: column; gap: 0.75rem; }
.sidebar-action {
    display: flex; align-items: center; gap: 0.5rem; width: 100%;
    padding: 0.6rem 0.75rem; background: var(--bg-secondary); border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--text-primary); text-decoration: none;
    font-size: 0.875rem; cursor: pointer; transition: all 0.15s;
}
.sidebar-action:hover { border-color: var(--accent); background: var(--bg-tertiary); }
.sidebar-action.danger { color: var(--danger); }
.sidebar-action.danger:hover { border-color: var(--danger); background: var(--danger-bg); }

.item-meta { font-size: 0.8rem; color: var(--text-muted); }
.item-meta dt { font-weight: 600; color: var(--text-secondary); margin-top: 0.5rem; }
.item-meta dd { margin: 0.15rem 0 0 0; }
</style>

<div class="page-header">
    <div>
        <a href="/admin/menus/<?= (int)$menu['id'] ?>/items" class="back-link">← Back to <?= esc($menu['name']) ?></a>
        <h1>✏️ Edit: <?= esc($item['title']) ?></h1>
    </div>
</div>

<div class="edit-layout">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📝 Item Details</h2>
        </div>
        <div class="card-body">
            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= esc($item['title']) ?>" required placeholder="Link text">
                </div>

                <div class="form-group">
                    <label>Icon</label>
                    <div class="icon-input-row">
                        <input type="text" id="icon" name="icon" class="form-control" value="<?= esc($item['icon'] ?? '') ?>" placeholder="Click to pick..." readonly>
                        <button type="button" class="clear-btn" id="clearIcon" style="<?= empty($item['icon']) ? 'display:none;' : '' ?>">✕</button>
                    </div>
                    <div class="icon-picker" id="iconPicker" style="display: none;">
                        <?php foreach ($emojis as $emoji): ?>
                        <button type="button" class="emoji-btn" data-emoji="<?= $emoji ?>"><?= $emoji ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (!empty($menuItems)): ?>
                <div class="form-group">
                    <label for="parent_id">Parent Item</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">— Top Level —</option>
                        <?php foreach ($menuItems as $mi): ?>
                        <option value="<?= (int)$mi['id'] ?>" <?= ($item['parent_id'] == $mi['id']) ? 'selected' : '' ?>><?= esc($mi['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="form-hint">Make this a child of another item</p>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Link Type</label>
                    <div class="link-tabs">
                        <button type="button" class="tab-btn <?= $linkType === 'page' ? 'active' : '' ?>" data-tab="page">📄 Page</button>
                        <button type="button" class="tab-btn <?= $linkType === 'article' ? 'active' : '' ?>" data-tab="article">📰 Article</button>
                        <button type="button" class="tab-btn <?= $linkType === 'url' ? 'active' : '' ?>" data-tab="url">🔗 URL</button>
                        <button type="button" class="tab-btn <?= $linkType === 'none' ? 'active' : '' ?>" data-tab="none">—</button>
                    </div>
                </div>

                <div id="tab-page" class="link-tab-content" style="<?= $linkType !== 'page' ? 'display:none;' : '' ?>">
                    <div class="form-group">
                        <label for="page_id">Select Page</label>
                        <select id="page_id" name="page_id" class="form-control">
                            <option value="">— Select Page —</option>
                            <?php foreach ($pages as $page): ?>
                            <option value="<?= (int)$page['id'] ?>" <?= ($item['page_id'] == $page['id']) ? 'selected' : '' ?>><?= esc($page['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="tab-article" class="link-tab-content" style="<?= $linkType !== 'article' ? 'display:none;' : '' ?>">
                    <div class="form-group">
                        <label for="article_url">Select Article</label>
                        <select id="article_url" class="form-control" onchange="document.getElementById('url').value = this.value ? '/article/' + this.value : '';">
                            <option value="">— Select Article —</option>
                            <?php foreach ($articles as $article): ?>
                            <option value="<?= esc($article['slug']) ?>" <?= ($articleSlug === $article['slug']) ? 'selected' : '' ?>><?= esc($article['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Links to: /article/[slug]</p>
                    </div>
                </div>

                <div id="tab-url" class="link-tab-content" style="<?= ($linkType !== 'url') ? 'display:none;' : '' ?>">
                    <div class="form-group">
                        <label for="url">Custom URL</label>
                        <input type="text" id="url" name="url" class="form-control" value="<?= esc($item['url'] ?? '') ?>" placeholder="https://... or /page">
                    </div>
                </div>

                <div id="tab-none" class="link-tab-content" style="<?= $linkType !== 'none' ? 'display:none;' : '' ?>">
                    <p class="form-hint">No link — useful for dropdown parent items.</p>
                </div>

                <div class="form-group">
                    <label for="visibility">Visibility</label>
                    <select id="visibility" name="visibility" class="form-control">
                        <option value="all" <?= ($item['visibility'] ?? 'all') === 'all' ? 'selected' : '' ?>>👁️ All visitors</option>
                        <option value="logged_in" <?= ($item['visibility'] ?? '') === 'logged_in' ? 'selected' : '' ?>>🔓 Logged in only</option>
                        <option value="logged_out" <?= ($item['visibility'] ?? '') === 'logged_out' ? 'selected' : '' ?>>🔒 Logged out only</option>
                        <option value="admin" <?= ($item['visibility'] ?? '') === 'admin' ? 'selected' : '' ?>>👑 Admins only</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="open_in_new_tab" value="1" <?= ($item['open_in_new_tab'] ?? ($item['target'] === '_blank' ? 1 : 0)) ? 'checked' : '' ?>>
                        Open in new tab
                    </label>
                </div>

                <div class="form-group">
                    <label for="css_class">CSS Class</label>
                    <input type="text" id="css_class" name="css_class" class="form-control" value="<?= esc($item['css_class'] ?? '') ?>" placeholder="Optional styling class">
                </div>

                <div class="form-group">
                    <label for="description">Tooltip</label>
                    <input type="text" id="description" name="description" class="form-control" value="<?= esc($item['description'] ?? '') ?>" placeholder="Shows on hover">
                </div>

                <input type="hidden" name="target" id="target_field" value="<?= ($item['open_in_new_tab'] ?? ($item['target'] === '_blank' ? 1 : 0)) ? '_blank' : '_self' ?>">

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/items" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="sidebar-card card">
        <div class="card-header">
            <h3 class="card-title">⚡ Actions</h3>
        </div>
        <div class="card-body">
            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/clone" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-action">📋 Clone Item</button>
            </form>

            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/toggle" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-action">
                    <?= ($item['is_active'] ?? 1) ? '👁️‍🗨️ Hide Item' : '👁️ Show Item' ?>
                </button>
            </form>

            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/delete"
                  onsubmit="return confirm('Delete this item?');" style="margin:0;">
                <?= csrf_field() ?>
                <button type="submit" class="sidebar-action danger">🗑️ Delete Item</button>
            </form>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 0.25rem 0;">

            <dl class="item-meta">
                <dt>Created</dt>
                <dd><?= esc($item['created_at'] ?? '—') ?></dd>
                <dt>Sort Order</dt>
                <dd>#<?= (int)($item['sort_order'] ?? 0) ?></dd>
                <?php if ($item['parent_title'] ?? null): ?>
                <dt>Parent</dt>
                <dd><?= esc($item['parent_title']) ?></dd>
                <?php endif; ?>
            </dl>
        </div>
    </div>
</div>

<script>
// Icon picker
var iconInput = document.getElementById('icon');
var iconPicker = document.getElementById('iconPicker');
var clearIcon = document.getElementById('clearIcon');

iconInput.addEventListener('click', function() {
    iconPicker.style.display = iconPicker.style.display === 'none' ? 'grid' : 'none';
});

iconPicker.querySelectorAll('.emoji-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        iconInput.value = this.dataset.emoji;
        iconPicker.style.display = 'none';
        clearIcon.style.display = 'inline-block';
    });
});

clearIcon.addEventListener('click', function() {
    iconInput.value = '';
    this.style.display = 'none';
});

// Link tabs
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.link-tab-content').forEach(function(c) { c.style.display = 'none'; });
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
        if (this.dataset.tab !== 'page') document.getElementById('page_id').value = '';
        if (this.dataset.tab !== 'url' && this.dataset.tab !== 'article') document.getElementById('url').value = '';
    });
});

// Target field sync
document.querySelector('input[name="open_in_new_tab"]').addEventListener('change', function() {
    document.getElementById('target_field').value = this.checked ? '_blank' : '_self';
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
