<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?= render_seo_meta(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
<style><?= generate_theme_css_variables(); ?></style>
<link rel="stylesheet" href="/themes/starter-magazine/assets/css/style.css">
</head>
<body class="<?= get_body_class() ?>">
<!-- Top Bar -->
<div class="topbar">
  <div class="container topbar-inner">
    <span class="topbar-date"><?= date('l, F j, Y') ?></span>
    <div class="topbar-ticker"><span class="ticker-label">Trending:</span> <span class="ticker-text">The future of AI in creative industries</span></div>
  </div>
</div>
<!-- Navigation -->
<header class="site-header">
  <div class="container header-inner">
    <div class="site-brand">
      <a href="/" class="site-title" data-ts="brand.site_name"><?= esc(get_site_name()) ?></a>
      <span class="site-tagline" data-ts="brand.tagline">Independent Journalism &amp; Culture</span>
    </div>
    <nav class="main-nav">
      <?= render_menu('header', ['wrap' => false, 'class' => 'nav-links', 'fallback_to_pages' => true]); ?>
    </nav>
    <button class="mobile-toggle" aria-label="Menu">&#9776;</button>
  </div>
</header>
<!-- Main -->
<main class="site-main">
  <div class="container">
    <div class="content-with-sidebar">
      <div class="content-area"><?= $content ?></div>
      <aside class="sidebar">
        <div class="widget widget-search">
          <input type="text" placeholder="Search articles..." class="search-input">
        </div>
        <div class="widget widget-recent">
          <h3 class="widget-title">Latest Stories</h3>
          <?php try { $stmtRecent = \core\Database::connection()->query("SELECT id,slug,title,created_at FROM articles WHERE status='published' ORDER BY created_at DESC LIMIT 5"); $recents = $stmtRecent->fetchAll(PDO::FETCH_ASSOC); foreach($recents as $r): ?>
          <a href="/articles/<?= esc($r['slug']) ?>" class="recent-item">
            <span class="recent-title"><?= esc($r['title']) ?></span>
            <span class="recent-date"><?= date('M j', strtotime($r['created_at'])) ?></span>
          </a>
          <?php endforeach; } catch(\Throwable $e) {} ?>
        </div>
        <div class="widget widget-newsletter">
          <h3 class="widget-title">Newsletter</h3>
          <p>Get the best stories delivered to your inbox weekly.</p>
          <form class="newsletter-form"><input type="email" placeholder="Your email"><button type="button">Subscribe</button></form>
        </div>
        <div class="widget widget-categories">
          <h3 class="widget-title">Categories</h3>
          <div class="category-tags">
            <span class="cat-tag">Technology</span><span class="cat-tag">Culture</span><span class="cat-tag">Science</span><span class="cat-tag">Opinion</span><span class="cat-tag">Arts</span>
          </div>
        </div>
      </aside>
    </div>
  </div>
</main>
<!-- Footer -->
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-col">
      <h4><?= esc(get_site_name()) ?></h4>
      <p>Independent journalism covering technology, culture, science, and the arts since 2020.</p>
    </div>
    <div class="footer-col">
      <h4>Sections</h4>
      <ul><li><a href="/articles">All Articles</a></li><li><a href="/about">About Us</a></li><li><a href="/gallery">Photo Stories</a></li><li><a href="/contact">Contact</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Connect</h4>
      <ul><li><a href="#">Twitter / X</a></li><li><a href="#">Instagram</a></li><li><a href="#">RSS Feed</a></li><li><a href="/subscribe">Newsletter</a></li></ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <p data-ts="footer.copyright">&copy; <?= date('Y') ?> <?= esc(get_site_name()) ?>. All rights reserved.</p>
  </div>
</footer>
<script src="/themes/starter-magazine/assets/js/main.js"></script>
</body>
</html>
