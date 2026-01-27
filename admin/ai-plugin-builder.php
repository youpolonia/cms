<?php
/**
 * AI Plugin Builder
 * Generate complete plugins using AI
 */

declare(strict_types=1);

define('CMS_ROOT', realpath(__DIR__ . '/..'));

require_once __DIR__ . '/../includes/init.php'; // Session init
require_once CMS_ROOT . '/config.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('DEV_MODE required');
}

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

require_once CMS_ROOT . '/core/ai_plugin_builder.php';

$pageTitle = 'AI Plugin Builder';
$message = '';
$messageType = '';
$generatedPlugin = null;
$previewFiles = null;

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    $action = $_POST['ajax_action'];
    
    switch ($action) {
        case 'preview':
            $result = AIPluginBuilder::preview([
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'menu_section' => $_POST['menu_section'] ?? 'plugins'
            ]);
            echo json_encode($result);
            break;
            
        case 'generate':
            $result = AIPluginBuilder::generate([
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'features' => $_POST['features'] ?? '',
                'template' => $_POST['template'] ?? 'basic',
                'menu_section' => $_POST['menu_section'] ?? 'plugins',
                'has_admin_page' => !empty($_POST['has_admin_page']),
                'has_database' => !empty($_POST['has_database']),
                'has_api' => !empty($_POST['has_api'])
            ]);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
    exit;
}

$templates = AIPluginBuilder::getTemplates();
$menuSections = AIPluginBuilder::getMenuSections();

require_once CMS_ROOT . '/admin/includes/header.php';
?>

<style>
.builder-container {
    max-width: 1200px;
    margin: 0 auto;
}

.builder-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 1024px) {
    .builder-grid {
        grid-template-columns: 1fr;
    }
}

.panel {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}

.panel-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-title {
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.panel-body {
    padding: 1.25rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text);
}

.form-hint {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: var(--content-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 0.9375rem;
    transition: border-color 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: var(--primary);
}

.form-textarea {
    min-height: 100px;
    resize: vertical;
    font-family: inherit;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
}

.template-btn {
    padding: 0.875rem;
    background: var(--content-bg);
    border: 2px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}

.template-btn:hover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.template-btn.active {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
}

.template-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.template-name {
    font-size: 0.8125rem;
    font-weight: 500;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-item input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.btn-generate {
    width: 100%;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, var(--primary), #8b5cf6);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-generate:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.btn-generate:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-preview {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--content-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 0.875rem;
    cursor: pointer;
    margin-bottom: 0.75rem;
    transition: all 0.2s;
}

.btn-preview:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.preview-area {
    background: var(--content-bg);
    border-radius: 8px;
    min-height: 400px;
}

.preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 400px;
    color: var(--text-muted);
    text-align: center;
    padding: 2rem;
}

.preview-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.file-tree {
    padding: 1rem;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
}

.file-item:hover {
    background: rgba(99, 102, 241, 0.1);
}

.file-item.active {
    background: rgba(99, 102, 241, 0.15);
    color: var(--primary);
}

.file-icon {
    font-size: 1rem;
}

.file-name {
    font-size: 0.8125rem;
    font-family: monospace;
}

.code-preview {
    background: #1e293b;
    color: #e2e8f0;
    padding: 1rem;
    border-radius: 8px;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.8125rem;
    line-height: 1.6;
    overflow-x: auto;
    max-height: 400px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

.success-message {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.success-message h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
}

.success-message p {
    margin: 0;
    font-size: 0.875rem;
}

.success-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    padding: 1rem 1.5rem;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    z-index: 1000;
    animation: slideIn 0.3s ease;
}

.toast.success { border-left: 4px solid var(--success); }
.toast.error { border-left: 4px solid var(--danger); }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<div class="builder-container">
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <h1>🔧 AI Plugin Builder</h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem;">
            Generate complete plugin structures using AI
        </p>
    </div>
    
    <div class="builder-grid">
        <!-- Input Panel -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📝 Plugin Configuration</div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label">Plugin Name *</label>
                    <input type="text" class="form-input" id="plugin-name" placeholder="My Awesome Plugin">
                    <div class="form-hint">Will be used to generate slug (e.g., my-awesome-plugin)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea class="form-textarea" id="plugin-desc" rows="2" placeholder="What does this plugin do?"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Features & Requirements</label>
                    <textarea class="form-textarea" id="plugin-features" rows="3" placeholder="Describe the features you want:&#10;- Feature 1&#10;- Feature 2&#10;- API endpoint for X"></textarea>
                    <div class="form-hint">Be specific about what the plugin should do</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Template</label>
                    <div class="template-grid">
                        <?php foreach ($templates as $key => $tpl): ?>
                        <button type="button" class="template-btn <?= $key === 'basic' ? 'active' : '' ?>" data-template="<?= $key ?>">
                            <div class="template-icon">
                                <?= match($key) {
                                    'basic' => '📦',
                                    'widget' => '🧩',
                                    'api' => '🔌',
                                    'integration' => '🔗',
                                    'content' => '📄',
                                    default => '📦'
                                } ?>
                            </div>
                            <div class="template-name"><?= htmlspecialchars($tpl['name']) ?></div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Menu Section</label>
                    <select class="form-select" id="menu-section">
                        <?php foreach ($menuSections as $key => $name): ?>
                        <option value="<?= $key ?>" <?= $key === 'plugins' ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Where the plugin will appear in admin menu</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Options</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" id="has-admin" checked>
                            <span>Admin Page</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="has-database">
                            <span>Database Tables</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="has-api">
                            <span>API Endpoints</span>
                        </label>
                    </div>
                </div>
                
                <button type="button" class="btn-preview" id="btn-preview">
                    👁️ Preview Structure
                </button>
                
                <button type="button" class="btn-generate" id="btn-generate">
                    <span class="btn-text">🚀 Generate Plugin with AI</span>
                </button>
            </div>
        </div>
        
        <!-- Preview Panel -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📂 Preview</div>
            </div>
            <div class="panel-body">
                <div class="preview-area" id="preview-area">
                    <div class="preview-placeholder" id="preview-placeholder">
                        <div class="preview-icon">🔧</div>
                        <div>Configure your plugin and click Preview</div>
                        <div style="font-size: 0.8125rem; margin-top: 0.5rem;">
                            Or click Generate to create with AI
                        </div>
                    </div>
                    <div id="preview-content" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
let currentTemplate = 'basic';
let previewFiles = {};
let selectedFile = null;

// Template selection
document.querySelectorAll('.template-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.template-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTemplate = btn.dataset.template;
    });
});

// Preview
document.getElementById('btn-preview').addEventListener('click', async () => {
    const name = document.getElementById('plugin-name').value.trim();
    const desc = document.getElementById('plugin-desc').value.trim();
    
    if (!name) {
        showToast('Please enter a plugin name', 'error');
        return;
    }
    
    const response = await fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax_action: 'preview',
            csrf_token: CSRF_TOKEN,
            name: name,
            description: desc,
            menu_section: document.getElementById('menu-section').value
        })
    });
    
    const result = await response.json();
    
    if (result.success) {
        previewFiles = result.files;
        renderPreview(result.slug, result.files);
    } else {
        showToast(result.error || 'Preview failed', 'error');
    }
});

// Generate
document.getElementById('btn-generate').addEventListener('click', async () => {
    const btn = document.getElementById('btn-generate');
    const name = document.getElementById('plugin-name').value.trim();
    const desc = document.getElementById('plugin-desc').value.trim();
    
    if (!name) {
        showToast('Please enter a plugin name', 'error');
        return;
    }
    
    if (!desc) {
        showToast('Please enter a description', 'error');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Generating...';
    
    try {
        const response = await fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                ajax_action: 'generate',
                csrf_token: CSRF_TOKEN,
                name: name,
                description: desc,
                features: document.getElementById('plugin-features').value,
                template: currentTemplate,
                menu_section: document.getElementById('menu-section').value,
                has_admin_page: document.getElementById('has-admin').checked ? '1' : '',
                has_database: document.getElementById('has-database').checked ? '1' : '',
                has_api: document.getElementById('has-api').checked ? '1' : ''
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessMessage(result);
            showToast('Plugin generated successfully!', 'success');
        } else {
            showToast(result.error || 'Generation failed', 'error');
        }
    } catch (e) {
        showToast('Network error: ' + e.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="btn-text">🚀 Generate Plugin with AI</span>';
    }
});

function renderPreview(slug, files) {
    document.getElementById('preview-placeholder').style.display = 'none';
    const content = document.getElementById('preview-content');
    content.style.display = 'block';
    
    let html = '<div class="file-tree">';
    html += '<div style="font-weight: 600; margin-bottom: 0.75rem;">📁 /plugins/' + slug + '/</div>';
    
    for (const filename of Object.keys(files)) {
        const icon = filename.endsWith('.json') ? '📋' : '📄';
        html += '<div class="file-item" data-file="' + filename + '">';
        html += '<span class="file-icon">' + icon + '</span>';
        html += '<span class="file-name">' + filename + '</span>';
        html += '</div>';
    }
    
    html += '</div>';
    html += '<div class="code-preview" id="code-preview" style="display: none;"></div>';
    
    content.innerHTML = html;
    
    // Click handlers for files
    content.querySelectorAll('.file-item').forEach(item => {
        item.addEventListener('click', () => {
            content.querySelectorAll('.file-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            
            const filename = item.dataset.file;
            const code = document.getElementById('code-preview');
            code.style.display = 'block';
            code.textContent = files[filename];
        });
    });
    
    // Auto-select first file
    const firstFile = content.querySelector('.file-item');
    if (firstFile) firstFile.click();
}

function showSuccessMessage(result) {
    document.getElementById('preview-placeholder').style.display = 'none';
    const content = document.getElementById('preview-content');
    content.style.display = 'block';
    
    content.innerHTML = `
        <div class="success-message">
            <h3>✅ Plugin Created Successfully!</h3>
            <p><strong>Name:</strong> ${result.slug}</p>
            <p><strong>Path:</strong> ${result.path}</p>
            <p><strong>Files:</strong> ${result.files.join(', ')}</p>
            <div class="success-actions">
                <a href="/admin/plugins-marketplace.php" class="btn primary">View in Plugins</a>
                <button onclick="location.reload()" class="btn btn-secondary">Create Another</button>
            </div>
        </div>
    `;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

<?php require_once CMS_ROOT . '/admin/includes/footer.php';
