<?php
/**
 * Template Editor View
 * Full-screen editor for templates (similar to builder.php)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Variables available: $pluginUrl, $template, $templateId, $content, $templateType, $modules, $pageTypes, $csrfToken
$isNew = $template === null;
$templateName = $template['name'] ?? 'New ' . ucfirst($templateType) . ' Template';
?>
<!DOCTYPE html>
<html lang="en" class="jtb-builder-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($templateName) ?> - Theme Builder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/builder.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/template-manager.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/animations.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/media-gallery.css?v=<?= time() ?>">
    <!-- Full AI Panel Styles (same as Page Builder) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/ai-panel.css?v=<?= time() ?>">
    <!-- Unified Theme System - Base Module Styles -->
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/jtb-base-modules.css?v=<?= time() ?>">
</head>
<body class="jtb-builder-page">
    <div class="jtb-builder">
        <!-- Header -->
        <header class="jtb-header">
            <div class="jtb-header-left">
                <a href="/admin" class="jtb-logo" title="Jessie CMS">
                    <img src="/public/assets/images/jessie-logo.svg" alt="Jessie" width="32" height="32">
                </a>
                <a href="/admin/jtb/templates" title="Back to Theme Builder">←</a>
                <input type="text" id="templateName" class="jtb-header-title-input" value="<?= htmlspecialchars($templateName) ?>">
                <span class="jtb-template-type-badge jtb-badge-<?= htmlspecialchars($templateType) ?>">
                    <?= ucfirst($templateType) ?>
                </span>
            </div>
            <div class="jtb-header-center jtb-device-switcher">
                <button class="jtb-device-btn active" data-device="desktop" title="Desktop">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </button>
                <button class="jtb-device-btn" data-device="tablet" title="Tablet">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </button>
                <button class="jtb-device-btn" data-device="phone" title="Phone">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="jtb-header-right">
                <button class="jtb-btn" id="conditionsBtn" onclick="JTBTemplateEditor.toggleConditions()">
                    Conditions
                    <?php if ($template && !empty($template['conditions'])): ?>
                        <span class="jtb-badge-count"><?= count($template['conditions']) ?></span>
                    <?php endif; ?>
                </button>
                <button class="jtb-btn" onclick="JTBTemplateEditor.preview()">Preview</button>
                <button class="jtb-btn jtb-btn-primary" onclick="JTBTemplateEditor.save()">Save Template</button>
            </div>
        </header>

        <!-- Main -->
        <div class="jtb-main jtb-main-no-sidebar">
            <!-- Canvas (Full Width) -->
            <main class="jtb-canvas jtb-preview-desktop" id="canvas">
                <div class="jtb-canvas-inner" id="canvasInner">
                    <!-- Content rendered here -->
                    <?php if (empty($content['content'])): ?>
                    <div class="jtb-empty-state">
                        <div class="jtb-empty-state-icon">📄</div>
                        <h3 class="jtb-empty-state-title">Start Building Your Page</h3>
                        <p class="jtb-empty-state-text">Click the button below to add your first section</p>
                        <div class="jtb-empty-state-actions">
                            <button class="jtb-btn" onclick="JTB.addSection()">+ Add Section</button>
                            <button class="jtb-btn" onclick="JTB.showLayoutPicker()">Choose Layout</button>
                            <button class="jtb-btn jtb-btn-primary" onclick="JTB.showLayoutLibrary()">From Library</button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>

            <!-- Settings Panel (Right Sidebar) -->
            <aside class="jtb-settings-panel" id="settingsPanel">
                <!-- Conditions Panel (hidden by default) -->
                <div class="jtb-conditions-panel" id="conditionsPanel" style="display: none;">
                    <div class="jtb-settings-header">
                        <span class="jtb-settings-icon">⚙️</span>
                        <h3 class="jtb-settings-title">Template Conditions</h3>
                        <button class="jtb-settings-close" onclick="JTBTemplateEditor.toggleConditions()">&times;</button>
                    </div>
                    <div class="jtb-conditions-content">
                        <!-- Use On Section -->
                        <div class="jtb-toggle-group open">
                            <div class="jtb-toggle-header">
                                <span class="jtb-toggle-icon"></span>
                                <span class="jtb-toggle-label">Use On (Display template on)</span>
                            </div>
                            <div class="jtb-toggle-content">
                                <div class="jtb-conditions-list" id="includeConditions">
                                    <!-- Conditions rendered by JS -->
                                </div>
                                <button class="jtb-btn jtb-btn-sm jtb-btn-add-condition" onclick="JTBConditionsBuilder.addCondition('include')">
                                    + Add Condition
                                </button>
                            </div>
                        </div>

                        <!-- Exclude From Section -->
                        <div class="jtb-toggle-group">
                            <div class="jtb-toggle-header">
                                <span class="jtb-toggle-icon"></span>
                                <span class="jtb-toggle-label">Exclude From (Don't display on)</span>
                            </div>
                            <div class="jtb-toggle-content">
                                <div class="jtb-conditions-list" id="excludeConditions">
                                    <!-- Conditions rendered by JS -->
                                </div>
                                <button class="jtb-btn jtb-btn-sm jtb-btn-add-condition" onclick="JTBConditionsBuilder.addCondition('exclude')">
                                    + Add Exclusion
                                </button>
                            </div>
                        </div>

                        <!-- Default Toggle -->
                        <div class="jtb-field jtb-default-toggle">
                            <label class="jtb-checkbox-item">
                                <input type="checkbox" id="isDefault" <?= ($template && $template['is_default']) ? 'checked' : '' ?>>
                                <span class="jtb-checkbox-label">Set as default <?= htmlspecialchars($templateType) ?></span>
                            </label>
                            <p class="jtb-field-description">
                                Default templates are used when no other template matches
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Module Settings Panel (shown when module selected) -->
                <div class="jtb-module-settings" id="moduleSettings">
                    <div class="jtb-settings-empty">
                        <div class="jtb-empty-icon">🖱️</div>
                        <p>Click on a module to edit its settings</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Condition Picker Modal -->
    <div class="jtb-modal-overlay" id="conditionModal" style="display: none;">
        <div class="jtb-modal">
            <div class="jtb-modal-header">
                <h3 class="jtb-modal-title">Select Condition</h3>
                <button class="jtb-modal-close" onclick="JTBConditionsBuilder.hideConditionModal()">&times;</button>
            </div>
            <div class="jtb-modal-body">
                <div class="jtb-field">
                    <label class="jtb-field-label">Page Type</label>
                    <select id="conditionPageType" class="jtb-input-select" onchange="JTBConditionsBuilder.onPageTypeChange()">
                        <option value="">Select page type...</option>
                        <?php foreach ($pageTypes as $value => $config): ?>
                        <option value="<?= htmlspecialchars($value) ?>"
                                data-has-objects="<?= $config['has_objects'] ? 'true' : 'false' ?>">
                            <?= htmlspecialchars($config['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="jtb-field" id="objectSelectWrapper" style="display: none;">
                    <label class="jtb-field-label" id="objectSelectLabel">Select Item</label>
                    <select id="conditionObjectId" class="jtb-input-select">
                        <option value="">All</option>
                    </select>
                </div>
            </div>
            <div class="jtb-modal-footer">
                <button class="jtb-btn" onclick="JTBConditionsBuilder.hideConditionModal()">Cancel</button>
                <button class="jtb-btn jtb-btn-primary" onclick="JTBConditionsBuilder.saveCondition()">Add Condition</button>
            </div>
        </div>
    </div>

    <!-- Module Picker Modal -->
    <div class="jtb-modal-overlay" id="moduleModal" style="display: none;">
        <div class="jtb-modal jtb-modal-large">
            <div class="jtb-modal-header">
                <h3 class="jtb-modal-title">Select Module</h3>
                <button class="jtb-modal-close" onclick="JTB.hideModuleModal()">&times;</button>
            </div>
            <div class="jtb-modal-body">
                <div class="jtb-category-tabs" id="modalCategoryTabs">
                    <!-- Categories rendered by JS -->
                </div>
                <div class="jtb-module-grid" id="modalModuleGrid">
                    <!-- Modules rendered by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="jtb-notifications" id="notifications"></div>

    <!-- Full AI Panel (same as Page Builder) -->
    <?php
    // Use TEMPLATE-SPECIFIC AI Panel (NOT Page Builder AI Panel!)
    // This panel has header/footer/body specific options
    require_once dirname(__DIR__) . '/views/ai-panel-template.php';
    ?>

    <!-- Media Gallery Modal -->
    <?php
    require_once dirname(__DIR__) . '/includes/jtb-media-gallery.php';
    jtb_render_media_gallery_modal($csrfToken ?? '');
    ?>

    <!-- Scripts -->
    <script>
        // Template data
        window.JTB_TEMPLATE_DATA = {
            id: <?= $templateId ? (int) $templateId : 'null' ?>,
            name: <?= json_encode($templateName) ?>,
            type: <?= json_encode($templateType) ?>,
            content: <?= json_encode($content) ?>,
            conditions: <?= json_encode($template['conditions'] ?? []) ?>,
            isDefault: <?= ($template && $template['is_default']) ? 'true' : 'false' ?>,
            isNew: <?= $isNew ? 'true' : 'false' ?>
        };

        // Modules data
        window.JTB_MODULES = <?= json_encode($modules) ?>;

        // Page types for conditions
        window.JTB_PAGE_TYPES = <?= json_encode($pageTypes) ?>;

        // CSRF token for API calls
        window.JTB_CSRF_TOKEN = '<?= htmlspecialchars($csrfToken ?? '') ?>';

        // AI Panel context for templates (tells AI panel we're in template mode)
        window.JTB_AI_CONTEXT = {
            mode: 'template',
            templateType: <?= json_encode($templateType) ?>,
            templateId: <?= $templateId ? (int) $templateId : 'null' ?>
        };
    </script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/builder.js"></script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/settings-panel.js"></script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/fields.js"></script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/media-gallery.js?v=<?= time() ?>"></script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/conditions-builder.js"></script>
    <!-- Full AI Panel JS (same as Page Builder) -->
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/ai-panel.js?v=<?= time() ?>"></script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/template-editor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            JTBTemplateEditor.init(window.JTB_TEMPLATE_DATA);

            // Initialize AI Panel with template context
            if (typeof JTB_AI !== 'undefined' && typeof JTB_AI.init === 'function') {
                JTB_AI.init({
                    mode: 'template',
                    templateType: window.JTB_AI_CONTEXT.templateType,
                    templateId: window.JTB_AI_CONTEXT.templateId,
                    csrfToken: window.JTB_CSRF_TOKEN
                });
            }
        });
    </script>
</body>
</html>
