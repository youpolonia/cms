<?php
/**
 * Default Theme Header
 * Variables: $pageTitle, $pageDescription (optional)
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CMS') ?></title>
    <?php if (!empty($pageDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/themes/default/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="main-nav">
                <a href="/" class="logo">CMS</a>
                <ul class="nav-menu">
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog">Blog</a></li>
                </ul>
            </nav>
        </div>
    </header>
