<?php
require_once __DIR__ . '/../../core/csrf.php';
// Admin Theme Switcher Controller
require_once __DIR__.'/../../includes/admin_auth.php';
require_once __DIR__.'/../../includes/settings.php';

// Get available public themes
$themesDir = __DIR__.'/../../../themes';
$themes = array_filter(scandir($themesDir), function($item) use ($themesDir) {
    return is_dir($themesDir.'/'.$item) && !in_array($item, ['.', '..', 'core', 'presets']);
});

// Handle theme switch request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    csrf_validate_or_403();
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid CSRF token'];
        header('Location: '.$_SERVER['REQUEST_URI']);
        exit;
    }

    // Validate theme
    $selectedTheme = basename($_POST['theme']);
    if (!in_array($selectedTheme, $themes)) {
        $_SESSION['admin_message'] = ['type' => 'error', 'text' => 'Invalid theme selected'];
        header('Location: '.$_SERVER['REQUEST_URI']);
        exit;
    }

    // Update active theme in settings
    update_site_setting('public_theme', $selectedTheme);
    
    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Public theme updated successfully'];
    header('Location: '.$_SERVER['REQUEST_URI']);
    exit;
}

// Get current active theme
$activeTheme = get_site_setting('public_theme', 'default_public');

// Include view
require_once __DIR__.'/../../admin/views/themes/public_switch.php';
