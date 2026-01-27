<?php
/**
 * Article Editor with TinyMCE WYSIWYG + AI Tools + SEO Analysis
 * Full-featured editor like WordPress with AI integration
 */

$pageTitle = $article ? 'Edit Article' : 'New Article';
$isEdit = $article !== null;

// Load Pexels API key from settings
// Note: /models/settingsmodel.php uses static methods, /admin/models/ uses instance methods
$pexelsApiKey = '';
try {
    $allSettings = SettingsModel::getAll();
    foreach ($allSettings as $s) {
        if (($s['key'] ?? $s['setting_key'] ?? '') === 'pexels_api_key') {
            $pexelsApiKey = $s['value'] ?? $s['setting_value'] ?? '';
            break;
        }
    }
} catch (Throwable $e) {
    // Settings table might not exist or have different structure
}

// Load AI settings
$aiSettingsFile = dirname(CMS_APP) . '/config/ai_settings.json';
$aiSettings = file_exists($aiSettingsFile) ? json_decode(file_get_contents($aiSettingsFile), true) : [];

// Check for API key in nested structure: providers.openai.api_key
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

// Set title for layout
$title = $pageTitle;
ob_start();
?>

<!-- TinyMCE Script -->
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
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: var(--border); color: var(--text); }
        .btn-success { background: var(--success); color: white; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
        .btn-ai { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; }
        .btn-ai:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); }
        
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.9375rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card-bg); color: var(--text); transition: border-color 0.2s; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        .form-textarea { min-height: 80px; resize: vertical; font-family: inherit; }
        .form-hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
        
        .title-input { font-size: 1.75rem; font-weight: 600; border: none; padding: 0.5rem 0; margin-bottom: 1rem; }
        .title-input:focus { outline: none; box-shadow: none; }
        
        .editor-wrapper { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        
        .sidebar-tabs { display: flex; border-bottom: 1px solid var(--border); background: var(--sidebar-bg); }
        .sidebar-tab { flex: 1; padding: 0.75rem; text-align: center; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; background: none; color: var(--text-muted); transition: all 0.2s; }
        .sidebar-tab:hover { color: var(--text); background: rgba(99, 102, 241, 0.05); }
        .sidebar-tab.active { color: var(--primary); background: var(--card-bg); border-bottom: 2px solid var(--primary); margin-bottom: -1px; }
        
        .sidebar-content { display: none; padding: 1.25rem; }
        .sidebar-content.active { display: block; }
        
        .sidebar-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
        .sidebar-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .sidebar-title { font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        
        .status-options { display: flex; flex-direction: column; gap: 0.5rem; }
        .status-option { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; transition: all 0.2s; }
        .status-option:hover { border-color: var(--primary); }
        .status-option.selected { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); }
        .status-option input { display: none; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; }
        .status-dot.draft { background: var(--warning); }
        .status-dot.published { background: var(--success); }
        .status-dot.archived { background: #94a3b8; }
        
        .featured-image-box { border: 2px dashed var(--border); border-radius: 8px; padding: 2rem 1rem; text-align: center; cursor: pointer; transition: all 0.2s; position: relative; }
        .featured-image-box:hover { border-color: var(--primary); background: rgba(99, 102, 241, 0.02); }
        .featured-image-box.has-image { padding: 0; border-style: solid; }
        .featured-image-box img { width: 100%; border-radius: 6px; }
        .featured-image-box .remove-btn { position: absolute; top: 0.5rem; right: 0.5rem; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; }
        
        .ai-tool-group { margin-bottom: 1rem; }
        .ai-tool-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .ai-tools-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .ai-tool-btn { padding: 0.5rem; font-size: 0.75rem; border-radius: 6px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 0.375rem; justify-content: center; }
        .ai-tool-btn:hover { border-color: var(--primary); background: rgba(99, 102, 241, 0.15); color: #a5b4fc; }
        .ai-tool-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .ai-tool-btn.loading { background: var(--primary); color: white; }
        
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
        .seo-check.pass { background: #d1fae5; color: #065f46; }
        .seo-check.fail { background: #fee2e2; color: #991b1b; }
        .seo-check.warn { background: #fef3c7; color: #92400e; }
        
        .word-count { display: flex; gap: 1rem; padding: 0.75rem; background: var(--sidebar-bg); border-radius: 6px; font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 1rem; }
        .word-count strong { color: var(--text); }
        
        .seo-preview { background: var(--sidebar-bg); border-radius: 8px; padding: 1rem; margin-top: 1rem; }
        .seo-preview-title { color: #1a0dab; font-size: 1.125rem; font-weight: 500; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .seo-preview-url { color: #006621; font-size: 0.8125rem; margin-bottom: 0.25rem; }
        .seo-preview-desc { color: #545454; font-size: 0.8125rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .char-count { font-size: 0.75rem; color: var(--text-muted); text-align: right; margin-top: 0.25rem; }
        .char-count.warning { color: var(--warning); }
        .char-count.danger { color: var(--danger); }
        
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
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
        .media-item.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3); }
        .media-item img { width: 100%; height: 100%; object-fit: cover; }
        .media-item .filename { position: absolute; bottom: 0; left: 0; right: 0; padding: 0.25rem 0.5rem; background: rgba(0,0,0,0.7); color: white; font-size: 0.625rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .upload-area { border: 2px dashed var(--border); border-radius: 8px; padding: 2rem; text-align: center; margin-bottom: 1.5rem; transition: all 0.2s; }
        .upload-area:hover, .upload-area.dragover { border-color: var(--primary); background: rgba(99, 102, 241, 0.05); }
        .upload-area input { display: none; }
        
        /* Media Tabs */
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
        
        .toast { position: fixed; bottom: 2rem; right: 2rem; padding: 1rem 1.5rem; background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); z-index: 1001; animation: slideIn 0.3s ease; }
        .toast.success { border-left: 4px solid var(--success); }
        .toast.error { border-left: 4px solid var(--danger); }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        @media (max-width: 1024px) {
            .editor-layout { flex-direction: column; }
            .editor-sidebar { width: 100%; height: auto; position: static; border-left: none; border-top: 1px solid var(--border); }
        }
        
        /* Preview Modal */
        .preview-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 2000; background: rgba(0,0,0,0.9); }
        .preview-modal.active { display: flex; flex-direction: column; }
        .preview-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background: var(--sidebar-bg); border-bottom: 1px solid var(--border); }
        .preview-header h3 { margin: 0; font-size: 1rem; }
        .preview-tabs { display: flex; gap: 0.5rem; }
        .preview-tab { padding: 0.5rem 1rem; background: var(--border); color: var(--text); border: none; border-radius: 6px; cursor: pointer; font-size: 0.8125rem; }
        .preview-tab.active { background: var(--primary); color: white; }
        .preview-frame { flex: 1; background: white; margin: 1.5rem; border-radius: 12px; overflow: hidden; }
        .preview-frame.mobile { max-width: 375px; margin: 1.5rem auto; }
        .preview-frame.tablet { max-width: 768px; margin: 1.5rem auto; }
        .preview-content { width: 100%; height: 100%; padding: 2rem; overflow-y: auto; color: #1a1a1a; font-family: Georgia, serif; line-height: 1.8; }
        .preview-content h1 { font-size: 2.5rem; margin-bottom: 1rem; font-family: 'Inter', sans-serif; }
        .preview-content .meta { color: #666; font-size: 0.875rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .preview-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.5rem 0; }
        .preview-content h2 { font-size: 1.75rem; margin: 2rem 0 1rem; font-family: 'Inter', sans-serif; }
        .preview-content h3 { font-size: 1.375rem; margin: 1.5rem 0 0.75rem; font-family: 'Inter', sans-serif; }
        .preview-content p { margin-bottom: 1.25rem; }
        .preview-content ul, .preview-content ol { margin: 1rem 0; padding-left: 1.5rem; }
        .preview-content li { margin-bottom: 0.5rem; }
        
        /* AI SEO Results */
        .seo-analysis-score { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--sidebar-bg); border-radius: 8px; margin-bottom: 1rem; }
        .seo-analysis-circle { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; font-weight: 700; border: 3px solid; }
        .seo-analysis-circle.good { border-color: var(--success); color: var(--success); }
        .seo-analysis-circle.ok { border-color: var(--warning); color: var(--warning); }
        .seo-analysis-circle.poor { border-color: var(--danger); color: var(--danger); }
        .seo-breakdown { margin-bottom: 1rem; }
        .seo-breakdown-item { margin-bottom: 0.5rem; }
        .seo-breakdown-label { display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.25rem; }
        .seo-breakdown-bar { height: 4px; background: var(--border); border-radius: 2px; overflow: hidden; }
        .seo-breakdown-fill { height: 100%; border-radius: 2px; }
        .seo-breakdown-fill.good { background: var(--success); }
        .seo-breakdown-fill.ok { background: var(--warning); }
        .seo-breakdown-fill.poor { background: var(--danger); }
        .seo-keywords-grid { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-bottom: 1rem; }
        .seo-keyword-pill { display: flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; background: var(--sidebar-bg); border: 1px solid var(--border); border-radius: 4px; font-size: 0.6875rem; }
        .seo-keyword-pill.used { border-color: var(--success); color: var(--success); }
        .seo-keyword-pill.missing { border-color: var(--warning); color: var(--warning); }
        .seo-keyword-count { background: var(--card-bg); padding: 0 0.25rem; border-radius: 2px; font-weight: 600; }
        .seo-tasks { list-style: none; margin: 0; padding: 0; }
        .seo-tasks li { display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.5rem; font-size: 0.75rem; border-bottom: 1px solid var(--border); }
        .seo-tasks li:last-child { border-bottom: none; }
        .seo-task-priority { font-size: 0.625rem; font-weight: 600; padding: 0.125rem 0.375rem; border-radius: 3px; text-transform: uppercase; }
        .seo-task-priority.high { background: rgba(243, 139, 168, 0.2); color: #f38ba8; }
        .seo-task-priority.medium { background: rgba(249, 226, 175, 0.2); color: #f9e2af; }
        .seo-task-priority.low { background: var(--sidebar-bg); color: var(--text-muted); }
        .seo-section { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .seo-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .seo-section-title { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem; }
        .seo-loading { text-align: center; padding: 2rem 1rem; }
        .seo-loading-spinner { width: 32px; height: 32px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 0.75rem; }
        
        /* Block Editor Styles */
        .faq-edit-item { background: var(--sidebar-bg); border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .faq-edit-item .form-input, .faq-edit-item .form-textarea { background: var(--bg); }
        #faq-items { max-height: 400px; overflow-y: auto; }
    </style>

    <form method="POST" id="article-form" action="<?= $isEdit ? '/admin/articles/' . (int)$article['id'] : '/admin/articles/' ?>">
        <?= csrf_field() ?>
        
        <div class="editor-layout">
            <div class="editor-main">
                <div class="editor-header">
                    <h1>
                        <a href="/admin/articles" style="text-decoration: none; color: var(--text-muted);">←</a>
                        <?= $isEdit ? 'Edit Article' : 'New Article' ?>
                    </h1>
                    <div class="header-actions">
                        <?php if ($isEdit && !empty($article['id'])): ?>
                        <a href="/preview/article/<?= (int)$article['id'] ?>" target="_blank" class="btn btn-secondary">👁️ Preview</a>
                        <?php endif; ?>
                        <a href="/admin/articles" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="status" value="draft" class="btn btn-secondary">💾 Save Draft</button>
                        <button type="submit" name="status" value="published" class="btn btn-success">🚀 Publish</button>
                    </div>
                </div>
                
                <input type="text" name="title" id="article-title" class="form-input title-input" 
                       placeholder="Article title..."
                       value="<?= esc($article['title'] ?? '') ?>"
                       required
                       oninput="updateSeoPreview(); analyzeSeo();">
                
                <div class="word-count" id="word-count">
                    <span><strong id="wc-words">0</strong> words</span>
                    <span><strong id="wc-chars">0</strong> characters</span>
                    <span><strong id="wc-reading">0</strong> min read</span>
                </div>
                
                <div class="editor-wrapper">
                    <textarea name="content" id="editor"><?= esc($article['content'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label">Excerpt</label>
                        <?php if ($aiConfigured): ?>
                        <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_excerpt')">✨ Generate</button>
                        <?php endif; ?>
                    </div>
                    <textarea name="excerpt" id="excerpt" class="form-textarea" rows="3" 
                              placeholder="Brief summary of the article..."
                              oninput="updateSeoPreview()"><?= esc($article['excerpt'] ?? '') ?></textarea>
                    <div class="form-hint">Used in article listings and SEO descriptions.</div>
                </div>
            </div>
            
            <div class="editor-sidebar">
                <div class="sidebar-tabs">
                    <button type="button" class="sidebar-tab active" data-tab="publish">📤 Publish</button>
                    <button type="button" class="sidebar-tab" data-tab="seo">🎯 SEO</button>
                    <button type="button" class="sidebar-tab" data-tab="ai">🤖 AI</button>
                </div>
                
                <!-- Publish Tab -->
                <div class="sidebar-content active" id="tab-publish">
                    <div class="sidebar-section">
                        <div class="sidebar-title">📤 Status</div>
                        <div class="status-options">
                            <label class="status-option <?= ($article['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>">
                                <input type="radio" name="status_select" value="draft" <?= ($article['status'] ?? 'draft') === 'draft' ? 'checked' : '' ?>>
                                <span class="status-dot draft"></span>
                                <span>Draft</span>
                            </label>
                            <label class="status-option <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>">
                                <input type="radio" name="status_select" value="published" <?= ($article['status'] ?? '') === 'published' ? 'checked' : '' ?>>
                                <span class="status-dot published"></span>
                                <span>Published</span>
                            </label>
                            <label class="status-option <?= ($article['status'] ?? '') === 'archived' ? 'selected' : '' ?>">
                                <input type="radio" name="status_select" value="archived" <?= ($article['status'] ?? '') === 'archived' ? 'checked' : '' ?>>
                                <span class="status-dot archived"></span>
                                <span>Archived</span>
                            </label>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1rem;">
                            <label class="form-label">Publish Date</label>
                            <input type="datetime-local" name="published_at" class="form-input"
                                   value="<?= !empty($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="sidebar-section">
                        <div class="sidebar-title">📁 Category</div>
                        <select name="category_id" class="form-select">
                            <option value="">— No Category —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ((int)($article['category_id'] ?? 0)) === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= esc($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="/admin/categories" style="font-size: 0.75rem; color: var(--primary); margin-top: 0.5rem; display: inline-block;">+ Manage Categories</a>
                    </div>
                    
                    <div class="sidebar-section">
                        <div class="sidebar-title">🖼️ Featured Image</div>
                        <div class="featured-image-box <?= !empty($article['featured_image']) ? 'has-image' : '' ?>" 
                             id="featured-image-box"
                             onclick="openMediaBrowser('featured')">
                            <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?= esc($article['featured_image']) ?>" alt="Featured">
                            <button type="button" class="remove-btn" onclick="event.stopPropagation(); removeFeaturedImage();">×</button>
                            <?php else: ?>
                            <div class="placeholder">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📷</div>
                                <div>Click to select image</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="featured_image" id="featured-image-value" 
                               value="<?= esc($article['featured_image'] ?? '') ?>">
                        
                        <!-- Image SEO Fields -->
                        <div id="image-seo-fields" style="margin-top: 0.75rem; <?= empty($article['featured_image']) ? 'display: none;' : '' ?>">
                            <div class="form-group" style="margin-bottom: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <label class="form-label" style="font-size: 0.7rem; margin: 0;">Alt Text (SEO) <span style="color: var(--success);">✓</span></label>
                                    <?php if ($aiConfigured): ?>
                                    <button type="button" class="btn btn-ai btn-sm" style="padding: 0.15rem 0.4rem; font-size: 0.65rem;" onclick="aiGenerateImageAlt()">✨ AI</button>
                                    <?php endif; ?>
                                </div>
                                <input type="text" name="featured_image_alt" id="featured-image-alt" class="form-input" 
                                       placeholder="Describe the image for SEO and accessibility"
                                       value="<?= esc($article['featured_image_alt'] ?? '') ?>"
                                       style="font-size: 0.8rem; margin-top: 0.25rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <label class="form-label" style="font-size: 0.7rem; margin: 0;">Image Title</label>
                                    <?php if ($aiConfigured): ?>
                                    <button type="button" class="btn btn-ai btn-sm" style="padding: 0.15rem 0.4rem; font-size: 0.65rem;" onclick="aiGenerateImageTitle()">✨ AI</button>
                                    <?php endif; ?>
                                </div>
                                <input type="text" name="featured_image_title" id="featured-image-title" class="form-input" 
                                       placeholder="Title shown on hover"
                                       value="<?= esc($article['featured_image_title'] ?? '') ?>"
                                       style="font-size: 0.8rem; margin-top: 0.25rem;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="sidebar-section">
                        <div class="sidebar-title">🔗 URL Slug</div>
                        <input type="text" name="slug" id="slug" class="form-input" 
                               placeholder="article-url-slug"
                               value="<?= esc($article['slug'] ?? '') ?>">
                        <div class="form-hint">Leave empty to auto-generate</div>
                    </div>
                    
                    <?php if ($isEdit && !empty($article['created_at'])): ?>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">
                        <p>Created: <?= date('M j, Y g:i A', strtotime($article['created_at'])) ?></p>
                        <?php if (!empty($article['updated_at'])): ?>
                        <p>Updated: <?= date('M j, Y g:i A', strtotime($article['updated_at'])) ?></p>
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
                        
                        <div class="form-group" style="background: var(--sidebar-bg); padding: 0.75rem; border-radius: 8px; border: 1px solid var(--primary);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="color: var(--primary); margin: 0;">🎯 Focus Keyword</label>
                                <?php if ($aiConfigured): ?>
                                <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_focus_keyword')">✨ Suggest</button>
                                <?php endif; ?>
                            </div>
                            <input type="text" name="focus_keyword" id="focus-keyword" class="form-input" 
                                   placeholder="Main keyword to optimize for (required for AI SEO)"
                                   value="<?= esc($article['focus_keyword'] ?? '') ?>"
                                   oninput="analyzeSeo()"
                                   style="border-color: var(--primary);">
                            <p style="font-size: 0.625rem; color: var(--text-muted); margin-top: 0.25rem;">Single primary keyword for SEO optimization</p>
                        </div>
                        
                        <button type="button" onclick="runFullSeoAnalysis()" class="btn btn-ai btn-sm" style="width: 100%; justify-content: center; margin-top: 0.5rem;">
                            🎯 Full AI SEO Analysis
                        </button>
                        
                        <div id="ai-seo-results" style="display: none; margin-top: 1rem;"></div>
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
                               placeholder="SEO title (max 60 chars)"
                               value="<?= esc($article['meta_title'] ?? '') ?>"
                               oninput="updateSeoPreview(); updateCharCount(this, 60); analyzeSeo()">
                        <div class="char-count" id="meta-title-count">0/60</div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; margin-top: 1rem;">
                            <label class="form-label" style="margin: 0;">Meta Description</label>
                            <?php if ($aiConfigured): ?>
                            <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_meta')">✨ Generate</button>
                            <?php endif; ?>
                        </div>
                        <textarea name="meta_description" class="form-textarea" id="meta-desc" rows="2"
                                  placeholder="SEO description (max 160 chars)"
                                  oninput="updateSeoPreview(); updateCharCount(this, 160); analyzeSeo()"><?= esc($article['meta_description'] ?? '') ?></textarea>
                        <div class="char-count" id="meta-desc-count">0/160</div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; margin-top: 1rem;">
                            <label class="form-label" style="margin: 0;">Keywords</label>
                            <?php if ($aiConfigured): ?>
                            <button type="button" class="btn btn-ai btn-sm" onclick="aiGenerate('generate_keywords')">✨ Extract</button>
                            <?php endif; ?>
                        </div>
                        <input type="text" name="meta_keywords" id="meta-keywords" class="form-input"
                               placeholder="keyword1, keyword2, keyword3"
                               value="<?= esc($article['meta_keywords'] ?? '') ?>">
                        
                        <div class="seo-preview">
                            <div class="seo-preview-title" id="seo-title"><?= esc($article['meta_title'] ?? $article['title'] ?? 'Article Title') ?></div>
                            <div class="seo-preview-url">example.com/article/<?= esc($article['slug'] ?? 'article-slug') ?></div>
                            <div class="seo-preview-desc" id="seo-desc"><?= esc($article['meta_description'] ?? $article['excerpt'] ?? 'Article description...') ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- AI Tab -->
                <div class="sidebar-content" id="tab-ai">
                    <?php if (!$aiConfigured): ?>
                    <div style="padding: 1rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b; font-size: 0.875rem;">
                        AI not configured. <a href="/admin/settings">Configure API key</a>
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
                            <button type="button" class="ai-tool-btn" onclick="aiGenerate('generate_excerpt')" style="justify-content: flex-start;">📄 Generate Excerpt</button>
                            <button type="button" class="ai-tool-btn" onclick="aiGenerate('generate_meta')" style="justify-content: flex-start;">🎯 Generate Meta Description</button>
                            <button type="button" class="ai-tool-btn" onclick="aiGenerate('generate_keywords')" style="justify-content: flex-start;">🔑 Extract Keywords</button>
                        </div>
                    </div>
                    
                    <div id="ai-result-container"></div>
                    
                    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.75rem;">🚀 MORE AI TOOLS</div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="/admin/ai-copywriter.php" target="_blank" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">✍️ AI Copywriter</a>
                            <a href="/admin/ai-content-rewrite.php" target="_blank" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">🔄 Content Rewriter</a>
                            <a href="/admin/ai-translate.php" target="_blank" class="btn btn-secondary btn-sm" style="justify-content: flex-start;">🌍 Full Translation Tool</a>
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
                <!-- Media Tabs -->
                <div class="media-tabs">
                    <button type="button" class="media-tab active" data-media-tab="upload">📤 Upload</button>
                    <button type="button" class="media-tab" data-media-tab="library">🖼️ Library</button>
                    <button type="button" class="media-tab" data-media-tab="stock">📷 Stock Photos</button>
                    <button type="button" class="media-tab" data-media-tab="ai-generate">✨ AI Generate</button>
                </div>
                
                <!-- Upload Tab -->
                <div class="media-tab-content active" id="media-tab-upload">
                    <div class="upload-area" id="upload-area">
                        <input type="file" id="media-upload" accept="image/*">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📤</div>
                        <div>Drop image here or <label for="media-upload" style="color: var(--primary); cursor: pointer;">browse</label></div>
                    </div>
                    <div id="upload-progress" style="display: none;">
                        <div style="background: var(--border); border-radius: 4px; overflow: hidden;">
                            <div id="upload-bar" style="height: 4px; background: var(--primary); width: 0%; transition: width 0.3s;"></div>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Uploading...</p>
                    </div>
                </div>
                
                <!-- Library Tab -->
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
    
    <!-- FAQ Block Modal -->
    <div class="modal-overlay" id="faq-modal">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h3>❓ FAQ Section</h3>
                <button type="button" class="modal-close" onclick="closeFaqModal()">×</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <p style="margin: 0; color: var(--text-muted);">Add questions and answers for your FAQ section.</p>
                </div>
                <div id="faq-items">
                    <div class="faq-edit-item" data-index="0">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin: 0;">Question 1</label>
                            <button type="button" class="btn btn-sm" style="background: var(--danger); padding: 0.25rem 0.5rem;" onclick="removeFaqItem(0)">×</button>
                        </div>
                        <input type="text" class="form-input faq-question" placeholder="Enter your question..." style="margin-bottom: 0.5rem;">
                        <textarea class="form-textarea faq-answer" placeholder="Enter your answer..." rows="2"></textarea>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addFaqItem()" style="margin-top: 1rem;">+ Add Question</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFaqModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="insertFaqBlock()">Insert FAQ</button>
            </div>
        </div>
    </div>
    
    <!-- CTA Block Modal -->
    <div class="modal-overlay" id="cta-modal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3>📢 Call to Action</h3>
                <button type="button" class="modal-close" onclick="closeCtaModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Headline</label>
                    <input type="text" class="form-input" id="cta-headline" placeholder="Ready to Get Started?" value="Ready to Get Started?">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" id="cta-description" rows="2" placeholder="Take the next step...">Take the next step and transform your business today.</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Button Text</label>
                    <input type="text" class="form-input" id="cta-button-text" placeholder="Get Started Now →" value="Get Started Now →">
                </div>
                <div class="form-group">
                    <label class="form-label">Button Link</label>
                    <input type="text" class="form-input" id="cta-button-link" placeholder="https://" value="#">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCtaModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="insertCtaBlock()">Insert CTA</button>
            </div>
        </div>
    </div>
    
    <!-- Callout Block Modal -->
    <div class="modal-overlay" id="callout-modal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3 id="callout-modal-title">💡 Callout</h3>
                <button type="button" class="modal-close" onclick="closeCalloutModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select class="form-input" id="callout-type" onchange="updateCalloutPreview()">
                        <option value="info">💡 Info / Tip</option>
                        <option value="warning">⚠️ Warning</option>
                        <option value="success">✅ Success</option>
                        <option value="error">❌ Error</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-input" id="callout-title" placeholder="Pro Tip:" value="Pro Tip:">
                </div>
                <div class="form-group">
                    <label class="form-label">Content</label>
                    <textarea class="form-textarea" id="callout-content" rows="3" placeholder="Your message here...">Add your helpful tip or important information here.</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCalloutModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="insertCalloutBlock()">Insert Callout</button>
            </div>
        </div>
    </div>
    
    <!-- Testimonial Block Modal -->
    <div class="modal-overlay" id="testimonial-modal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3>💬 Testimonial</h3>
                <button type="button" class="modal-close" onclick="closeTestimonialModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Quote</label>
                    <textarea class="form-textarea" id="testimonial-quote" rows="3" placeholder="Customer testimonial...">This is an amazing product. It exceeded all my expectations!</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Author Name</label>
                    <input type="text" class="form-input" id="testimonial-author" placeholder="John Doe" value="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Author Title / Company</label>
                    <input type="text" class="form-input" id="testimonial-title" placeholder="CEO at Company" value="CEO at Company">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTestimonialModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="insertTestimonialBlock()">Insert Testimonial</button>
            </div>
        </div>
    </div>
    
    <script>
    console.log('=== MVC ARTICLE FORM LOADED ===');
    
    const CSRF_TOKEN = '<?= csrf_token() ?>';
    const ARTICLE_ID = <?= $isEdit ? (int)$article['id'] : 0 ?>;
    const AI_ENDPOINT = '/admin/api/ai-content.php';
    let selectedMediaUrl = null;
    
    // Wait for DOM and TinyMCE to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded - starting TinyMCE init');
        initTinyMCE();
    });
    
    // Initialize TinyMCE
    let initAttempts = 0;
    function initTinyMCE() {
        initAttempts++;
        console.log('initTinyMCE attempt ' + initAttempts + ', tinymce exists:', typeof tinymce !== 'undefined');
        if (typeof tinymce === 'undefined') {
            if (initAttempts < 50) {
                setTimeout(initTinyMCE, 200);
            } else {
                console.error('TinyMCE failed to load after 50 attempts');
                // Show plain textarea as fallback
                document.getElementById('editor').style.cssText = 'display:block;min-height:400px;padding:1rem;background:#1e1e2e;color:#cdd6f4;border:1px solid #313244;border-radius:8px;width:100%;font-family:monospace;';
            }
            return;
        }
        console.log('TinyMCE loaded! Initializing...');
        tinymce.init({
        selector: '#editor',
        base_url: '/assets/vendor/tinymce',
        suffix: '.min',
        height: 700,
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
                 'insertblocks | emoticons charmap | code fullscreen preview | removeformat help',
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
                onAction: function() {
                    openMediaBrowser('editor');
                }
            });
            
            // Insert Blocks dropdown menu
            editor.ui.registry.addMenuButton('insertblocks', {
                text: '+ Block',
                tooltip: 'Insert content block',
                fetch: function(callback) {
                    var items = [
                        {
                            type: 'menuitem',
                            text: '❓ FAQ Section',
                            onAction: function() { openFaqModal(); }
                        },
                        {
                            type: 'menuitem',
                            text: '📢 Call to Action',
                            onAction: function() { openCtaModal(); }
                        },
                        {
                            type: 'menuitem',
                            text: '💡 Callout Box',
                            onAction: function() { openCalloutModal(); }
                        },
                        {
                            type: 'menuitem',
                            text: '💬 Testimonial',
                            onAction: function() { openTestimonialModal(); }
                        },
                        {
                            type: 'menuitem',
                            text: '📋 Table of Contents',
                            onAction: function() {
                                editor.insertContent(`
<div class="toc-block" style="background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin: 24px 0;">
<h4 style="margin: 0 0 12px 0; color: #333;">📋 Table of Contents</h4>
<ul style="margin: 0; padding-left: 20px; color: #555;">
<li><a href="#section-1" style="color: #1976D2; text-decoration: none;">Section 1: Introduction</a></li>
<li><a href="#section-2" style="color: #1976D2; text-decoration: none;">Section 2: Main Topic</a></li>
<li><a href="#section-3" style="color: #1976D2; text-decoration: none;">Section 3: Details</a></li>
<li><a href="#section-4" style="color: #1976D2; text-decoration: none;">Section 4: Conclusion</a></li>
</ul>
</div>`);
                            }
                        },
                        {
                            type: 'menuitem',
                            text: '⭐ Feature Box (3 columns)',
                            onAction: function() {
                                editor.insertContent(`
<div class="feature-box" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 24px 0;">
<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; text-align: center;">
<div style="font-size: 32px; margin-bottom: 12px;">🚀</div>
<h4 style="margin: 0 0 8px 0; color: #333;">Feature One</h4>
<p style="margin: 0; color: #666; font-size: 14px;">Description of this amazing feature.</p>
</div>
<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; text-align: center;">
<div style="font-size: 32px; margin-bottom: 12px;">💪</div>
<h4 style="margin: 0 0 8px 0; color: #333;">Feature Two</h4>
<p style="margin: 0; color: #666; font-size: 14px;">Description of this amazing feature.</p>
</div>
<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; text-align: center;">
<div style="font-size: 32px; margin-bottom: 12px;">✨</div>
<h4 style="margin: 0 0 8px 0; color: #333;">Feature Three</h4>
<p style="margin: 0; color: #666; font-size: 14px;">Description of this amazing feature.</p>
</div>
</div>`);
                            }
                        },
                        {
                            type: 'menuitem',
                            text: '📊 Stats/Numbers',
                            onAction: function() {
                                editor.insertContent(`
<div class="stats-block" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin: 24px 0; text-align: center;">
<div style="background: #667eea; border-radius: 8px; padding: 20px; color: white;">
<div style="font-size: 36px; font-weight: 700;">500+</div>
<div style="font-size: 14px; opacity: 0.9;">Happy Clients</div>
</div>
<div style="background: #764ba2; border-radius: 8px; padding: 20px; color: white;">
<div style="font-size: 36px; font-weight: 700;">99%</div>
<div style="font-size: 14px; opacity: 0.9;">Satisfaction</div>
</div>
<div style="background: #f093fb; border-radius: 8px; padding: 20px; color: white;">
<div style="font-size: 36px; font-weight: 700;">24/7</div>
<div style="font-size: 14px; opacity: 0.9;">Support</div>
</div>
<div style="background: #4facfe; border-radius: 8px; padding: 20px; color: white;">
<div style="font-size: 36px; font-weight: 700;">10+</div>
<div style="font-size: 14px; opacity: 0.9;">Years Experience</div>
</div>
</div>`);
                            }
                        }
                    ];
                    callback(items);
                }
            });
            
            editor.on('keyup change', function() {
                updateWordCount();
                analyzeSeo();
            });
            
            editor.on('init', function() {
                updateWordCount();
                analyzeSeo();
            });
        }
    });
    } // end initTinyMCE
    
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
    document.getElementById('article-title')?.addEventListener('input', function() {
        const slugInput = document.getElementById('slug');
        if (slugInput && !slugInput.dataset.manual) {
            slugInput.value = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }
    });
    document.getElementById('slug')?.addEventListener('input', function() {
        this.dataset.manual = 'true';
    });
    
    // Word count
    function updateWordCount() {
        if (typeof tinymce !== 'undefined' && tinymce.get('editor')) {
            const content = tinymce.get('editor').getContent({ format: 'text' });
            const words = content.trim() ? content.trim().split(/\s+/).length : 0;
            const chars = content.length;
            const reading = Math.ceil(words / 200);
            
            document.getElementById('wc-words').textContent = words;
            document.getElementById('wc-chars').textContent = chars;
            document.getElementById('wc-reading').textContent = reading;
        }
    }
    
    // SEO Preview
    function updateSeoPreview() {
        const title = document.getElementById('meta-title').value || document.getElementById('article-title').value || 'Article Title';
        const desc = document.getElementById('meta-desc').value || document.getElementById('excerpt').value || 'Article description...';
        
        document.getElementById('seo-title').textContent = title;
        document.getElementById('seo-desc').textContent = desc;
    }
    
    // Char count
    function updateCharCount(input, max) {
        const count = input.value.length;
        const countEl = document.getElementById(input.id + '-count');
        countEl.textContent = count + '/' + max;
        
        countEl.classList.remove('warning', 'danger');
        if (count > max) countEl.classList.add('danger');
        else if (count > max * 0.9) countEl.classList.add('warning');
    }
    
    // Readability Score (Flesch-Kincaid)
    function calculateReadability(text) {
        if (!text || text.trim().length < 100) return { score: null, grade: null };
        
        const sentences = (text.match(/[.!?]+/g) || []).length || 1;
        const words = text.trim().split(/\s+/).filter(w => w.length > 0);
        const wordCount = words.length;
        if (wordCount < 10) return { score: null, grade: null };
        
        function countSyllables(word) {
            word = word.toLowerCase().replace(/[^a-z]/g, '');
            if (word.length <= 3) return 1;
            word = word.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '');
            word = word.replace(/^y/, '');
            const syllables = word.match(/[aeiouy]{1,2}/g);
            return syllables ? syllables.length : 1;
        }
        
        let totalSyllables = 0;
        words.forEach(w => totalSyllables += countSyllables(w));
        
        const avgSentenceLen = wordCount / sentences;
        const avgSyllables = totalSyllables / wordCount;
        const flesch = 206.835 - (1.015 * avgSentenceLen) - (84.6 * avgSyllables);
        const grade = (0.39 * avgSentenceLen) + (11.8 * avgSyllables) - 15.59;
        
        return {
            score: Math.round(Math.max(0, Math.min(100, flesch))),
            grade: Math.round(Math.max(1, Math.min(18, grade)))
        };
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
    
    // SEO Analysis
    function analyzeSeo() {
        updateReadability();
        const title = document.getElementById('article-title').value;
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
            
            if (title.toLowerCase().includes(focusKeyword)) {
                score += 15;
                checks.push({ pass: true, text: 'Keyword in title' });
            } else {
                checks.push({ pass: false, text: 'Add keyword to title' });
            }
            
            if (metaDesc.toLowerCase().includes(focusKeyword)) {
                score += 10;
                checks.push({ pass: true, text: 'Keyword in meta description' });
            } else {
                checks.push({ pass: false, text: 'Add keyword to meta description' });
            }
            
            const keywordCount = (content.toLowerCase().match(new RegExp(focusKeyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length;
            const density = wordCount > 0 ? (keywordCount / wordCount * 100) : 0;
            
            if (keywordCount >= 3 && density >= 0.5 && density <= 2.5) {
                score += 15;
                checks.push({ pass: true, text: `Keyword density: ${density.toFixed(1)}% (${keywordCount}x)` });
            } else if (keywordCount > 0) {
                score += 5;
                checks.push({ pass: 'warn', text: `Keyword density: ${density.toFixed(1)}% - aim for 0.5-2.5%` });
            } else {
                checks.push({ pass: false, text: 'Add keyword to content' });
            }
        } else {
            checks.push({ pass: false, text: 'Set a focus keyword' });
        }
        
        if (metaTitle.length >= 30 && metaTitle.length <= 60) {
            score += 15;
            checks.push({ pass: true, text: `Meta title length: ${metaTitle.length}/60` });
        } else if (metaTitle.length > 0) {
            score += 5;
            checks.push({ pass: 'warn', text: `Meta title: ${metaTitle.length} chars (aim for 30-60)` });
        } else {
            checks.push({ pass: false, text: 'Add meta title' });
        }
        
        if (metaDesc.length >= 120 && metaDesc.length <= 160) {
            score += 15;
            checks.push({ pass: true, text: `Meta description length: ${metaDesc.length}/160` });
        } else if (metaDesc.length > 0) {
            score += 5;
            checks.push({ pass: 'warn', text: `Meta description: ${metaDesc.length} chars (aim for 120-160)` });
        } else {
            checks.push({ pass: false, text: 'Add meta description' });
        }
        
        if (wordCount >= 300) {
            score += 20;
            checks.push({ pass: true, text: `Content length: ${wordCount} words` });
        } else if (wordCount >= 100) {
            score += 10;
            checks.push({ pass: 'warn', text: `Content: ${wordCount} words (aim for 300+)` });
        } else {
            checks.push({ pass: false, text: `Add more content (${wordCount} words)` });
        }
        
        const scoreCircle = document.getElementById('seo-score-circle');
        scoreCircle.textContent = score;
        scoreCircle.className = 'seo-score-circle';
        
        if (score >= 70) {
            scoreCircle.classList.add('good');
            document.getElementById('seo-score-hint').textContent = 'Great! Your content is well optimized';
        } else if (score >= 40) {
            scoreCircle.classList.add('ok');
            document.getElementById('seo-score-hint').textContent = 'Good, but could be improved';
        } else {
            scoreCircle.classList.add('poor');
            document.getElementById('seo-score-hint').textContent = 'Needs improvement';
        }
        
        document.getElementById('seo-checklist').innerHTML = checks.map(c => `
            <li>
                <span class="seo-check ${c.pass === true ? 'pass' : c.pass === 'warn' ? 'warn' : 'fail'}">
                    ${c.pass === true ? '✓' : c.pass === 'warn' ? '!' : '✗'}
                </span>
                ${c.text}
            </li>
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
    
    function closeMediaBrowser() {
        document.getElementById('media-modal').classList.remove('active');
    }
    
    document.querySelectorAll('.media-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            selectedMediaUrl = this.dataset.url;
            document.getElementById('select-media-btn').disabled = false;
        });
    });
    
    function selectMedia() {
        if (!selectedMediaUrl) return;
        
        if (mediaTarget === 'featured') {
            document.getElementById('featured-image-value').value = selectedMediaUrl;
            const box = document.getElementById('featured-image-box');
            box.innerHTML = `<img src="${selectedMediaUrl}" alt="Featured"><button type="button" class="remove-btn" onclick="event.stopPropagation(); removeFeaturedImage();">×</button>`;
            box.classList.add('has-image');
            // Show SEO fields
            document.getElementById('image-seo-fields').style.display = 'block';
        } else if (mediaTarget === 'editor') {
            tinymce.get('editor').insertContent(`<img src="${selectedMediaUrl}" alt="" style="max-width: 100%;">`);
        }
        
        closeMediaBrowser();
    }
    
    function removeFeaturedImage() {
        document.getElementById('featured-image-value').value = '';
        document.getElementById('featured-image-alt').value = '';
        document.getElementById('featured-image-title').value = '';
        const box = document.getElementById('featured-image-box');
        box.innerHTML = '<div class="placeholder"><div style="font-size: 2rem; margin-bottom: 0.5rem;">📷</div><div>Click to select image</div></div>';
        box.classList.remove('has-image');
        // Hide SEO fields
        document.getElementById('image-seo-fields').style.display = 'none';
    }
    
    // File upload
    const uploadArea = document.getElementById('upload-area');
    const uploadInput = document.getElementById('media-upload');
    
    uploadArea?.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea?.addEventListener('dragleave', () => { uploadArea.classList.remove('dragover'); });
    uploadArea?.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
    });
    uploadInput?.addEventListener('change', () => { if (uploadInput.files.length) uploadFile(uploadInput.files[0]); });
    
    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', CSRF_TOKEN);
        
        document.getElementById('upload-progress').style.display = 'block';
        
        fetch('/admin/api/article-image-upload.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            document.getElementById('upload-progress').style.display = 'none';
            const success = data.success || data.ok;
            const url = data.url || (data.file && data.file.url);
            const filename = data.filename || (data.file && data.file.name) || 'image';
            
            if (success && url) {
                const grid = document.getElementById('media-grid');
                const item = document.createElement('div');
                item.className = 'media-item selected';
                item.dataset.url = url;
                item.innerHTML = `<img src="${url}" alt=""><div class="filename">${filename}</div>`;
                item.addEventListener('click', function() {
                    document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
                    document.querySelectorAll('.stock-item').forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedMediaUrl = this.dataset.url;
                    document.getElementById('select-media-btn').disabled = false;
                });
                grid.insertBefore(item, grid.firstChild);
                selectedMediaUrl = url;
                document.getElementById('select-media-btn').disabled = false;
                // Switch to library tab
                document.querySelector('[data-media-tab="library"]').click();
                showToast('Image uploaded!', 'success');
            } else {
                showToast(data.error || 'Upload failed', 'error');
            }
        })
        .catch(() => {
            document.getElementById('upload-progress').style.display = 'none';
            showToast('Upload failed', 'error');
        });
    }
    
    // Media Tabs
    document.querySelectorAll('.media-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.media-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.media-tab-content').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('media-tab-' + this.dataset.mediaTab).classList.add('active');
        });
    });
    
    // Stock Photos (Pexels API via proxy - same as Media Library)
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
            const style = document.createElement('style');
            style.id = 'spinner-style';
            style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(style);
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
        const title = document.getElementById('article-title').value || '';
        
        const noContentRequired = ['generate_title', 'generate_faq', 'generate_cta', 'generate_testimonial'];
        if (!content.trim() && !title.trim() && !noContentRequired.includes(action)) {
            showToast('Add some content first', 'error');
            return;
        }

        // For block generation, include title
        let text = content.substring(0, 2000);
        if (['generate_faq', 'generate_cta', 'generate_testimonial'].includes(action)) {
            text = `Title: ${title}\n\nContent: ${content.substring(0, 1500)}`;
        }
        showToast('AI is working...', 'success');
        
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: action,
                    text: text
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAiResult(action, result.content);
            } else {
                showToast(result.error || 'AI request failed', 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    }
    
    async function aiTransformSelection(action) {
        const editor = tinymce.get('editor');
        const selection = editor.selection.getContent({ format: 'text' });
        
        if (!selection.trim()) {
            showToast('Select some text first', 'error');
            return;
        }
        
        showToast('AI is working...', 'success');
        
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: action,
                    text: selection
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAiResult(action, result.content, selection);
            } else {
                showToast(result.error || 'AI request failed', 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    }
    
    function showAiResult(action, content, originalSelection = null) {
        const container = document.getElementById('ai-result-container');
        const actionLabels = {
            'generate_title': '📝 Generated Titles',
            'generate_excerpt': '📄 Generated Excerpt',
            'generate_meta': '🎯 Meta Description',
            'generate_keywords': '🔑 Keywords',
            'generate_focus_keyword': '🎯 Focus Keyword',
            'generate_faq': '❓ Generated FAQ',
            'generate_cta': '📢 Generated CTA',
            'generate_testimonial': '💬 Generated Testimonial',
            'generate_image_alt': '🖼️ Image Alt Text',
            'generate_image_title': '🖼️ Image Title',
            'improve_content': '✨ Improved Text',
            'expand_content': '📝 Expanded Text',
            'simplify': '📖 Simplified Text',
            'translate_en': '🇬🇧 English Translation',
            'translate_pl': '🇵🇱 Polish Translation',
            'translate_de': '🇩🇪 German Translation',
            'fix_grammar': '✓ Fixed Text',
            'make_formal': '👔 Formal Version',
            'make_casual': '😊 Casual Version'
        };
        
        let applyBtn = '';
        if (action === 'generate_excerpt') {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('excerpt').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent; showToast('Applied!', 'success');">Apply to Excerpt</button>`;
        } else if (action === 'generate_meta') {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('meta-desc').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent.substring(0, 160); updateCharCount(document.getElementById('meta-desc'), 160); showToast('Applied!', 'success');">Apply</button>`;
        } else if (action === 'generate_keywords') {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('meta-keywords').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent; showToast('Applied!', 'success');">Apply</button>`;
        } else if (action === 'generate_focus_keyword') {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('focus-keyword').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent.trim(); showToast('Applied!', 'success');">Apply</button>`;
        } else if (action === 'generate_image_alt') {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('featured-image-alt').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent.trim(); showToast('Applied!', 'success');">Apply</button>`;
        } else if (action === 'generate_image_title') {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('featured-image-title').value = this.closest('.ai-result').querySelector('.ai-result-content').textContent.trim(); showToast('Applied!', 'success');">Apply</button>`;
        } else if (action === 'generate_faq') {
            // Parse FAQ and fill modal fields
            parseFaqResponse(content);
            return;
        } else if (action === 'generate_cta') {
            // Parse CTA and fill modal fields
            parseCtaResponse(content);
            return;
        } else if (action === 'generate_testimonial') {
            // Parse testimonial and fill modal fields
            parseTestimonialResponse(content);
            return;
        } else if (originalSelection && ['improve_content', 'expand_content', 'simplify', 'fix_grammar', 'make_formal', 'make_casual', 'translate_en', 'translate_pl', 'translate_de'].includes(action)) {
            applyBtn = `<button type="button" class="btn btn-sm btn-primary" onclick="tinymce.get('editor').selection.setContent(this.closest('.ai-result').querySelector('.ai-result-content').textContent); showToast('Replaced!', 'success');">Replace Selection</button>`;
        }
        
        container.innerHTML = `
            <div class="ai-result">
                <div class="ai-result-header">
                    <div class="ai-result-title">${actionLabels[action] || 'AI Result'}</div>
                    <button type="button" style="background: none; border: none; cursor: pointer;" onclick="this.closest('.ai-result').remove()">×</button>
                </div>
                <div class="ai-result-content">${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                <div class="ai-result-actions">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="navigator.clipboard.writeText(this.closest('.ai-result').querySelector('.ai-result-content').textContent); showToast('Copied!', 'success');">📋 Copy</button>
                    ${applyBtn}
                </div>
            </div>
        `;
        
        // Switch to AI tab
        document.querySelector('[data-tab="ai"]').click();
    }
    
    // AI Image SEO
    async function aiGenerateImageAlt() {
        const title = document.getElementById('article-title').value;
        if (!title) {
            showToast('Add article title first', 'error');
            return;
        }
        showToast('Generating alt text...', 'success');
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: 'generate_image_alt',
                    text: title
                })
            });
            const data = await response.json();
            if (data.success && data.content) {
                document.getElementById('featured-image-alt').value = data.content.trim();
                showToast('Alt text generated!', 'success');
            } else {
                showToast(data.error || 'Generation failed', 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    }
    
    async function aiGenerateImageTitle() {
        const title = document.getElementById('article-title').value;
        if (!title) {
            showToast('Add article title first', 'error');
            return;
        }
        showToast('Generating image title...', 'success');
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: 'generate_image_title',
                    text: title
                })
            });
            const data = await response.json();
            if (data.success && data.content) {
                document.getElementById('featured-image-title').value = data.content.trim();
                showToast('Image title generated!', 'success');
            } else {
                showToast(data.error || 'Generation failed', 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    }
    
    function showToast(message, type = 'success') {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    // Init
    updateCharCount(document.getElementById('meta-title'), 60);
    updateCharCount(document.getElementById('meta-desc'), 160);
    
    // Preview functionality
    async function openPreview() {
        const title = document.getElementById('article-title').value || 'Untitled Article';
        const body = tinymce.get('editor') ? tinymce.get('editor').getContent() : '';
        const featuredImage = document.getElementById('featured-image-value').value;
        const category = document.querySelector('select[name="category_id"] option:checked')?.textContent || '';
        
        // For existing articles - use central preview system
        if (ARTICLE_ID > 0) {
            try {
                // Save to session via AJAX
                const response = await fetch('/admin/articles/preview', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        article_id: ARTICLE_ID,
                        title: title,
                        content: body,
                        featured_image: featuredImage,
                        category_name: category !== '— No Category —' ? category : ''
                    })
                });
                
                const result = await response.json();
                if (result.success) {
                    window.open('/preview/article/' + ARTICLE_ID + '?session=1', '_blank');
                } else {
                    alert('Preview error: ' + (result.error || 'Unknown error'));
                }
            } catch (err) {
                alert('Preview error: ' + err.message);
            }
        } else {
            // For new articles - use modal preview
            const modal = document.getElementById('preview-modal');
            const content = document.getElementById('preview-content');
            const date = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            let html = `<h1>${escapeHtml(title)}</h1>`;
            html += `<div class="meta">`;
            if (category && category !== '— No Category —') html += `<span>📁 ${escapeHtml(category)}</span> · `;
            html += `<span>📅 ${date}</span>`;
            html += `</div>`;
            if (featuredImage) html += `<img src="${featuredImage}" alt="${escapeHtml(title)}">`;
            html += body;
            
            content.innerHTML = html;
            modal.classList.add('active');
        }
    }
    
    function closePreview() {
        document.getElementById('preview-modal').classList.remove('active');
    }
    
    function setPreviewSize(size) {
        const frame = document.getElementById('preview-frame');
        frame.className = 'preview-frame ' + size;
        document.querySelectorAll('.preview-tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Full AI SEO Analysis
    async function runFullSeoAnalysis() {
        let focusKeyword = document.getElementById('focus-keyword').value.trim();
        
        // Fallback: use first meta keyword if focus keyword is empty
        if (!focusKeyword) {
            const metaKeywords = document.getElementById('meta-keywords').value.trim();
            if (metaKeywords) {
                focusKeyword = metaKeywords.split(',')[0].trim();
                document.getElementById('focus-keyword').value = focusKeyword;
                showToast('Using first keyword as focus keyword', 'success');
            }
        }
        
        if (!focusKeyword) {
            showToast('Please enter a focus keyword or meta keywords first', 'error');
            return;
        }
        
        const editor = tinymce.get('editor');
        const content = editor ? editor.getContent() : '';
        if (!content.trim()) {
            showToast('Please add some content first', 'error');
            return;
        }
        
        const resultsContainer = document.getElementById('ai-seo-results');
        resultsContainer.style.display = 'block';
        resultsContainer.innerHTML = `
            <div class="seo-loading">
                <div class="seo-loading-spinner"></div>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Analyzing SEO...</p>
                <p style="font-size: 0.625rem; color: var(--text-muted);">This may take 30-60 seconds</p>
            </div>
        `;
        
        // Switch to SEO tab
        document.querySelector('[data-tab="seo"]').click();
        
        try {
            const response = await fetch('/admin/api/ai-seo-analyze.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    csrf_token: CSRF_TOKEN,
                    focus_keyword: focusKeyword,
                    secondary_keywords: document.getElementById('meta-keywords')?.value || '',
                    title: document.getElementById('article-title')?.value || '',
                    content_html: content,
                    content_type: 'blog_post',
                    language: 'en'
                })
            });
            
            const data = await response.json();
            
            if (!data.ok) {
                resultsContainer.innerHTML = `<div class="alert" style="background: rgba(243,139,168,0.1); border: 1px solid rgba(243,139,168,0.3); color: #f38ba8; padding: 0.75rem; border-radius: 6px; font-size: 0.75rem;">❌ ${escapeHtml(data.error)}</div>`;
                return;
            }
            
            const report = data.report;
            const score = report.health_score || 0;
            const scoreClass = score >= 80 ? 'good' : (score >= 60 ? 'ok' : 'poor');
            
            let html = '';
            
            // Score
            html += `<div class="seo-analysis-score">
                <div class="seo-analysis-circle ${scoreClass}">${score}</div>
                <div>
                    <div style="font-weight: 600; font-size: 0.8125rem;">SEO Score</div>
                    <div style="font-size: 0.6875rem; color: var(--text-muted);">${escapeHtml(report.summary || '')}</div>
                </div>
            </div>`;
            
            // Score Breakdown
            const breakdown = report.content_score_breakdown || {};
            if (Object.keys(breakdown).length > 0) {
                html += `<div class="seo-section"><div class="seo-section-title">📊 Score Breakdown</div><div class="seo-breakdown">`;
                const icons = { word_count: '📝', headings: '📑', keywords: '🎯', structure: '🏗️', media: '🖼️', links: '🔗' };
                for (const [key, val] of Object.entries(breakdown)) {
                    if (!val || typeof val !== 'object') continue;
                    const s = val.score || 0;
                    const cls = s >= 70 ? 'good' : (s >= 50 ? 'ok' : 'poor');
                    const icon = icons[key] || '📌';
                    html += `<div class="seo-breakdown-item">
                        <div class="seo-breakdown-label"><span>${icon} ${key.replace('_', ' ')}</span><span>${s}/100</span></div>
                        <div class="seo-breakdown-bar"><div class="seo-breakdown-fill ${cls}" style="width:${s}%"></div></div>
                    </div>`;
                }
                html += `</div></div>`;
            }
            
            // Keywords
            const keywords = report.keyword_difficulty || [];
            if (keywords.length > 0) {
                const contentLower = content.toLowerCase();
                html += `<div class="seo-section"><div class="seo-section-title">🎯 Keywords</div><div class="seo-keywords-grid">`;
                for (const kw of keywords) {
                    const kwText = kw.keyword || '';
                    const count = (contentLower.match(new RegExp(kwText.toLowerCase(), 'g')) || []).length;
                    const cls = count > 0 ? 'used' : 'missing';
                    html += `<div class="seo-keyword-pill ${cls}"><span>${escapeHtml(kwText)}</span><span class="seo-keyword-count">${count}×</span></div>`;
                }
                html += `</div></div>`;
            }
            
            // Quick Wins
            const quickWins = report.quick_wins || [];
            if (quickWins.length > 0) {
                html += `<div class="seo-section"><div class="seo-section-title">⚡ Quick Wins</div><ul class="seo-tasks">`;
                for (const win of quickWins.slice(0, 5)) {
                    html += `<li><span style="color: var(--success);">💡</span><span>${escapeHtml(win)}</span></li>`;
                }
                html += `</ul></div>`;
            }
            
            // Tasks
            const tasks = report.actionable_tasks || [];
            if (tasks.length > 0) {
                html += `<div class="seo-section"><div class="seo-section-title">✅ Tasks (${tasks.length})</div><ul class="seo-tasks">`;
                for (const task of tasks.slice(0, 5)) {
                    const p = task.priority || 'medium';
                    html += `<li><span class="seo-task-priority ${p}">${p}</span><span>${escapeHtml(task.task || '')}</span></li>`;
                }
                if (tasks.length > 5) {
                    html += `<li style="color: var(--text-muted); justify-content: center;">+${tasks.length - 5} more tasks</li>`;
                }
                html += `</ul></div>`;
            }
            
            // Meta suggestions
            const meta = report.on_page_checks?.meta_suggestions || {};
            if (meta.recommended_title || meta.recommended_meta_description) {
                html += `<div class="seo-section"><div class="seo-section-title">💡 Suggested Meta</div>`;
                if (meta.recommended_title) {
                    html += `<div style="margin-bottom: 0.5rem;"><label style="font-size: 0.625rem; color: var(--text-muted);">Title</label><div style="font-size: 0.75rem; padding: 0.5rem; background: var(--sidebar-bg); border-radius: 4px; cursor: pointer;" onclick="document.getElementById('meta-title').value=this.textContent;updateCharCount(document.getElementById('meta-title'),60);showToast('Applied!','success');">${escapeHtml(meta.recommended_title)}</div></div>`;
                }
                if (meta.recommended_meta_description) {
                    html += `<div><label style="font-size: 0.625rem; color: var(--text-muted);">Description (click to apply)</label><div style="font-size: 0.75rem; padding: 0.5rem; background: var(--sidebar-bg); border-radius: 4px; cursor: pointer;" onclick="document.getElementById('meta-desc').value=this.textContent;updateCharCount(document.getElementById('meta-desc'),160);showToast('Applied!','success');">${escapeHtml(meta.recommended_meta_description)}</div></div>`;
                }
                html += `</div>`;
            }
            
            resultsContainer.innerHTML = html;
            showToast('SEO analysis complete!', 'success');
            
        } catch (e) {
            resultsContainer.innerHTML = `<div class="alert" style="background: rgba(243,139,168,0.1); border: 1px solid rgba(243,139,168,0.3); color: #f38ba8; padding: 0.75rem; border-radius: 6px; font-size: 0.75rem;">❌ Network error: ${escapeHtml(e.message)}</div>`;
        }
    }
    
    // ============ PARSE AI RESPONSES FOR MODALS ============
    
    function parseFaqResponse(content) {
        const lines = content.split('\n').filter(l => l.trim());
        const container = document.getElementById('faq-items');
        container.innerHTML = '';
        let faqCount = 0;
        let currentQ = '';
        
        lines.forEach(line => {
            if (line.match(/^Q\d*[:\.]|^\d+\.|^Question/i)) {
                currentQ = line.replace(/^Q\d*[:\.]|^\d+\.|^Question[:\s]*/i, '').trim();
            } else if ((line.match(/^A[:\.]|^Answer/i) || !line.match(/^Q|^\d+\./)) && currentQ) {
                const answer = line.replace(/^A[:\.]|^Answer[:\s]*/i, '').trim();
                if (answer) {
                    faqCount++;
                    const newItem = document.createElement('div');
                    newItem.className = 'faq-edit-item';
                    newItem.dataset.index = faqCount - 1;
                    newItem.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin: 0;">Question ${faqCount}</label>
                            <button type="button" class="btn btn-sm" style="background: var(--danger); padding: 0.25rem 0.5rem;" onclick="removeFaqItem(${faqCount - 1})">×</button>
                        </div>
                        <input type="text" class="form-input faq-question" value="${escapeHtml(currentQ)}" style="margin-bottom: 0.5rem;">
                        <textarea class="form-textarea faq-answer" rows="2">${escapeHtml(answer)}</textarea>
                    `;
                    container.appendChild(newItem);
                    currentQ = '';
                }
            }
        });
        
        if (faqCount === 0) {
            addFaqItem();
        }
        showToast('FAQ generated! Edit and click Insert.', 'success');
    }
    
    function parseCtaResponse(content) {
        const lines = content.split('\n').filter(l => l.trim());
        
        lines.forEach(line => {
            if (line.match(/^Headline[:\s]/i)) {
                document.getElementById('cta-headline').value = line.replace(/^Headline[:\s]*/i, '').trim();
            } else if (line.match(/^Description[:\s]/i)) {
                document.getElementById('cta-description').value = line.replace(/^Description[:\s]*/i, '').trim();
            } else if (line.match(/^Button[:\s]/i)) {
                document.getElementById('cta-button-text').value = line.replace(/^Button[:\s]*/i, '').trim();
            }
        });
        
        showToast('CTA generated! Edit and click Insert.', 'success');
    }
    
    function parseTestimonialResponse(content) {
        const lines = content.split('\n').filter(l => l.trim());
        
        lines.forEach(line => {
            if (line.match(/^Quote[:\s]/i)) {
                document.getElementById('testimonial-quote').value = line.replace(/^Quote[:\s]*/i, '').replace(/^"|"$/g, '').trim();
            } else if (line.match(/^Name[:\s]/i)) {
                document.getElementById('testimonial-author').value = line.replace(/^Name[:\s]*/i, '').trim();
            } else if (line.match(/^Title[:\s]|^Position[:\s]/i)) {
                document.getElementById('testimonial-title').value = line.replace(/^Title[:\s]*|^Position[:\s]*/i, '').trim();
            }
        });
        
        showToast('Testimonial generated! Edit and click Insert.', 'success');
    }
    
    // ============ BLOCK MODALS ============
    
    // FAQ Modal
    let faqItemCount = 1;
    
    function openFaqModal() {
        faqItemCount = 1;
        document.getElementById('faq-items').innerHTML = `
            <div class="faq-edit-item" data-index="0">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label class="form-label" style="margin: 0;">Question 1</label>
                    <button type="button" class="btn btn-sm" style="background: var(--danger); padding: 0.25rem 0.5rem;" onclick="removeFaqItem(0)">×</button>
                </div>
                <input type="text" class="form-input faq-question" placeholder="Enter your question..." style="margin-bottom: 0.5rem;">
                <textarea class="form-textarea faq-answer" placeholder="Enter your answer..." rows="2"></textarea>
            </div>
        `;
        document.getElementById('faq-modal').classList.add('active');
    }
    
    function closeFaqModal() {
        document.getElementById('faq-modal').classList.remove('active');
    }
    
    function addFaqItem() {
        faqItemCount++;
        const container = document.getElementById('faq-items');
        const newItem = document.createElement('div');
        newItem.className = 'faq-edit-item';
        newItem.dataset.index = faqItemCount - 1;
        newItem.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label class="form-label" style="margin: 0;">Question ${faqItemCount}</label>
                <button type="button" class="btn btn-sm" style="background: var(--danger); padding: 0.25rem 0.5rem;" onclick="removeFaqItem(${faqItemCount - 1})">×</button>
            </div>
            <input type="text" class="form-input faq-question" placeholder="Enter your question..." style="margin-bottom: 0.5rem;">
            <textarea class="form-textarea faq-answer" placeholder="Enter your answer..." rows="2"></textarea>
        `;
        container.appendChild(newItem);
    }
    
    function removeFaqItem(index) {
        const items = document.querySelectorAll('.faq-edit-item');
        if (items.length > 1) {
            items.forEach(item => {
                if (parseInt(item.dataset.index) === index) {
                    item.remove();
                }
            });
        }
    }
    
    function insertFaqBlock() {
        const items = document.querySelectorAll('.faq-edit-item');
        let faqHtml = `<div class="faq-section" style="background: #f8f9fa; border-radius: 8px; padding: 24px; margin: 24px 0;">
<h3 style="margin-top: 0; color: #1a1a2e;">❓ Frequently Asked Questions</h3>`;
        
        items.forEach((item, idx) => {
            const question = item.querySelector('.faq-question').value.trim() || `Question ${idx + 1}`;
            const answer = item.querySelector('.faq-answer').value.trim() || 'Answer...';
            const isLast = idx === items.length - 1;
            faqHtml += `
<div class="faq-item" style="margin-bottom: ${isLast ? '0' : '16px'}; padding-bottom: ${isLast ? '0' : '16px'}; border-bottom: ${isLast ? 'none' : '1px solid #e0e0e0'};">
<h4 style="margin: 0 0 8px 0; color: #333;">${escapeHtml(question)}</h4>
<p style="margin: 0; color: #666;">${escapeHtml(answer)}</p>
</div>`;
        });
        
        faqHtml += `</div>`;
        
        tinymce.get('editor').insertContent(faqHtml);
        closeFaqModal();
        showToast('FAQ block inserted!', 'success');
    }
    
    async function aiGenerateFaq() {
        const title = document.getElementById('article-title').value;
        const content = tinymce.get('editor').getContent({format: 'text'}).substring(0, 2000);
        
        if (!title && !content) {
            showToast('Add article title/content first', 'error');
            return;
        }
        
        showToast('Generating FAQ...', 'success');
        
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: 'generate_faq',
                    text: `Title: ${title}\n\nContent: ${content}`
                })
            });
            
            const data = await response.json();
            if (data.success && data.content) {
                // Parse FAQ from AI response
                const lines = data.content.split('\n').filter(l => l.trim());
                const container = document.getElementById('faq-items');
                container.innerHTML = '';
                faqItemCount = 0;
                
                let currentQ = '';
                lines.forEach(line => {
                    if (line.match(/^Q\d*[:\.]|^\d+\.|^Question/i)) {
                        currentQ = line.replace(/^Q\d*[:\.]|^\d+\.|^Question[:\s]*/i, '').trim();
                    } else if (line.match(/^A[:\.]|^Answer/i) && currentQ) {
                        const answer = line.replace(/^A[:\.]|^Answer[:\s]*/i, '').trim();
                        faqItemCount++;
                        const newItem = document.createElement('div');
                        newItem.className = 'faq-edit-item';
                        newItem.dataset.index = faqItemCount - 1;
                        newItem.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="margin: 0;">Question ${faqItemCount}</label>
                                <button type="button" class="btn btn-sm" style="background: var(--danger); padding: 0.25rem 0.5rem;" onclick="removeFaqItem(${faqItemCount - 1})">×</button>
                            </div>
                            <input type="text" class="form-input faq-question" value="${escapeHtml(currentQ)}" style="margin-bottom: 0.5rem;">
                            <textarea class="form-textarea faq-answer" rows="2">${escapeHtml(answer)}</textarea>
                        `;
                        container.appendChild(newItem);
                        currentQ = '';
                    }
                });
                
                if (faqItemCount === 0) {
                    // Fallback - just add one item with raw content
                    addFaqItem();
                }
                
                showToast('FAQ generated! Edit as needed.', 'success');
            } else {
                showToast(data.error || 'Generation failed', 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    }
    
    // CTA Modal
    function openCtaModal() {
        document.getElementById('cta-headline').value = 'Ready to Get Started?';
        document.getElementById('cta-description').value = 'Take the next step and transform your business today.';
        document.getElementById('cta-button-text').value = 'Get Started Now →';
        document.getElementById('cta-button-link').value = '#';
        document.getElementById('cta-modal').classList.add('active');
    }
    
    function closeCtaModal() {
        document.getElementById('cta-modal').classList.remove('active');
    }
    
    function insertCtaBlock() {
        const headline = document.getElementById('cta-headline').value.trim() || 'Ready to Get Started?';
        const desc = document.getElementById('cta-description').value.trim() || '';
        const btnText = document.getElementById('cta-button-text').value.trim() || 'Learn More';
        const btnLink = document.getElementById('cta-button-link').value.trim() || '#';
        
        const html = `
<div class="cta-block" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 32px; margin: 24px 0; text-align: center;">
<h3 style="margin: 0 0 12px 0; color: white; font-size: 24px;">${escapeHtml(headline)}</h3>
<p style="margin: 0 0 20px 0; color: rgba(255,255,255,0.9); font-size: 16px;">${escapeHtml(desc)}</p>
<a href="${escapeHtml(btnLink)}" style="display: inline-block; background: white; color: #667eea; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: 600;">${escapeHtml(btnText)}</a>
</div>`;
        
        tinymce.get('editor').insertContent(html);
        closeCtaModal();
        showToast('CTA block inserted!', 'success');
    }
    
    async function aiGenerateCta() {
        const title = document.getElementById('article-title').value;
        const content = tinymce.get('editor').getContent({format: 'text'}).substring(0, 1000);
        
        if (!title && !content) {
            showToast('Add article title/content first', 'error');
            return;
        }
        
        showToast('Generating CTA...', 'success');
        
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: 'generate_cta',
                    text: `Title: ${title}\n\nContent: ${content}`
                })
            });
            
            const data = await response.json();
            
            if (data.success && data.content) {
                const lines = data.content.split('\n').filter(l => l.trim());
                
                let headline = '', description = '', button = '';
                lines.forEach(line => {
                    if (line.match(/^Headline[:\s]/i)) {
                        headline = line.replace(/^Headline[:\s]*/i, '').trim();
                    } else if (line.match(/^Description[:\s]/i)) {
                        description = line.replace(/^Description[:\s]*/i, '').trim();
                    } else if (line.match(/^Button[:\s]/i)) {
                        button = line.replace(/^Button[:\s]*/i, '').trim();
                    }
                });
                
                if (!headline && lines[0]) headline = lines[0];
                if (!description && lines[1]) description = lines[1];
                if (!button && lines[2]) button = lines[2];
                
                if (headline) document.getElementById('cta-headline').value = headline;
                if (description) document.getElementById('cta-description').value = description;
                if (button) document.getElementById('cta-button-text').value = button;
                
                showToast('CTA generated!', 'success');
            } else {
                showToast(data.error || 'Generation failed', 'error');
            }
        } catch (e) {
            showToast('Network error: ' + e.message, 'error');
        }
    }
    
    // Callout Modal
    function openCalloutModal() {
        document.getElementById('callout-type').value = 'info';
        document.getElementById('callout-title').value = 'Pro Tip:';
        document.getElementById('callout-content').value = 'Add your helpful tip or important information here.';
        document.getElementById('callout-modal').classList.add('active');
    }
    
    function closeCalloutModal() {
        document.getElementById('callout-modal').classList.remove('active');
    }
    
    function insertCalloutBlock() {
        const type = document.getElementById('callout-type').value;
        const title = document.getElementById('callout-title').value.trim();
        const content = document.getElementById('callout-content').value.trim();
        
        const styles = {
            info: { bg: '#e7f3ff', border: '#2196F3', titleColor: '#1565C0', textColor: '#1976D2', icon: '💡' },
            warning: { bg: '#fff8e1', border: '#ff9800', titleColor: '#e65100', textColor: '#f57c00', icon: '⚠️' },
            success: { bg: '#e8f5e9', border: '#4caf50', titleColor: '#2e7d32', textColor: '#388e3c', icon: '✅' },
            error: { bg: '#ffebee', border: '#f44336', titleColor: '#c62828', textColor: '#d32f2f', icon: '❌' }
        };
        
        const s = styles[type];
        const html = `
<div class="callout callout-${type}" style="background: ${s.bg}; border-left: 4px solid ${s.border}; border-radius: 4px; padding: 16px 20px; margin: 20px 0;">
<strong style="color: ${s.titleColor};">${s.icon} ${escapeHtml(title)}</strong>
<p style="margin: 8px 0 0 0; color: ${s.textColor};">${escapeHtml(content)}</p>
</div>`;
        
        tinymce.get('editor').insertContent(html);
        closeCalloutModal();
        showToast('Callout inserted!', 'success');
    }
    
    // Testimonial Modal
    function openTestimonialModal() {
        document.getElementById('testimonial-quote').value = 'This is an amazing product. It exceeded all my expectations!';
        document.getElementById('testimonial-author').value = 'John Doe';
        document.getElementById('testimonial-title').value = 'CEO at Company';
        document.getElementById('testimonial-modal').classList.add('active');
    }
    
    function closeTestimonialModal() {
        document.getElementById('testimonial-modal').classList.remove('active');
    }
    
    function insertTestimonialBlock() {
        const quote = document.getElementById('testimonial-quote').value.trim();
        const author = document.getElementById('testimonial-author').value.trim();
        const title = document.getElementById('testimonial-title').value.trim();
        const initials = author.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        
        const html = `
<div class="testimonial" style="background: #fafafa; border-radius: 12px; padding: 24px; margin: 24px 0; border-left: 4px solid #667eea;">
<p style="font-size: 18px; font-style: italic; color: #444; margin: 0 0 16px 0; line-height: 1.6;">"${escapeHtml(quote)}"</p>
<div style="display: flex; align-items: center; gap: 12px;">
<div style="width: 48px; height: 48px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">${initials}</div>
<div>
<div style="font-weight: 600; color: #333;">${escapeHtml(author)}</div>
<div style="font-size: 14px; color: #666;">${escapeHtml(title)}</div>
</div>
</div>
</div>`;
        
        tinymce.get('editor').insertContent(html);
        closeTestimonialModal();
        showToast('Testimonial inserted!', 'success');
    }
    
    async function aiGenerateTestimonial() {
        const title = document.getElementById('article-title').value;
        
        showToast('Generating testimonial...', 'success');
        
        try {
            const response = await fetch(AI_ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    csrf_token: CSRF_TOKEN,
                    action: 'generate_testimonial',
                    text: `Generate a realistic testimonial for an article about: ${title}`
                })
            });
            
            const data = await response.json();
            if (data.success && data.content) {
                const lines = data.content.split('\n').filter(l => l.trim());
                lines.forEach(line => {
                    if (line.match(/^Quote[:\s]/i)) document.getElementById('testimonial-quote').value = line.replace(/^Quote[:\s]*/i, '').replace(/^"|"$/g, '');
                    else if (line.match(/^Name[:\s]/i)) document.getElementById('testimonial-author').value = line.replace(/^Name[:\s]*/i, '');
                    else if (line.match(/^Title[:\s]|^Position[:\s]/i)) document.getElementById('testimonial-title').value = line.replace(/^Title[:\s]*|^Position[:\s]*/i, '');
                });
                showToast('Testimonial generated!', 'success');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    }
    
    // ============ END BLOCK MODALS ============
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+S / Cmd+S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.querySelector('button[value="draft"]').click();
        }
        // Escape to close preview
        if (e.key === 'Escape') {
            closePreview();
        }
    });
    </script>

<!-- Preview Modal -->
<div class="preview-modal" id="preview-modal">
    <div class="preview-header">
        <h3>👀 Article Preview</h3>
        <div class="preview-tabs">
            <button type="button" class="preview-tab active" onclick="setPreviewSize('desktop')">🖥️ Desktop</button>
            <button type="button" class="preview-tab" onclick="setPreviewSize('tablet')">📱 Tablet</button>
            <button type="button" class="preview-tab" onclick="setPreviewSize('mobile')">📱 Mobile</button>
        </div>
        <button type="button" class="btn btn-secondary" onclick="closePreview()">✕ Close</button>
    </div>
    <div class="preview-frame" id="preview-frame">
        <div class="preview-content" id="preview-content"></div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';