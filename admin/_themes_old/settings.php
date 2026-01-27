<?php
require_once __DIR__ . '/../core/csrf.php';
// Verify admin access
require_once __DIR__ . '/../includes/auth.php';
if (!is_admin()) {
    header('Location: /admin/login.php');
    exit;
}

csrf_boot('admin');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Get available themes by scanning themes directory
    $themesDir = __DIR__ . '/../../themes';
    $availableThemes = array_diff(scandir($themesDir), ['.', '..']);
    
    // Validate font input
    function validate_font($font) {
        $font = htmlspecialchars(trim($font), ENT_QUOTES);
        return preg_match('/^[a-zA-Z0-9\s\-,"\']+$/', $font) ? $font : 'Arial, sans-serif';
    }

    // Validate and save theme settings
    $settings = [
        'theme_active' => in_array($_POST['active_theme'], $availableThemes) ? $_POST['active_theme'] : 'default',
        'theme_primary_color' => preg_match('/^#[a-f0-9]{6}$/i', $_POST['primary_color']) ? $_POST['primary_color'] : '#007bff',
        'theme_title_font' => validate_font($_POST['title_font'])
    ];

    // Save to system_settings with error handling
    try {
        foreach ($settings as $key => $value) {
            $query = "REPLACE INTO system_settings (setting_key, setting_value) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            if (!$stmt->execute([$key, $value])) {
                throw new Exception("Failed to save setting: $key");
            }
        }
        $_SESSION['success_message'] = 'Theme settings saved successfully';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error saving settings: ' . $e->getMessage();
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get current settings
$currentSettings = [];
$query = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'theme_%'";
$stmt = $db->query($query);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}

// Get available themes
$themesDir = __DIR__ . '/../../themes';
$availableThemes = array_diff(scandir($themesDir), ['.', '..']);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Settings</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/header.php'; 
?>    <div class="admin-container">
        <h1>Theme Settings</h1>
        
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']); ?> 
        <?php endif; ?>
        <form method="post" class="theme-settings-form">
            <?= csrf_field();  ?>
            <div class="form-group">
                <label for="active_theme">Active Theme</label>
                <select name="active_theme" id="active_theme" class="form-control">
                    <?php foreach ($availableThemes as $theme): ?>                        <option value="<?= htmlspecialchars($theme) ?>" <?= ($currentSettings['theme_active'] ?? '') === $theme ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($theme))  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="primary_color">Primary Color</label>
                <input type="color" name="primary_color" id="primary_color" 
                       value="<?= htmlspecialchars($currentSettings['theme_primary_color'] ?? '#007bff') ?>" 
                       class="form-control">
?>            </div>
            
            <div class="form-group">
                <label for="title_font">Title Font</label>
                <input type="text" name="title_font" id="title_font" 
                       value="<?= htmlspecialchars($currentSettings['theme_title_font'] ?? 'Arial, sans-serif') ?>" 
                       class="form-control" placeholder="Enter font family (e.g. Arial, sans-serif)">
?>            </div>
            
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>

    <?php require_once __DIR__ . '/../includes/footer.php';
</body>
</html>
