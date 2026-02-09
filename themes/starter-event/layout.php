<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?= render_seo_meta(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<?= generate_theme_css_variables(); ?>
<link rel="stylesheet" href="/themes/starter-event/assets/css/style.css">
</head>
<body class="<?= get_body_class() ?>">
<header class="site-header">
  <div class="container header-inner">
    <a href="/" class="site-logo"><?= esc(get_site_name()) ?></a>
    <nav class="main-nav">
      <?= render_menu('header', ['wrap' => false, 'class' => 'nav-links', 'fallback_to_pages' => true]); ?>
    </nav>
    <a href="/contact" class="nav-cta">Get Tickets 🎟️</a>
    <button class="mobile-toggle" aria-label="Menu">☰</button>
  </div>
</header>
<main class="site-main"><?= $content ?></main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-col"><h4><?= esc(get_site_name()) ?></h4><p>The premier tech conference bringing together innovators, creators, and leaders.</p></div>
    <div class="footer-col"><h4>Event</h4><ul><li><a href="/about">About</a></li><li><a href="/speakers">Speakers</a></li><li><a href="/schedule">Schedule</a></li><li><a href="/gallery">Gallery</a></li></ul></div>
    <div class="footer-col"><h4>Info</h4><ul><li><a href="/venue">Venue</a></li><li><a href="/articles">Blog</a></li><li><a href="/contact">Contact</a></li><li><a href="#">Code of Conduct</a></li></ul></div>
    <div class="footer-col"><h4>Connect</h4><ul><li><a href="#">Twitter / X</a></li><li><a href="#">LinkedIn</a></li><li><a href="#">YouTube</a></li><li><a href="#">Discord</a></li></ul></div>
  </div>
  <div class="container footer-bottom"><p>&copy; <?= date('Y') ?> <?= esc(get_site_name()) ?>. All rights reserved.</p></div>
</footer>
<script src="/themes/starter-event/assets/js/main.js"></script>
</body>
</html>
