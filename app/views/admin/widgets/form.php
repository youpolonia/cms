<?php
/**
 * Widget Form (Create/Edit) - Full Upgrade
 */
$title = $widget ? 'Edit Widget' : 'New Widget';
$isEdit = $widget !== null;
ob_start();

// Common emojis for icon picker
$emojiGrid = [
    '📦', '📁', '📋', '📝', '📰', '🏷️', '🔍', '🔗',
    '⚙️', '🏠', '📄', '💬', '📧', '🔔', '📊', '📈',
    '👤', '👥', '🌐', '🎨', '🛒', '💡', '📌', '⭐',
    '❤️', '🎯', '🔥', '✨', '📱', '💻', '🖥️', '📸'
];
?>

<div class="page-header">
    <div>
        <h1 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);">
            <?= $isEdit ? 'Edit Widget' : 'Create New Widget' ?>
        </h1>
        <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.9rem;">
            <?= $isEdit ? 'Update widget settings and content' : 'Add a new widget to your site' ?>
        </p>
    </div>
    <a href="/admin/widgets" class="btn btn-secondary">&larr; Back to Widgets</a>
</div>

<form method="post" action="<?= $isEdit ? '/admin/widgets/' . (int)$widget['id'] : '/admin/widgets/' ?>" class="widget-form">
    <?= csrf_field() ?>

    <div class="form-grid">
        <!-- Left Column: Main Settings -->
        <div class="form-column">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Widget Details</h3>
                </div>
                <div class="card-body">
                    <!-- Icon Picker (if new columns available) -->
                    <?php if ($hasNewColumns ?? false): ?>
                        <div class="form-group">
                            <label>Icon (optional)</label>
                            <div class="icon-picker-container">
                                <div class="icon-display" id="iconDisplay" onclick="toggleIconPicker()">
                                    <span id="selectedIcon"><?= esc($widget['icon'] ?? '') ?: '➕' ?></span>
                                </div>
                                <input type="hidden" id="icon" name="icon" value="<?= esc($widget['icon'] ?? '') ?>">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearIcon()">Clear</button>
                            </div>
                            <div id="iconPicker" class="icon-picker" style="display: none;">
                                <?php foreach ($emojiGrid as $emoji): ?>
                                    <button type="button" class="icon-option" onclick="selectIcon('<?= $emoji ?>')"><?= $emoji ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" required value="<?= esc($widget['name'] ?? '') ?>" placeholder="Widget name" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" value="<?= esc($widget['slug'] ?? '') ?>" placeholder="widget-slug" class="form-input">
                            <small class="form-hint">Auto-generated if left blank</small>
                        </div>
                    </div>

                    <?php if ($hasNewColumns ?? false): ?>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="2" placeholder="Brief description of this widget" class="form-input"><?= esc($widget['description'] ?? '') ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Widget Type Selection -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Widget Type</h3>
                </div>
                <div class="card-body">
                    <div class="type-grid">
                        <?php
                        $typeIcons = [
                            'html' => '🌐',
                            'text' => '📝',
                            'menu' => '📋',
                            'recent_posts' => '📰',
                            'categories' => '🏷️',
                            'search' => '🔍',
                            'social' => '🔗',
                            'custom' => '⚙️'
                        ];
                        $typeDescriptions = [
                            'html' => 'Rich HTML content',
                            'text' => 'Plain text only',
                            'menu' => 'Navigation menu',
                            'recent_posts' => 'Latest blog posts',
                            'categories' => 'Category listing',
                            'search' => 'Search form',
                            'social' => 'Social media links',
                            'custom' => 'Custom code/shortcodes'
                        ];
                        foreach ($types as $key => $label):
                            $isSelected = ($widget['type'] ?? 'html') === $key;
                            $icon = $typeIcons[$key] ?? '📦';
                            $desc = $typeDescriptions[$key] ?? '';
                        ?>
                            <label class="type-card <?= $isSelected ? 'selected' : '' ?>">
                                <input type="radio" name="type" value="<?= esc($key) ?>" <?= $isSelected ? 'checked' : '' ?> onchange="updateTypeSelection(this)">
                                <span class="type-icon"><?= $icon ?></span>
                                <span class="type-label"><?= esc($label) ?></span>
                                <span class="type-desc"><?= esc($desc) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Menu Selector (shown when type=menu) -->
            <div class="card" id="menuSelectorCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">📋 Select Menu</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="menu_id">Navigation Menu *</label>
                        <select id="menu_id" name="menu_id" class="form-input" onchange="updateMenuContent()">
                            <option value="">-- Select Menu --</option>
                            <?php foreach ($menus ?? [] as $menu): ?>
                                <option value="<?= (int)$menu['id'] ?>" data-name="<?= esc($menu['name']) ?>"><?= esc($menu['name']) ?> (<?= esc($menu['location'] ?? 'no location') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-hint">Select which menu to display in this widget</small>
                    </div>
                    <?php if (empty($menus)): ?>
                        <p style="color: var(--warning); margin-top: 0.5rem;">
                            ⚠️ No menus available. <a href="/admin/menus/create" style="color: var(--accent);">Create one first</a>.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Posts Settings (shown when type=recent_posts) -->
            <div class="card" id="recentPostsCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">📰 Recent Posts Settings</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="posts_limit">Number of Posts</label>
                        <input type="number" id="posts_limit" name="posts_limit" min="1" max="20" value="5" class="form-input" onchange="updateRecentPostsContent()">
                        <small class="form-hint">How many recent posts to display (1-20)</small>
                    </div>
                </div>
            </div>

            <!-- Social Links Settings (shown when type=social) -->
            <div class="card" id="socialLinksCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">🔗 Social Links</h3>
                </div>
                <div class="card-body">
                    <div class="social-links-editor">
                        <div class="form-group">
                            <label><span class="social-icon">📘</span> Facebook</label>
                            <input type="url" id="social_facebook" class="form-input social-input" placeholder="https://facebook.com/yourpage" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">🐦</span> X (Twitter)</label>
                            <input type="url" id="social_twitter" class="form-input social-input" placeholder="https://x.com/yourhandle" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">📸</span> Instagram</label>
                            <input type="url" id="social_instagram" class="form-input social-input" placeholder="https://instagram.com/yourprofile" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">💼</span> LinkedIn</label>
                            <input type="url" id="social_linkedin" class="form-input social-input" placeholder="https://linkedin.com/in/yourprofile" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">📺</span> YouTube</label>
                            <input type="url" id="social_youtube" class="form-input social-input" placeholder="https://youtube.com/@yourchannel" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">🎵</span> TikTok</label>
                            <input type="url" id="social_tiktok" class="form-input social-input" placeholder="https://tiktok.com/@yourprofile" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">📌</span> Pinterest</label>
                            <input type="url" id="social_pinterest" class="form-input social-input" placeholder="https://pinterest.com/yourprofile" onchange="updateSocialLinksContent()">
                        </div>
                        <div class="form-group">
                            <label><span class="social-icon">💻</span> GitHub</label>
                            <input type="url" id="social_github" class="form-input social-input" placeholder="https://github.com/yourprofile" onchange="updateSocialLinksContent()">
                        </div>
                    </div>
                    <small class="form-hint">Enter URLs for the social platforms you want to display. Empty fields will be hidden.</small>
                </div>
            </div>

            <!-- Content Editor -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Content</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($aiConfigured ?? false): ?>
                        <button type="button" class="btn btn-sm btn-ai" id="aiContentBtn" onclick="openAiModal()" style="display: none;">
                            🤖 AI Generate
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="toggleExpandedEditor()">⛶ Expand</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom: 0;">
                        <textarea id="content" name="content" rows="12" placeholder="Enter widget content..." class="form-input content-editor"><?= esc($widget['content'] ?? '') ?></textarea>
                        <div class="editor-hints">
                            <span class="hint-item" data-type="html">HTML tags supported</span>
                            <span class="hint-item" data-type="text">Plain text only</span>
                            <span class="hint-item" data-type="custom">PHP code is not executed</span>
                            <span class="hint-item" data-type="menu">Menu selected above</span>
                            <span class="hint-item" data-type="recent_posts">Settings configured above</span>
                            <span class="hint-item" data-type="categories">Configure in settings</span>
                            <span class="hint-item" data-type="search">Search form auto-generated</span>
                            <span class="hint-item" data-type="social">Configure links above ↑</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Settings -->
        <div class="form-sidebar">
            <!-- Display Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Display Settings</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="area">Display Area *</label>
                        <select id="area" name="area" class="form-input">
                            <?php foreach ($areas as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= ($widget['area'] ?? 'sidebar') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($hasNewColumns ?? false): ?>
                        <div class="form-group">
                            <label for="visibility">Visibility</label>
                            <select id="visibility" name="visibility" class="form-input">
                                <?php foreach ($visibilities as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" <?= ($widget['visibility'] ?? 'all') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-hint">Who can see this widget</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="toggle-label">
                            <input type="checkbox" name="is_active" value="1" <?= ($widget['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span class="toggle-text">Active (visible on frontend)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Performance Settings -->
            <?php if ($hasNewColumns ?? false): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Performance</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="cache_ttl">Cache Duration (seconds)</label>
                            <input type="number" id="cache_ttl" name="cache_ttl" value="<?= (int)($widget['cache_ttl'] ?? 0) ?>" min="0" class="form-input" placeholder="0">
                            <small class="form-hint">0 = no caching. Recommended: 300-3600</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Meta Information (Edit only) -->
            <?php if ($isEdit): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Information</h3>
                    </div>
                    <div class="card-body meta-info">
                        <div class="meta-row">
                            <span class="meta-label">ID</span>
                            <span class="meta-value">#<?= (int)$widget['id'] ?></span>
                        </div>
                        <?php if ($hasNewColumns ?? false): ?>
                            <div class="meta-row">
                                <span class="meta-label">Version</span>
                                <span class="meta-value">v<?= (int)($widget['version'] ?? 1) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="meta-row">
                            <span class="meta-label">Created</span>
                            <span class="meta-value"><?= $widget['created_at'] ? date('M j, Y g:i A', strtotime($widget['created_at'])) : '—' ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="form-actions-sidebar">
                <button type="submit" class="btn btn-primary btn-block"><?= $isEdit ? 'Save Changes' : 'Create Widget' ?></button>
                <a href="/admin/widgets" class="btn btn-secondary btn-block">Cancel</a>
            </div>
        </div>
    </div>
</form>

<!-- Expanded Editor Modal -->
<div id="expandedEditorModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 900px; height: 80vh;">
        <div class="modal-header">
            <h3>Content Editor</h3>
            <button type="button" class="modal-close" onclick="closeExpandedEditor()">&times;</button>
        </div>
        <div class="modal-body" style="height: calc(100% - 130px); padding: 0;">
            <textarea id="expandedContent" style="width: 100%; height: 100%; border: none; padding: 1rem; font-family: monospace; font-size: 14px; resize: none;"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeExpandedEditor()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="applyExpandedContent()">Apply</button>
        </div>
    </div>
</div>

<!-- AI Content Generator Modal -->
<?php if ($aiConfigured ?? false): ?>
<div id="aiModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>🤖 AI Content Generator</h3>
            <button type="button" class="modal-close" onclick="closeAiModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="aiContentType">Content Type</label>
                <select id="aiContentType" class="form-input">
                    <!-- Options populated dynamically by JavaScript -->
                </select>
            </div>
            <div class="form-group">
                <label for="aiPrompt">Describe what you need</label>
                <textarea id="aiPrompt" class="form-input" rows="4"></textarea>
            </div>
            <!-- HTML Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsHtml">
                <span class="ai-chip" data-prompt="Welcome banner with gradient background and CTA button" data-type="html_banner">Banner</span>
                <span class="ai-chip" data-prompt="Promotional card for summer sale 50% off" data-type="html_card">Promo Card</span>
                <span class="ai-chip" data-prompt="Contact information with address, phone, email" data-type="html_contact">Contact</span>
                <span class="ai-chip" data-prompt="Features list: fast, secure, reliable, 24/7 support" data-type="html_list">Features</span>
                <span class="ai-chip" data-prompt="Newsletter signup box with email input styling" data-type="html">Newsletter</span>
            </div>
            <!-- Custom Code Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsCustom" style="display: none;">
                <span class="ai-chip" data-prompt="Google Analytics 4 tracking script" data-type="code_analytics">Analytics</span>
                <span class="ai-chip" data-prompt="JSON configuration object for widget settings" data-type="code_json">JSON Config</span>
                <span class="ai-chip" data-prompt="JavaScript countdown timer to New Year" data-type="code_js">Countdown JS</span>
                <span class="ai-chip" data-prompt="CSS animation keyframes for fade-in and slide effects" data-type="code_css">CSS Animation</span>
                <span class="ai-chip" data-prompt="Schema.org JSON-LD structured data for local business" data-type="code_schema">Schema.org</span>
            </div>
            <!-- Text Suggestions -->
            <div class="ai-suggestions ai-suggestions-group" id="aiSuggestionsText" style="display: none;">
                <span class="ai-chip" data-prompt="Welcome message for website visitors" data-type="paragraph">Welcome</span>
                <span class="ai-chip" data-prompt="About us brief company description" data-type="paragraph">About Us</span>
                <span class="ai-chip" data-prompt="Call to action encouraging visitors to contact" data-type="cta">CTA</span>
                <span class="ai-chip" data-prompt="Short customer testimonial quote" data-type="testimonial">Testimonial</span>
            </div>
            <div id="aiLoading" class="ai-loading" style="display: none;">
                <div class="spinner"></div>
                <span>Generating content...</span>
            </div>
            <div id="aiResult" class="form-group" style="display: none;">
                <label>Generated Content</label>
                <textarea id="aiGeneratedContent" class="form-input" rows="6" readonly></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeAiModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="aiGenerateBtn" onclick="generateAiContent()">🤖 Generate</button>
            <button type="button" class="btn btn-success" id="aiApplyBtn" onclick="applyAiContent()" style="display: none;">✓ Use This</button>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}

.form-column { display: flex; flex-direction: column; gap: 1.5rem; }
.form-sidebar { display: flex; flex-direction: column; gap: 1rem; }

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 0.95rem;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.form-input:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--text-muted);
}

.card-title {
    font-size: 1rem;
    margin: 0;
}

/* Icon Picker */
.icon-picker-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.icon-display {
    width: 50px;
    height: 50px;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    cursor: pointer;
    background: var(--bg-secondary);
    transition: all 0.2s;
}

.icon-display:hover {
    border-color: var(--accent-color);
    background: var(--bg-primary);
}

.icon-picker {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 0.25rem;
    padding: 0.75rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-top: 0.5rem;
}

.icon-option {
    width: 36px;
    height: 36px;
    border: none;
    background: var(--bg-primary);
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.25rem;
    transition: all 0.15s;
}

.icon-option:hover {
    background: var(--accent-color);
    transform: scale(1.1);
}

/* Type Grid */
.type-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.75rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
    background: var(--bg-primary);
}

.type-card:hover {
    border-color: var(--accent-color);
    background: var(--bg-secondary);
}

.type-card.selected {
    border-color: var(--accent-color);
    background: rgba(59, 130, 246, 0.1);
}

.type-card input { display: none; }

.type-icon {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.type-label {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-primary);
}

.type-desc {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

/* Content Editor */
.content-editor {
    font-family: 'Fira Code', 'Monaco', 'Consolas', monospace;
    font-size: 0.9rem;
    line-height: 1.5;
}

.editor-hints {
    margin-top: 0.5rem;
}

.hint-item {
    display: none;
    font-size: 0.8rem;
    color: var(--text-muted);
    padding: 0.25rem 0.5rem;
    background: var(--bg-secondary);
    border-radius: 4px;
}

.hint-item.visible { display: inline-block; }

/* Toggle Label */
.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.toggle-text {
    font-size: 0.9rem;
    color: var(--text-primary);
}

/* Meta Info */
.meta-info { padding: 0; }

.meta-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color);
}

.meta-row:last-child { border-bottom: none; }

.meta-label {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.meta-value {
    font-size: 0.85rem;
    color: var(--text-primary);
    font-weight: 500;
}

/* Sidebar Actions */
.form-actions-sidebar {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.btn-block { width: 100%; }

/* Modal Styles */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--bg-primary);
    border-radius: 8px;
    width: 90%;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 { margin: 0; }

/* AI Styles */
.btn-ai {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}
.btn-ai:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

.ai-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 1rem 0;
}

/* Social Links Editor */
.social-links-editor .form-group {
    margin-bottom: 0.75rem;
}
.social-links-editor label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}
.social-icon {
    font-size: 1.1rem;
}
.social-input {
    font-size: 0.9rem;
}

.ai-chip {
    padding: 0.35rem 0.75rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
}
.ai-chip:hover {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

.ai-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 2rem;
    color: var(--text-muted);
}

.ai-loading .spinner {
    width: 24px;
    height: 24px;
    border: 3px solid var(--border-color);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-body { padding: 1.5rem; }

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .type-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
// Auto-generate slug from name
document.getElementById('name')?.addEventListener('input', function() {
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

// Icon picker
function toggleIconPicker() {
    const picker = document.getElementById('iconPicker');
    picker.style.display = picker.style.display === 'none' ? 'grid' : 'none';
}

function selectIcon(icon) {
    document.getElementById('icon').value = icon;
    document.getElementById('selectedIcon').textContent = icon;
    document.getElementById('iconPicker').style.display = 'none';
}

function clearIcon() {
    document.getElementById('icon').value = '';
    document.getElementById('selectedIcon').textContent = '➕';
}

// Type selection
function updateTypeSelection(input) {
    document.querySelectorAll('.type-card').forEach(card => card.classList.remove('selected'));
    input.closest('.type-card').classList.add('selected');
    updateEditorHint(input.value);
    updateTypeSpecificCards(input.value);
    
    // Load social links when switching to social type
    if (input.value === 'social') {
        loadSocialLinksFromContent();
    }
}

function updateTypeSpecificCards(type) {
    // Show/hide menu selector
    const menuCard = document.getElementById('menuSelectorCard');
    if (menuCard) {
        menuCard.style.display = (type === 'menu') ? 'block' : 'none';
    }
    
    // Show/hide recent posts settings
    const postsCard = document.getElementById('recentPostsCard');
    if (postsCard) {
        postsCard.style.display = (type === 'recent_posts') ? 'block' : 'none';
    }
    
    // Show/hide social links settings
    const socialCard = document.getElementById('socialLinksCard');
    if (socialCard) {
        socialCard.style.display = (type === 'social') ? 'block' : 'none';
    }
    
    // Show/hide AI button (only for html, text, custom)
    const aiBtn = document.getElementById('aiContentBtn');
    if (aiBtn) {
        const aiTypes = ['html', 'text', 'custom'];
        aiBtn.style.display = aiTypes.includes(type) ? 'inline-flex' : 'none';
    }
}

function updateMenuContent() {
    const select = document.getElementById('menu_id');
    const content = document.getElementById('content');
    if (select && content && select.value) {
        content.value = JSON.stringify({ menu_id: parseInt(select.value) });
    }
}

function updateRecentPostsContent() {
    const limitInput = document.getElementById('posts_limit');
    const content = document.getElementById('content');
    if (limitInput && content) {
        content.value = JSON.stringify({ limit: parseInt(limitInput.value) || 5 });
    }
}

function updateSocialLinksContent() {
    const platforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'github'];
    const socialData = {};
    
    platforms.forEach(platform => {
        const input = document.getElementById('social_' + platform);
        if (input && input.value.trim()) {
            socialData[platform] = input.value.trim();
        }
    });
    
    const content = document.getElementById('content');
    if (content) {
        content.value = Object.keys(socialData).length > 0 ? JSON.stringify(socialData) : '';
    }
}

function loadSocialLinksFromContent() {
    const content = document.getElementById('content');
    if (!content || !content.value) return;
    
    try {
        const data = JSON.parse(content.value);
        const platforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'github'];
        
        platforms.forEach(platform => {
            const input = document.getElementById('social_' + platform);
            if (input && data[platform]) {
                input.value = data[platform];
            }
        });
    } catch (e) {
        // Content is not valid JSON
    }
}

function updateEditorHint(type) {
    document.querySelectorAll('.hint-item').forEach(hint => {
        hint.classList.toggle('visible', hint.dataset.type === type);
    });
}

// Initialize hints
updateEditorHint(document.querySelector('input[name="type"]:checked')?.value || 'html');

// Initialize type-specific cards
(function initTypeCards() {
    const currentType = document.querySelector('input[name="type"]:checked')?.value || 'html';
    updateTypeSpecificCards(currentType);
    
    // Parse existing content and set selectors
    const contentField = document.getElementById('content');
    if (contentField && contentField.value) {
        try {
            const data = JSON.parse(contentField.value);
            
            // Set menu selector if menu_id exists
            if (data.menu_id) {
                const menuSelect = document.getElementById('menu_id');
                if (menuSelect) menuSelect.value = data.menu_id;
            }
            
            // Set posts limit if limit exists
            if (data.limit) {
                const limitInput = document.getElementById('posts_limit');
                if (limitInput) limitInput.value = data.limit;
            }
            
            // Load social links if present
            if (currentType === 'social') {
                loadSocialLinksFromContent();
            }
        } catch (e) {
            // Content is not JSON, that's fine
        }
    }
    
    // Also load social links if type is social (even if not valid JSON initially)
    if (currentType === 'social') {
        loadSocialLinksFromContent();
    }
})();

// Expanded editor
function toggleExpandedEditor() {
    const modal = document.getElementById('expandedEditorModal');
    const mainContent = document.getElementById('content');
    const expandedContent = document.getElementById('expandedContent');

    expandedContent.value = mainContent.value;
    modal.style.display = 'flex';
    expandedContent.focus();
}

function closeExpandedEditor() {
    document.getElementById('expandedEditorModal').style.display = 'none';
}

function applyExpandedContent() {
    document.getElementById('content').value = document.getElementById('expandedContent').value;
    closeExpandedEditor();
}

// Close modal on backdrop click
document.getElementById('expandedEditorModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeExpandedEditor();
});

// Close icon picker when clicking outside
document.addEventListener('click', function(e) {
    const picker = document.getElementById('iconPicker');
    const display = document.getElementById('iconDisplay');
    if (picker && !picker.contains(e.target) && !display.contains(e.target)) {
        picker.style.display = 'none';
    }
});

// ═══════════════════════════════════════════════════════════
// AI CONTENT GENERATOR
// ═══════════════════════════════════════════════════════════
const CSRF_TOKEN = '<?= csrf_token() ?>';

// AI Content Generator Options per widget type
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
                { value: 'heading', label: '✏️ Heading' },
                { value: 'cta', label: '🚀 Call to Action' }
            ]}
        ],
        placeholder: 'E.g., Welcome banner with blue gradient, promo card for sale, contact info block...',
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
        placeholder: 'E.g., Welcome message, about us description, call to action text...',
        suggestions: 'aiSuggestionsText'
    },
    custom: {
        options: [
            { group: 'Code Snippets', items: [
                { value: 'code_js', label: '📜 JavaScript Code' },
                { value: 'code_css', label: '🎨 CSS Styles' },
                { value: 'code_json', label: '📋 JSON Configuration' },
                { value: 'code_schema', label: '🔖 Schema.org JSON-LD' },
                { value: 'code_analytics', label: '📊 Analytics Script' }
            ]},
            { group: 'Embeds', items: [
                { value: 'code_embed', label: '🔗 Embed Code' },
                { value: 'code_iframe', label: '🖼️ iFrame Generator' }
            ]}
        ],
        placeholder: 'E.g., Google Analytics tracking, JSON config for slider, countdown timer script...',
        suggestions: 'aiSuggestionsCustom'
    }
};

function openAiModal() {
    const modal = document.getElementById('aiModal');
    const select = document.getElementById('aiContentType');
    const prompt = document.getElementById('aiPrompt');
    
    // Get current widget type
    const widgetType = document.querySelector('input[name="type"]:checked')?.value || 'html';
    const config = aiOptions[widgetType] || aiOptions.html;
    
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
    const generated = document.getElementById('aiGeneratedContent').value;
    const contentField = document.getElementById('content');
    
    // Append or replace based on current content
    if (contentField.value.trim()) {
        if (confirm('Replace existing content? Click Cancel to append instead.')) {
            contentField.value = generated;
        } else {
            contentField.value += '\n\n' + generated;
        }
    } else {
        contentField.value = generated;
    }
    
    closeAiModal();
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
