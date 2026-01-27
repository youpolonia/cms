<?php
/**
 * JTB Library API - Preview template
 * GET /api/jtb/library-preview/{id}
 *
 * Returns rendered HTML preview of a template
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

// Get ID from query string (router sets $_GET['post_id'] for IDs in URL)
$id = $_GET['post_id'] ?? $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><body><p>Template ID required</p></body></html>';
    exit;
}

try {
    // Ensure library tables exist
    if (!JTB_Library::tablesExist()) {
        throw new \Exception('Library not initialized');
    }

    $template = JTB_Library::get((int)$id);

    if (!$template) {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><body><p>Template not found</p></body></html>';
        exit;
    }

    // Decode content if it's a string
    $content = $template['content'];
    if (is_string($content)) {
        $content = json_decode($content, true);
    }

    // Ensure content has the correct structure for JTB_Renderer::render()
    // The render() method expects ['content' => [...sections...]]
    if (!isset($content['content'])) {
        // If content is directly an array of sections, wrap it
        if (is_array($content) && isset($content[0])) {
            $content = ['content' => $content];
        } else {
            $content = ['content' => []];
        }
    }

    // Render using JTB_Renderer::render() - the correct method
    $renderedHtml = JTB_Renderer::render($content);

    // Output full HTML page for iframe
    header('Content-Type: text/html; charset=UTF-8');

    echo '<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Preview - ' . htmlspecialchars($template['name']) . '</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/animations.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: "Plus Jakarta Sans", "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #ffffff;
            color: #1f2937;
            line-height: 1.6;
            min-width: 1200px;
            width: 1200px;
        }
        .jtb-content {
            min-height: 100vh;
            width: 1200px;
        }
        img {
            max-width: 100%;
            height: auto;
        }

        /* =====================================================
           FORCE DESKTOP LAYOUT - Override ALL responsive rules
           These must override @media (max-width: 767px) rules
           ===================================================== */

        /* Reset all columns to normal flex behavior first */
        .jtb-row > .jtb-column {
            flex: unset !important;
            max-width: unset !important;
        }

        /* 1 Column */
        .jtb-row-cols-1 > .jtb-column {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        /* 2 Columns - Equal (50% + 50%) */
        .jtb-row-cols-1-2-1-2 > .jtb-column {
            flex: 0 0 calc(50% - 15px) !important;
            max-width: calc(50% - 15px) !important;
        }

        /* 3 Columns - Equal */
        .jtb-row-cols-1-3-1-3-1-3 > .jtb-column {
            flex: 0 0 calc(33.333% - 20px) !important;
            max-width: calc(33.333% - 20px) !important;
        }

        /* 4 Columns - Equal */
        .jtb-row-cols-1-4-1-4-1-4-1-4 > .jtb-column {
            flex: 0 0 calc(25% - 22.5px) !important;
            max-width: calc(25% - 22.5px) !important;
        }

        /* 2 Columns - 2/3 + 1/3 */
        .jtb-row-cols-2-3-1-3 > .jtb-column:first-child {
            flex: 0 0 calc(66.666% - 15px) !important;
            max-width: calc(66.666% - 15px) !important;
        }
        .jtb-row-cols-2-3-1-3 > .jtb-column:last-child {
            flex: 0 0 calc(33.333% - 15px) !important;
            max-width: calc(33.333% - 15px) !important;
        }

        /* 2 Columns - 1/3 + 2/3 */
        .jtb-row-cols-1-3-2-3 > .jtb-column:first-child {
            flex: 0 0 calc(33.333% - 15px) !important;
            max-width: calc(33.333% - 15px) !important;
        }
        .jtb-row-cols-1-3-2-3 > .jtb-column:last-child {
            flex: 0 0 calc(66.666% - 15px) !important;
            max-width: calc(66.666% - 15px) !important;
        }

        /* 5 Columns */
        .jtb-row-cols-1-5-1-5-1-5-1-5-1-5 > .jtb-column {
            flex: 0 0 calc(20% - 24px) !important;
            max-width: calc(20% - 24px) !important;
        }

        /* 6 Columns */
        .jtb-row-cols-1-6-1-6-1-6-1-6-1-6-1-6 > .jtb-column {
            flex: 0 0 calc(16.666% - 25px) !important;
            max-width: calc(16.666% - 25px) !important;
        }

        /* Row - force desktop layout - NOWRAP to prevent column stacking */
        .jtb-row {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 30px !important;
        }

        .jtb-section-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        /* Force desktop menu visibility */
        .jtb-mobile-menu-toggle {
            display: none !important;
        }
        .jtb-desktop-menu {
            display: block !important;
        }
        .jtb-mobile-menu {
            display: none !important;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    ' . $renderedHtml . '
    <script src="/plugins/jessie-theme-builder/assets/js/frontend.js"></script>
</body>
</html>';

} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>
<html>
<head><title>Error</title></head>
<body>
    <div style="padding: 40px; text-align: center; color: #dc2626;">
        <h2>Preview Error</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
    </div>
</body>
</html>';
}
