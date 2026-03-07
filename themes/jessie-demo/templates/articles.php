<?php
/**
 * Articles listing template — Jessie CMS Demo
 */
?>
<div class="jd-page">
    <h1>Blog</h1>
    <p class="jd-page-sub">Latest articles and updates from Jessie CMS</p>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px; max-width: var(--jd-container, 1200px); margin: 0 auto;">
        <?php if (!empty($articles)): foreach ($articles as $article): ?>
        <a href="/article/<?= esc($article['slug'] ?? '') ?>" class="jd-feature-card" style="text-decoration: none; display: flex; flex-direction: column; overflow: hidden;">
            <?php if (!empty($article['featured_image'])): ?>
            <div style="width: 100%; height: 200px; overflow: hidden; border-radius: 12px 12px 0 0; margin: -24px -24px 16px -24px; width: calc(100% + 48px);">
                <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['featured_image_alt'] ?? $article['title'] ?? '') ?>"
                     style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
            </div>
            <?php endif; ?>
            <h3 style="color: var(--jd-text, #e2e8f0);"><?= esc($article['title'] ?? 'Untitled') ?></h3>
            <p style="flex: 1;"><?= esc(mb_substr(strip_tags($article['content'] ?? ''), 0, 150)) ?>...</p>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
                <span style="font-size: 0.8rem; color: var(--jd-text-dim, #94a3b8);"><?= date('M j, Y', strtotime($article['created_at'] ?? 'now')) ?></span>
                <?php if (!empty($article['category_name'])): ?>
                <span style="font-size: 0.75rem; padding: 2px 10px; background: rgba(139, 92, 246, 0.15); color: var(--jd-purple, #8b5cf6); border-radius: 20px;"><?= esc($article['category_name']) ?></span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; else: ?>
        <p style="color: var(--jd-text-muted, #94a3b8);">No articles yet. Check back soon!</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($totalPages) && $totalPages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 48px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="/blog?page=<?= $i ?>" style="padding: 8px 16px; border-radius: 8px; text-decoration: none; <?= ($i === ($currentPage ?? 1)) ? 'background: var(--jd-purple, #8b5cf6); color: #fff;' : 'background: var(--jd-surface, #0f172a); color: var(--jd-text-dim, #94a3b8); border: 1px solid var(--jd-border, #1e293b);' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
