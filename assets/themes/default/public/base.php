<?php
/**
 * Base template for theme inheritance
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->get('title', 'My CMS') ?></title>
    <link rel="stylesheet" href="/themes/default/css/main.css">
</head>
<body>
    <header class="site-header">
        <h1><a href="/">My CMS</a></h1>
        <nav>
            <a href="/">Home</a>
            <a href="/blog">Blog</a>
        </nav>
    </header>

    <main class="content">
        <?php $this->yield('content') 
?>    </main>

    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> My CMS</p>
    </footer>
</body>
</html>
