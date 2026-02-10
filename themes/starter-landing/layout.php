<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?= render_seo_meta(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style><?= generate_theme_css_variables(); ?></style>
<link rel="stylesheet" href="/themes/starter-landing/assets/css/style.css">
</head>
<body class="<?= get_body_class() ?>">
<header class="site-header">
  <div class="container header-inner">
    <a href="/" class="site-logo" data-ts="brand.logo"><?= esc(get_site_name()) ?></a>
    <nav class="main-nav">
      <?= render_menu('header', ['wrap' => false, 'class' => 'nav-links', 'fallback_to_pages' => true]); ?>
    </nav>
    <a href="/contact" class="nav-cta" data-ts="header.cta_text" data-ts-href="header.cta_link">Get Started →</a>
    <button class="mobile-toggle" aria-label="Menu">☰</button>
  </div>
</header>
<main class="site-main"><?= $content ?></main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-col"><h4 data-ts="brand.site_name"><?= esc(get_site_name()) ?></h4><p data-ts="footer.description">The smarter way to manage your workflow and boost productivity.</p></div>
    <div class="footer-col"><h4>Product</h4><ul><li><a href="#">Features</a></li><li><a href="#">Pricing</a></li><li><a href="#">Integrations</a></li><li><a href="#">Changelog</a></li></ul></div>
    <div class="footer-col"><h4>Company</h4><ul><li><a href="/about">About</a></li><li><a href="/articles">Blog</a></li><li><a href="/contact">Contact</a></li><li><a href="/gallery">Gallery</a></li></ul></div>
    <div class="footer-col"><h4>Legal</h4><ul><li><a href="#">Privacy</a></li><li><a href="#">Terms</a></li><li><a href="#">Security</a></li></ul></div>
  </div>
  <div class="container footer-bottom"><p data-ts="footer.copyright">&copy; <?= date('Y') ?> <?= esc(get_site_name()) ?>. All rights reserved.</p></div>
</footer>
<script src="/themes/starter-landing/assets/js/main.js"></script>
</body>
</html>
