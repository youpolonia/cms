<?php
/**
 * Single Article Template — AppFlow
 * Available: $page (article array), $content (string)
 */
?>
<section class="article-header">
    <div class="container">
        <h1><?= esc($article['title'] ?? 'Article') ?></h1>
        <div class="article-meta">
            <?= date('F j, Y', strtotime($article['published_at'] ?? $article['created_at'] ?? 'now')) ?>
            <?php if (!empty($article['author'])): ?> · <?= esc($article['author']) ?><?php endif; ?>
        </div>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (!empty($article['featured_image'])): ?>
        <div style="max-width:800px;margin:0 auto 40px;">
            <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>" style="border-radius:16px;width:100%;">
        </div>
        <?php endif; ?>
        <div class="content-body">
            <?= $article["content"] ?? "" ?>
        </div>
        <div style="max-width:800px;margin:60px auto 0;padding-top:32px;border-top:1px solid #e2e8f0;text-align:center;">
            <a href="/articles" class="btn-primary" style="padding:12px 28px;font-size:.95rem;">← Back to Blog</a>
        </div>
    </div>
</section>
