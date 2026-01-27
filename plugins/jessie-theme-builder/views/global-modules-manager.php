<?php
/**
 * Global Modules Manager View
 * Library of reusable modules
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Variables available: $pluginUrl, $modules, $types, $count, $csrfToken
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Modules - Jessie CMS</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/builder.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($pluginUrl) ?>/assets/css/template-manager.css">
</head>
<body class="jtb-builder-page">
    <div class="jtb-template-manager">
        <!-- Header -->
        <header class="jtb-header">
            <div class="jtb-header-left">
                <a href="/admin" class="jtb-logo" title="Jessie CMS">
                    <img src="/public/assets/images/jessie-logo.svg" alt="Jessie" width="32" height="32">
                </a>
                <a href="/admin/jtb/templates" title="Back to Theme Builder">←</a>
                <span class="jtb-header-title">Global Modules Library</span>
            </div>
            <div class="jtb-header-center">
                <span class="jtb-badge"><?= (int) $count ?> Modules</span>
            </div>
            <div class="jtb-header-right">
                <div class="jtb-search-wrapper">
                    <input type="text" id="moduleSearch" class="jtb-input-text" placeholder="Search modules...">
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="jtb-template-main">
            <!-- Filter Tabs -->
            <div class="jtb-template-filters">
                <button class="jtb-category-tab active" data-type="all">
                    All (<?= (int) $count ?>)
                </button>
                <?php foreach ($types as $typeInfo): ?>
                <button class="jtb-category-tab" data-type="<?= htmlspecialchars($typeInfo['type']) ?>">
                    <?= ucfirst(htmlspecialchars($typeInfo['type'])) ?> (<?= (int) $typeInfo['count'] ?>)
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Modules Grid -->
            <div class="jtb-global-modules-container">
                <?php if (empty($modules)): ?>
                <div class="jtb-empty-state">
                    <div class="jtb-empty-state-icon">📦</div>
                    <h3 class="jtb-empty-state-title">No Global Modules Yet</h3>
                    <p class="jtb-empty-state-text">
                        Save sections, rows, or modules from the page builder to reuse them across your site.
                    </p>
                </div>
                <?php else: ?>
                <div class="jtb-global-modules-grid" id="modulesGrid">
                    <?php foreach ($modules as $type => $typeModules): ?>
                        <?php foreach ($typeModules as $module): ?>
                        <div class="jtb-global-module-card" data-id="<?= (int) $module['id'] ?>" data-type="<?= htmlspecialchars($type) ?>">
                            <div class="jtb-module-thumbnail">
                                <?php if ($module['thumbnail']): ?>
                                    <img src="<?= htmlspecialchars($module['thumbnail']) ?>" alt="">
                                <?php else: ?>
                                    <div class="jtb-module-thumbnail-placeholder">
                                        <?php if ($type === 'section'): ?>📑<?php elseif ($type === 'row'): ?>📊<?php else: ?>📦<?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="jtb-module-info">
                                <h3 class="jtb-module-name"><?= htmlspecialchars($module['name']) ?></h3>
                                <span class="jtb-module-type"><?= ucfirst(htmlspecialchars($type)) ?></span>
                                <?php if ($module['description']): ?>
                                    <p class="jtb-module-description"><?= htmlspecialchars($module['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="jtb-module-actions">
                                <button class="jtb-btn jtb-btn-sm" onclick="JTBGlobalModules.edit(<?= (int) $module['id'] ?>)">
                                    Edit
                                </button>
                                <button class="jtb-btn jtb-btn-sm" onclick="JTBGlobalModules.duplicate(<?= (int) $module['id'] ?>)">
                                    Duplicate
                                </button>
                                <button class="jtb-btn jtb-btn-sm jtb-btn-danger" onclick="JTBGlobalModules.delete(<?= (int) $module['id'] ?>, '<?= htmlspecialchars(addslashes($module['name'])) ?>')">
                                    Delete
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Notifications -->
        <div class="jtb-notifications" id="notifications"></div>
    </div>

    <script>
        // CSRF token for API calls
        window.JTB_CSRF_TOKEN = '<?= htmlspecialchars($csrfToken ?? '') ?>';
    </script>
    <script src="<?= htmlspecialchars($pluginUrl) ?>/assets/js/global-modules.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            JTBGlobalModules.init();
        });
    </script>
</body>
</html>
