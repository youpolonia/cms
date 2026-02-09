<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?= render_seo_meta(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style><?= generate_theme_css_variables(); ?></style>
<link rel="stylesheet" href="/themes/starter-docs/assets/css/style.css">
</head>
<body class="<?= get_body_class() ?>">

<header class="site-header">
  <div class="header-inner">
    <a href="/" class="site-logo"><?= esc(get_site_name()) ?></a>
    <div class="header-search">
      <input type="text" placeholder="Search documentation..." class="search-input" id="docsSearch">
      <kbd>⌘K</kbd>
    </div>
    <nav class="header-nav">
      <?= render_menu('header', ['wrap' => false, 'class' => 'header-links', 'fallback_to_pages' => true]); ?>
    </nav>
    <button class="mobile-toggle" aria-label="Menu">☰</button>
  </div>
</header>

<div class="docs-layout">
  <aside class="docs-sidebar" id="docsSidebar">
    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <h4 class="sidebar-heading">Navigation</h4>
        <?= render_menu('header', ['wrap' => false, 'class' => 'sidebar-links', 'fallback_to_pages' => true]); ?>
      </div>
    </nav>
  </aside>
  <main class="docs-content">
    <div class="content-inner"><?= $content ?></div>
  </main>
</div>

<footer class="site-footer">
  <div class="container footer-inner">
    <p>&copy; <?= date('Y') ?> <?= esc(get_site_name()) ?>. Built with Jessie CMS.</p>
    <div class="footer-links">
      <a href="/about">About</a>
      <a href="/contact">Contact</a>
      <a href="#">GitHub</a>
    </div>
  </div>
</footer>

<script src="/themes/starter-docs/assets/js/main.js"></script>
</body>
</html>
