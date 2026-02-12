<?php
/**
 * Page Editor with TinyMCE WYSIWYG + AI Tools + SEO Analysis
 * Full-featured editor for static pages - Catppuccin Dark Theme
 */

$pageTitle = $page ? 'Edit Page' : 'New Page';
$isEdit = $page !== null;

// Load AI settings
$aiSettingsFile = dirname(CMS_APP) . '/config/ai_settings.json';
$aiSettings = file_exists($aiSettingsFile) ? json_decode(file_get_contents($aiSettingsFile), true) : [];

// Check for API key in nested structure
$aiConfigured = false;
if (!empty($aiSettings['providers']['openai']['api_key'])) {
    $aiConfigured = true;
} elseif (!empty($aiSettings['api_key'])) {
    $aiConfigured = true;
} elseif (!empty($aiSettings['openai_api_key'])) {
    $aiConfigured = true;
}

// Get media files from upload folder
$mediaFiles = [];
$mediaDir = dirname(CMS_APP) . '/uploads/media/';
if (is_dir($mediaDir)) {
    $files = scandir($mediaDir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'thumbs' || is_dir($mediaDir . $file)) continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            $mediaFiles[] = ['filename' => $file];
        }
        if (count($mediaFiles) >= 100) break;
    }
}

// Get all pages for parent selection (exclude current page)
$allPages = [];
$pdo = db();
$currentId = (int)($page['id'] ?? 0);
try {
    $stmt = $pdo->prepare("SELECT id, title FROM pages WHERE id != ? ORDER BY title ASC");
    $stmt->execute([$currentId]);
    $allPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $allPages = [];
}

// Available templates
$templates = [
    'default' => 'Default Template',
    'full-width' => 'Full Width',
    'sidebar-left' => 'Sidebar Left',
    'sidebar-right' => 'Sidebar Right',
    'landing' => 'Landing Page',
    'contact' => 'Contact Page',
    'blank' => 'Blank (No Header/Footer)',
    'gallery' => 'Gallery Page'
];

// Set title for layout
$title = $pageTitle;
ob_start();
?>

<!-- TinyMCE Script (Local) -->
<script src="/assets/vendor/tinymce/tinymce.min.js"></script>

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --primary: #89b4fa;
        --primary-dark: #b4befe;
        --sidebar-bg: #181825;
        --content-bg: #1e1e2e;
        --card-bg: #181825;
        --border: #313244;
        --text: #cdd6f4;
        --text-muted: #6c7086;
        --success: #a6e3a1;
        --warning: #f9e2af;
        --danger: #f38ba8;
    }
    
    body { font-family: 'Inter', -apple-system, sans-serif; background: var(--content-bg); color: var(--text); line-height: 1.5; margin: 0; }
    
    .editor-layout { display: flex; min-height: 100vh; }
    .editor-main { flex: 1; padding: 1.5rem; max-width: 900px; }
    .editor-sidebar { width: 360px; background: var(--card-bg); border-left: 1px solid var(--border); }
    
    .editor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
    .editor-header h1 { font-size: 1.25rem; font-weight: 600; }
    .header-actions { display: flex; gap: 0.75rem; }
    
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .btn-primary { background: var(--primary); color: #1e1e2e; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-secondary { background: var(--border); color: var(--text); }
    .btn-secondary:hover { background: #45475a; }
    .btn-success { background: var(--success); color: #1e1e2e; }
    .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
    .btn-ai { background: linear-gradient(135deg, #cba6f7, #89b4fa); color: #1e1e2e; }
    .btn-ai:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(203, 166, 247, 0.4); }
    
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.9375rem; border: 1px solid var(--border); border-radius: 6px; background: var(--content-bg); color: var(--text); transition: border-color 0.2s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.1); }
    .form-textarea { min-height: 80px; resize: vertical; font-family: inherit; }
    .form-hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
    
    .title-input { font-size: 1.75rem; font-weight: 600; border: none; padding: 0.5rem 0; margin-bottom: 1rem; background: transparent; }
    .title-input:focus { outline: none; box-shadow: none; }
    
    .editor-wrapper { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
    
    .sidebar-tabs { display: flex; border-bottom: 1px solid var(--border); background: var(--sidebar-bg); }
    .sidebar-tab { flex: 1; padding: 0.75rem; text-align: center; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; background: none; color: var(--text-muted); transition: all 0.2s; }
    .sidebar-tab:hover { color: var(--text); background: rgba(137, 180, 250, 0.05); }
    .sidebar-tab.active { color: var(--primary); background: var(--card-bg); border-bottom: 2px solid var(--primary); margin-bottom: -1px; }
    
    .sidebar-content { display: none; padding: 1.25rem; }
    .sidebar-content.active { display: block; }
    
    .sidebar-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
    .sidebar-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .sidebar-title { font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    
    .status-options { display: flex; flex-direction: column; gap: 0.5rem; }
    .status-option { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; transition: all 0.2s; }
    .status-option:hover { border-color: var(--primary); }
    .status-option.selected { border-color: var(--primary); background: rgba(137, 180, 250, 0.1); }
    .status-option input { display: none; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; }
    .status-dot.draft { background: var(--warning); }
    .status-dot.published { background: var(--success); }
    
    .featured-image-box { border: 2px dashed var(--border); border-radius: 8px; padding: 2rem 1rem; text-align: center; cursor: pointer; transition: all 0.2s; position: relative; }
    .featured-image-box:hover { border-color: var(--primary); background: rgba(137, 180, 250, 0.02); }
    .featured-image-box.has-image { padding: 0; border-style: solid; }
    .featured-image-box img { width: 100%; border-radius: 6px; }
    .featured-image-box .remove-btn { position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; }
    
    .ai-tool-group { margin-bottom: 1rem; }
    .ai-tool-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .ai-tools-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .ai-tool-btn { padding: 0.5rem; font-size: 0.75rem; border-radius: 6px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 0.375rem; justify-content: center; }
    .ai-tool-btn:hover { border-color: var(--primary); background: rgba(137, 180, 250, 0.15); color: var(--primary); }

    .seo-score { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--sidebar-bg); border-radius: 8px; margin-bottom: 1rem; }
    .seo-score-circle { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: white; }
    .seo-score-circle.good { background: var(--success); }
    .seo-score-circle.ok { background: var(--warning); }
    .seo-score-circle.poor { background: var(--danger); }
    .seo-score-details { flex: 1; }
    .seo-score-label { font-size: 0.875rem; font-weight: 600; }
    .seo-score-hint { font-size: 0.75rem; color: var(--text-muted); }
    
    .seo-checklist { list-style: none; }
    .seo-checklist li { display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.5rem 0; font-size: 0.8125rem; border-bottom: 1px solid var(--border); }
    .seo-checklist li:last-child { border-bottom: none; }
    .seo-check { width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; flex-shrink: 0; }
    .seo-check.pass { background: rgba(166, 227, 161, 0.2); color: #a6e3a1; }
    .seo-check.fail { background: rgba(243, 139, 168, 0.2); color: #f38ba8; }
    .seo-check.warn { background: rgba(249, 226, 175, 0.2); color: #f9e2af; }
    
    .word-count { display: flex; gap: 1rem; padding: 0.75rem; background: var(--sidebar-bg); border-radius: 6px; font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1rem; }
    .word-count strong { color: var(--text); }
    
    .seo-preview { background: var(--sidebar-bg); border-radius: 8px; padding: 1rem; margin-top: 1rem; }
    .seo-preview-title { color: #89b4fa; font-size: 1.125rem; font-weight: 500; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .seo-preview-url { color: #a6e3a1; font-size: 0.8125rem; margin-bottom: 0.25rem; }
    .seo-preview-desc { color: var(--text-muted); font-size: 0.8125rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
    .char-count { font-size: 0.75rem; color: var(--text-muted); text-align: right; margin-top: 0.25rem; }
    .char-count.warning { color: var(--warning); }
    .char-count.danger { color: var(--danger); }
    
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal { background: var(--card-bg); border-radius: 12px; width: 90%; max-width: 900px; max-height: 80vh; overflow: hidden; display: flex; flex-direction: column; }
    .modal-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { font-size: 1rem; font-weight: 600; }
    .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); }
    .modal-body { flex: 1; overflow-y: auto; padding: 1.5rem; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; }
    
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 1rem; }
    .media-item { aspect-ratio: 1; border: 2px solid var(--border); border-radius: 8px; overflow: hidden; cursor: pointer; transition: all 0.2s; position: relative; }
    .media-item:hover { border-color: var(--primary); }
    .media-item.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.3); }
    .media-item img { width: 100%; height: 100%; object-fit: cover; }
    .media-item .filename { position: absolute; bottom: 0; left: 0; right: 0; padding: 0.25rem 0.5rem; background: rgba(0,0,0,0.7); color: white; font-size: 0.625rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    
    .upload-area { border: 2px dashed var(--border); border-radius: 8px; padding: 2rem; text-align: center; margin-bottom: 1.5rem; transition: all 0.2s; }
    .upload-area:hover, .upload-area.dragover { border-color: var(--primary); background: rgba(137, 180, 250, 0.05); }
    .upload-area input { display: none; }
    
    .media-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; }
    .media-tab { padding: 0.625rem 1rem; background: transparent; color: var(--text-muted); border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; }
    .media-tab:hover { color: var(--text); background: rgba(255,255,255,0.05); }
    .media-tab.active { color: var(--primary); background: rgba(137, 180, 250, 0.15); }
    .media-tab-content { display: none; }
    .media-tab-content.active { display: block; }

    /* Stock Photos */
    .stock-search { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; }
    .stock-search input { flex: 1; padding: 0.75rem 1rem; background: var(--sidebar-bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.875rem; }
    .stock-search input:focus { outline: none; border-color: var(--primary); }
    .stock-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem; }
    .stock-item { aspect-ratio: 4/3; border-radius: 8px; overflow: hidden; cursor: pointer; position: relative; border: 2px solid transparent; transition: all 0.2s; }
    .stock-item:hover { border-color: var(--primary); transform: scale(1.02); }
    .stock-item.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.3); }
    .stock-item img { width: 100%; height: 100%; object-fit: cover; }
    .stock-item .credit { position: absolute; bottom: 0; left: 0; right: 0; padding: 0.375rem 0.5rem; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; font-size: 0.625rem; }
    .stock-loading { text-align: center; padding: 3rem; color: var(--text-muted); }

    /* AI Image Generator */
    .ai-gen-form { display: flex; flex-direction: column; gap: 1rem; }
    .ai-gen-prompt { padding: 0.875rem 1rem; background: var(--sidebar-bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 0.875rem; min-height: 80px; resize: vertical; font-family: inherit; }
    .ai-gen-prompt:focus { outline: none; border-color: var(--primary); }
    .ai-gen-options { display: flex; gap: 1rem; flex-wrap: wrap; }
    .ai-gen-options select { padding: 0.625rem 1rem; background: var(--sidebar-bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-size: 0.875rem; }
    .ai-gen-preview { margin-top: 1.5rem; }
    .ai-gen-result { max-width: 100%; border-radius: 12px; border: 2px solid var(--primary); }
    .ai-gen-status { text-align: center; padding: 2rem; color: var(--text-muted); }

    .ai-result { background: var(--sidebar-bg); border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-top: 1rem; }
    .ai-result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
    .ai-result-title { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); }
    .ai-result-content { font-size: 0.875rem; line-height: 1.6; white-space: pre-wrap; }
    .ai-result-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; }
    
    .toast { position: fixed; bottom: 2rem; right: 2rem; padding: 1rem 1.5rem; background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); z-index: 1001; animation: slideIn 0.3s ease; }
    .toast.success { border-left: 4px solid var(--success); }
    .toast.error { border-left: 4px solid var(--danger); }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    
    .template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .template-option { padding: 0.625rem; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; text-align: center; font-size: 0.75rem; transition: all 0.2s; }
    .template-option:hover { border-color: var(--primary); }
    .template-option.selected { border-color: var(--primary); background: rgba(137, 180, 250, 0.1); }
    
    .preview-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2000; background: rgba(0,0,0,0.9); }
    .preview-modal.active { display: flex; flex-direction: column; }
    .preview-header { display: flex; min-height: 60px; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background: var(--sidebar-bg); border-bottom: 1px solid var(--border); flex-shrink: 0; }
    .preview-header h3 { margin: 0; font-size: 1rem; color: var(--text); }
    .preview-tabs { display: flex; gap: 0.5rem; }
    .preview-tab { padding: 0.5rem 1rem; background: var(--border); color: var(--text); border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; }
    .preview-tab.active { background: var(--primary); color: white; }
    .preview-frame { flex: 1; background: white; margin: 1.5rem; border-radius: 12px; overflow: hidden; }
    .preview-frame.mobile { max-width: 375px; margin: 1.5rem auto; }
    .preview-frame.tablet { max-width: 768px; margin: 1.5rem auto; }
    .preview-content { width: 100%; height: 100%; padding: 2rem; overflow-y: auto; color: #1a1a1a; font-family: Georgia, serif; line-height: 1.8; }
    .preview-content h1 { font-size: 2.5rem; margin-bottom: 1rem; font-family: 'Inter', sans-serif; color: #1a1a1a; }
    .preview-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.5rem 0; }
    .preview-content p { margin-bottom: 1.25rem; }
    @media (max-width: 1024px) {
        .editor-layout { flex-direction: column; }
        .editor-sidebar { width: 100%; height: auto; position: static; border-left: none; border-top: 1px solid var(--border); }
    }
</style>

<form method="POST" id="page-form" action="<?= $isEdit ? '/admin/pages/' . (int)$page['id'] : '/admin/pages/' ?>">
    <?= csrf_field() ?>
    
    <div class="editor-layout">
        <div class="editor-main">
            <div class="editor-header">
                <h1>
                    <a href="/admin/pages" style="text-decoration: none; color: var(--text-muted);">←</a>
                    <?= $isEdit ? 'Edit Page' : 'New Page' ?>
                </h1>
                <div class="header-actions">
                    <?php if ($isEdit && !empty($page['id'])): ?>
                    <a href="/preview/page/<?= (int)$page['id'] ?>" target="_blank" class="btn btn-secondary">👁️ Preview</a>
                    <?php endif; ?>
                    <a href="/admin/pages" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="status" value="draft" class="btn btn-secondary">💾 Save Draft</button>
                    <button type="submit" name="status" value="published" class="btn btn-success">🚀 Publish</button>
                </div>
            </div>
            
            <input type="text" name="title" id="page-title" class="form-input title-input" 
                   placeholder="Page title..."
                   value="<?= esc($page['title'] ?? '') ?>"
                   required
                   oninput="updateSeoPreview(); analyzeSeo();">
            
            <div class="word-count" id="word-count">
                <span><strong id="wc-words">0</strong> words</span>
                <span><strong id="wc-chars">0</strong> characters</span>
                <span><strong id="wc-reading">0</strong> min read</span>
            </div>
            
            <div class="editor-wrapper">
                <textarea name="content" id="editor"><?= esc($page['content'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label class="form-label">Page Excerpt / Summary</label>
                    <?php if ($aiConfigured): ?>
                    <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_excerpt')">✨ Generate</button>
                    <?php endif; ?>
                </div>
                <textarea name="excerpt" id="excerpt" class="form-textarea" rows="3" 
                          placeholder="Brief summary of the page..."
                          oninput="updateSeoPreview()"><?= esc($page['excerpt'] ?? '') ?></textarea>
                <div class="form-hint">Used for SEO and page previews.</div>
            </div>
        </div>

        <div class="editor-sidebar">
            <div class="sidebar-tabs">
                <button type="button" class="sidebar-tab active" data-tab="publish">📄 Page</button>
                <button type="button" class="sidebar-tab" data-tab="seo">🎯 SEO</button>
                <button type="button" class="sidebar-tab" data-tab="ai">🤖 AI</button>
            </div>
            
            <!-- Page Tab -->
            <div class="sidebar-content active" id="tab-publish">
                <div class="sidebar-section">
                    <div class="sidebar-title">📤 Status</div>
                    <div class="status-options">
                        <label class="status-option <?= ($page['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>">
                            <input type="radio" name="status_select" value="draft" <?= ($page['status'] ?? 'draft') === 'draft' ? 'checked' : '' ?>>
                            <span class="status-dot draft"></span>
                            <span>Draft</span>
                        </label>
                        <label class="status-option <?= ($page['status'] ?? '') === 'published' ? 'selected' : '' ?>">
                            <input type="radio" name="status_select" value="published" <?= ($page['status'] ?? '') === 'published' ? 'checked' : '' ?>>
                            <span class="status-dot published"></span>
                            <span>Published</span>
                        </label>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">📐 Template <span class="tip"><span class="tip-text">Controls the visual layout. Different templates show different sections.</span></span></div>
                    <select name="template" class="form-select">
                        <?php foreach ($templates as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($page['template'] ?? 'default') === $key ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Choose page layout template</div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">📁 Parent Page <span class="tip"><span class="tip-text">Nest this page under another to create a hierarchy (e.g. About → Team).</span></span></div>
                    <select name="parent_id" class="form-select">
                        <option value="">— No Parent (Top Level) —</option>
                        <?php foreach ($allPages as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= ((int)($page['parent_id'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Create page hierarchy</div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">🖼️ Featured Image <span class="tip"><span class="tip-text">Main image shown in cards, social shares, and page headers.</span></span></div>
                    <div class="featured-image-box <?= !empty($page['featured_image']) ? 'has-image' : '' ?>" 
                         id="featured-image-box"
                         onclick="openMediaBrowser('featured')">
                        <?php if (!empty($page['featured_image'])): ?>
                        <img src="<?= esc($page['featured_image']) ?>" alt="Featured">
                        <button type="button" class="remove-btn" onclick="event.stopPropagation(); removeFeaturedImage();">×</button>
                        <?php else: ?>
                        <div class="placeholder">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">📷</div>
                            <div>Click to select image</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="featured_image" id="featured-image-value" 
                           value="<?= esc($page['featured_image'] ?? '') ?>">
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">🔗 URL Slug <span class="tip"><span class="tip-text">The URL path for this page. Auto-generated from title if left empty.</span></span></div>
                    <input type="text" name="slug" id="slug" class="form-input" 
                           placeholder="page-url-slug"
                           value="<?= esc($page['slug'] ?? '') ?>">
                    <div class="form-hint">Leave empty to auto-generate</div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">📊 Menu Order <span class="tip"><span class="tip-text">Lower numbers appear first in navigation menus.</span></span></div>
                    <input type="number" name="menu_order" class="form-input" 
                           placeholder="0"
                           value="<?= (int)($page['menu_order'] ?? 0) ?>"
                           min="0">
                    <div class="form-hint">Order in navigation menus</div>
                </div>
                
                <?php if ($isEdit && !empty($page['created_at'])): ?>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">
                    <p>Created: <?= date('M j, Y g:i A', strtotime($page['created_at'])) ?></p>
                    <?php if (!empty($page['updated_at'])): ?>
                    <p>Updated: <?= date('M j, Y g:i A', strtotime($page['updated_at'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- SEO Tab -->
            <div class="sidebar-content" id="tab-seo">
                <div class="sidebar-section">
                    <div class="seo-score" id="seo-score">
                        <div class="seo-score-circle ok" id="seo-score-circle">0</div>
                        <div class="seo-score-details">
                            <div class="seo-score-label">SEO Score</div>
                            <div class="seo-score-hint" id="seo-score-hint">Add content to analyze</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                        <div style="flex: 1; background: var(--sidebar-bg); padding: 0.5rem; border-radius: 6px; text-align: center;">
                            <div style="font-size: 0.625rem; color: var(--text-muted); text-transform: uppercase;">Readability</div>
                            <div id="readability-score" style="font-size: 1rem; font-weight: 600; color: var(--success);">—</div>
                        </div>
                        <div style="flex: 1; background: var(--sidebar-bg); padding: 0.5rem; border-radius: 6px; text-align: center;">
                            <div style="font-size: 0.625rem; color: var(--text-muted); text-transform: uppercase;">Grade</div>
                            <div id="reading-grade" style="font-size: 1rem; font-weight: 600;">—</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Focus Keyword <span class="tip"><span class="tip-text">The main keyword you want this page to rank for in search engines.</span></span></label>
                        <input type="text" name="focus_keyword" id="focus-keyword" class="form-input" 
                               placeholder="Main keyword to optimize for"
                               oninput="analyzeSeo()">
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">📋 SEO Checklist</div>
                    <ul class="seo-checklist" id="seo-checklist">
                        <li><span class="seo-check fail">✗</span> Add focus keyword</li>
                    </ul>
                </div>
                
                <div class="sidebar-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label class="form-label" style="margin: 0;">Meta Title</label>
                        <?php if ($aiConfigured): ?>
                        <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_title')">✨ Generate</button>
                        <?php endif; ?>
                    </div>
                    <input type="text" name="meta_title" class="form-input" id="meta-title"
                           placeholder="SEO title (max 60 chars)"<span class="tip"><span class="tip-text"> <span class="tip"><span class="tip-text">Appears in browser tabs and search results. Keep under 60 chars.</span></span></span></span>
                           value="<?= esc($page['meta_title'] ?? '') ?>"
                           oninput="updateSeoPreview(); updateCharCount(this, 60); analyzeSeo()">
                    <div class="char-count" id="meta-title-count">0/60</div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; margin-top: 1rem;">
                        <label class="form-label" style="margin: 0;">Meta Description</label>
                        <?php if ($aiConfigured): ?>
                        <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_meta')">✨ Generate</button>
                        <?php endif; ?>
                    </div>
                    <textarea name="meta_description" class="form-textarea" id="meta-desc" rows="2"
                              placeholder="SEO description (max 160 chars)"<span class="tip"><span class="tip-text"> <span class="tip"><span class="tip-text">Shown below the title in Google. Keep under 160 chars.</span></span></span></span>
                              oninput="updateSeoPreview(); updateCharCount(this, 160); analyzeSeo()"><?= esc($page['meta_description'] ?? '') ?></textarea>
                    <div class="char-count" id="meta-desc-count">0/160</div>
                    
                    <div class="seo-preview">
                        <div class="seo-preview-title" id="seo-title"><?= esc($page['meta_title'] ?? $page['title'] ?? 'Page Title') ?></div>
                        <div class="seo-preview-url">example.com/<?= esc($page['slug'] ?? 'page-slug') ?></div>
                        <div class="seo-preview-desc" id="seo-desc"><?= esc($page['meta_description'] ?? $page['excerpt'] ?? 'Page description...') ?></div>
                    </div>
                </div>
            </div>

            
            <!-- AI Tab -->
            <div class="sidebar-content" id="tab-ai">
                <?php if (!$aiConfigured): ?>
                <div style="padding: 1rem; background: rgba(243, 139, 168, 0.1); border: 1px solid rgba(243, 139, 168, 0.3); border-radius: 8px; color: #f38ba8; font-size: 0.875rem;">
                    AI not configured. <a href="/admin/settings" style="color: #89b4fa;">Configure API key</a>
                </div>
                <?php else: ?>
                <div class="sidebar-section">
                    <div class="sidebar-title">✍️ Content Tools</div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">
                        Select text in editor first, then click a tool.
                    </p>
                    
                    <div class="ai-tool-group">
                        <div class="ai-tool-label">Improve</div>
                        <div class="ai-tools-grid">
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('improve_content')">✨ Improve</button>
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('expand_content')">📝 Expand</button>
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('simplify')">📖 Simplify</button>
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('fix_grammar')">✓ Fix Grammar</button>
                        </div>
                    </div>
                    
                    <div class="ai-tool-group">
                        <div class="ai-tool-label">Tone</div>
                        <div class="ai-tools-grid">
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('make_formal')">👔 Formal</button>
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('make_casual')">😊 Casual</button>
                        </div>
                    </div>
                    
                    <div class="ai-tool-group">
                        <div class="ai-tool-label">Translate</div>
                        <div class="ai-tools-grid">
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('translate_en')">🇬🇧 English</button>
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('translate_pl')">🇵🇱 Polish</button>
                            <button type="button" class="ai-tool-btn" onclick="aiTransformSelection('translate_de')">🇩🇪 German</button>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <div class="sidebar-title">🚀 Quick Generate</div>
                    <div class="ai-tools-grid" style="grid-template-columns: 1fr;">
                        <button type="button" class="ai-tool-btn" onclick="aiGenerate('generate_title')" style="justify-content: flex-start;">📝 Generate Titles</button>
                        <button type="button" class="ai-tool-btn" onclick="aiGenerate('generate_excerpt')" style="justify-content: flex-start;">📄 Generate Summary</button>
                        <button type="button" class="ai-tool-btn" onclick="aiGenerate('generate_meta')" style="justify-content: flex-start;">🎯 Generate Meta Description</button>
                    </div>
                </div>
                
                <div id="ai-result-container"></div>
                
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.75rem;">🚀 MORE AI TOOLS</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="/admin/ai-copywriter.php" target="_blank" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">✍️ AI Copywriter</a>
                        <a href="/admin/ai-content-rewrite.php" target="_blank" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">🔄 Content Rewriter</a>
                        <a href="/admin/ai-seo-assistant.php" target="_blank" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">🎯 AI SEO Assistant</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<!-- Media Browser Modal -->
<div class="modal-overlay" id="media-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>📁 Media Library</h3>
            <button type="button" class="modal-close" onclick="closeMediaBrowser()">×</button>
        </div>
        <div class="modal-body">
            <div class="media-tabs">
                <button type="button" class="media-tab active" data-media-tab="upload">📤 Upload</button>
                <button type="button" class="media-tab" data-media-tab="library">🖼️ Library</button>
                <button type="button" class="media-tab" data-media-tab="stock">📷 Stock Photos</button>
                <button type="button" class="media-tab" data-media-tab="ai-generate">✨ AI Generate</button>
            </div>

            <div class="media-tab-content active" id="media-tab-upload">
                <div class="upload-area" id="upload-area">
                    <input type="file" id="media-upload" accept="image/*">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📤</div>
                    <div>Drop image here or <label for="media-upload" style="color: var(--primary); cursor: pointer;">browse</label></div>
                </div>
            </div>

            <div class="media-tab-content" id="media-tab-library">
                <div class="media-grid" id="media-grid">
                    <?php foreach ($mediaFiles as $media): ?>
                    <div class="media-item" data-url="/uploads/media/<?= esc($media['filename']) ?>">
                        <img src="/uploads/media/<?= esc($media['filename']) ?>" alt="">
                        <div class="filename"><?= esc($media['filename']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Stock Photos Tab (Pexels) -->
            <div class="media-tab-content" id="media-tab-stock">
                <div class="stock-search">
                    <input type="text" id="stock-search-input" placeholder="Search free stock photos (Pexels)...">
                    <button type="button" class="btn btn-primary" onclick="searchStockPhotos()">🔍 Search</button>
                </div>
                <div id="stock-results">
                    <div class="stock-loading">
                        <p style="font-size: 1.5rem; margin-bottom: 0.5rem;">📷</p>
                        <p>Search for beautiful free photos from Pexels</p>
                    </div>
                </div>
            </div>

            <!-- AI Generate Tab -->
            <div class="media-tab-content" id="media-tab-ai-generate">
                <div class="ai-gen-form">
                    <label style="font-weight: 500; margin-bottom: 0.25rem;">Describe the image you want to create:</label>
                    <textarea class="ai-gen-prompt" id="ai-image-prompt" placeholder="A futuristic cityscape at sunset with flying cars and neon lights, digital art style..."></textarea>

                    <div class="ai-gen-options">
                        <select id="ai-image-style">
                            <option value="photorealistic">📸 Photorealistic</option>
                            <option value="digital-art">🎨 Digital Art</option>
                            <option value="illustration">✏️ Illustration</option>
                            <option value="3d-render">🧊 3D Render</option>
                            <option value="anime">🎌 Anime</option>
                            <option value="watercolor">🖌️ Watercolor</option>
                        </select>
                        <select id="ai-image-size">
                            <option value="1024x1024">Square (1024×1024)</option>
                            <option value="1792x1024">Landscape (1792×1024)</option>
                            <option value="1024x1792">Portrait (1024×1792)</option>
                        </select>
                        <button type="button" class="btn btn-ai" onclick="generateAiImage()">✨ Generate Image</button>
                    </div>
                </div>

                <div class="ai-gen-preview" id="ai-gen-preview">
                    <div class="ai-gen-status">
                        <p style="font-size: 2rem; margin-bottom: 0.5rem;">🎨</p>
                        <p>Describe your image and click Generate</p>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Powered by DALL-E 3</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMediaBrowser()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="selectMedia()" id="select-media-btn" disabled>Select Image</button>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
const AI_ENDPOINT = '/admin/api/ai-content.php';
let selectedMediaUrl = null;

// Wait for DOM and TinyMCE to be ready
document.addEventListener('DOMContentLoaded', function() {
    initTinyMCE();
});

// Initialize TinyMCE
let initAttempts = 0;
function initTinyMCE() {
    initAttempts++;
    if (typeof tinymce === 'undefined') {
        if (initAttempts < 50) {
            setTimeout(initTinyMCE, 200);
        } else {
            document.getElementById('editor').style.cssText = 'display:block;min-height:400px;padding:1rem;background:#1e1e2e;color:#cdd6f4;border:1px solid #313244;border-radius:8px;width:100%;font-family:monospace;';
        }
        return;
    }
    
    tinymce.init({
        selector: '#editor',
        base_url: '/assets/vendor/tinymce',
        suffix: '.min',
        height: 500,
        menubar: true,
        skin: 'oxide-dark',
        content_css: 'dark',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount',
            'emoticons', 'codesample', 'quickbars'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
                 'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
                 'bullist numlist outdent indent | link customimage media table | ' +
                 'emoticons charmap | code fullscreen preview | removeformat help',
        toolbar_mode: 'sliding',
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
        quickbars_insert_toolbar: 'customimage quicktable',
        content_style: `
            body { font-family: 'Inter', -apple-system, sans-serif; font-size: 16px; line-height: 1.7; max-width: 100%; padding: 1rem; background: #1e1e2e; color: #cdd6f4; }
            h1, h2, h3, h4, h5, h6 { font-weight: 600; margin-top: 1.5em; margin-bottom: 0.5em; color: #cdd6f4; }
            p { margin-bottom: 1em; }
            img { max-width: 100%; height: auto; }
            pre { background: #313244; color: #cdd6f4; padding: 1rem; border-radius: 6px; }
            blockquote { border-left: 4px solid #89b4fa; padding-left: 1rem; margin: 1rem 0; color: #a6adc8; font-style: italic; }
            a { color: #89b4fa; }
        `,
        promotion: false,
        branding: false,
        setup: function(editor) {
            editor.ui.registry.addButton('customimage', {
                icon: 'image',
                tooltip: 'Insert image from Media Library',
                onAction: function() { openMediaBrowser('editor'); }
            });
            editor.on('keyup change', function() { updateWordCount(); analyzeSeo(); });
            editor.on('init', function() { updateWordCount(); analyzeSeo(); });
        }
    });
}

// Tab switching
document.querySelectorAll('.sidebar-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.sidebar-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

// Status options
document.querySelectorAll('.status-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.status-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
    });
});

// Auto-generate slug
document.getElementById('page-title')?.addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput && !slugInput.dataset.manual) {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }
});
document.getElementById('slug')?.addEventListener('input', function() { this.dataset.manual = 'true'; });

function updateWordCount() {
    if (typeof tinymce !== 'undefined' && tinymce.get('editor')) {
        const content = tinymce.get('editor').getContent({ format: 'text' });
        const words = content.trim() ? content.trim().split(/\s+/).length : 0;
        document.getElementById('wc-words').textContent = words;
        document.getElementById('wc-chars').textContent = content.length;
        document.getElementById('wc-reading').textContent = Math.ceil(words / 200);
    }
}

function updateSeoPreview() {
    const title = document.getElementById('meta-title').value || document.getElementById('page-title').value || 'Page Title';
    const desc = document.getElementById('meta-desc').value || document.getElementById('excerpt').value || 'Page description...';
    document.getElementById('seo-title').textContent = title;
    document.getElementById('seo-desc').textContent = desc;
}

function updateCharCount(input, max) {
    const count = input.value.length;
    const countEl = document.getElementById(input.id + '-count');
    countEl.textContent = count + '/' + max;
    countEl.classList.remove('warning', 'danger');
    if (count > max) countEl.classList.add('danger');
    else if (count > max * 0.9) countEl.classList.add('warning');
}

function calculateReadability(text) {
    if (!text || text.trim().length < 100) return { score: null, grade: null };
    const sentences = (text.match(/[.!?]+/g) || []).length || 1;
    const words = text.trim().split(/\s+/).filter(w => w.length > 0);
    if (words.length < 10) return { score: null, grade: null };
    
    function countSyllables(word) {
        word = word.toLowerCase().replace(/[^a-z]/g, '');
        if (word.length <= 3) return 1;
        word = word.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '').replace(/^y/, '');
        return (word.match(/[aeiouy]{1,2}/g) || [1]).length;
    }
    
    let totalSyllables = 0;
    words.forEach(w => totalSyllables += countSyllables(w));
    
    const flesch = 206.835 - (1.015 * words.length / sentences) - (84.6 * totalSyllables / words.length);
    const grade = (0.39 * words.length / sentences) + (11.8 * totalSyllables / words.length) - 15.59;
    
    return { score: Math.round(Math.max(0, Math.min(100, flesch))), grade: Math.round(Math.max(1, Math.min(18, grade))) };
}

function updateReadability() {
    const content = tinymce.get('editor') ? tinymce.get('editor').getContent({ format: 'text' }) : '';
    const result = calculateReadability(content);
    const scoreEl = document.getElementById('readability-score');
    const gradeEl = document.getElementById('reading-grade');
    
    if (result.score !== null) {
        scoreEl.textContent = result.score;
        gradeEl.textContent = result.grade;
        scoreEl.style.color = result.score >= 60 ? 'var(--success)' : result.score >= 40 ? 'var(--warning)' : 'var(--danger)';
    } else {
        scoreEl.textContent = '—';
        gradeEl.textContent = '—';
    }
}


function analyzeSeo() {
    updateReadability();
    const title = document.getElementById('page-title').value;
    const metaTitle = document.getElementById('meta-title').value;
    const metaDesc = document.getElementById('meta-desc').value;
    const focusKeyword = document.getElementById('focus-keyword').value.toLowerCase();
    const content = tinymce.get('editor') ? tinymce.get('editor').getContent({ format: 'text' }) : '';
    const wordCount = content.trim() ? content.trim().split(/\s+/).length : 0;
    
    let score = 0;
    let checks = [];
    
    if (focusKeyword) {
        score += 10;
        checks.push({ pass: true, text: 'Focus keyword is set' });
        if (title.toLowerCase().includes(focusKeyword)) { score += 15; checks.push({ pass: true, text: 'Keyword in title' }); }
        else { checks.push({ pass: false, text: 'Add keyword to title' }); }
        if (metaDesc.toLowerCase().includes(focusKeyword)) { score += 10; checks.push({ pass: true, text: 'Keyword in meta description' }); }
        else { checks.push({ pass: false, text: 'Add keyword to meta description' }); }
        
        const keywordCount = (content.toLowerCase().match(new RegExp(focusKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length;
        const density = wordCount > 0 ? (keywordCount / wordCount * 100) : 0;
        if (keywordCount >= 2 && density >= 0.5 && density <= 2.5) { score += 15; checks.push({ pass: true, text: `Keyword density: ${density.toFixed(1)}%` }); }
        else if (keywordCount > 0) { score += 5; checks.push({ pass: 'warn', text: `Keyword density: ${density.toFixed(1)}%` }); }
        else { checks.push({ pass: false, text: 'Add keyword to content' }); }
    } else {
        checks.push({ pass: false, text: 'Set a focus keyword' });
    }
    
    if (metaTitle.length >= 30 && metaTitle.length <= 60) { score += 15; checks.push({ pass: true, text: `Meta title: ${metaTitle.length}/60` }); }
    else if (metaTitle.length > 0) { score += 5; checks.push({ pass: 'warn', text: `Meta title: ${metaTitle.length} chars` }); }
    else { checks.push({ pass: false, text: 'Add meta title' }); }
    
    if (metaDesc.length >= 120 && metaDesc.length <= 160) { score += 15; checks.push({ pass: true, text: `Meta description: ${metaDesc.length}/160` }); }
    else if (metaDesc.length > 0) { score += 5; checks.push({ pass: 'warn', text: `Meta description: ${metaDesc.length} chars` }); }
    else { checks.push({ pass: false, text: 'Add meta description' }); }
    
    if (wordCount >= 200) { score += 20; checks.push({ pass: true, text: `Content: ${wordCount} words` }); }
    else if (wordCount >= 50) { score += 10; checks.push({ pass: 'warn', text: `Content: ${wordCount} words` }); }
    else { checks.push({ pass: false, text: `Add more content (${wordCount} words)` }); }
    
    const scoreCircle = document.getElementById('seo-score-circle');
    scoreCircle.textContent = score;
    scoreCircle.className = 'seo-score-circle ' + (score >= 70 ? 'good' : score >= 40 ? 'ok' : 'poor');
    document.getElementById('seo-score-hint').textContent = score >= 70 ? 'Great! Well optimized' : score >= 40 ? 'Could be improved' : 'Needs work';
    
    document.getElementById('seo-checklist').innerHTML = checks.map(c => `
        <li><span class="seo-check ${c.pass === true ? 'pass' : c.pass === 'warn' ? 'warn' : 'fail'}">${c.pass === true ? '✓' : c.pass === 'warn' ? '!' : '✗'}</span>${c.text}</li>
    `).join('');
}

// Media Browser
let mediaTarget = 'featured';
function openMediaBrowser(target = 'featured') {
    mediaTarget = target;
    selectedMediaUrl = null;
    document.getElementById('select-media-btn').disabled = true;
    document.querySelectorAll('.media-item').forEach(item => item.classList.remove('selected'));
    document.getElementById('media-modal').classList.add('active');
}
function closeMediaBrowser() { document.getElementById('media-modal').classList.remove('active'); }

document.querySelectorAll('.media-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        selectedMediaUrl = this.dataset.url;
        document.getElementById('select-media-btn').disabled = false;
    });
});

// Media Tabs
document.querySelectorAll('.media-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.media-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.media-tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('media-tab-' + this.dataset.mediaTab).classList.add('active');
    });
});

function selectMedia() {
    if (!selectedMediaUrl) return;
    if (mediaTarget === 'featured') {
        document.getElementById('featured-image-value').value = selectedMediaUrl;
        const box = document.getElementById('featured-image-box');
        box.innerHTML = `<img src="${selectedMediaUrl}" alt="Featured"><button type="button" class="remove-btn" onclick="event.stopPropagation(); removeFeaturedImage();">×</button>`;
        box.classList.add('has-image');
    } else {
        tinymce.get('editor').insertContent(`<img src="${selectedMediaUrl}" alt="" style="max-width: 100%;">`);
    }
    closeMediaBrowser();
}

function removeFeaturedImage() {
    document.getElementById('featured-image-value').value = '';
    const box = document.getElementById('featured-image-box');
    box.innerHTML = '<div class="placeholder"><div style="font-size: 2rem; margin-bottom: 0.5rem;">📷</div><div>Click to select image</div></div>';
    box.classList.remove('has-image');
}

// Upload
const uploadArea = document.getElementById('upload-area');
const uploadInput = document.getElementById('media-upload');
uploadArea?.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
uploadArea?.addEventListener('dragleave', () => { uploadArea.classList.remove('dragover'); });
uploadArea?.addEventListener('drop', (e) => { e.preventDefault(); uploadArea.classList.remove('dragover'); if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]); });
uploadInput?.addEventListener('change', () => { if (uploadInput.files.length) uploadFile(uploadInput.files[0]); });

function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('csrf_token', CSRF_TOKEN);
    
    fetch('/admin/api/article-image-upload.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        const url = data.url || (data.file && data.file.url);
        if ((data.success || data.ok) && url) {
            const grid = document.getElementById('media-grid');
            const item = document.createElement('div');
            item.className = 'media-item selected';
            item.dataset.url = url;
            item.innerHTML = `<img src="${url}" alt=""><div class="filename">${data.filename || 'image'}</div>`;
            item.addEventListener('click', function() {
                document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                selectedMediaUrl = this.dataset.url;
                document.getElementById('select-media-btn').disabled = false;
            });
            grid.insertBefore(item, grid.firstChild);
            selectedMediaUrl = url;
            document.getElementById('select-media-btn').disabled = false;
            document.querySelector('[data-media-tab="library"]').click();
            showToast('Image uploaded!', 'success');
        } else {
            showToast(data.error || 'Upload failed', 'error');
        }
    }).catch(() => showToast('Upload failed', 'error'));
}

// Stock Photos (Pexels API via proxy)
function searchStockPhotos() {
    const query = document.getElementById('stock-search-input').value.trim();
    if (!query) {
        showToast('Please enter a search term', 'error');
        return;
    }

    const results = document.getElementById('stock-results');
    results.innerHTML = '<div class="stock-loading"><p>🔍 Searching Pexels...</p></div>';

    fetch(`/api/stock-images.php?q=${encodeURIComponent(query)}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            results.innerHTML = `<div class="stock-loading"><p>❌ ${data.error}</p></div>`;
            return;
        }
        if (!data.images || data.images.length === 0) {
            results.innerHTML = '<div class="stock-loading"><p>No photos found. Try different keywords.</p></div>';
            return;
        }

        let html = '<div class="stock-grid">';
        data.images.forEach(img => {
            html += `
                <div class="stock-item" data-url="${img.url}" data-small="${img.preview}">
                    <img src="${img.preview}" alt="${img.alt || ''}">
                    <div class="credit">📷 ${img.photographer || 'Pexels'}</div>
                </div>
            `;
        });
        html += '</div>';
        html += '<p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem; text-align: center;">Photos from <a href="https://www.pexels.com" target="_blank" style="color: var(--primary);">Pexels</a> - Free to use</p>';
        results.innerHTML = html;

        // Bind click events
        document.querySelectorAll('.stock-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
                document.querySelectorAll('.stock-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                selectedMediaUrl = this.dataset.url;
                document.getElementById('select-media-btn').disabled = false;
            });
        });
    })
    .catch(err => {
        results.innerHTML = '<div class="stock-loading"><p>❌ Error loading photos: ' + err.message + '</p></div>';
        console.error('Stock photo search error:', err);
    });
}

// Enter key for stock search
document.getElementById('stock-search-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchStockPhotos();
    }
});

// AI Image Generator
function generateAiImage() {
    const prompt = document.getElementById('ai-image-prompt').value.trim();
    if (!prompt) {
        showToast('Please describe the image you want to create', 'error');
        return;
    }

    const style = document.getElementById('ai-image-style').value;
    const size = document.getElementById('ai-image-size').value;
    const preview = document.getElementById('ai-gen-preview');

    preview.innerHTML = '<div class="ai-gen-status"><div class="spinner" style="width: 40px; height: 40px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div><p>🎨 Generating your image...</p><p style="font-size: 0.75rem; color: var(--text-muted);">This may take 10-30 seconds</p></div>';

    // Add spinner animation if not exists
    if (!document.getElementById('spinner-style')) {
        const spinStyle = document.createElement('style');
        spinStyle.id = 'spinner-style';
        spinStyle.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(spinStyle);
    }

    fetch('/admin/api/ai-image-generate.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: CSRF_TOKEN,
            prompt: prompt,
            style: style,
            size: size
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.error || 'HTTP ' + response.status);
            }).catch(() => {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.ok && data.url) {
            preview.innerHTML = `
                <div style="text-align: center;">
                    <img src="${data.url}" alt="AI Generated" class="ai-gen-result" style="max-height: 400px;">
                    <p style="font-size: 0.75rem; color: var(--success); margin-top: 1rem;">✅ Image generated and saved to media library!</p>
                </div>
            `;
            selectedMediaUrl = data.url;
            document.getElementById('select-media-btn').disabled = false;

            // Add to media grid
            const grid = document.getElementById('media-grid');
            const item = document.createElement('div');
            item.className = 'media-item';
            item.dataset.url = data.url;
            item.innerHTML = `<img src="${data.url}" alt="AI Generated"><div class="filename">ai-generated.png</div>`;
            item.addEventListener('click', function() {
                document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
                document.querySelectorAll('.stock-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                selectedMediaUrl = this.dataset.url;
                document.getElementById('select-media-btn').disabled = false;
            });
            grid.insertBefore(item, grid.firstChild);

            showToast('Image generated successfully!', 'success');
        } else {
            preview.innerHTML = `<div class="ai-gen-status"><p>❌ ${data.error || 'Generation failed'}</p><p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Try a different prompt or check your API key</p></div>`;
        }
    })
    .catch(err => {
        preview.innerHTML = '<div class="ai-gen-status"><p>❌ ' + err.message + '</p><p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Check console for details</p></div>';
        console.error('AI image generation error:', err);
    });
}

// AI Functions
async function aiGenerate(action) {
    const content = tinymce.get('editor') ? tinymce.get('editor').getContent({ format: 'text' }) : '';
    if (!content.trim() && action !== 'generate_title') { showToast('Add some content first', 'error'); return; }
    showToast('AI is working...', 'success');
    
    try {
        const response = await fetch(AI_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: CSRF_TOKEN, action: action, text: content.substring(0, 2000) })
        });
        const result = await response.json();
        if (result.success) { showAiResult(action, result.content); }
        else { showToast(result.error || 'AI request failed', 'error'); }
    } catch (e) { showToast('Network error', 'error'); }
}

async function aiTransformSelection(action) {
    const selection = tinymce.get('editor').selection.getContent({ format: 'text' });
    if (!selection.trim()) { showToast('Select some text first', 'error'); return; }
    showToast('AI is working...', 'success');
    
    try {
        const response = await fetch(AI_ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: CSRF_TOKEN, action: action, text: selection })
        });
        const result = await response.json();
        if (result.success) { showAiResult(action, result.content, selection); }
        else { showToast(result.error || 'AI request failed', 'error'); }
    } catch (e) { showToast('Network error', 'error'); }
}

function showAiResult(action, content, originalSelection = null) {
    const labels = {
        'generate_title': '📝 Generated Titles', 'generate_excerpt': '📄 Generated Summary', 'generate_meta': '🎯 Meta Description',
        'improve_content': '✨ Improved', 'expand_content': '📝 Expanded', 'simplify': '📖 Simplified',
        'translate_en': '🇬🇧 English', 'translate_pl': '🇵🇱 Polish', 'translate_de': '🇩🇪 German',
        'fix_grammar': '✓ Fixed', 'make_formal': '👔 Formal', 'make_casual': '😊 Casual'
    };
    
    let applyBtn = '';
    if (action === 'generate_excerpt') applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('excerpt').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent; showToast('Applied!', 'success');">Apply</button>`;
    else if (action === 'generate_meta') applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('meta-desc').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent.substring(0, 160); updateCharCount(document.getElementById('meta-desc'), 160); showToast('Applied!', 'success');">Apply</button>`;
    else if (originalSelection) applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="tinymce.get('editor').selection.setContent(this.closest('.ai-result').querySelector('.ai-result-content').textContent); showToast('Replaced!', 'success');">Replace</button>`;
    
    document.getElementById('ai-result-container').innerHTML = `
        <div class="ai-result">
            <div class="ai-result-header"><div class="ai-result-title">${labels[action] || 'AI Result'}</div><button type="button" style="background: none; border: none; cursor: pointer; color: var(--text-muted);" onclick="this.closest('.ai-result').remove()">×</button></div>
            <div class="ai-result-content">${content.replace(/</g, '&lt;')}</div>
            <div class="ai-result-actions"><button type="button" class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText(this.closest('.ai-result').querySelector('.ai-result-content').textContent); showToast('Copied!', 'success');">📋 Copy</button>${applyBtn}</div>
        </div>`;
    document.querySelector('[data-tab="ai"]').click();
}

function showToast(message, type = 'success') {
    document.querySelector('.toast')?.remove();
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Init
updateCharCount(document.getElementById('meta-title'), 60);
updateCharCount(document.getElementById('meta-desc'), 160);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.querySelector('button[value="draft"]').click();
    }
    if (e.key === 'Escape') {
        closePreview();
    }
});

// Preview Functions - move modal to body on first open for proper z-index
var previewModalMoved = false;
function openPreview() {
    var modal = document.getElementById('preview-modal');
    // Move modal to body if not already done (fixes stacking context issues)
    if (!previewModalMoved && modal && modal.parentNode !== document.body) {
        document.body.appendChild(modal);
        previewModalMoved = true;
    }
    var content = document.getElementById('preview-content');
    var title = document.getElementById('page-title').value || 'Untitled Page';
    var body = tinymce.get('editor') ? tinymce.get('editor').getContent() : '';
    var featuredImage = document.getElementById('featured-image-value').value;
    
    var html = '<h1>' + title.replace(/</g, '&lt;') + '</h1>';
    if (featuredImage) {
        html += '<img src="' + featuredImage + '" alt="">';
    }
    html += body;
    
    content.innerHTML = html;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    console.log('Preview opened, modal moved to body:', previewModalMoved);
}

function closePreview() {
    console.log('closePreview called');
    var modal = document.getElementById('preview-modal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        console.log('Preview closed');
    }
}

function setPreviewSize(size) {
    var frame = document.getElementById('preview-frame');
    frame.className = 'preview-frame ' + size;
    var tabs = document.querySelectorAll('.preview-tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    event.target.classList.add('active');
}
</script>

<!-- Preview Modal -->
<div class="preview-modal" id="preview-modal" onclick="if(event.target===this)closePreview()">
    <button type="button" onclick="closePreview()" style="position:fixed; top:80px; right:20px; z-index:9999; background:#f38ba8; color:#1e1e2e; border:none; border-radius:50%; width:50px; height:50px; font-size:24px; cursor:pointer; font-weight:bold;">✕</button>
    <div class="preview-header" style="display:flex !important; justify-content:space-between !important; align-items:center !important; background:#181825 !important; padding:1rem 1.5rem !important; border-bottom:1px solid #313244 !important;">
        <h3 style="color:#cdd6f4; margin:0;">Page Preview</h3>
        <div class="preview-tabs">
            <button type="button" class="preview-tab active" onclick="event.stopPropagation();setPreviewSize('desktop')">Desktop</button>
            <button type="button" class="preview-tab" onclick="event.stopPropagation();setPreviewSize('tablet')">Tablet</button>
            <button type="button" class="preview-tab" onclick="event.stopPropagation();setPreviewSize('mobile')">Mobile</button>
        </div>
        <button type="button" class="btn btn-secondary" onclick="event.stopPropagation();closePreview()">Close</button>
    </div>
    <div class="preview-frame desktop" id="preview-frame">
        <div class="preview-content" id="preview-content"></div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
