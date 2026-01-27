<?php
/**
 * AI Designer 4.0 - Theme Preview View
 * 
 * Shows preview of generated theme.
 *
 * @package Admin\Views
 * @version 4.0
 */

$theme = $theme ?? [];
$themePath = $themePath ?? '';
$slug = $slug ?? '';

// Include admin header
$title = 'Theme Preview - AI Designer 4.0';
ob_start();
?>
<input type="hidden" id="csrf_token" value="<?= csrf_token() ?>">

<style>
.preview-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 2rem;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.preview-header h1 {
    color: #cdd6f4;
    margin: 0;
}

.preview-actions {
    display: flex;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #89b4fa;
    color: #1e1e2e;
}

.btn-secondary {
    background: #313244;
    color: #cdd6f4;
}

.btn:hover {
    transform: translateY(-2px);
}

.theme-meta {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.meta-card {
    background: #1e1e2e;
    border: 1px solid #313244;
    border-radius: 12px;
    padding: 1.5rem;
}

.meta-card h3 {
    color: #89b4fa;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.meta-card p {
    color: #cdd6f4;
    font-size: 1.25rem;
    margin: 0;
}

.preview-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 0;
    border-bottom: 1px solid #313244;
}

.preview-tab {
    padding: 1rem 2rem;
    background: transparent;
    border: none;
    color: #a6adc8;
    cursor: pointer;
    font-size: 1rem;
    border-bottom: 2px solid transparent;
    transition: all 0.3s;
}

.preview-tab:hover {
    color: #cdd6f4;
}

.preview-tab.active {
    color: #89b4fa;
    border-bottom-color: #89b4fa;
}

.preview-content {
    background: #1e1e2e;
    border: 1px solid #313244;
    border-top: none;
    border-radius: 0 0 12px 12px;
    min-height: 600px;
}

.preview-iframe {
    width: 100%;
    height: 700px;
    border: none;
    background: #fff;
}

.page-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    padding: 2rem;
}

.page-card {
    background: #313244;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
}

.page-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

.page-card-preview {
    height: 200px;
    background: #45475a;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c7086;
    font-size: 3rem;
}

.page-card-info {
    padding: 1rem;
}

.page-card-info h4 {
    color: #cdd6f4;
    margin: 0 0 0.25rem 0;
}

.page-card-info p {
    color: #6c7086;
    margin: 0;
    font-size: 0.875rem;
}

.design-system {
    padding: 2rem;
}

.ds-section {
    margin-bottom: 2rem;
}

.ds-section h3 {
    color: #cdd6f4;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #313244;
}

.color-swatches {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.color-swatch {
    text-align: center;
}

.color-swatch-box {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    border: 1px solid #45475a;
}

.color-swatch-name {
    color: #a6adc8;
    font-size: 0.75rem;
}

.color-swatch-value {
    color: #6c7086;
    font-size: 0.75rem;
    font-family: monospace;
}

.typography-sample {
    background: #313244;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.typography-sample h1,
.typography-sample h2,
.typography-sample h3,
.typography-sample p {
    color: #cdd6f4;
    margin: 0 0 0.5rem 0;
}
</style>

<div class="preview-container">
    <div class="preview-header">
        <h1>📦 <?= htmlspecialchars($theme['name'] ?? 'Theme Preview') ?></h1>
        <div class="preview-actions">
            <a href="/admin/ai-designer.php" class="btn btn-secondary">← Back to Designer</a>
            <button class="btn btn-primary" onclick="deployTheme()">🚀 Deploy Theme</button>
        </div>
    </div>
    
    <div class="theme-meta">
        <div class="meta-card">
            <h3>Style</h3>
            <p><?= htmlspecialchars(ucfirst($theme['design_style'] ?? 'Modern')) ?></p>
        </div>
        <div class="meta-card">
            <h3>Industry</h3>
            <p><?= htmlspecialchars(ucfirst($theme['industry'] ?? 'Business')) ?></p>
        </div>
        <div class="meta-card">
            <h3>Pages</h3>
            <p><?= count($theme['pages'] ?? []) ?> pages</p>
        </div>
        <div class="meta-card">
            <h3>Created</h3>
            <p><?= htmlspecialchars($theme['created_at'] ?? date('Y-m-d')) ?></p>
        </div>
    </div>
    
    <div class="preview-tabs">
        <button class="preview-tab active" data-tab="pages">Pages</button>
        <button class="preview-tab" data-tab="design">Design System</button>
        <button class="preview-tab" data-tab="files">Files</button>
    </div>
    
    <div class="preview-content">
        <!-- Pages Tab -->
        <div class="tab-content active" id="tab-pages">
            <div class="page-list">
                <?php 
                $pages = $theme['pages'] ?? [];
                $pageIcons = [
                    'homepage' => '🏠',
                    'about' => '👥',
                    'services' => '⚙️',
                    'contact' => '📧',
                    'blog' => '📝',
                    'portfolio' => '🖼️',
                    'pricing' => '💰',
                    'team' => '👨‍💼',
                    'faq' => '❓',
                    'testimonials' => '⭐'
                ];
                foreach ($pages as $page): 
                    $pageKey = strtolower(str_replace(' ', '_', $page));
                    $icon = $pageIcons[$pageKey] ?? '📄';
                ?>
                <div class="page-card" onclick="previewPage('<?= htmlspecialchars($pageKey) ?>')">
                    <div class="page-card-preview"><?= $icon ?></div>
                    <div class="page-card-info">
                        <h4><?= htmlspecialchars(ucfirst($page)) ?></h4>
                        <p>Click to preview</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Design System Tab -->
        <div class="tab-content" id="tab-design" style="display: none;">
            <div class="design-system">
                <?php 
                $ds = $theme['design_system'] ?? [];
                $colors = $ds['colors'] ?? [];
                ?>
                
                <div class="ds-section">
                    <h3>Colors</h3>
                    <div class="color-swatches">
                        <?php foreach ($colors as $name => $value): ?>
                        <?php if (is_string($value)): ?>
                        <div class="color-swatch">
                            <div class="color-swatch-box" style="background: <?= htmlspecialchars($value) ?>;"></div>
                            <div class="color-swatch-name"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $name))) ?></div>
                            <div class="color-swatch-value"><?= htmlspecialchars($value) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="ds-section">
                    <h3>Typography</h3>
                    <div class="typography-sample">
                        <h1 style="font-family: <?= htmlspecialchars($ds['typography']['heading_font'] ?? 'serif') ?>;">Heading Font</h1>
                        <p style="font-family: <?= htmlspecialchars($ds['typography']['body_font'] ?? 'sans-serif') ?>;">Body text using the body font family. This is how your content will look.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Files Tab -->
        <div class="tab-content" id="tab-files" style="display: none;">
            <div class="design-system">
                <div class="ds-section">
                    <h3>Theme Files</h3>
                    <ul style="color: #a6adc8; line-height: 2;">
                        <li>📁 /themes/<?= htmlspecialchars($slug) ?>/</li>
                        <li>&nbsp;&nbsp;&nbsp;├── 📄 theme.json</li>
                        <li>&nbsp;&nbsp;&nbsp;├── 📄 header.php</li>
                        <li>&nbsp;&nbsp;&nbsp;├── 📄 footer.php</li>
                        <li>&nbsp;&nbsp;&nbsp;├── 📁 pages/</li>
                        <?php foreach ($pages as $page): 
                            $filename = strtolower(str_replace(' ', '_', $page));
                        ?>
                        <li>&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 <?= $filename ?>.php</li>
                        <?php endforeach; ?>
                        <li>&nbsp;&nbsp;&nbsp;├── 📁 assets/css/</li>
                        <li>&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 style.css</li>
                        <li>&nbsp;&nbsp;&nbsp;└── 📁 tb-export/</li>
                        <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📄 theme-export.json</li>
                        <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📄 header.json</li>
                        <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📄 footer.json</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const slug = '<?= htmlspecialchars($slug) ?>';

// Tab switching
document.querySelectorAll('.preview-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.preview-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
        
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
    });
});

function previewPage(page) {
    // Could open in iframe or new window
    window.open('/themes/' + slug + '/pages/' + page + '.php', '_blank');
}

async function deployTheme() {
    if (!confirm('Deploy this theme to Theme Builder?')) return;
    
    try {
        const response = await fetch('/admin/ai-designer/deploy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slug: slug, csrf_token: document.getElementById('csrf_token').value })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Theme deployed successfully!');
        } else {
            alert('Deploy failed: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
