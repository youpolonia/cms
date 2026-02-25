<?php
/**
 * Articles listing template
 */
?>
<div class="jd-page">
    <h1>Blog</h1>
    <p class="jd-page-sub">Latest articles and updates from Jessie CMS</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
        <?php if (!empty($articles)): foreach ($articles as $article): ?>
        <div class="jd-feature-card">
            <h3><a href="/article/<?= esc($article['slug'] ?? '') ?>"><?= esc($article['title'] ?? 'Untitled') ?></a></h3>
            <p><?= esc(mb_substr(strip_tags($article['content'] ?? ''), 0, 150)) ?>...</p>
            <span style="font-size: 0.8rem; color: var(--jd-text-dim);"><?= date('M j, Y', strtotime($article['created_at'] ?? 'now')) ?></span>
        </div>
        <?php endforeach; else: ?>
        <p style="color: var(--jd-text-muted);">No articles yet. Check back soon!</p>
        <?php endif; ?>
    </div>
</div>
