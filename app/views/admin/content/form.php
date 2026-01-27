<?php
/**
 * Content Block Form (Create/Edit) - Professional Design
 */
$title = $block ? 'Edit Content Block' : 'New Content Block';
$isEdit = $block !== null;
ob_start();

$typeIcons = [
    'html' => '</>',
    'text' => 'Aa',
    'json' => '{}',
    'markdown' => 'MD',
    'shortcode' => '[]'
];

$categoryIcons = [
    'header' => '🏠',
    'footer' => '📋',
    'sidebar' => '📁',
    'global' => '🌍',
    'uncategorized' => '📦'
];
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/content" class="back-link">← Back to Content Blocks</a>
        <h1><?= $isEdit ? 'Edit Content Block' : 'Create New Content Block' ?></h1>
        <p class="page-subtitle"><?= $isEdit ? 'Update block content and settings' : 'Create a reusable content block for your templates' ?></p>
    </div>
</div>

<?php
$flashSuccess = \Core\Session::getFlash('success');
$flashError = \Core\Session::getFlash('error');
?>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= esc($flashSuccess) ?></div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-error"><?= esc($flashError) ?></div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? '/admin/content/' . (int)$block['id'] : '/admin/content' ?>" class="content-form">
    <?= csrf_field() ?>

    <div class="form-layout">
        <!-- Main Settings Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📦 Basic Information</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Block Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required
                               value="<?= esc($block['name'] ?? '') ?>"
                               placeholder="e.g. Hero Banner, Footer Contact"
                               class="form-control">
                        <p class="form-hint">Choose a descriptive name for this content block</p>
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <div class="input-with-prefix">
                            <span class="input-prefix">block/</span>
                            <input type="text" id="slug" name="slug"
                                   value="<?= esc($block['slug'] ?? '') ?>"
                                   placeholder="hero-banner"
                                   class="form-control">
                        </div>
                        <p class="form-hint">
                            Use in templates: <code>&lt;?= get_content('<span id="slugPreview"><?= esc($block['slug'] ?? 'slug') ?></span>') ?&gt;</code>
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description"
                           value="<?= esc($block['description'] ?? '') ?>"
                           placeholder="Brief description of this content block..."
                           class="form-control">
                </div>
            </div>
        </div>

        <!-- Type & Category Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🏷️ Type & Category</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Content Type</label>
                        <select id="type" name="type" class="form-control">
                            <?php foreach ($types as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= ($block['type'] ?? 'html') === $key ? 'selected' : '' ?>>
                                    <?= $typeIcons[$key] ?? '</>' ?> <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Determines how content is rendered and displayed</p>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control">
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= ($block['category'] ?? 'uncategorized') === $key ? 'selected' : '' ?>>
                                    <?= $categoryIcons[$key] ?? '📦' ?> <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Helps organize blocks by where they appear</p>
                    </div>
                </div>

                <!-- Type Cards Selection (Visual) -->
                <div class="type-cards">
                    <p class="section-label">Quick select type:</p>
                    <div class="type-grid">
                        <?php foreach ($types as $key => $label): ?>
                            <button type="button" class="type-card <?= ($block['type'] ?? 'html') === $key ? 'selected' : '' ?>" data-type="<?= $key ?>">
                                <span class="type-icon"><?= $typeIcons[$key] ?? '</>' ?></span>
                                <span class="type-label"><?= esc($label) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📝 Content</h2>
                <div class="card-header-actions">
                    <button type="button" class="btn btn-ai btn-sm" onclick="openAiModal()">🤖 AI Generate</button>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="expandEditor()">⤢ Expand</button>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="content">Block Content</label>
                    <div class="editor-toolbar">
                        <span class="editor-mode" id="editorMode"><?= strtoupper($block['type'] ?? 'html') ?></span>
                    </div>
                    <textarea id="content" name="content" rows="18"
                              placeholder="Enter your content here..."
                              class="form-control code-editor"><?= esc($block['content'] ?? '') ?></textarea>
                    <p class="form-hint" id="contentHint">
                        <?php
                        $typeHints = [
                            'html' => 'HTML code will be rendered as-is. Use proper HTML structure.',
                            'text' => 'Plain text will be displayed with line breaks preserved.',
                            'json' => 'Enter valid JSON data. Will be parsed and made available as array.',
                            'markdown' => 'Supports basic Markdown: **bold**, *italic*, `code`, etc.',
                            'shortcode' => 'Enter shortcode(s) that will be processed by the template engine.'
                        ];
                        echo $typeHints[$block['type'] ?? 'html'];
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Advanced Settings Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚙️ Advanced Settings</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="cache_ttl">Cache TTL (seconds)</label>
                        <input type="number" id="cache_ttl" name="cache_ttl" min="0"
                               value="<?= (int)($block['cache_ttl'] ?? 0) ?>"
                               placeholder="0"
                               class="form-control">
                        <p class="form-hint">Time to cache in seconds. 0 = no caching. 3600 = 1 hour.</p>
                    </div>

                    <div class="form-group">
                        <label>Block Status</label>
                        <input type="hidden" name="is_active" value="0">
                        <label class="switch-toggle">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= ($block['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                            <span class="switch-text"></span>
                        </label>
                        <p class="form-hint">Inactive blocks won't be rendered in templates</p>
                    </div>
                </div>

                <?php if ($isEdit && isset($block['version'])): ?>
                <div class="meta-info">
                    <span class="meta-item">Version: <strong>v<?= (int)$block['version'] ?></strong></span>
                    <?php if (!empty($block['created_at'])): ?>
                        <span class="meta-item">Created: <strong><?= date('M j, Y', strtotime($block['created_at'])) ?></strong></span>
                    <?php endif; ?>
                    <?php if (!empty($block['updated_at'])): ?>
                        <span class="meta-item">Updated: <strong><?= date('M j, Y g:i A', strtotime($block['updated_at'])) ?></strong></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions-bar">
        <a href="/admin/content" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <?= $isEdit ? '💾 Save Changes' : '✨ Create Block' ?>
        </button>
    </div>
</form>

<!-- Expanded Editor Modal -->
<div id="editorModal" class="modal" style="display: none;">
    <div class="modal-content modal-fullscreen">
        <div class="modal-header">
            <h3>Edit Content</h3>
            <button type="button" class="modal-close" onclick="closeExpandedEditor()">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <textarea id="expandedContent" class="form-control code-editor expanded-editor"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeExpandedEditor()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="applyExpandedContent()">Apply</button>
        </div>
    </div>
</div>

<!-- AI Content Generator Modal -->
<div id="aiModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>🤖 AI Content Generator</h3>
            <button type="button" class="modal-close" onclick="closeAiModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="aiContentType">Content Type</label>
                <select id="aiContentType" class="form-control">
                    <!-- Options populated dynamically by JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="aiPrompt">Describe what you need</label>
                <textarea id="aiPrompt" class="form-control" rows="4"></textarea>
            </div>
            <!-- HTML Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsHtml">
                <span class="ai-chip" data-prompt="Hero banner with gradient background, heading, subtitle and CTA button" data-type="html_banner">Banner</span>
                <span class="ai-chip" data-prompt="Feature cards grid with icons and descriptions" data-type="html_card">Features</span>
                <span class="ai-chip" data-prompt="Contact information block with address, phone, email" data-type="html_contact">Contact</span>
                <span class="ai-chip" data-prompt="Footer with links, social icons and copyright" data-type="html">Footer</span>
                <span class="ai-chip" data-prompt="Pricing table with 3 plans" data-type="html">Pricing</span>
            </div>
            <!-- Text Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsText" style="display: none;">
                <span class="ai-chip" data-prompt="Welcome message for homepage" data-type="paragraph">Welcome</span>
                <span class="ai-chip" data-prompt="About us company description" data-type="paragraph">About Us</span>
                <span class="ai-chip" data-prompt="Call to action encouraging contact" data-type="cta">CTA</span>
                <span class="ai-chip" data-prompt="Mission statement for company" data-type="paragraph">Mission</span>
                <span class="ai-chip" data-prompt="Privacy policy summary" data-type="paragraph">Privacy</span>
            </div>
            <!-- JSON Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsJson" style="display: none;">
                <span class="ai-chip" data-prompt="Navigation menu structure with Home, About, Services, Contact" data-type="code_json">Menu</span>
                <span class="ai-chip" data-prompt="Social media links configuration for Facebook, Twitter, Instagram, LinkedIn" data-type="code_json">Social Links</span>
                <span class="ai-chip" data-prompt="Slider settings with autoplay, duration, transition" data-type="code_json">Slider Config</span>
                <span class="ai-chip" data-prompt="Contact form fields configuration" data-type="code_json">Form Fields</span>
                <span class="ai-chip" data-prompt="FAQ items with questions and answers" data-type="code_json">FAQ Data</span>
            </div>
            <!-- Markdown Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsMarkdown" style="display: none;">
                <span class="ai-chip" data-prompt="Terms and conditions document structure" data-type="markdown">Terms</span>
                <span class="ai-chip" data-prompt="Privacy policy document" data-type="markdown">Privacy Policy</span>
                <span class="ai-chip" data-prompt="FAQ section with questions and answers" data-type="markdown">FAQ</span>
                <span class="ai-chip" data-prompt="Product documentation with features" data-type="markdown">Documentation</span>
                <span class="ai-chip" data-prompt="Blog post about company announcement" data-type="markdown">Blog Post</span>
            </div>
            <!-- Shortcode Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsShortcode" style="display: none;">
                <span class="ai-chip" data-prompt="Gallery shortcode with lightbox" data-type="shortcode">Gallery</span>
                <span class="ai-chip" data-prompt="Contact form shortcode with name, email, message" data-type="shortcode">Contact Form</span>
                <span class="ai-chip" data-prompt="Recent posts shortcode with 5 items" data-type="shortcode">Recent Posts</span>
                <span class="ai-chip" data-prompt="Social sharing buttons shortcode" data-type="shortcode">Social Share</span>
                <span class="ai-chip" data-prompt="Newsletter subscription form shortcode" data-type="shortcode">Newsletter</span>
            </div>
            <div id="aiLoading" class="ai-loading" style="display: none;">
                <div class="spinner"></div>
                <span>Generating content...</span>
            </div>
            <div id="aiResult" class="form-group" style="display: none;">
                <label>Generated Content</label>
                <textarea id="aiGeneratedContent" class="form-control" rows="6" readonly></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeAiModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="aiGenerateBtn" onclick="generateAiContent()">🤖 Generate</button>
            <button type="button" class="btn btn-success" id="aiApplyBtn" onclick="applyAiContent()" style="display: none;">✓ Use This</button>
        </div>
    </div>
</div>

<style>
/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}
.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

/* Page Header */
.page-header {
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    padding: 2rem 2rem 1.5rem 2rem;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
    border-bottom: 1px solid var(--border-color);
}
.back-link {
    display: inline-block;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    transition: color 0.2s;
}
.back-link:hover { color: var(--accent-color); }
.page-header h1 {
    margin: 0 0 0.25rem 0;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
}
.page-subtitle {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.95rem;
}

/* Form Layout */
.form-layout {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 900px;
}

/* Cards */
.card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.card-header {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
}
.card-body {
    padding: 1.5rem;
}

/* Form Controls */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 640px) {
    .form-row { grid-template-columns: 1fr; }
}

.form-group {
    margin-bottom: 1.25rem;
}
.form-group:last-child { margin-bottom: 0; }

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.9rem;
}
.required { color: #ef4444; }

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.form-control::placeholder {
    color: var(--text-muted);
    opacity: 0.7;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.code-editor {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
    line-height: 1.5;
    tab-size: 4;
}

select.form-control {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

/* Input with Prefix */
.input-with-prefix {
    display: flex;
    align-items: stretch;
}
.input-prefix {
    padding: 0.75rem 0.75rem;
    background: var(--bg-tertiary, #1e1e2e);
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 8px 0 0 8px;
    color: var(--text-muted);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}
.input-with-prefix .form-control {
    border-radius: 0 8px 8px 0;
}

.form-hint {
    margin: 0.5rem 0 0 0;
    font-size: 0.8rem;
    color: var(--text-muted);
}
.form-hint code {
    background: var(--bg-secondary);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.75rem;
    color: var(--accent-color);
}

.section-label {
    margin: 1rem 0 0.75rem 0;
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Type Cards */
.type-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.75rem;
}
@media (max-width: 640px) {
    .type-grid { grid-template-columns: repeat(3, 1fr); }
}

.type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem 0.5rem;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.type-card:hover {
    border-color: var(--accent-color);
    background: rgba(99, 102, 241, 0.05);
}
.type-card.selected {
    border-color: var(--accent-color);
    background: rgba(99, 102, 241, 0.1);
}
.type-icon {
    font-family: monospace;
    font-size: 1.25rem;
    font-weight: bold;
    color: var(--accent-color);
    margin-bottom: 0.25rem;
}
.type-label {
    font-size: 0.75rem;
    color: var(--text-primary);
}

/* Editor Toolbar */
.editor-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.editor-mode {
    font-family: monospace;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background: var(--bg-tertiary, #1e1e2e);
    border-radius: 4px;
    color: var(--accent-color);
}

/* Switch Toggle */
.switch-toggle {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 8px 0;
    user-select: none;
}
.switch-toggle input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.switch-slider {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    background: #4b5563;
    border-radius: 14px;
    transition: background 0.25s ease;
    flex-shrink: 0;
}
.switch-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: white;
    border-radius: 50%;
    transition: transform 0.25s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.switch-toggle input:checked + .switch-slider {
    background: #22c55e;
}
.switch-toggle input:checked + .switch-slider::after {
    transform: translateX(24px);
}
.switch-text {
    font-weight: 500;
    font-size: 0.95rem;
}
.switch-text::after {
    content: 'Inactive';
    color: var(--text-muted);
}
.switch-toggle input:checked ~ .switch-text::after {
    content: 'Active';
    color: #22c55e;
}

/* Meta Info */
.meta-info {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}
.meta-item {
    font-size: 0.85rem;
    color: var(--text-muted);
}
.meta-item strong {
    color: var(--text-primary);
}

/* Form Actions */
.form-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem 0;
    margin-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    max-width: 900px;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.btn-primary {
    background: var(--accent-color);
    color: white;
}
.btn-primary:hover {
    background: var(--accent-hover, #4f46e5);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-tertiary, #313244);
}
.btn-ghost {
    background: transparent;
    color: var(--text-muted);
    border: none;
    padding: 0.5rem 0.75rem;
}
.btn-ghost:hover {
    color: var(--accent-color);
    background: var(--bg-tertiary, #313244);
}
.btn-lg {
    padding: 0.875rem 2rem;
    font-size: 1rem;
}
.btn-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
}

/* Modal */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: var(--bg-primary);
    border-radius: 8px;
    width: 90%;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-fullscreen {
    width: 95%;
    height: 90vh;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
}
.modal-header h3 { margin: 0; }
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    line-height: 1;
}
.modal-body {
    padding: 1.5rem;
    flex: 1;
    overflow: auto;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    flex-shrink: 0;
}

.expanded-editor {
    width: 100%;
    height: 100%;
    min-height: 400px;
    resize: none;
    border: none;
    border-radius: 0;
}

/* AI Content Generator */
.btn-ai {
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    color: white;
    border: none;
}
.btn-ai:hover {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
    transform: translateY(-1px);
}
.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 1rem 0;
}
.ai-chip {
    padding: 0.4rem 0.8rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.ai-chip:hover {
    background: var(--accent-color);
    color: white;
    border-color: var(--accent-color);
}
.ai-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.5rem;
    color: var(--text-muted);
}
.ai-loading .spinner {
    width: 24px;
    height: 24px;
    border: 3px solid var(--border-color);
    border-top-color: var(--accent-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// Auto-generate slug from name
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');
const slugPreview = document.getElementById('slugPreview');

nameInput?.addEventListener('input', function() {
    if (!slugInput.dataset.manual) {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
        slugPreview.textContent = slug || 'slug';
    }
});

slugInput?.addEventListener('input', function() {
    this.dataset.manual = 'true';
    slugPreview.textContent = this.value || 'slug';
});

// Type card selection
const typeSelect = document.getElementById('type');
const typeCards = document.querySelectorAll('.type-card');
const editorMode = document.getElementById('editorMode');
const contentHint = document.getElementById('contentHint');

const typeHints = {
    'html': 'HTML code will be rendered as-is. Use proper HTML structure.',
    'text': 'Plain text will be displayed with line breaks preserved.',
    'json': 'Enter valid JSON data. Will be parsed and made available as array.',
    'markdown': 'Supports basic Markdown: **bold**, *italic*, `code`, etc.',
    'shortcode': 'Enter shortcode(s) that will be processed by the template engine.'
};

typeCards.forEach(card => {
    card.addEventListener('click', function() {
        const type = this.dataset.type;
        typeSelect.value = type;
        typeCards.forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        editorMode.textContent = type.toUpperCase();
        contentHint.textContent = typeHints[type] || typeHints['html'];
    });
});

typeSelect?.addEventListener('change', function() {
    typeCards.forEach(card => {
        card.classList.toggle('selected', card.dataset.type === this.value);
    });
    editorMode.textContent = this.value.toUpperCase();
    contentHint.textContent = typeHints[this.value] || typeHints['html'];
});

// Expanded editor
function expandEditor() {
    const modal = document.getElementById('editorModal');
    const content = document.getElementById('content');
    const expanded = document.getElementById('expandedContent');
    expanded.value = content.value;
    modal.style.display = 'flex';
    expanded.focus();
}

function closeExpandedEditor() {
    document.getElementById('editorModal').style.display = 'none';
}

function applyExpandedContent() {
    const content = document.getElementById('content');
    const expanded = document.getElementById('expandedContent');
    content.value = expanded.value;
    closeExpandedEditor();
}

// Close modal on backdrop click
document.getElementById('editorModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeExpandedEditor();
});

// Keyboard shortcut for expanded editor
document.getElementById('content')?.addEventListener('keydown', function(e) {
    if (e.key === 'F11' || (e.ctrlKey && e.key === 'Enter')) {
        e.preventDefault();
        expandEditor();
    }
});

document.getElementById('expandedContent')?.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        e.preventDefault();
        closeExpandedEditor();
    }
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        applyExpandedContent();
    }
});

// ═══════════════════════════════════════════════════════════
// AI CONTENT GENERATOR
// ═══════════════════════════════════════════════════════════
const CSRF_TOKEN = '<?= csrf_token() ?>';

// AI Options per content block type
const aiOptions = {
    html: {
        options: [
            { group: 'HTML Components', items: [
                { value: 'html', label: '🌐 HTML Block (auto-styled)' },
                { value: 'html_banner', label: '🎯 Banner / Hero' },
                { value: 'html_card', label: '📦 Card Component' },
                { value: 'html_list', label: '📋 List / Features' },
                { value: 'html_contact', label: '📧 Contact Info' }
            ]},
            { group: 'Plain Text', items: [
                { value: 'paragraph', label: '📝 Paragraph' },
                { value: 'heading', label: '✏️ Heading' }
            ]}
        ],
        placeholder: 'E.g., Hero banner with gradient, pricing table, footer with links...',
        suggestions: 'aiSuggestionsHtml'
    },
    text: {
        options: [
            { group: 'Text Content', items: [
                { value: 'paragraph', label: '📝 Paragraph / Description' },
                { value: 'heading', label: '✏️ Heading / Title' },
                { value: 'cta', label: '🚀 Call to Action' },
                { value: 'features', label: '⭐ Features Text' },
                { value: 'testimonial', label: '💬 Testimonial' }
            ]}
        ],
        placeholder: 'E.g., Welcome message, about us, mission statement...',
        suggestions: 'aiSuggestionsText'
    },
    json: {
        options: [
            { group: 'JSON Data', items: [
                { value: 'code_json', label: '📋 JSON Configuration' },
                { value: 'code_schema', label: '🔖 Schema.org JSON-LD' }
            ]}
        ],
        placeholder: 'E.g., Navigation menu structure, slider config, FAQ data...',
        suggestions: 'aiSuggestionsJson'
    },
    markdown: {
        options: [
            { group: 'Markdown Content', items: [
                { value: 'markdown', label: '📄 Markdown Document' },
                { value: 'markdown_faq', label: '❓ FAQ Section' },
                { value: 'markdown_terms', label: '📜 Legal Document' }
            ]}
        ],
        placeholder: 'E.g., Terms and conditions, privacy policy, documentation...',
        suggestions: 'aiSuggestionsMarkdown'
    },
    shortcode: {
        options: [
            { group: 'Shortcodes', items: [
                { value: 'shortcode', label: '🔧 Custom Shortcode' },
                { value: 'shortcode_form', label: '📝 Form Shortcode' },
                { value: 'shortcode_gallery', label: '🖼️ Gallery Shortcode' }
            ]}
        ],
        placeholder: 'E.g., Contact form shortcode, gallery with lightbox, recent posts...',
        suggestions: 'aiSuggestionsShortcode'
    }
};

function openAiModal() {
    const modal = document.getElementById('aiModal');
    const select = document.getElementById('aiContentType');
    const prompt = document.getElementById('aiPrompt');
    
    // Get current content block type
    const blockType = document.getElementById('type')?.value || 'html';
    const config = aiOptions[blockType] || aiOptions.html;
    
    // Populate select options
    select.innerHTML = '';
    config.options.forEach(group => {
        const optgroup = document.createElement('optgroup');
        optgroup.label = group.group;
        group.items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.label;
            optgroup.appendChild(option);
        });
        select.appendChild(optgroup);
    });
    
    // Set placeholder
    prompt.placeholder = config.placeholder;
    
    // Show/hide suggestion groups
    document.querySelectorAll('.ai-suggestions-group').forEach(el => el.style.display = 'none');
    const suggestionsEl = document.getElementById(config.suggestions);
    if (suggestionsEl) suggestionsEl.style.display = 'flex';
    
    // Reset state
    prompt.value = '';
    document.getElementById('aiResult').style.display = 'none';
    document.getElementById('aiApplyBtn').style.display = 'none';
    document.getElementById('aiLoading').style.display = 'none';
    
    modal.style.display = 'flex';
    prompt.focus();
}

function closeAiModal() {
    document.getElementById('aiModal').style.display = 'none';
}

// AI chips click handler
document.querySelectorAll('.ai-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.getElementById('aiPrompt').value = chip.dataset.prompt;
        if (chip.dataset.type) {
            document.getElementById('aiContentType').value = chip.dataset.type;
        }
    });
});

// Close AI modal on backdrop click
document.getElementById('aiModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAiModal();
});

async function generateAiContent() {
    const type = document.getElementById('aiContentType').value;
    const prompt = document.getElementById('aiPrompt').value.trim();
    
    if (!prompt) {
        alert('Please describe what you need');
        return;
    }
    
    const generateBtn = document.getElementById('aiGenerateBtn');
    generateBtn.disabled = true;
    generateBtn.textContent = '⏳ Generating...';
    document.getElementById('aiLoading').style.display = 'flex';
    document.getElementById('aiResult').style.display = 'none';
    
    try {
        const response = await fetch('/api/ai/generate-content.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                csrf_token: CSRF_TOKEN,
                type: type,
                prompt: prompt
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.content) {
            document.getElementById('aiGeneratedContent').value = result.content;
            document.getElementById('aiResult').style.display = 'block';
            document.getElementById('aiApplyBtn').style.display = 'inline-flex';
        } else {
            alert(result.error || 'Failed to generate content');
        }
    } catch (error) {
        console.error('AI generation error:', error);
        alert('Connection error. Please try again.');
    } finally {
        generateBtn.disabled = false;
        generateBtn.textContent = '🤖 Generate';
        document.getElementById('aiLoading').style.display = 'none';
    }
}

function applyAiContent() {
    const generatedContent = document.getElementById('aiGeneratedContent').value;
    document.getElementById('content').value = generatedContent;
    closeAiModal();
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
