<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_validate_or_403();
}

require_once __DIR__ . '/../../includes/thememanager.php';
require_once __DIR__ . '/../../models/settingsmodel.php';

// Check admin authentication
cms_session_start('admin');
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle theme activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_theme'])) {
    $settings = SettingsModel::getSettings();
    $settings['active_theme'] = $_POST['activate_theme'];
    SettingsModel::saveSettings($settings);
}

// Get all available themes
$themes = \includes\ThemeManager::getAvailableThemes();
$activeTheme = \includes\ThemeManager::getActiveTheme();
?><!DOCTYPE html>
<html>
<head>
    <title>Theme Management</title>
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Theme Management</h1>
        
        <div class="theme-list">
            <?php foreach ($themes as $theme): ?>
                <div class="theme-card <?= $theme === $activeTheme ? 'active' : '' ?>">
                    <h3><?= htmlspecialchars($theme) ?></h3>
                    
                    <?php $metadata = \includes\ThemeManager::getThemeMetadata($theme); ?>
                    <?php if (!empty($metadata['description'])): ?>
                        <p><?= htmlspecialchars($metadata['description']) ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="activate_theme" value="<?= htmlspecialchars($theme) ?>">
                        <button type="submit" <?= $theme === $activeTheme ? 'disabled' : '' ?>>
                            <?= $theme === $activeTheme ? 'Active' : 'Activate' 
?>                        </button>
                    </form>
                    
                    <?php $versions = \includes\ThemeManager::getThemeVersions($theme); ?>
                    <?php if (!empty($versions)): ?>
                        <div class="theme-versions">
                            <h4>Versions</h4>
                            <ul>
                                <?php foreach ($versions as $version => $data): ?>
                                    <li>
                                        v<?= $version ?> - <?= htmlspecialchars($data['created_at']) ?>
                                        <small><?= htmlspecialchars($data['notes']) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
