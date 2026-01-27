<?php
require_once __DIR__ . '/../../includes/thememanager.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot();


// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
// Get available themes
$themes = ['core', 'corporate', 'default', 'light'];
$activeTheme = $_SESSION['active_theme'] ?? 'default';

// Process theme activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_theme'])) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    $themeName = $_POST['activate_theme'];
    if (\includes\ThemeManager::applyTheme($themeName)) {
        $activeTheme = $themeName;
        $success = "Theme activated successfully";
    } else {
        $error = "Failed to activate theme";
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Theme Management</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Theme Management</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif  ?>
        <div class="theme-grid">
            <?php foreach ($themes as $theme): ?>                <?php $metadata = \includes\ThemeManager::loadThemeMetadata($theme); 
?>                <div class="theme-card <?= $theme === $activeTheme ? 'active' : '' ?>">
                    <div class="theme-preview" style="background: <?= $metadata['preview_color'] ?? '#f0f0f0' ?>">
                        <h3><?= $metadata['name'] ?? ucfirst($theme) ?></h3>
                        <p><?= $metadata['description'] ?? 'No description available' ?></p>
                    </div>
                    <div class="theme-meta">
                        <ul>
                            <li>Version: <?= $metadata['version'] ?? '1.0' ?></li>
                            <li>Author: <?= $metadata['author'] ?? 'Unknown' ?></li>
                        </ul>
                        <form method="POST">
                            <?= csrf_field();  ?>
                            <input type="hidden" name="activate_theme" value="<?= $theme ?>">
                            <button type="submit" class="btn <?= $theme === $activeTheme ? 'btn-primary' : 'btn-secondary' ?>">
                                <?= $theme === $activeTheme ? 'Active' : 'Activate' 
?>                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach  ?>
        </div>
    </div>
</body>
</html>
