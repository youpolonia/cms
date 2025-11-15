<?php
/**
 * Default Theme Layout Template
 * @var string $title Page title
 * @var string $content Main content
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
    <header>
        <?= render_widget_region('header') ?>
    </header>

<div class="container">
        <aside class="sidebar">
            <?= render_widget_region('sidebar') ?>
        </aside>

<main class="content">
            <?= $content ?>
        </main>
    </div>

<footer>
        <?= render_widget_region('footer') ?>
    </footer>
</body>
</html>
