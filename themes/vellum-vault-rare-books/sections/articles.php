<?php
$blogBadge = theme_get('blog.badge', '');
$blogTitle = theme_get('blog.title', 'Latest from Vellum & Vault');
$blogSubtitle = theme_get('blog.subtitle', 'Insights, news, and updates from our team.');
$posts = [];
for ($i = 1; $i <= 3; $i++) {
    $post = [
        'title'    => theme_get("blog.post{$i}_title", $i === 1 ? 'Getting Started with Our Platform' : ($i === 2 ? 'Best Practices for Success' : 'What\'s New This Month')),
        'excerpt'  => theme_get("blog.post{$i}_excerpt", 'Discover the latest insights and strategies to help you grow.'),
        'image'    => theme_get("blog.post{$i}_image", ''),
        'date'     => theme_get("blog.post{$i}_date", $i === 1 ? 'Jan 15, 2026' : ($i === 2 ? 'Jan 10, 2026' : 'Jan 5, 2026')),
        'link'     => theme_get("blog.post{$i}_link", '/blog/article-' . $i),
        'category' => theme_get("blog.post{$i}_category", 'News'),
    ];
    if ($post['title']) $posts[] = $post;
}
?>
<section class="vvr-blog vvr-blog--grid-masonry" id="blog">
  <div class="container">
    <div class="vvr-blog-header" data-animate="fade-up">
      <?php if ($blogBadge): ?><span class="vvr-blog-badge" data-ts="blog.badge"><?= esc($blogBadge) ?></span><?php endif; ?>
      <h2 class="vvr-blog-title" data-ts="blog.title"><?= esc($blogTitle) ?></h2>
      <?php if ($blogSubtitle): ?><p class="vvr-blog-subtitle" data-ts="blog.subtitle"><?= esc($blogSubtitle) ?></p><?php endif; ?>
    </div>
    <div class="vvr-blog-masonry" data-animate="fade-up">
      <?php foreach ($posts as $idx => $post): $n = $idx + 1; ?>
      <article class="vvr-blog-masonry-item">
        <?php if ($post['image']): ?>
          <div class="vvr-blog-masonry-image">
            <a href="<?= esc($post['link']) ?>" data-ts-href="blog.post<?= $n ?>_link">
              <img src="<?= esc($post['image']) ?>" alt="<?= esc($post['title']) ?>" loading="lazy" data-ts="blog.post<?= $n ?>_image">
            </a>
          </div>
        <?php endif; ?>
        <div class="vvr-blog-masonry-body">
          <?php if ($post['category']): ?><span class="vvr-blog-card-category" data-ts="blog.post<?= $n ?>_category"><?= esc($post['category']) ?></span><?php endif; ?>
          <h3 class="vvr-blog-masonry-title"><a href="<?= esc($post['link']) ?>" data-ts="blog.post<?= $n ?>_title" data-ts-href="blog.post<?= $n ?>_link"><?= esc($post['title']) ?></a></h3>
          <p class="vvr-blog-masonry-excerpt" data-ts="blog.post<?= $n ?>_excerpt"><?= esc($post['excerpt']) ?></p>
          <time class="vvr-blog-card-date" data-ts="blog.post<?= $n ?>_date"><?= esc($post['date']) ?></time>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
