<?php
/**
 * Starter SaaS — Blog/Insights Section (Articles from DB)
 * Shows latest SaaS articles
 * Editable via Theme Studio. data-ts for live preview.
 */
$toolsLabel = theme_get('tools.label', 'Blog');
$toolsTitle = theme_get('tools.title', 'Latest Insights');
$toolsDesc  = theme_get('tools.description', 'Thought leadership, product updates, and best practices from our team.');
?>
<!-- Blog / Insights (Articles) -->
<?php if (!empty($articles)): ?>
<section class="features-section" style="padding-top:0">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="tools.label"><?= esc($toolsLabel) ?></span>
            <h2 data-ts="tools.title"><?= esc($toolsTitle) ?></h2>
            <p data-ts="tools.description"><?= esc($toolsDesc) ?></p>
        </div>
        <div class="features-grid" style="grid-template-columns:repeat(3, 1fr)">
            <?php foreach (array_slice($articles, 0, 3) as $a): ?>
            <a href="/article/<?= esc($a['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                <?php if (!empty($a['featured_image'])): ?>
                <div style="margin:-32px -28px 20px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="feature-icon"><i class="fas fa-newspaper"></i></div>
                <?php endif; ?>
                <h3 class="feature-title" style="color:#f8fafc"><?= esc($a['title']) ?></h3>
                <p class="feature-desc">
                    <?php if (!empty($a['excerpt'])): ?>
                        <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                    <?php else: ?>
                        <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                    <?php endif; ?>
                </p>
                <span style="font-size:0.8rem;color:#94a3b8;margin-top:auto"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px">
            <a href="/articles" class="btn btn-outline">Read Our Blog <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>
