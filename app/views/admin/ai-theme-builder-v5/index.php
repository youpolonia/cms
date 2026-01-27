<?php
/**
 * AI Theme Builder 5.0 - Main View
 *
 * Fully integrated with JTB (Jessie Theme Builder)
 * Uses CMS admin layout (topbar.php) for consistent navigation
 *
 * @package AiThemeBuilder
 * @version 5.0
 */

$models = $data['models'] ?? [];
$defaultModel = $data['defaultModel'] ?? 'gpt-4o-mini';
$csrfToken = $data['csrfToken'] ?? '';

// Set title for topbar layout
$title = 'AI Theme Builder 5.0';

// Start output buffering for content
ob_start();
?>
<style>
    /* AI Theme Builder 5.0 Styles - uses CMS theme variables from topbar.php */

    .aitb-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0;
    }

    .aitb-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .aitb-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .aitb-header h1 span {
        color: var(--accent);
    }

    .aitb-badge {
        background: linear-gradient(135deg, var(--accent), #8b5cf6);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Wizard Steps */
    .aitb-steps {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 40px;
    }

    .aitb-step {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: var(--bg-primary);
        border-radius: 10px;
        border: 2px solid transparent;
        transition: all 0.3s;
    }

    .aitb-step.active {
        border-color: var(--accent);
        background: var(--accent-muted);
    }

    .aitb-step.completed {
        border-color: var(--success);
    }

    .aitb-step-number {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-tertiary);
        border-radius: 50%;
        font-weight: 600;
        font-size: 14px;
        color: var(--text-primary);
    }

    .aitb-step.active .aitb-step-number {
        background: var(--accent);
        color: #fff;
    }

    .aitb-step.completed .aitb-step-number {
        background: var(--success);
        color: #fff;
    }

    .aitb-step-label {
        font-weight: 500;
        color: var(--text-muted);
    }

    .aitb-step.active .aitb-step-label {
        color: var(--text-primary);
    }

    /* Panels */
    .aitb-panel {
        display: none;
        background: var(--bg-primary);
        border-radius: var(--radius-lg);
        padding: 30px;
        border: 1px solid var(--border);
    }

    .aitb-panel.active {
        display: block;
    }

    /* Section Headers */
    .aitb-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .aitb-section-title .emoji {
        font-size: 20px;
    }

    /* Form */
    .aitb-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .aitb-form-group {
        margin-bottom: 20px;
    }

    .aitb-form-group.full-width {
        grid-column: span 2;
    }

    .aitb-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-primary);
    }

    .aitb-form-group input,
    .aitb-form-group textarea,
    .aitb-form-group select {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-tertiary);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .aitb-form-group textarea {
        min-height: 120px;
        resize: vertical;
    }

    .aitb-form-group input:focus,
    .aitb-form-group textarea:focus,
    .aitb-form-group select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-muted);
    }

    .aitb-form-group input::placeholder,
    .aitb-form-group textarea::placeholder {
        color: var(--text-muted);
    }

    .aitb-hint {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    /* Radio/Checkbox Grid */
    .aitb-options-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .aitb-option {
        flex: 0 0 auto;
    }

    .aitb-option input {
        display: none;
    }

    .aitb-option label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: var(--bg-tertiary);
        border: 2px solid transparent;
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
        color: var(--text-primary);
    }

    .aitb-option input:checked + label {
        border-color: var(--accent);
        background: var(--accent-muted);
        color: var(--accent);
    }

    .aitb-option label:hover {
        background: var(--bg-tertiary);
        border-color: var(--border);
    }

    /* Buttons */
    .aitb-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border: none;
        border-radius: var(--radius);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .aitb-btn-primary {
        background: var(--accent);
        color: #fff;
    }

    .aitb-btn-primary:hover {
        background: var(--accent-hover);
        color: #fff;
    }

    .aitb-btn-secondary {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .aitb-btn-secondary:hover {
        background: var(--border);
    }

    .aitb-btn-success {
        background: var(--success);
        color: #fff;
    }

    .aitb-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .aitb-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    /* Progress Panel */
    .aitb-progress {
        text-align: center;
        padding: 60px 20px;
    }

    .aitb-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid var(--bg-tertiary);
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 30px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .aitb-progress-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--text-primary);
    }

    .aitb-progress-status {
        color: var(--text-muted);
        margin-bottom: 30px;
    }

    .aitb-progress-steps {
        max-width: 400px;
        margin: 0 auto;
        text-align: left;
    }

    .aitb-progress-step {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
        margin-bottom: 10px;
        color: var(--text-primary);
    }

    .aitb-progress-step.active {
        background: var(--accent-muted);
    }

    .aitb-progress-step.completed {
        background: var(--success-bg);
    }

    .aitb-progress-step-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Preview Panel */
    .aitb-preview-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .aitb-preview-card {
        background: var(--bg-tertiary);
        border-radius: var(--radius-lg);
        overflow: hidden;
    }

    .aitb-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        background: var(--bg-secondary);
        border-bottom: 1px solid var(--border);
    }

    .aitb-preview-title {
        font-weight: 600;
        font-size: 16px;
        color: var(--text-primary);
    }

    .aitb-preview-actions {
        display: flex;
        gap: 8px;
    }

    .aitb-preview-actions button {
        padding: 6px 12px;
        font-size: 12px;
    }

    .aitb-preview-content {
        padding: 20px;
        min-height: 200px;
        max-height: 400px;
        overflow: auto;
    }

    .aitb-preview-iframe {
        width: 100%;
        min-height: 300px;
        border: none;
        background: white;
        border-radius: var(--radius);
    }

    .aitb-preview-stats {
        display: flex;
        gap: 16px;
        padding: 12px 20px;
        background: var(--bg-secondary);
        border-top: 1px solid var(--border);
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Pages List */
    .aitb-pages-list {
        display: grid;
        gap: 12px;
    }

    .aitb-page-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        background: var(--bg-secondary);
        border-radius: var(--radius);
    }

    .aitb-page-title {
        font-weight: 500;
        color: var(--text-primary);
    }

    /* Deploy Panel */
    .aitb-deploy-options {
        display: grid;
        gap: 16px;
        margin-bottom: 30px;
    }

    .aitb-deploy-option {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        background: var(--bg-tertiary);
        border-radius: var(--radius);
    }

    .aitb-deploy-option input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-top: 2px;
    }

    .aitb-deploy-option-text h4 {
        margin: 0 0 4px 0;
        font-size: 14px;
        color: var(--text-primary);
    }

    .aitb-deploy-option-text p {
        margin: 0;
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Success Panel */
    .aitb-success {
        text-align: center;
        padding: 60px 20px;
    }

    .aitb-success-icon {
        width: 80px;
        height: 80px;
        background: var(--success-bg);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 40px;
        color: var(--success);
    }

    .aitb-success h2 {
        margin-bottom: 12px;
        color: var(--text-primary);
    }

    .aitb-success p {
        color: var(--text-muted);
        margin-bottom: 30px;
    }

    .aitb-success-links {
        display: flex;
        justify-content: center;
        gap: 16px;
    }

    /* Error */
    .aitb-error {
        background: var(--danger-bg);
        border: 1px solid var(--danger);
        border-radius: var(--radius);
        padding: 16px;
        margin-bottom: 20px;
        color: var(--danger);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .aitb-form-grid {
            grid-template-columns: 1fr;
        }

        .aitb-form-group.full-width {
            grid-column: span 1;
        }

        .aitb-steps {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<div class="aitb-container">
    <!-- Header -->
    <div class="aitb-header">
        <h1><span>AI</span> Theme Builder 5.0</h1>
        <span class="aitb-badge">JTB Integrated</span>
    </div>

    <!-- Steps -->
    <div class="aitb-steps">
        <div class="aitb-step active" data-step="1">
            <span class="aitb-step-number">1</span>
            <span class="aitb-step-label">Brief</span>
        </div>
        <div class="aitb-step" data-step="2">
            <span class="aitb-step-number">2</span>
            <span class="aitb-step-label">Generate</span>
        </div>
        <div class="aitb-step" data-step="3">
            <span class="aitb-step-number">3</span>
            <span class="aitb-step-label">Preview</span>
        </div>
        <div class="aitb-step" data-step="4">
            <span class="aitb-step-number">4</span>
            <span class="aitb-step-label">Deploy</span>
        </div>
    </div>

    <!-- Error Container -->
    <div id="error-container" class="aitb-error" style="display: none;"></div>

    <!-- Panel 1: Brief -->
    <div class="aitb-panel active" data-panel="1">
        <form id="brief-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="aitb-form-grid">
                <div class="aitb-form-group">
                    <label>Project Name</label>
                    <input type="text" name="project_name" placeholder="my-website" pattern="[a-z0-9-]+" required>
                    <div class="aitb-hint">Lowercase, no spaces (used for file naming)</div>
                </div>

                <div class="aitb-form-group">
                    <label>Business/Brand Name</label>
                    <input type="text" name="business_name" placeholder="Acme Corporation" required>
                </div>

                <div class="aitb-form-group full-width">
                    <label>Describe Your Website</label>
                    <textarea name="brief" placeholder="E.g., A modern tech startup website showcasing our AI-powered analytics platform. Target audience is B2B enterprise clients. Should convey innovation, trust, and cutting-edge technology." required></textarea>
                    <div class="aitb-hint">Be detailed! Include industry, target audience, brand personality, key selling points.</div>
                </div>

                <div class="aitb-form-group">
                    <label>Industry</label>
                    <div class="aitb-options-grid">
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="business" id="ind-business" checked>
                            <label for="ind-business">Business</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="technology" id="ind-tech">
                            <label for="ind-tech">Technology</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="restaurant" id="ind-restaurant">
                            <label for="ind-restaurant">Restaurant</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="healthcare" id="ind-health">
                            <label for="ind-health">Healthcare</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="ecommerce" id="ind-ecom">
                            <label for="ind-ecom">E-commerce</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="portfolio" id="ind-portfolio">
                            <label for="ind-portfolio">Portfolio</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="education" id="ind-edu">
                            <label for="ind-edu">Education</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="industry" value="fitness" id="ind-fitness">
                            <label for="ind-fitness">Fitness</label>
                        </div>
                    </div>
                </div>

                <div class="aitb-form-group">
                    <label>Design Style</label>
                    <div class="aitb-options-grid">
                        <div class="aitb-option">
                            <input type="radio" name="style" value="modern" id="style-modern" checked>
                            <label for="style-modern">Modern</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="style" value="minimal" id="style-minimal">
                            <label for="style-minimal">Minimal</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="style" value="bold" id="style-bold">
                            <label for="style-bold">Bold</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="style" value="elegant" id="style-elegant">
                            <label for="style-elegant">Elegant</label>
                        </div>
                        <div class="aitb-option">
                            <input type="radio" name="style" value="creative" id="style-creative">
                            <label for="style-creative">Creative</label>
                        </div>
                    </div>
                </div>

                <div class="aitb-form-group full-width">
                    <label>Pages to Generate</label>
                    <div class="aitb-options-grid">
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="Home" id="page-home" checked>
                            <label for="page-home">Home</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="About" id="page-about">
                            <label for="page-about">About</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="Services" id="page-services">
                            <label for="page-services">Services</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="Contact" id="page-contact">
                            <label for="page-contact">Contact</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="Pricing" id="page-pricing">
                            <label for="page-pricing">Pricing</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="Portfolio" id="page-portfolio">
                            <label for="page-portfolio">Portfolio</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="Team" id="page-team">
                            <label for="page-team">Team</label>
                        </div>
                        <div class="aitb-option">
                            <input type="checkbox" name="pages[]" value="FAQ" id="page-faq">
                            <label for="page-faq">FAQ</label>
                        </div>
                    </div>
                </div>

                <div class="aitb-form-group">
                    <label>AI Model</label>
                    <select name="model">
                        <?php foreach ($models as $model): ?>
                        <option value="<?= htmlspecialchars($model['id']) ?>"
                            <?= $model['id'] === $defaultModel ? 'selected' : '' ?>
                            <?= empty($model['available']) ? 'disabled' : '' ?>
                            data-provider="<?= htmlspecialchars($model['provider'] ?? '') ?>">
                            <?= htmlspecialchars($model['name']) ?>
                            <?php if (!empty($model['recommended'])): ?> ⭐<?php endif; ?>
                            <?php if (empty($model['available'])): ?> (No API Key)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); margin-top: 5px; display: block;">
                        Recommended: Claude Opus 4.5 for best theme layouts and quality
                    </small>
                </div>
                <div class="aitb-form-group">
                    <label>Image Source</label>
                    <select name="image_source">
                        <option value="pexels">Pexels (Free)</option>
                        <option value="unsplash">Unsplash (Free)</option>
                        <option value="none">No Images</option>
                    </select>
                </div>
            </div>

            <div class="aitb-actions">
                <a href="/admin/themes" class="aitb-btn aitb-btn-secondary">Cancel</a>
                <button type="submit" class="aitb-btn aitb-btn-primary">
                    Generate Theme
                </button>
            </div>
        </form>
    </div>

    <!-- Panel 2: Generate -->
    <div class="aitb-panel" data-panel="2">
        <div class="aitb-progress">
            <div class="aitb-spinner"></div>
            <div class="aitb-progress-title">Generating Your Theme...</div>
            <div class="aitb-progress-status" id="progress-status">Initializing AI...</div>

            <div class="aitb-progress-steps">
                <div class="aitb-progress-step" id="step-header">
                    <span class="aitb-progress-step-icon">1</span>
                    <span>Generating Header</span>
                </div>
                <div class="aitb-progress-step" id="step-pages">
                    <span class="aitb-progress-step-icon">2</span>
                    <span>Generating Pages</span>
                </div>
                <div class="aitb-progress-step" id="step-footer">
                    <span class="aitb-progress-step-icon">3</span>
                    <span>Generating Footer</span>
                </div>
                <div class="aitb-progress-step" id="step-images">
                    <span class="aitb-progress-step-icon">4</span>
                    <span>Fetching Images</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel 3: Preview -->
    <div class="aitb-panel" data-panel="3">
        <div class="aitb-preview-grid">
            <!-- Header Preview -->
            <div class="aitb-preview-card">
                <div class="aitb-preview-header">
                    <span class="aitb-preview-title">Header Template</span>
                    <div class="aitb-preview-actions">
                        <button class="aitb-btn aitb-btn-secondary" onclick="AITB5.regenerate('header')">Regenerate</button>
                        <button class="aitb-btn aitb-btn-primary" onclick="AITB5.openInJTB('header')">Edit in JTB</button>
                    </div>
                </div>
                <div class="aitb-preview-content" id="preview-header">
                    <p style="color: var(--text-muted);">Header preview will appear here...</p>
                </div>
                <div class="aitb-preview-stats" id="stats-header"></div>
            </div>

            <!-- Pages Preview -->
            <div class="aitb-preview-card">
                <div class="aitb-preview-header">
                    <span class="aitb-preview-title">Page Layouts</span>
                    <div class="aitb-preview-actions">
                        <button class="aitb-btn aitb-btn-secondary" onclick="AITB5.regenerateAllPages()">Regenerate All</button>
                    </div>
                </div>
                <div class="aitb-preview-content" id="preview-pages">
                    <p style="color: var(--text-muted);">Pages preview will appear here...</p>
                </div>
            </div>

            <!-- Footer Preview -->
            <div class="aitb-preview-card">
                <div class="aitb-preview-header">
                    <span class="aitb-preview-title">Footer Template</span>
                    <div class="aitb-preview-actions">
                        <button class="aitb-btn aitb-btn-secondary" onclick="AITB5.regenerate('footer')">Regenerate</button>
                        <button class="aitb-btn aitb-btn-primary" onclick="AITB5.openInJTB('footer')">Edit in JTB</button>
                    </div>
                </div>
                <div class="aitb-preview-content" id="preview-footer">
                    <p style="color: var(--text-muted);">Footer preview will appear here...</p>
                </div>
                <div class="aitb-preview-stats" id="stats-footer"></div>
            </div>
        </div>

        <div class="aitb-actions">
            <button class="aitb-btn aitb-btn-secondary" onclick="AITB5.goToStep(1)">Back to Brief</button>
            <button class="aitb-btn aitb-btn-primary" onclick="AITB5.goToStep(4)">Continue to Deploy</button>
        </div>
    </div>

    <!-- Panel 4: Deploy -->
    <div class="aitb-panel" data-panel="4">
        <h2 style="margin-bottom: 24px; color: var(--text-primary);">Deploy Your Theme</h2>

        <div class="aitb-deploy-options">
            <div class="aitb-deploy-option">
                <input type="checkbox" id="opt-templates" checked>
                <div class="aitb-deploy-option-text">
                    <h4>Save Header/Footer to JTB Templates</h4>
                    <p>Store templates in jtb_templates table for use across your site</p>
                </div>
            </div>

            <div class="aitb-deploy-option">
                <input type="checkbox" id="opt-library" checked>
                <div class="aitb-deploy-option-text">
                    <h4>Save Pages to Layout Library</h4>
                    <p>Store page layouts for reuse in other projects</p>
                </div>
            </div>

            <div class="aitb-deploy-option">
                <input type="checkbox" id="opt-pages">
                <div class="aitb-deploy-option-text">
                    <h4>Create CMS Pages</h4>
                    <p>Create actual pages in your CMS (as drafts)</p>
                </div>
            </div>
        </div>

        <div class="aitb-actions">
            <button class="aitb-btn aitb-btn-secondary" onclick="AITB5.goToStep(3)">Back to Preview</button>
            <button class="aitb-btn aitb-btn-success" onclick="AITB5.deploy()">Deploy Theme</button>
        </div>
    </div>

    <!-- Panel 5: Success -->
    <div class="aitb-panel" data-panel="success">
        <div class="aitb-success">
            <div class="aitb-success-icon">✓</div>
            <h2>Theme Deployed Successfully!</h2>
            <p>Your theme components have been saved to the CMS.</p>

            <div class="aitb-success-links">
                <a href="/admin/jtb/templates" class="aitb-btn aitb-btn-secondary">View Templates</a>
                <a href="/admin/pages" class="aitb-btn aitb-btn-secondary">View Pages</a>
                <button class="aitb-btn aitb-btn-primary" onclick="location.reload()">Create Another</button>
            </div>
        </div>
    </div>
</div>

<script>
const AITB5 = {
    state: {
        theme: null,
        formData: null,
        currentStep: 1,
        isGenerating: false
    },

    init() {
        document.getElementById('brief-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.startGeneration();
        });
    },

    goToStep(step) {
        // Update step indicators
        document.querySelectorAll('.aitb-step').forEach(el => {
            const s = parseInt(el.dataset.step);
            el.classList.remove('active', 'completed');
            if (s < step) el.classList.add('completed');
            if (s === step) el.classList.add('active');
        });

        // Show panel
        document.querySelectorAll('.aitb-panel').forEach(p => p.classList.remove('active'));
        const panel = document.querySelector(`.aitb-panel[data-panel="${step}"]`);
        if (panel) panel.classList.add('active');

        this.state.currentStep = step;
    },

    showError(message) {
        const container = document.getElementById('error-container');
        container.textContent = message;
        container.style.display = 'block';
        setTimeout(() => container.style.display = 'none', 10000);
    },

    collectFormData() {
        const form = document.getElementById('brief-form');
        const formData = new FormData(form);

        // Get selected pages
        const pages = [];
        document.querySelectorAll('input[name="pages[]"]:checked').forEach(cb => {
            pages.push(cb.value);
        });

        return {
            csrf_token: formData.get('csrf_token'),
            project_name: formData.get('project_name'),
            business_name: formData.get('business_name'),
            brief: formData.get('brief'),
            industry: formData.get('industry'),
            style: formData.get('style'),
            model: formData.get('model'),
            image_source: formData.get('image_source'),
            pages: pages.length > 0 ? pages : ['Home']
        };
    },

    async startGeneration() {
        if (this.state.isGenerating) return;

        this.state.formData = this.collectFormData();
        this.state.isGenerating = true;

        this.goToStep(2);
        this.updateProgress('Generating theme...', 'step-header', 'active');

        try {
            // Call generate API
            const response = await fetch('/api/jtb/ai/generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.state.formData)
            });

            const data = await response.json();
            console.log('Generate response:', data);

            if (!data.success) {
                throw new Error(data.error || 'Generation failed');
            }

            this.state.theme = data.theme;

            // Mark steps complete
            this.updateProgress('Generation complete!', 'step-header', 'completed');
            this.updateProgress('', 'step-pages', 'completed');
            this.updateProgress('', 'step-footer', 'completed');

            // Fetch images if needed
            if (this.state.formData.image_source !== 'none') {
                this.updateProgress('Fetching images...', 'step-images', 'active');
                await this.fetchImages();
            }
            this.updateProgress('', 'step-images', 'completed');

            // Render previews and go to step 3
            this.renderPreviews();
            this.goToStep(3);

        } catch (error) {
            console.error('Generation error:', error);
            this.showError(error.message);
            this.goToStep(1);
        } finally {
            this.state.isGenerating = false;
        }
    },

    updateProgress(status, stepId, state) {
        if (status) {
            document.getElementById('progress-status').textContent = status;
        }
        if (stepId) {
            const step = document.getElementById(stepId);
            step.classList.remove('active', 'completed');
            if (state) step.classList.add(state);
        }
    },

    async fetchImages() {
        try {
            const response = await fetch('/api/jtb/ai/fetch-images', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    theme: this.state.theme,
                    image_source: this.state.formData.image_source,
                    csrf_token: this.state.formData.csrf_token
                })
            });

            const data = await response.json();
            if (data.success) {
                this.state.theme = data.theme;
            }
        } catch (error) {
            console.warn('Image fetch failed:', error);
        }
    },

    renderPreviews() {
        const theme = this.state.theme;
        if (!theme) return;

        // Header preview
        if (theme.header) {
            this.renderPreview('preview-header', theme.header);
            this.renderStats('stats-header', theme.header.stats);
        }

        // Pages preview
        this.renderPagesPreview(theme.pages);

        // Footer preview
        if (theme.footer) {
            this.renderPreview('preview-footer', theme.footer);
            this.renderStats('stats-footer', theme.footer.stats);
        }
    },

    renderPreview(containerId, component) {
        const container = document.getElementById(containerId);
        if (!component || !component.source_html) {
            container.innerHTML = '<p style="color: var(--text-muted);">No content</p>';
            return;
        }

        // Create iframe for safe HTML preview
        const iframe = document.createElement('iframe');
        iframe.className = 'aitb-preview-iframe';
        iframe.srcdoc = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body { margin: 0; font-family: system-ui, -apple-system, sans-serif; }
                    * { box-sizing: border-box; }
                </style>
            </head>
            <body>${component.source_html}</body>
            </html>
        `;
        container.innerHTML = '';
        container.appendChild(iframe);
    },

    renderPagesPreview(pages) {
        const container = document.getElementById('preview-pages');
        if (!pages || pages.length === 0) {
            container.innerHTML = '<p style="color: var(--text-muted);">No pages generated</p>';
            return;
        }

        let html = '<div class="aitb-pages-list">';
        pages.forEach((page, index) => {
            const sections = page.jtb_content?.length || 0;
            html += `
                <div class="aitb-page-item">
                    <div>
                        <span class="aitb-page-title">${page.title}</span>
                        <span style="color: var(--text-muted); margin-left: 8px; font-size: 12px;">
                            ${sections} sections
                        </span>
                    </div>
                    <div>
                        <button class="aitb-btn aitb-btn-secondary" style="padding: 6px 12px; font-size: 12px;"
                            onclick="AITB5.previewPage(${index})">Preview</button>
                        <button class="aitb-btn aitb-btn-primary" style="padding: 6px 12px; font-size: 12px;"
                            onclick="AITB5.openInJTB('page', ${index})">Edit in JTB</button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    },

    renderStats(containerId, stats) {
        const container = document.getElementById(containerId);
        if (!stats) {
            container.innerHTML = '';
            return;
        }
        container.innerHTML = `
            <span>Modules: ${stats.modules_count || 0}</span>
        `;
    },

    previewPage(index) {
        const page = this.state.theme?.pages?.[index];
        if (!page) return;

        // Open in new window
        const win = window.open('', '_blank');
        win.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>${page.title} - Preview</title>
                <style>
                    body { margin: 0; font-family: system-ui, -apple-system, sans-serif; }
                    * { box-sizing: border-box; }
                </style>
            </head>
            <body>${page.source_html}</body>
            </html>
        `);
    },

    openInJTB(type, pageIndex = 0) {
        let component;
        let title;

        if (type === 'header') {
            component = this.state.theme?.header;
            title = 'Header';
        } else if (type === 'footer') {
            component = this.state.theme?.footer;
            title = 'Footer';
        } else if (type === 'page') {
            component = this.state.theme?.pages?.[pageIndex];
            title = component?.title || 'Page';
        }

        if (!component || !component.jtb_content) {
            alert('No content available to edit');
            return;
        }

        // Prepare JTB import data
        const jtbData = {
            title: title,
            content: component.jtb_content,
            source_html: component.source_html,
            source: 'ai-theme-builder',
            timestamp: Date.now()
        };

        // Store in sessionStorage
        sessionStorage.setItem('jtb_import_data', JSON.stringify(jtbData));

        // Open JTB
        window.open('/admin/jessie-theme-builder/edit/0?import=ai-theme-builder', '_blank');
    },

    async regenerate(type, pageIndex = 0) {
        if (this.state.isGenerating) return;
        this.state.isGenerating = true;

        try {
            const response = await fetch('/api/jtb/ai/regenerate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    type: type,
                    page_index: pageIndex,
                    ...this.state.formData
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Regeneration failed');
            }

            // Update state
            if (type === 'header') {
                this.state.theme.header = data.component;
                this.renderPreview('preview-header', data.component);
                this.renderStats('stats-header', data.component.stats);
            } else if (type === 'footer') {
                this.state.theme.footer = data.component;
                this.renderPreview('preview-footer', data.component);
                this.renderStats('stats-footer', data.component.stats);
            } else if (type === 'page') {
                this.state.theme.pages[pageIndex] = data.component;
                this.renderPagesPreview(this.state.theme.pages);
            }

        } catch (error) {
            this.showError(error.message);
        } finally {
            this.state.isGenerating = false;
        }
    },

    async regenerateAllPages() {
        for (let i = 0; i < this.state.theme.pages.length; i++) {
            await this.regenerate('page', i);
        }
    },

    async deploy() {
        const options = {
            save_to_templates: document.getElementById('opt-templates').checked,
            save_to_library: document.getElementById('opt-library').checked,
            create_pages: document.getElementById('opt-pages').checked
        };

        try {
            const response = await fetch('/api/jtb/ai/deploy', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    theme: this.state.theme,
                    options: options,
                    csrf_token: this.state.formData.csrf_token
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Deployment failed');
            }

            this.goToStep('success');

        } catch (error) {
            this.showError(error.message);
        }
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => AITB5.init());
</script>

<?php
// End content buffering and include CMS admin layout
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
