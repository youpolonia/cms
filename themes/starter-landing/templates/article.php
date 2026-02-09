<?php
/**
 * Single Article Template — AppFlow
 * Available: $page (article array), $content (string)
 */
?>
<section class="article-header">
    <div class="container">
        <h1><?= esc($page['title'] ?? 'Article') ?></h1>
        <div class="article-meta">
            <?= date('F j, Y', strtotime($page['published_at'] ?? $page['created_at'] ?? 'now')) ?>
            <?php if (!empty($page['author'])): ?> · <?= esc($page['author']) ?><?php endif; ?>
        </div>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <?php if (!empty($page['featured_image'])): ?>
        <div style="max-width:800px;margin:0 auto 40px;">
            <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>" style="border-radius:16px;width:100%;">
        </div>
        <?php endif; ?>
        <div class="content-body">
            <?= $content ?>
        </div>
        <div style="max-width:800px;margin:60px auto 0;padding-top:32px;border-top:1px solid #e2e8f0;text-align:center;">
            <a href="/articles" class="btn-primary" style="padding:12px 28px;font-size:.95rem;">← Back to Blog</a>
        </div>
    </div>
</section>
