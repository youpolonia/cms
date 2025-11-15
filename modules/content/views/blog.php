<?php
/**
 * Blog Post Template
 */
?><!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($content['title']) ?></title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <header>
        <h1><?= htmlspecialchars($content['title']) ?></h1>
    </header>
    <main>
        <article>
            <?= $content['body']  ?>
        </article>
        <footer>
            Published: <?= date('F j, Y', strtotime($content['published_at']))  ?>
        </footer>
    </main>
</body>
</html>
