<?php
$title = 'API Keys';
ob_start();
?>

<style>
.api-page { max-width: 900px; margin: 0 auto; padding: 2rem; }
.api-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.api-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.api-desc { color: var(--text-muted); font-size: 0.875rem; margin-bottom: 2rem; line-height: 1.6; }
.api-desc code { background: var(--bg-tertiary); padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; }

.api-card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem; }
.api-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.api-card-name { font-weight: 600; color: var(--text-primary); }
.api-card-meta { display: flex; gap: 1rem; font-size: 0.75rem; color: var(--text-muted); flex-wrap: wrap; }
.api-key-display { font-family: monospace; font-size: 0.8rem; color: var(--accent); background: var(--bg-tertiary); padding: 8px 12px; border-radius: 6px; word-break: break-all; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 8px; }
.api-key-display .copy-btn { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; padding: 2px; }
.api-key-display .copy-btn:hover { color: var(--accent); }

.api-actions { display: flex; gap: 0.5rem; }
.badge-active { background: rgba(16,185,129,.15); color: #10b981; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.badge-inactive { background: rgba(239,68,68,.15); color: #ef4444; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }

.api-empty { text-align: center; padding: 3rem; color: var(--text-muted); }

.new-key-form { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; margin-bottom: 2rem; display: none; }
.new-key-form.open { display: block; }
.new-key-form .form-row { display: flex; gap: 1rem; align-items: end; }
.new-key-form label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.25rem; }
.new-key-form input { flex: 1; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary); font-size: 0.875rem; }
.new-key-form input:focus { outline: none; border-color: var(--accent); }

.api-docs { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; margin-top: 2rem; }
.api-docs h3 { font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem; }
.api-docs table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.api-docs th { text-align: left; padding: 6px 10px; background: var(--bg-tertiary); color: var(--text-secondary); font-weight: 600; }
.api-docs td { padding: 6px 10px; border-bottom: 1px solid var(--border); color: var(--text-primary); }
.api-docs td code { background: var(--bg-tertiary); padding: 1px 5px; border-radius: 3px; font-size: 0.75rem; }

.btn-xs { padding: 4px 10px; font-size: 0.75rem; border-radius: 5px; border: none; cursor: pointer; font-weight: 500; }
.btn-xs.primary { background: var(--accent); color: white; }
.btn-xs.primary:hover { filter: brightness(1.1); }
.btn-xs.danger { background: rgba(239,68,68,.15); color: #ef4444; }
.btn-xs.danger:hover { background: rgba(239,68,68,.25); }
.btn-xs.secondary { background: var(--bg-tertiary); color: var(--text-secondary); }
.btn-xs.secondary:hover { background: var(--border); }
</style>

<div class="api-page">
    <div class="api-header">
        <h1>🔑 API Keys</h1>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('new-key-form').classList.toggle('open')">+ New API Key</button>
    </div>

    <p class="api-desc">
        API keys authenticate requests to the <strong>REST API</strong> (<code>/api/v1/*</code>).
        Use them to build headless frontends, mobile apps, or integrate with third-party services.
        Pass the key via <code>X-API-Key</code> header or <code>?api_key=</code> query parameter.
    </p>

    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <form id="new-key-form" class="new-key-form" method="POST" action="/admin/api-keys/create">
        <input type="hidden" name="_token" value="<?= $csrfToken ?>">
        <div class="form-row">
            <div style="flex:1">
                <label for="key-name">Key Name</label>
                <input type="text" name="name" id="key-name" placeholder="e.g. Mobile App, Next.js Frontend" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Generate Key</button>
        </div>
    </form>

    <?php if (empty($keys)): ?>
    <div class="api-empty">
        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔑</div>
        <p>No API keys yet. Create one to get started.</p>
    </div>
    <?php else: ?>
    <?php foreach ($keys as $key): ?>
    <div class="api-card">
        <div class="api-card-header">
            <span class="api-card-name"><?= esc($key['name']) ?></span>
            <span class="<?= $key['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                <?= $key['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
        </div>
        <div class="api-key-display">
            <span id="key-<?= $key['id'] ?>"><?= esc($key['api_key']) ?></span>
            <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('key-<?= $key['id'] ?>').textContent);this.textContent='✓';setTimeout(()=>this.textContent='📋',1500)" title="Copy">📋</button>
        </div>
        <div class="api-card-meta">
            <span>Created: <?= date('M j, Y', strtotime($key['created_at'])) ?></span>
            <span>Last used: <?= $key['last_used_at'] ? date('M j, Y H:i', strtotime($key['last_used_at'])) : 'Never' ?></span>
            <span>Requests: <?= number_format($key['request_count']) ?></span>
        </div>
        <div class="api-actions" style="margin-top: 0.75rem;">
            <form method="POST" action="/admin/api-keys/<?= $key['id'] ?>/toggle" style="display:inline">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                <button type="submit" class="btn-xs secondary"><?= $key['is_active'] ? '⏸ Disable' : '▶ Enable' ?></button>
            </form>
            <form method="POST" action="/admin/api-keys/<?= $key['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this API key? This cannot be undone.')">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                <button type="submit" class="btn-xs danger">🗑 Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="api-docs">
        <h3>📡 API Endpoints</h3>
        <table>
            <thead>
                <tr><th>Method</th><th>Endpoint</th><th>Description</th></tr>
            </thead>
            <tbody>
                <tr><td>GET</td><td><code>/api/v1/site</code></td><td>Site metadata (name, theme, language)</td></tr>
                <tr><td>GET</td><td><code>/api/v1/pages</code></td><td>List pages (paginated, filterable)</td></tr>
                <tr><td>GET</td><td><code>/api/v1/pages/{slug}</code></td><td>Single page by slug</td></tr>
                <tr><td>GET</td><td><code>/api/v1/articles</code></td><td>List articles (?category=, ?status=)</td></tr>
                <tr><td>GET</td><td><code>/api/v1/articles/{slug}</code></td><td>Single article by slug</td></tr>
                <tr><td>GET</td><td><code>/api/v1/menus</code></td><td>All menus</td></tr>
                <tr><td>GET</td><td><code>/api/v1/menus/{location}</code></td><td>Menu items by location (tree)</td></tr>
                <tr><td>GET</td><td><code>/api/v1/categories</code></td><td>Article categories</td></tr>
                <tr><td>GET</td><td><code>/api/v1/media</code></td><td>Media library (?type=image)</td></tr>
                <tr><td>GET</td><td><code>/api/v1/search?q=</code></td><td>Search pages and articles</td></tr>
                <tr><td>GET</td><td><code>/api/v1/theme</code></td><td>Current theme info + settings</td></tr>
            </tbody>
        </table>

        <h3 style="margin-top: 1rem;">🔧 Query Parameters</h3>
        <table>
            <thead>
                <tr><th>Parameter</th><th>Description</th><th>Example</th></tr>
            </thead>
            <tbody>
                <tr><td><code>page</code></td><td>Page number (default: 1)</td><td><code>?page=2</code></td></tr>
                <tr><td><code>per_page</code></td><td>Items per page (1–100, default: 20)</td><td><code>?per_page=50</code></td></tr>
                <tr><td><code>fields</code></td><td>Select specific fields</td><td><code>?fields=id,title,slug</code></td></tr>
                <tr><td><code>status</code></td><td>Filter by status</td><td><code>?status=draft</code></td></tr>
                <tr><td><code>category</code></td><td>Filter articles by category slug</td><td><code>?category=tech</code></td></tr>
                <tr><td><code>type</code></td><td>Filter media by MIME type</td><td><code>?type=image</code></td></tr>
            </tbody>
        </table>

        <h3 style="margin-top: 1rem;">💻 Example</h3>
        <pre style="background: var(--bg-tertiary); padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 0.8rem; color: var(--text-primary);">curl -H "X-API-Key: YOUR_KEY" http://yoursite.com/api/v1/pages?per_page=5&fields=id,title,slug</pre>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
