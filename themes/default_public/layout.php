<?php
/**
 * Base template structure for public theme
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'CMS Site'; ?></title>
    <link rel="stylesheet" href="/themes/default_public/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/header.php'; 
?>    <main>
        <?php echo $content ?? ''; 
?>    </main>

    <?php require_once __DIR__ . '/footer.php'; 
?></body>
</html>
