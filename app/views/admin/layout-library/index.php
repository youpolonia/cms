<?php
/**
 * Layout Library Browser
 * Browse, preview, and import layout templates
 */
$title = 'Layout Library';
ob_start();

$layouts = $layouts ?? [];
$categories = $categories ?? [];
$industries = $industries ?? [];
$styles = $styles ?? [];
$filters = $filters ?? [];
?>

<style>
:root {
    --ll-bg: #1e1e2e;
    --ll-surface: #313244;
    --ll-border: #45475a;
    --ll-text: #cdd6f4;
    --ll-text-dim: #a6adc8;
    --ll-accent: #89b4fa;
    --ll-success: #a6e3a1;
    --ll-warning: #f9e2af;
}

.ll-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
}

.ll-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.ll-header h1 {
    font-size: 28px;
    color: var(--ll-text);
    margin: 0;
}

.ll-filters {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    padding: 20px;
    background: var(--ll-surface);
    border-radius: 12px;
    margin-bottom: 24px;
}

.ll-filters select,
.ll-filters input {
    padding: 10px 14px;
    background: var(--ll-bg);
    border: 1px solid var(--ll-border);
    border-radius: 8px;
    color: var(--ll-text);
    font-size: 14px;
    min-width: 150px;
}

.ll-filters select:focus,
.ll-filters input:focus {
    outline: none;
    border-color: var(--ll-accent);
}

.ll-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.ll-card {
    background: var(--ll-surface);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--ll-border);
    transition: transform 0.2s, box-shadow 0.2s;
}

.ll-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.3);
}

.ll-card-thumb {
    height: 180px;
    background: linear-gradient(135deg, #313244 0%, #1e1e2e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ll-text-dim);
    font-size: 48px;
    position: relative;
}

.ll-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ll-card-badges {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 6px;
}

.ll-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.ll-badge-ai {
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
}

.ll-badge-premium {
    background: var(--ll-warning);
    color: #1e1e2e;
}

.ll-badge-pages {
    background: var(--ll-accent);
    color: #1e1e2e;
}

.ll-card-body {
    padding: 20px;
}

.ll-card-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--ll-text);
    margin: 0 0 8px 0;
}

.ll-card-desc {
    font-size: 13px;
    color: var(--ll-text-dim);
    margin: 0 0 12px 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ll-card-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--ll-text-dim);
    margin-bottom: 16px;
}

.ll-card-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.ll-card-actions {
    display: flex;
    gap: 10px;
}

.ll-btn {
    flex: 1;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--ll-border);
    background: var(--ll-bg);
    color: var(--ll-text);
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
}

.ll-btn:hover {
    border-color: var(--ll-accent);
    color: var(--ll-accent);
}

.ll-btn-primary {
    background: var(--ll-accent);
    border-color: var(--ll-accent);
    color: #1e1e2e;
}

.ll-btn-primary:hover {
    background: #b4befe;
    border-color: #b4befe;
    color: #1e1e2e;
}

.ll-btn-danger {
    background: #f38ba8;
    color: #1e1e2e;
    flex: 0;
    padding: 10px 12px;
}

.ll-btn-danger:hover {
    background: #eba0ac;
    border-color: #eba0ac;
    color: #1e1e2e;
}

.ll-empty {
    text-align: center;
    padding: 80px 20px;
    color: var(--ll-text-dim);
}

.ll-empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.ll-empty h3 {
    color: var(--ll-text);
    margin: 0 0 8px 0;
}

/* Modal */
.ll-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.8);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.ll-modal.active {
    display: flex;
}

.ll-modal-content {
    background: var(--ll-bg);
    border-radius: 16px;
    width: 100%;
    max-width: 1000px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.ll-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--ll-border);
}

.ll-modal-header h2 {
    margin: 0;
    color: var(--ll-text);
    font-size: 20px;
}

.ll-modal-close {
    background: none;
    border: none;
    color: var(--ll-text-dim);
    font-size: 24px;
    cursor: pointer;
    padding: 4px;
}

.ll-modal-close:hover {
    color: var(--ll-text);
}

.ll-modal-body {
    flex: 1;
    overflow: auto;
    padding: 24px;
}

.ll-preview-frame {
    width: 100%;
    height: 400px;
    border: 1px solid var(--ll-border);
    border-radius: 8px;
    background: white;
}

.ll-modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-top: 1px solid var(--ll-border);
    gap: 12px;
}

.ll-pages-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}

.ll-page-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--ll-surface);
    border-radius: 20px;
    font-size: 13px;
    color: var(--ll-text);
}

.ll-page-chip input {
    accent-color: var(--ll-accent);
}

.ll-status {
    padding: 12px 16px;
    border-radius: 8px;
    margin-top: 16px;
    display: none;
}

.ll-status.success {
    display: block;
    background: rgba(166, 227, 161, 0.15);
    border: 1px solid var(--ll-success);
    color: var(--ll-success);
}

.ll-status.error {
    display: block;
    background: rgba(243, 139, 168, 0.15);
    border: 1px solid #f38ba8;
    color: #f38ba8;
}
</style>

<div class="ll-container">
    <div class="ll-header">
        <h1>📚 Layout Library</h1>
        <div style="display:flex;gap:12px;">
            <button class="ll-btn" onclick="openUploadModal()">
                📤 Upload Layout
            </button>
            <a href="/admin/ai-theme-builder" class="ll-btn ll-btn-primary">
                ✨ Generate New Layout
            </a>
        </div>
    </div>

    <div class="ll-filters">
        <select id="filterCategory" onchange="applyFilters()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($cat)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="filterIndustry" onchange="applyFilters()">
            <option value="">All Industries</option>
            <?php foreach ($industries as $ind): ?>
                <option value="<?= htmlspecialchars($ind) ?>" <?= $filters['industry'] === $ind ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($ind)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="filterStyle" onchange="applyFilters()">
            <option value="">All Styles</option>
            <?php foreach ($styles as $sty): ?>
                <option value="<?= htmlspecialchars($sty) ?>" <?= $filters['style'] === $sty ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($sty)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" id="filterSearch" placeholder="Search layouts..."
               value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
               onkeyup="if(event.key==='Enter')applyFilters()">

        <button class="ll-btn" onclick="applyFilters()">🔍 Search</button>
        <button class="ll-btn" onclick="clearFilters()">✕ Clear</button>
    </div>

    <?php if (empty($layouts)): ?>
        <div class="ll-empty">
            <div class="ll-empty-icon">📭</div>
            <h3>No layouts found</h3>
            <p>Generate your first layout using AI Theme Builder</p>
            <a href="/admin/ai-theme-builder" class="ll-btn ll-btn-primary" style="display:inline-block;margin-top:16px">
                ✨ Generate Layout
            </a>
        </div>
    <?php else: ?>
        <div class="ll-grid">
            <?php foreach ($layouts as $layout): ?>
                <div class="ll-card" data-layout-id="<?= $layout['id'] ?>">
                    <div class="ll-card-thumb">
                        <?php if ($layout['thumbnail']): ?>
                            <img src="<?= htmlspecialchars($layout['thumbnail']) ?>" alt="">
                        <?php else: ?>
                            🎨
                        <?php endif; ?>
                        <div class="ll-card-badges">
                            <?php if ($layout['is_ai_generated']): ?>
                                <span class="ll-badge ll-badge-ai">AI</span>
                            <?php endif; ?>
                            <?php if ($layout['is_premium']): ?>
                                <span class="ll-badge ll-badge-premium">Premium</span>
                            <?php endif; ?>
                            <span class="ll-badge ll-badge-pages"><?= $layout['page_count'] ?> pages</span>
                        </div>
                    </div>
                    <div class="ll-card-body">
                        <h3 class="ll-card-title"><?= htmlspecialchars($layout['name']) ?></h3>
                        <p class="ll-card-desc"><?= htmlspecialchars($layout['description'] ?? 'No description') ?></p>
                        <div class="ll-card-meta">
                            <span>📁 <?= ucfirst($layout['category']) ?></span>
                            <?php if ($layout['industry']): ?>
                                <span>🏢 <?= ucfirst($layout['industry']) ?></span>
                            <?php endif; ?>
                            <span>⭅️ <?= $layout['downloads'] ?></span>
                        </div>
                        <div class="ll-card-actions">
                            <button class="ll-btn" onclick="previewLayout(<?= $layout['id'] ?>)">👁 Preview</button>
                            <button class="ll-btn ll-btn-primary" onclick="importLayout(<?= $layout['id'] ?>)">📥 Import</button>
                            <button class="ll-btn ll-btn-danger" onclick="deleteLayout(<?= $layout['id'] ?>, '<?= esc($layout['name']) ?>')" title="Delete">🗑️</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Preview Modal -->
<div class="ll-modal" id="previewModal">
    <div class="ll-modal-content">
        <div class="ll-modal-header">
            <h2 id="modalTitle">Layout Preview</h2>
            <button class="ll-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="ll-modal-body">
            <div class="ll-pages-list" id="pagesList"></div>
            <iframe class="ll-preview-frame" id="previewFrame"></iframe>
            <div class="ll-status" id="importStatus"></div>
        </div>
        <div class="ll-modal-footer">
            <button class="ll-btn" onclick="closeModal()">Cancel</button>
            <button class="ll-btn ll-btn-primary" id="importBtn" onclick="confirmImport()">
                📥 Import Selected Pages
            </button>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="ll-modal" id="uploadModal">
    <div class="ll-modal-content" style="max-width:600px;">
        <div class="ll-modal-header">
            <h2>📤 Upload Layout</h2>
            <button class="ll-modal-close" onclick="closeUploadModal()">&times;</button>
        </div>
        <div class="ll-modal-body">
            <div style="margin-bottom:24px;">
                <p style="color:var(--ll-text-dim);margin-bottom:16px;">
                    Upload a layout JSON file to add it to your library. The file must contain:
                </p>
                <ul style="color:var(--ll-text-dim);margin-left:20px;line-height:1.8;">
                    <li><code>name</code> - Layout name</li>
                    <li><code>pages</code> - Array of pages with title and content</li>
                    <li>Optional: description, category, industry, style, thumbnail</li>
                </ul>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="ll-upload-zone" id="uploadZone" onclick="document.getElementById('layoutFile').click()">
                    <div class="ll-upload-icon">📁</div>
                    <div class="ll-upload-text">
                        <strong>Click to select file</strong><br>
                        <span style="color:var(--ll-text-dim)">or drag & drop .json file here</span>
                    </div>
                    <input type="file" id="layoutFile" name="layout_file" accept=".json" style="display:none" onchange="handleFileSelect(this)">
                </div>
                <div id="selectedFile" style="display:none;margin-top:16px;padding:16px;background:var(--ll-surface);border-radius:8px;border:1px solid var(--ll-border);">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span id="fileName" style="color:var(--ll-text);"></span>
                        <button type="button" onclick="clearFile()" style="background:none;border:none;color:var(--ll-text-dim);cursor:pointer;">&times;</button>
                    </div>
                </div>
            </form>
            <div class="ll-status" id="uploadStatus" style="display:none;margin-top:16px;"></div>
        </div>
        <div class="ll-modal-footer">
            <button class="ll-btn" onclick="closeUploadModal()">Cancel</button>
            <button class="ll-btn ll-btn-primary" id="uploadBtn" onclick="submitUpload()" disabled>
                📤 Install Layout
            </button>
        </div>
    </div>
</div>

<style>
.ll-upload-zone {
    border: 2px dashed var(--ll-border);
    border-radius: 12px;
    padding: 48px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: var(--ll-bg);
}
.ll-upload-zone:hover, .ll-upload-zone.dragover {
    border-color: var(--ll-accent);
    background: rgba(137, 180, 250, 0.05);
}
.ll-upload-icon {
    font-size: 48px;
    margin-bottom: 16px;
}
.ll-upload-text {
    font-size: 14px;
    color: var(--ll-text);
}
</style>

<script>
const csrfToken = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
let currentLayoutId = null;
let currentPages = [];

function applyFilters() {
    const params = new URLSearchParams();
    const category = document.getElementById('filterCategory').value;
    const industry = document.getElementById('filterIndustry').value;
    const style = document.getElementById('filterStyle').value;
    const search = document.getElementById('filterSearch').value;

    if (category) params.set('category', category);
    if (industry) params.set('industry', industry);
    if (style) params.set('style', style);
    if (search) params.set('search', search);

    window.location.href = '/admin/layout-library' + (params.toString() ? '?' + params.toString() : '');
}

function clearFilters() {
    window.location.href = '/admin/layout-library';
}

async function previewLayout(id) {
    currentLayoutId = id;
    document.getElementById('previewModal').classList.add('active');
    document.getElementById('previewFrame').srcdoc = '<html><body style="display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;color:#666">Loading preview...</body></html>';
    document.getElementById('importStatus').className = 'll-status';
    document.getElementById('importStatus').style.display = 'none';

    try {
        const response = await fetch('/admin/layout-library/preview?id=' + id);
        const data = await response.json();

        if (data.success) {
            document.getElementById('modalTitle').textContent = data.layout.name;
            currentPages = data.pages;

            // Render pages list with checkboxes
            let pagesHtml = '';
            data.pages.forEach((page, index) => {
                pagesHtml += `
                    <label class="ll-page-chip">
                        <input type="checkbox" checked data-index="${index}">
                        ${escapeHtml(page.title || 'Page ' + (index + 1))}
                    </label>
                `;
            });
            document.getElementById('pagesList').innerHTML = pagesHtml;

            // Show preview
            if (data.preview_html) {
                document.getElementById('previewFrame').srcdoc = data.preview_html;
            }
        } else {
            alert('Error: ' + data.error);
            closeModal();
        }
    } catch (err) {
        alert('Failed to load preview');
        closeModal();
    }
}

function importLayout(id) {
    previewLayout(id);
}

async function deleteLayout(id, name) {
    if (!confirm('Delete layout "' + name + '"? This cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('/admin/layout-library/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ id: id, csrf_token: csrfToken })
        });

        const data = await response.json();

        if (data.success) {
            // Remove card from DOM
            const card = document.querySelector(`[data-layout-id="${id}"]`);
            if (card) card.remove();
            else window.location.reload();
        } else {
            alert('Delete failed: ' + (data.error || 'Unknown error'));
        }
    } catch (e) {
        alert('Network error: ' + e.message);
    }
}

async function confirmImport() {
    if (!currentLayoutId) return;

    const checkboxes = document.querySelectorAll('#pagesList input[type="checkbox"]:checked');
    const pageIndices = Array.from(checkboxes).map(cb => parseInt(cb.dataset.index));

    if (pageIndices.length === 0) {
        alert('Please select at least one page to import');
        return;
    }

    const statusEl = document.getElementById('importStatus');
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.textContent = 'Importing...';

    try {
        const response = await fetch('/admin/layout-library/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                csrf_token: csrfToken,
                layout_id: currentLayoutId,
                page_indices: pageIndices
            })
        });

        const data = await response.json();

        if (data.success) {
            statusEl.className = 'll-status success';
            let linksHtml = '✅ ' + data.message + '<br><br>';
            data.pages.forEach(page => {
                linksHtml += `<a href="${page.edit_url}" style="color:var(--ll-accent);margin-right:12px">${escapeHtml(page.title)} →</a>`;
            });
            statusEl.innerHTML = linksHtml;
            btn.textContent = '✓ Imported';
        } else {
            statusEl.className = 'll-status error';
            statusEl.textContent = 'Error: ' + data.error;
            btn.disabled = false;
            btn.textContent = '📥 Import Selected Pages';
        }
    } catch (err) {
        statusEl.className = 'll-status error';
        statusEl.textContent = 'Import failed: ' + err.message;
        btn.disabled = false;
        btn.textContent = '📥 Import Selected Pages';
    }
}

function closeModal() {
    document.getElementById('previewModal').classList.remove('active');
    currentLayoutId = null;
    currentPages = [];
    document.getElementById('importBtn').disabled = false;
    document.getElementById('importBtn').textContent = '📥 Import Selected Pages';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
        closeUploadModal();
    }
});

// Upload Modal Functions
function openUploadModal() {
    document.getElementById('uploadModal').classList.add('active');
    clearFile();
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.remove('active');
    clearFile();
    document.getElementById('uploadStatus').style.display = 'none';
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (!file.name.endsWith('.json')) {
            alert('Please select a .json file');
            input.value = '';
            return;
        }
        document.getElementById('fileName').textContent = file.name + ' (' + formatBytes(file.size) + ')';
        document.getElementById('selectedFile').style.display = 'block';
        document.getElementById('uploadBtn').disabled = false;
    }
}

function clearFile() {
    document.getElementById('layoutFile').value = '';
    document.getElementById('selectedFile').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

async function submitUpload() {
    const fileInput = document.getElementById('layoutFile');
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Please select a file first');
        return;
    }

    const btn = document.getElementById('uploadBtn');
    const statusEl = document.getElementById('uploadStatus');
    
    btn.disabled = true;
    btn.textContent = 'Installing...';
    statusEl.style.display = 'none';

    const formData = new FormData();
    formData.append('layout_file', fileInput.files[0]);
    formData.append('csrf_token', csrfToken);

    try {
        const response = await fetch('/admin/layout-library/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            statusEl.className = 'll-status success';
            statusEl.innerHTML = '✅ ' + data.message + '<br><br><a href="/admin/layout-library" style="color:var(--ll-accent)">Refresh to see layout →</a>';
            statusEl.style.display = 'block';
            btn.textContent = '✓ Installed';
            
            // Auto-refresh after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            statusEl.className = 'll-status error';
            statusEl.textContent = '❌ ' + data.error;
            statusEl.style.display = 'block';
            btn.disabled = false;
            btn.textContent = '📤 Install Layout';
        }
    } catch (err) {
        statusEl.className = 'll-status error';
        statusEl.textContent = '❌ Upload failed: ' + err.message;
        statusEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = '📤 Install Layout';
    }
}

// Drag and drop support
const uploadZone = document.getElementById('uploadZone');
if (uploadZone) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
        });
    });
    
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => uploadZone.classList.add('dragover'));
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => uploadZone.classList.remove('dragover'));
    });
    
    uploadZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('layoutFile').files = files;
            handleFileSelect(document.getElementById('layoutFile'));
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/topbar.php';