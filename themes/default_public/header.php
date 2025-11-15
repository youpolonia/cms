<?php
/**
 * Public header template
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'My CMS Site'; ?></title>
    <link rel="stylesheet" href="/themes/default_public/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <nav class="main-nav">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/page/about">About</a></li>
                <li><a href="/page/contact">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>
