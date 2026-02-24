<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$suppliers = $suppliers ?? [];
$imports = $imports ?? [];
ob_start();
?>
<style>
.ds-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#6366f1}
.form-group textarea{min-height:80px;resize:vertical;font-family:inherit}
.form-group .hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s}
.btn-ds:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(99,102,241,.4)}
.btn-ds:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.ai-spinner{display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:aispin .6s linear infinite}
@keyframes aispin{to{transform:rotate(360deg)}}

/* Tabs */
.imp-tabs{display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--border,#334155)}
.imp-tab{background:transparent;border:none;color:var(--muted,#94a3b8);padding:10px 18px;font-size:.85rem;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.2s}
.imp-tab:hover{color:var(--text,#e2e8f0)}
.imp-tab.active{color:#a5b4fc;border-bottom-color:#6366f1}
.imp-panel{display:none}
.imp-panel.active{display:block}

/* Toggle */
.toggle-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.toggle-row input[type="checkbox"]{width:18px;height:18px;accent-color:#6366f1}
.toggle-row label{font-size:.82rem;color:var(--text,#e2e8f0);cursor:pointer}

/* Results */
.import-result{background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:16px;margin-top:16px;display:none}
.import-result.show{display:block}
.import-result.error{background:rgba(239,68,68,.06);border-color:rgba(239,68,68,.2)}
.result-product{display:flex;gap:14px;align-items:flex-start;margin-top:10px}
.result-product img{width:80px;height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--border,#334155)}
.result-product .info{flex:1}
.result-product .name{font-weight:700;font-size:.95rem;color:var(--text,#e2e8f0)}
.result-product .prices{display:flex;gap:16px;margin-top:6px;font-size:.82rem}
.result-product .prices .cost{color:#f59e0b}
.result-product .prices .sell{color:#10b981;font-weight:700}
.result-product .prices .margin{color:#a5b4fc}

/* History */
.import-history{margin-top:24px}
.imp-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(51,65,85,.5);font-size:.82rem}
.imp-row:last-child{border-bottom:none}
.imp-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.imp-badge.completed{background:rgba(16,185,129,.15);color:#34d399}
.imp-badge.failed{background:rgba(239,68,68,.15);color:#fca5a5}
.imp-badge.pending{background:rgba(245,158,11,.15);color:#fbbf24}
.imp-badge.processing{background:rgba(99,102,241,.15);color:#a5b4fc}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>📥 Import Products</h1>
        <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
    </div>

    <!-- Tabs -->
    <div class="imp-tabs">
        <button class="imp-tab active" onclick="switchTab('url',this)">🔗 Import from URL</button>
        <button class="imp-tab" onclick="switchTab('batch',this)">📋 Batch Import</button>
        <button class="imp-tab" onclick="switchTab('csv',this)">📄 CSV Import</button>
    </div>

    <!-- URL Import -->
    <div class="imp-panel active" id="panel-url">
        <div class="ds-card">
            <h3>🔗 AI One-Click Import</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">Paste a product URL → AI extracts all data → rewrites description → generates SEO → creates product. Magic. ✨</p>

            <div class="form-group">
                <label>Product URL *</label>
                <input type="url" id="import-url" placeholder="https://aliexpress.com/item/123456.html or any product page">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Supplier</label>
                    <select id="import-supplier">
                        <option value="">— No supplier —</option>
                        <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <select id="import-lang">
                        <option value="en">🇬🇧 English</option>
                        <option value="pl">🇵🇱 Polish</option>
                        <option value="de">🇩🇪 German</option>
                        <option value="fr">🇫🇷 French</option>
                        <option value="es">🇪🇸 Spanish</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:16px">
                <div class="toggle-row"><input type="checkbox" id="opt-rewrite" checked><label for="opt-rewrite">✍️ AI Rewrite description (unique content)</label></div>
                <div class="toggle-row"><input type="checkbox" id="opt-seo" checked><label for="opt-seo">🔍 AI Generate SEO meta</label></div>
                <div class="toggle-row"><input type="checkbox" id="opt-images"><label for="opt-images">🖼️ AI Process images (remove BG + ALT text)</label></div>
            </div>

            <button type="button" class="btn-ds" onclick="importUrl()" id="btn-import-url">
                <span class="ai-spinner" id="url-spinner"></span>
                ✨ Import with AI
            </button>

            <div class="import-result" id="url-result"></div>
        </div>
    </div>

    <!-- Batch Import -->
    <div class="imp-panel" id="panel-batch">
        <div class="ds-card">
            <h3>📋 Batch URL Import</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">Paste multiple URLs (one per line). Each will go through the full AI pipeline.</p>

            <div class="form-group">
                <label>Product URLs (one per line) *</label>
                <textarea id="batch-urls" rows="8" placeholder="https://example.com/product-1&#10;https://example.com/product-2&#10;https://example.com/product-3"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Supplier</label>
                    <select id="batch-supplier">
                        <option value="">— No supplier —</option>
                        <?php foreach ($suppliers as $s): ?>
                        <option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <select id="batch-lang">
                        <option value="en">English</option>
                        <option value="pl">Polish</option>
                        <option value="de">German</option>
                    </select>
                </div>
            </div>

            <button type="button" class="btn-ds" onclick="importBatch()" id="btn-import-batch">
                <span class="ai-spinner" id="batch-spinner"></span>
                📋 Import All URLs
            </button>

            <div class="import-result" id="batch-result"></div>
        </div>
    </div>

    <!-- CSV Import -->
    <div class="imp-panel" id="panel-csv">
        <div class="ds-card">
            <h3>📄 CSV Import</h3>
            <p style="color:var(--muted,#94a3b8);font-size:.82rem;margin:0 0 16px">Paste CSV data with product information. Map columns to fields below.</p>

            <div class="form-group">
                <label>CSV Data *</label>
                <textarea id="csv-content" rows="6" placeholder="name,price,description,image_url&#10;Product 1,29.99,Great product,https://img.com/1.jpg"></textarea>
            </div>

            <div class="form-group">
                <label>Column Mapping</label>
                <div class="hint" style="margin-bottom:8px">Enter column numbers (0-based) for each field</div>
                <div class="form-row">
                    <div class="form-group"><label style="font-size:.75rem">Name (col #)</label><input type="number" id="csv-col-name" value="0" min="0" max="20"></div>
                    <div class="form-group"><label style="font-size:.75rem">Price (col #)</label><input type="number" id="csv-col-price" value="1" min="0" max="20"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label style="font-size:.75rem">Description (col #)</label><input type="number" id="csv-col-desc" value="2" min="0" max="20"></div>
                    <div class="form-group"><label style="font-size:.75rem">Image URL (col #)</label><input type="number" id="csv-col-image" value="3" min="0" max="20"></div>
                </div>
            </div>

            <button type="button" class="btn-ds" onclick="importCsv()" id="btn-import-csv">
                <span class="ai-spinner" id="csv-spinner"></span>
                📄 Import CSV
            </button>

            <div class="import-result" id="csv-result"></div>
        </div>
    </div>

    <!-- Import History -->
    <?php if (!empty($imports)): ?>
    <div class="ds-card import-history">
        <h3>📋 Recent Import History</h3>
        <?php foreach ($imports as $imp): ?>
        <div class="imp-row">
            <span class="imp-badge <?= h($imp['status']) ?>"><?= h($imp['status']) ?></span>
            <span style="color:var(--text,#e2e8f0);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($imp['product_name'] ?? $imp['source_url'] ?? $imp['source_type']) ?></span>
            <span style="color:var(--muted,#94a3b8);white-space:nowrap"><?= h(date('M j, H:i', strtotime($imp['created_at']))) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.imp-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.imp-panel').forEach(function(p){ p.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
}

function getCsrf() {
    var el = document.querySelector('input[name="csrf_token"]');
    return el ? el.value : '';
}

function apiCall(url, payload, spinnerId, callback) {
    var spinner = document.getElementById(spinnerId);
    if (spinner) spinner.style.display = 'inline-block';
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': getCsrf()},
        body: JSON.stringify(payload),
        credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { callback(null, data); })
    .catch(function(e) { callback(e.message || 'Network error', null); })
    .finally(function() { if (spinner) spinner.style.display = 'none'; });
}

function esc(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

function importUrl() {
    var url = document.getElementById('import-url').value.trim();
    if (!url) { alert('Enter a product URL'); return; }

    var btn = document.getElementById('btn-import-url');
    btn.disabled = true;
    var resEl = document.getElementById('url-result');
    resEl.classList.remove('show', 'error');

    var payload = {
        url: url,
        supplier_id: document.getElementById('import-supplier').value || null,
        language: document.getElementById('import-lang').value,
        ai_rewrite: document.getElementById('opt-rewrite').checked,
        ai_seo: document.getElementById('opt-seo').checked,
        ai_images: document.getElementById('opt-images').checked
    };

    apiCall('/api/dropshipping/import-url', payload, 'url-spinner', function(err, data) {
        btn.disabled = false;
        if (err || !data || !data.ok) {
            resEl.classList.add('show', 'error');
            resEl.innerHTML = '<strong style="color:#fca5a5">❌ Import Failed</strong><p style="color:#fca5a5;font-size:.85rem;margin:6px 0 0">' + esc((data && data.error) ? data.error : (err || 'Unknown error')) + '</p>';
            return;
        }

        var d = data.data || {};
        var html = '<strong style="color:#34d399">✅ Product Imported Successfully!</strong>';
        html += '<div class="result-product">';
        if (d.image) html += '<img src="' + esc(d.image) + '" alt="Product">';
        html += '<div class="info">';
        html += '<div class="name">' + esc(d.name || 'Imported Product') + '</div>';
        html += '<div class="prices">';
        html += '<span class="cost">Cost: $' + (d.supplier_price || 0).toFixed(2) + '</span>';
        html += '<span class="sell">Sell: $' + (d.sell_price || 0).toFixed(2) + '</span>';
        html += '<span class="margin">Margin: $' + (d.margin || 0).toFixed(2) + ' (' + (d.margin_pct || 0) + '%)</span>';
        html += '</div>';
        html += '<div style="margin-top:6px;font-size:.78rem;color:var(--muted,#94a3b8)">';
        html += 'Rule: ' + esc(d.rule_name || 'Default') + ' • ';
        html += (d.ai_rewritten ? '✅' : '❌') + ' Rewrite • ';
        html += (d.ai_seo ? '✅' : '❌') + ' SEO • ';
        html += (d.ai_images ? '✅' : '❌') + ' Images';
        html += '</div>';
        html += '<div style="margin-top:8px"><a href="/admin/shop/products/' + data.product_id + '/edit" class="btn-ds" style="padding:6px 14px;font-size:.78rem;text-decoration:none">✏️ Edit Product</a></div>';
        html += '</div></div>';

        resEl.classList.add('show');
        resEl.classList.remove('error');
        resEl.innerHTML = html;
    });
}

function importBatch() {
    var urls = document.getElementById('batch-urls').value.trim();
    if (!urls) { alert('Enter URLs'); return; }

    var btn = document.getElementById('btn-import-batch');
    btn.disabled = true;
    var resEl = document.getElementById('batch-result');
    resEl.classList.remove('show', 'error');
    resEl.classList.add('show');
    resEl.innerHTML = '<div style="color:#a5b4fc;font-size:.85rem">⏳ Processing... This may take a while for multiple URLs.</div>';

    apiCall('/api/dropshipping/import-batch', {
        urls: urls,
        supplier_id: document.getElementById('batch-supplier').value || null,
        language: document.getElementById('batch-lang').value,
        ai_rewrite: true,
        ai_seo: true
    }, 'batch-spinner', function(err, data) {
        btn.disabled = false;
        if (err || !data || !data.ok) {
            resEl.classList.add('error');
            resEl.innerHTML = '<strong style="color:#fca5a5">❌ Batch import failed</strong>';
            return;
        }
        var html = '<strong style="color:#34d399">✅ Batch Complete: ' + data.success + ' imported, ' + data.failed + ' failed</strong>';
        if (data.results) {
            html += '<div style="margin-top:10px;font-size:.82rem">';
            data.results.forEach(function(r) {
                var icon = r.ok ? '✅' : '❌';
                html += '<div style="padding:4px 0;border-bottom:1px solid rgba(51,65,85,.3)">' + icon + ' ' + esc(r.url) + (r.error ? ' — <span style="color:#fca5a5">' + esc(r.error) + '</span>' : '') + '</div>';
            });
            html += '</div>';
        }
        resEl.innerHTML = html;
    });
}

function importCsv() {
    var csv = document.getElementById('csv-content').value.trim();
    if (!csv) { alert('Enter CSV data'); return; }

    var columnMap = {};
    columnMap[document.getElementById('csv-col-name').value] = 'name';
    columnMap[document.getElementById('csv-col-price').value] = 'price';
    columnMap[document.getElementById('csv-col-desc').value] = 'description';
    columnMap[document.getElementById('csv-col-image').value] = 'image';

    var btn = document.getElementById('btn-import-csv');
    btn.disabled = true;
    var resEl = document.getElementById('csv-result');
    resEl.classList.remove('show', 'error');

    apiCall('/api/dropshipping/import-csv', {
        csv_content: csv,
        column_map: columnMap
    }, 'csv-spinner', function(err, data) {
        btn.disabled = false;
        resEl.classList.add('show');
        if (err || !data || !data.ok) {
            resEl.classList.add('error');
            resEl.innerHTML = '<strong style="color:#fca5a5">❌ CSV import failed: ' + esc((data && data.error) || err) + '</strong>';
            return;
        }
        resEl.innerHTML = '<strong style="color:#34d399">✅ CSV Import: ' + data.success + ' imported, ' + data.failed + ' failed out of ' + data.total + '</strong>';
    });
}
</script>

<?php
$content = ob_get_clean();
$title = 'Import Products';
require CMS_APP . '/views/admin/layouts/topbar.php';
