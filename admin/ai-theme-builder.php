<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $themeName = isset($_POST['theme_name']) ? preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($_POST['theme_name']))) : '';
    $palette   = isset($_POST['palette']) ? trim($_POST['palette']) : '';
    if ($themeName === '') {
        $result = ['type' => 'error', 'msg' => 'Theme name is required'];
    } else {
        $result = ['type' => 'info', 'msg' => 'DRY RUN: '.$themeName, 'palette' => $palette];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>AI Theme Builder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<main class="container">
    <h1>AI Theme Builder</h1>
    <?php if ($result): ?>
        <div class="notice <?=htmlspecialchars($result['type'])?>">
            <strong><?=htmlspecialchars($result['msg'])?></strong>
        </div>
        <?php if (!empty($result['palette'])): ?>
            <pre><?=htmlspecialchars($result['palette'])?></pre>
        <?php endif; ?>
    <?php endif; ?>
    <form method="post" action="">
        <?php csrf_field(); ?>
        <div>
            <label for="theme_name">Theme name</label>
            <input type="text" id="theme_name" name="theme_name" required>
        </div>
        <div>
            <label for="palette">Palette (JSON)</label>
            <textarea id="palette" name="palette" rows="6" placeholder='{"background":"#ffffff","primary":"#111111","secondary":"#666666","accent":"#0ea5e9"}'></textarea>
        </div>
        <button type="submit">Preview (Dry Run)</button>
    </form>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
