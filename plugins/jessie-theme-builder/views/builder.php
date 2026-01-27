<?php
/**
 * Builder View
 * Main editor interface
 *
 * @package JessieThemeBuilder
 *
 * Variables:
 * @var int $postId
 * @var string $postTitle
 * @var string $postSlug
 * @var string $postType (page|article)
 * @var string $csrfToken
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Use $pluginUrl from controller, or get from plugin class if available
if (!isset($pluginUrl)) {
    if (class_exists(__NAMESPACE__ . '\\JessieThemeBuilderPlugin')) {
        $pluginUrl = JessieThemeBuilderPlugin::pluginUrl();
    } else {
        $pluginUrl = '/plugins/jessie-theme-builder';
    }
}

$esc = function($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
};

// Fetch Pexels API key for Stock Photos feature
$pexelsApiKey = '';
try {
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($result && !empty($result['value'])) {
        $pexelsApiKey = $result['value'];
    }
} catch (\Exception $e) {
    // Silently fail - Stock Photos will show error message if key is missing
}
?>
<!DOCTYPE html>
<html lang="en" class="jtb-builder-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit: <?php echo $esc($postTitle); ?> - Jessie Theme Builder</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@400;600&family=Montserrat:wght@400;500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Builder CSS -->
    <link rel="stylesheet" href="<?php echo $esc($pluginUrl); ?>/assets/css/builder.css?v=<?php echo time(); ?>">

    <!-- Frontend CSS (for preview) -->
    <link rel="stylesheet" href="<?php echo $esc($pluginUrl); ?>/assets/css/frontend.css?v=<?php echo time(); ?>">

    <!-- Animations CSS -->
    <link rel="stylesheet" href="<?php echo $esc($pluginUrl); ?>/assets/css/animations.css?v=<?php echo time(); ?>">

    <!-- Media Gallery CSS -->
    <link rel="stylesheet" href="<?php echo $esc($pluginUrl); ?>/assets/css/media-gallery.css?v=<?php echo time(); ?>">

    <style>
        /* Preview button styles */
        .jtb-preview-button {
            display: inline-block;
            padding: 8px 16px;
            background: #7c3aed;
            color: #fff;
            border-radius: 4px;
            font-size: 13px;
        }
        .jtb-preview-button.jtb-button-outline {
            background: transparent;
            border: 2px solid #7c3aed;
            color: #7c3aed;
        }
        .jtb-preview-button.jtb-button-ghost {
            background: transparent;
            color: #7c3aed;
        }
        .jtb-preview-heading {
            margin: 0;
        }
        .jtb-preview-text p:last-child {
            margin-bottom: 0;
        }
        .jtb-preview-placeholder {
            padding: 20px;
            background: #f3f4f6;
            color: #9ca3af;
            text-align: center;
            border-radius: 4px;
            font-size: 13px;
        }

        /* Wide dark scrollbars */
        .jtb-canvas::-webkit-scrollbar {
            width: 20px;
        }
        .jtb-canvas::-webkit-scrollbar-track {
            background: #1a1a2e;
        }
        .jtb-canvas::-webkit-scrollbar-thumb {
            background: #5a5a8c;
            border-radius: 10px;
            border: 4px solid #1a1a2e;
        }
        .jtb-canvas::-webkit-scrollbar-thumb:hover {
            background: #7a7aac;
        }

        .jtb-settings-panel::-webkit-scrollbar {
            width: 16px;
        }
        .jtb-settings-panel::-webkit-scrollbar-track {
            background: #1e1e32;
        }
        .jtb-settings-panel::-webkit-scrollbar-thumb {
            background: #5a5a8c;
            border-radius: 8px;
            border: 3px solid #1e1e32;
        }
        .jtb-settings-panel::-webkit-scrollbar-thumb:hover {
            background: #7a7aac;
        }
    </style>
</head>
<body class="jtb-builder-page">

    <div class="jtb-builder">

        <!-- Header -->
        <header class="jtb-header">
            <div class="jtb-header-left">
                <a href="/admin" class="jtb-logo" title="Jessie CMS">
                    <img src="/public/assets/images/jessie-logo.svg" alt="Jessie" width="32" height="32">
                </a>
                <a href="javascript:history.back()" class="jtb-back-btn" title="Go back">← Back</a>
                <span class="jtb-header-title">Editing: <strong><?php echo $esc($postTitle); ?></strong></span>
            </div>

            <div class="jtb-header-center">
                <div class="jtb-device-switcher">
                    <button class="jtb-device-btn active" data-device="desktop" title="Desktop">🖥️</button>
                    <button class="jtb-device-btn" data-device="tablet" title="Tablet">📱</button>
                    <button class="jtb-device-btn" data-device="phone" title="Phone">📲</button>
                </div>
            </div>

            <div class="jtb-header-right">
                <button class="jtb-btn" data-action="undo" title="Undo (Ctrl+Z)">↩️ Undo</button>
                <button class="jtb-btn" data-action="redo" title="Redo (Ctrl+Y)">↪️ Redo</button>
                <button class="jtb-btn" data-action="preview" title="Preview">👁️ Preview</button>
                <button class="jtb-btn jtb-btn-primary" data-action="save" title="Save (Ctrl+S)">💾 Save</button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="jtb-main">

            <!-- Canvas -->
            <div class="jtb-canvas jtb-preview-desktop">
                <div class="jtb-canvas-inner">
                    <div class="jtb-loading">
                        <div class="jtb-spinner"></div>
                        <p>Loading builder...</p>
                    </div>
                </div>
            </div>

            <!-- Settings Panel (Right Sidebar) -->
            <aside class="jtb-settings-panel">
                <div class="jtb-settings-empty">
                    <div class="jtb-empty-icon">⚙️</div>
                    <p>Select a module to edit its settings</p>
                </div>
            </aside>

        </main>

    </div>

    <!-- Notifications Container -->
    <div class="jtb-notifications"></div>

    <!-- Media Gallery Modal -->
    <?php
    require_once dirname(__DIR__) . '/includes/jtb-media-gallery.php';
    jtb_render_media_gallery_modal($csrfToken, $pexelsApiKey);
    ?>

    <!-- JavaScript -->
    <script src="<?php echo $esc($pluginUrl); ?>/assets/js/feather-icons.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo $esc($pluginUrl); ?>/assets/js/builder.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo $esc($pluginUrl); ?>/assets/js/settings-panel.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo $esc($pluginUrl); ?>/assets/js/fields.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo $esc($pluginUrl); ?>/assets/js/media-gallery.js?v=<?php echo time(); ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check for AI Theme Builder import
            const urlParams = new URLSearchParams(window.location.search);
            const importSource = urlParams.get('import');
            let importedContent = null;
            let importTitle = null;

            if (importSource === 'ai-theme-builder') {
                try {
                    // Try localStorage first (shared between tabs), then sessionStorage
                    const storedData = localStorage.getItem('jtb_import_data') || sessionStorage.getItem('jtb_import_data');
                    if (storedData) {
                        const importData = JSON.parse(storedData);
                        // Clear storage after reading
                        localStorage.removeItem('jtb_import_data');
                        sessionStorage.removeItem('jtb_import_data');

                        if (importData.content && Array.isArray(importData.content)) {
                            // Wrap in JTB expected structure: {version, content: [...]}
                            importedContent = {
                                version: '1.0',
                                content: importData.content
                            };
                            importTitle = importData.title || 'AI Generated Layout';
                            console.log('AI Theme Builder import loaded:', {
                                title: importTitle,
                                modulesCount: importedContent.length
                            });

                            // Update page title
                            document.title = 'Edit: ' + importTitle + ' - Jessie Theme Builder';
                            const titleEl = document.querySelector('.jtb-header-title strong');
                            if (titleEl) titleEl.textContent = importTitle;
                        }
                    }
                } catch (e) {
                    console.error('Failed to parse import data:', e);
                }
            }

            JTB.init({
                postId: importedContent ? 0 : <?php echo (int) $postId; ?>,
                postType: '<?php echo $esc($postType ?? 'page'); ?>',
                csrfToken: '<?php echo $esc($csrfToken); ?>',
                apiUrl: '/api/jtb',
                pexelsApiKey: '<?php echo $esc($pexelsApiKey); ?>',
                content: importedContent // If set, JTB will use this instead of loading from API
            });
        });
    </script>

</body>
</html>
