<?php
/**
 * System Settings View
 */
?><!DOCTYPE html>
<html>
<head>
    <title>System Settings</title>
</head>
<body>
    <h1>System Configuration</h1>
    <div class="settings">
        <?php foreach ($data['settings'] as $key => $value): ?>
            <p><?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?></p>
        <?php endforeach;  ?>
    </div>
</body>
</html>
