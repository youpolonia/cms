<?php
/**
 * JTB AI Panel for Theme Builder (Templates)
 *
 * Specialized AI interface for generating header, footer, and body templates.
 * Different from Page Builder - focused on template-specific options.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

$csrfToken = function_exists('csrf_token') ? csrf_token() : ($_SESSION['csrf_token'] ?? '');
$devMode = defined('DEV_MODE') && DEV_MODE === true;

// Get template type from context
$templateType = $templateType ?? 'header';
$templateId = $templateId ?? null;
?>

<!-- AI Toggle Button (FAB) -->
<button type="button" id="jtb-ai-toggle" class="jtb-ai-toggle" title="AI <?php echo ucfirst(htmlspecialchars($templateType)); ?> Generator">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/>
        <circle cx="12" cy="12" r="3"/>
        <path d="M12 6v2M12 16v2M6 12h2M16 12h2"/>
    </svg>
    <span>AI</span>
</button>

<!-- AI Panel for Templates -->
<div id="jtb-ai-panel" class="jtb-ai-panel jtb-ai-panel-template">
    <div class="jtb-ai-panel-overlay"></div>

    <!-- Loading Overlay -->
    <div id="jtb-ai-loading-overlay" class="jtb-ai-loading-overlay">
        <div class="jtb-ai-loading-content">
            <div class="jtb-ai-loading-spinner">
                <svg viewBox="0 0 50 50">
                    <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round">
                        <animate attributeName="stroke-dasharray" dur="1.5s" repeatCount="indefinite" values="1,150;90,150;90,150"/>
                        <animate attributeName="stroke-dashoffset" dur="1.5s" repeatCount="indefinite" values="0;-35;-124"/>
                    </circle>
                </svg>
            </div>
            <div class="jtb-ai-loading-title" id="jtb-ai-loading-title">Generating your <?php echo htmlspecialchars($templateType); ?>...</div>
            <div class="jtb-ai-loading-steps">
                <div class="jtb-ai-loading-step active" data-step="1">
                    <span class="step-icon">✓</span>
                    <span class="step-text">Analyzing requirements</span>
                </div>
                <div class="jtb-ai-loading-step" data-step="2">
                    <span class="step-icon">○</span>
                    <span class="step-text">Designing structure</span>
                </div>
                <div class="jtb-ai-loading-step" data-step="3">
                    <span class="step-icon">○</span>
                    <span class="step-text">Applying styles</span>
                </div>
                <div class="jtb-ai-loading-step" data-step="4">
                    <span class="step-icon">○</span>
                    <span class="step-text">Finalizing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="jtb-ai-panel-header">
        <div class="jtb-ai-panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/>
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 6v2M12 16v2M6 12h2M16 12h2"/>
            </svg>
            <span>AI <?php echo ucfirst(htmlspecialchars($templateType)); ?> Generator</span>
        </div>
        <button type="button" class="jtb-ai-panel-close" title="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Body -->
    <div class="jtb-ai-panel-body">
        <div class="jtb-ai-form">

            <!-- Description -->
            <div class="jtb-ai-field jtb-ai-field-primary">
                <label>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Describe your <?php echo htmlspecialchars($templateType); ?>
                </label>
                <textarea id="jtb-ai-template-prompt" rows="3" placeholder="<?php
                    $placeholders = [
                        'header' => 'e.g., Modern header with logo on left, horizontal menu in center, and CTA button on right. Should be sticky with transparent option for hero sections.',
                        'footer' => 'e.g., 4-column footer with logo and description, quick links, contact info, and newsletter signup. Include social icons and copyright bar.',
                        'body' => 'e.g., Blog post layout with large featured image, title, meta info, content area, author box at bottom, and related posts section.'
                    ];
                    echo $placeholders[$templateType] ?? 'Describe what you want...';
                ?>"></textarea>
                <div class="jtb-ai-field-hint">
                    Be specific about layout, modules, and style preferences.
                </div>
            </div>

            <?php if ($templateType === 'header'): ?>
            <!-- HEADER OPTIONS -->
            <div class="jtb-ai-field">
                <label>Header Style</label>
                <div class="jtb-ai-intent-grid jtb-ai-intent-grid-compact">
                    <button type="button" class="jtb-ai-intent-btn active" data-intent="classic">
                        <div class="jtb-ai-intent-icon">📋</div>
                        <div class="jtb-ai-intent-label">Classic</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="centered">
                        <div class="jtb-ai-intent-icon">⬛</div>
                        <div class="jtb-ai-intent-label">Centered</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="split">
                        <div class="jtb-ai-intent-icon">↔️</div>
                        <div class="jtb-ai-intent-label">Split</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="minimal">
                        <div class="jtb-ai-intent-icon">➖</div>
                        <div class="jtb-ai-intent-label">Minimal</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="mega">
                        <div class="jtb-ai-intent-icon">📊</div>
                        <div class="jtb-ai-intent-label">Mega Menu</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="transparent">
                        <div class="jtb-ai-intent-icon">👻</div>
                        <div class="jtb-ai-intent-label">Transparent</div>
                    </button>
                </div>
            </div>

            <div class="jtb-ai-field-row">
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Logo Position</label>
                    <select id="jtb-ai-header-logo">
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                </div>
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Navigation Style</label>
                    <select id="jtb-ai-header-nav">
                        <option value="horizontal">Horizontal Menu</option>
                        <option value="hamburger">Hamburger (Mobile-first)</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="mega">Mega Menu</option>
                    </select>
                </div>
            </div>

            <div class="jtb-ai-field">
                <label>Header Features</label>
                <div class="jtb-ai-checkbox-group">
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-header-sticky" checked>
                        <span>Sticky Header</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-header-search">
                        <span>Search Icon</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-header-cta" checked>
                        <span>CTA Button</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-header-social">
                        <span>Social Icons</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-header-topbar">
                        <span>Top Bar (phone/email)</span>
                    </label>
                </div>
            </div>

            <?php elseif ($templateType === 'footer'): ?>
            <!-- FOOTER OPTIONS -->
            <div class="jtb-ai-field">
                <label>Footer Style</label>
                <div class="jtb-ai-intent-grid jtb-ai-intent-grid-compact">
                    <button type="button" class="jtb-ai-intent-btn active" data-intent="columns">
                        <div class="jtb-ai-intent-icon">📊</div>
                        <div class="jtb-ai-intent-label">Multi-Column</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="centered">
                        <div class="jtb-ai-intent-icon">⬛</div>
                        <div class="jtb-ai-intent-label">Centered</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="minimal">
                        <div class="jtb-ai-intent-icon">➖</div>
                        <div class="jtb-ai-intent-label">Minimal</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="big">
                        <div class="jtb-ai-intent-icon">📋</div>
                        <div class="jtb-ai-intent-label">Big Footer</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="cta">
                        <div class="jtb-ai-intent-icon">🎯</div>
                        <div class="jtb-ai-intent-label">CTA Footer</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="dark">
                        <div class="jtb-ai-intent-icon">🌙</div>
                        <div class="jtb-ai-intent-label">Dark Footer</div>
                    </button>
                </div>
            </div>

            <div class="jtb-ai-field-row">
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Number of Columns</label>
                    <select id="jtb-ai-footer-columns">
                        <option value="2">2 Columns</option>
                        <option value="3">3 Columns</option>
                        <option value="4" selected>4 Columns</option>
                        <option value="5">5 Columns</option>
                    </select>
                </div>
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Background Style</label>
                    <select id="jtb-ai-footer-bg">
                        <option value="dark">Dark</option>
                        <option value="light">Light</option>
                        <option value="gradient">Gradient</option>
                        <option value="image">Background Image</option>
                    </select>
                </div>
            </div>

            <div class="jtb-ai-field">
                <label>Footer Elements</label>
                <div class="jtb-ai-checkbox-group">
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-footer-logo" checked>
                        <span>Logo</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-footer-menu" checked>
                        <span>Navigation Links</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-footer-social" checked>
                        <span>Social Icons</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-footer-newsletter">
                        <span>Newsletter Signup</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-footer-contact" checked>
                        <span>Contact Info</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-footer-copyright" checked>
                        <span>Copyright Bar</span>
                    </label>
                </div>
            </div>

            <?php elseif ($templateType === 'body'): ?>
            <!-- BODY TEMPLATE OPTIONS -->
            <div class="jtb-ai-field">
                <label>Template Type</label>
                <div class="jtb-ai-intent-grid jtb-ai-intent-grid-compact">
                    <button type="button" class="jtb-ai-intent-btn active" data-intent="single_post">
                        <div class="jtb-ai-intent-icon">📝</div>
                        <div class="jtb-ai-intent-label">Single Post</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="single_page">
                        <div class="jtb-ai-intent-icon">📄</div>
                        <div class="jtb-ai-intent-label">Single Page</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="archive">
                        <div class="jtb-ai-intent-icon">📚</div>
                        <div class="jtb-ai-intent-label">Archive/Blog</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="product">
                        <div class="jtb-ai-intent-icon">🛒</div>
                        <div class="jtb-ai-intent-label">Product</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="portfolio">
                        <div class="jtb-ai-intent-icon">🎨</div>
                        <div class="jtb-ai-intent-label">Portfolio Item</div>
                    </button>
                    <button type="button" class="jtb-ai-intent-btn" data-intent="404">
                        <div class="jtb-ai-intent-icon">❌</div>
                        <div class="jtb-ai-intent-label">404 Page</div>
                    </button>
                </div>
            </div>

            <div class="jtb-ai-field-row">
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Layout</label>
                    <select id="jtb-ai-body-layout">
                        <option value="full">Full Width</option>
                        <option value="sidebar_right" selected>Sidebar Right</option>
                        <option value="sidebar_left">Sidebar Left</option>
                        <option value="narrow">Narrow Content</option>
                    </select>
                </div>
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Featured Image</label>
                    <select id="jtb-ai-body-featured">
                        <option value="large" selected>Large (above title)</option>
                        <option value="medium">Medium (inline)</option>
                        <option value="background">Background (behind title)</option>
                        <option value="none">No featured image</option>
                    </select>
                </div>
            </div>

            <div class="jtb-ai-field">
                <label>Body Elements</label>
                <div class="jtb-ai-checkbox-group">
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-title" checked>
                        <span>Post Title</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-meta" checked>
                        <span>Post Meta (date, author)</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-content" checked>
                        <span>Post Content</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-author" checked>
                        <span>Author Box</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-related">
                        <span>Related Posts</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-comments">
                        <span>Comments Section</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-breadcrumbs">
                        <span>Breadcrumbs</span>
                    </label>
                    <label class="jtb-ai-checkbox">
                        <input type="checkbox" id="jtb-ai-body-navigation">
                        <span>Post Navigation (prev/next)</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <!-- Common: Visual Style -->
            <div class="jtb-ai-field-row">
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Visual Style</label>
                    <select id="jtb-ai-template-style">
                        <option value="modern">Modern</option>
                        <option value="minimal">Minimal</option>
                        <option value="bold">Bold</option>
                        <option value="elegant">Elegant</option>
                        <option value="corporate">Corporate</option>
                    </select>
                </div>
                <div class="jtb-ai-field jtb-ai-field-half">
                    <label>Industry</label>
                    <select id="jtb-ai-template-industry">
                        <option value="general">General</option>
                        <option value="technology">Technology</option>
                        <option value="business">Business</option>
                        <option value="creative">Creative</option>
                        <option value="healthcare">Healthcare</option>
                        <option value="education">Education</option>
                        <option value="ecommerce">E-commerce</option>
                        <option value="restaurant">Restaurant</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <div class="jtb-ai-panel-footer">
        <button type="button" id="jtb-ai-generate-template-btn" class="jtb-ai-btn jtb-ai-btn-primary jtb-ai-btn-full">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
            </svg>
            <span id="jtb-ai-generate-btn-text">Generate <?php echo ucfirst(htmlspecialchars($templateType)); ?></span>
        </button>
    </div>
</div>

<!-- Preview Modal -->
<div id="jtb-ai-preview-modal" class="jtb-ai-modal">
    <div class="jtb-ai-modal-content jtb-ai-modal-large">
        <div class="jtb-ai-modal-header">
            <h3>Preview Generated <?php echo ucfirst(htmlspecialchars($templateType)); ?></h3>
            <button type="button" class="jtb-ai-modal-close">&times;</button>
        </div>
        <div class="jtb-ai-modal-body">
            <div class="jtb-ai-preview-frame"></div>
        </div>
        <div class="jtb-ai-modal-footer">
            <button type="button" class="jtb-ai-btn jtb-ai-btn-secondary" id="jtb-ai-regenerate-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                    <path d="M23 4v6h-6M1 20v-6h6"/>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                </svg>
                Regenerate
            </button>
            <button type="button" class="jtb-ai-btn jtb-ai-btn-primary" id="jtb-ai-apply-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                    <path d="M20 6L9 17l-5-5"/>
                </svg>
                Apply to Canvas
            </button>
        </div>
    </div>
</div>

<script>
// Initialize AI Panel for Template mode
document.addEventListener('DOMContentLoaded', function() {
    if (typeof JTB_AI !== 'undefined' && JTB_AI.init) {
        JTB_AI.init({
            csrfToken: <?php echo json_encode($csrfToken); ?>,
            mode: 'template',
            templateType: <?php echo json_encode($templateType); ?>,
            templateId: <?php echo json_encode($templateId); ?>
        });
    }
});
</script>
