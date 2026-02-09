<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php render_seo_meta(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
<?php echo generate_theme_css_variables(); ?>
<link rel="stylesheet" href="/themes/starter-photography/assets/css/style.css">
</head>
<body class="<?= get_body_class() ?>">
<header class="site-header">
  <div class="header-inner">
    <a href="/" class="site-logo"><?= esc(get_site_name()) ?></a>
    <button class="hamburger" id="menuToggle" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</header>
<!-- Full screen overlay menu -->
<div class="overlay-menu" id="overlayMenu">
  <nav class="overlay-nav">
    <?php render_menu('header', ['wrap' => false, 'class' => 'overlay-links', 'fallback_to_pages' => true]); ?>
  </nav>
</div>
<main class="site-main"><?= $content ?></main>
<footer class="site-footer">
  <div class="container footer-inner">
    <span>&copy; <?= date('Y') ?> <?= esc(get_site_name()) ?></span>
    <span class="footer-links"><a href="/contact">Contact</a> · <a href="/about">About</a> · <a href="https://instagram.com" target="_blank">Instagram</a></span>
  </div>
</footer>
<script src="/themes/starter-photography/assets/js/main.js"></script>
</body>
</html>
