<?php
/**
 * Default Theme - Home Template
 */
require_once __DIR__ . '/../../../core/bootstrap.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'CMS Page'; ?></title>
    <link rel="stylesheet" href="<?php echo THEMES_DIR; ?>default/assets/css/style.css">
</head>
<body>
    <header>
        <h1>Site Header</h1>
        <nav>
            <a href="/">Home</a>
        </nav>
    </header>

    <main>
        <?php echo $content ?? ''; 
?>    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> My CMS</p>
    </footer>

    <script src="<?php echo THEMES_DIR; ?>default/assets/js/main.js"></script>
</body>
</html>
