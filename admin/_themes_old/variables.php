<?php
require_once __DIR__.'/../../includes/theme_functions.php';
require_once __DIR__.'/../../core/csrf.php';

csrf_boot();

// Check admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$currentTheme = $_GET['theme'] ?? 'default';
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Update existing variables
    foreach ($_POST['variables'] as $name => $value) {
        set_theme_variable($name, $value, null, $currentTheme);
    }
    
    // Add new variable if provided
    if (!empty($_POST['new_variable_name'])) {
        $type = $_POST['new_variable_type'] ?? 'string';
        $value = $_POST['new_variable_value'];
        
        // Type conversion
        switch ($type) {
            case 'number':
                $value = is_numeric($value) ? $value + 0 : $value;
                break;
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'json':
                $value = json_decode($value, true) ?? $value;
                break;
        }
        
        set_theme_variable($_POST['new_variable_name'], $value, $type, $currentTheme);
    }
    
    $message = 'Theme variables updated successfully';
    $themeVars = ThemeVariableManager::getInstance()->getAllVariables($currentTheme); // Refresh list
}

// Get all variables for current theme
$themeVars = ThemeVariableManager::getInstance()->getAllVariables($currentTheme);

?><!DOCTYPE html>
<html>
<head>
    <title>Theme Variables</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Theme Variables: <?= htmlspecialchars($currentTheme) ?></h1>
        
        <?php if ($message): ?>
            <div class="alert success"><?= $message ?></div>
        <?php endif;  ?>
        <form method="post">
            <?= csrf_field();  ?>
            <div class="form-group">
                <label>Theme:</label>
                <select name="theme" onchange="this.form.submit()">
                    <?php foreach (get_available_themes() as $theme): ?>                        <option value="<?= $theme ?>" <?= $theme === $currentTheme ? 'selected' : '' ?>>
                            <?= $theme  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
            </div>

            <div class="variables-list">
                <?php foreach ($themeVars as $name => $value): ?>
                    <div class="form-group variable-item">
                        <label><?= htmlspecialchars($name) ?></label>
                        <input type="text" name="variables[<?= htmlspecialchars($name) ?>]"
                               value="<?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?>">
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="
if(confirm('Delete this variable?')) { this.closest('.variable-item').remove(); }">
                            Delete
                        </button>
                    </div>
                <?php endforeach;  ?>
            </div>

            <div class="form-group">
                <h3>Add New Variable</h3>
                <div class="new-variable">
                    <input type="text" name="new_variable_name" placeholder="Variable name" class="form-control">
                    <input type="text" name="new_variable_value" placeholder="Value" class="form-control">
                    <select name="new_variable_type" class="form-control">
                        <option value="string">String</option>
                        <option value="number">Number</option>
                        <option value="boolean">Boolean</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
