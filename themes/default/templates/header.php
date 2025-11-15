<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'CMS'; ?></title>
    <meta name="description" content="<?php echo $metaDescription ?? 'Default CMS description'; ?>">
    <link rel="canonical" href="<?php echo $canonicalUrl ?? ''; ?>">
    
    <!-- OpenGraph -->
    <meta property="og:title" content="<?php echo $title ?? 'CMS'; ?>">
    <meta property="og:description" content="<?php echo $metaDescription ?? 'Default CMS description'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $canonicalUrl ?? ''; ?>">
    
    <?php echo AssetManager::renderStyles(); ?>
</head>
<body>
    <header>
        <h1><?php echo $siteName ?? 'CMS'; ?></h1>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </nav>
    </header>
    <main>
