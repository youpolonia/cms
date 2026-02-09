<?php
/**
 * Website Editor - Unified editor for complete websites
 * 
 * This is a NEW view that does NOT modify existing Page Builder or Template Editor.
 * It provides iframe-based click-to-edit for header + pages + footer.
 * 
 * @package JessieThemeBuilder
 * @since 2026-02-07
 */

defined('CMS_ROOT') or die('Direct access not allowed');

// Session & Auth
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    header('Location: /admin/login');
    exit;
}

// CSRF token
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
$csrfToken = $_SESSION['csrf_token'] ?? '';

// Get session ID from URL
$sessionId = $_GET['session'] ?? null;

// Page title
$pageTitle = 'Website Editor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Jessie CMS</title>
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            color: #fff;
        }
        
        .we-toolbar {
            height: 50px;
            background: #252538;
            border-bottom: 1px solid #2d2d44;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
        }
        
        .we-toolbar-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .we-container {
            display: flex;
            height: calc(100vh - 50px);
        }
        
        .we-preview {
            flex: 1;
            background: #0f0f1a;
            padding: 20px;
            overflow: auto;
            display: flex;
            justify-content: center;
        }
        
        .we-sidebar {
            width: 360px;
            background: #1a1a2e;
            border-left: 1px solid #2d2d44;
            overflow-y: auto;
        }
        
        .we-iframe {
            background: #fff;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 1400px;
            min-height: calc(100vh - 90px);
            border-radius: 4px;
        }
        
        .we-btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .we-btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
        }
        
        .we-btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .we-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #2d2d44;
            background: #252538;
            color: #fff;
            font-size: 14px;
        }
        
        .we-panel-header {
            padding: 16px;
            border-bottom: 1px solid #2d2d44;
        }
        
        .we-panel-header h3 {
            font-size: 16px;
            font-weight: 600;
        }
        
        .we-panel-content {
            padding: 16px;
        }
        
        .we-empty {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .we-status { color: #888; font-size: 13px; }
        .we-status.success { color: #10b981; }
        .we-status.error { color: #ef4444; }
    </style>
</head>
<body>
    <div class="we-toolbar">
        <div class="we-toolbar-section">
            <a href="/admin/jtb/website-builder" class="we-btn we-btn-secondary">← Back</a>
            <select class="we-select" id="we-page-select">
                <option value="">Loading...</option>
            </select>
        </div>
        <div class="we-toolbar-section">
            <span class="we-status" id="we-status">Ready</span>
        </div>
        <div class="we-toolbar-section">
            <button class="we-btn we-btn-secondary" id="we-preview-btn">👁️ Preview</button>
            <button class="we-btn we-btn-primary" id="we-save-btn">💾 Save</button>
        </div>
    </div>

    <div class="we-container">
        <div class="we-preview">
            <iframe id="we-iframe" class="we-iframe"></iframe>
        </div>
        <div class="we-sidebar">
            <div class="we-panel-header">
                <h3 id="we-panel-title">Settings</h3>
            </div>
            <div class="we-panel-content" id="we-panel-content">
                <div class="we-empty">
                    <p>👆 Click an element to edit</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.WE_CONFIG = {
            csrfToken: '<?= htmlspecialchars($csrfToken) ?>',
            sessionId: <?= $sessionId ? json_encode($sessionId) : 'null' ?>,
            apiBase: '/api/jtb'
        };
    </script>
    <script src="/plugins/jessie-theme-builder/assets/js/website-editor.js"></script>
</body>
</html>
