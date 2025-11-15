<?php
/**
 * Base template layout for the CMS
 * 
 * Template hierarchy:
 * 1. layout.php (base structure)
 * 2. page.php/post.php/company.php (content type specific)
 * 3. default_page.php (fallback)
 */

declare(strict_types=1);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CMS Page') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? '') ?>">
    
    <!-- Basic accessibility features -->
    <meta name="theme-color" content="#ffffff">
    <link rel="icon" href="/favicon.ico">
    
    <!-- Mobile responsive meta tags -->
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta http-equiv="cleartype" content="on">
    
    <?php if (isset($styles)): ?>        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
        <?php endforeach; ?>    <?php endif; ?>
</head>
<body>
    <header role="banner">
        <?php if (isset($headerContent)): ?>            <?= $headerContent ?>        <?php else: ?>
            <h1><?= htmlspecialchars($siteTitle ?? 'CMS') ?></h1>
            <nav role="navigation">
                <?= $navigation ?? '' ?>
            </nav>
        <?php endif; ?>
    </header>

    <main role="main">
        <?= $content ?>
    </main>

    <footer role="contentinfo">
        <?= $footerContent ?? '' ?>
    </footer>

    <?php if (isset($scripts)): ?>        <?php foreach ($scripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>    <?php endif; ?>
</body>
</html>
