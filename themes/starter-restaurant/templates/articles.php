<?php
/**
 * Starter Restaurant — Articles List Template
 * Grid with sidebar categories
 */
?>
<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">News & Stories</h1>
        <div class="page-breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <span>Articles</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="articles-layout">
            <div>
                <?php if (!empty($articles)): ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $a): ?>
                    <a href="/article/<?= esc($a['slug']) ?>" class="card-standard" data-animate>
                        <div class="card-img">
                            <?php if (!empty($a['featured_image'])): ?>
                            <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="img-placeholder"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="card-tag"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            </div>
                            <h3><?= esc($a['title']) ?></h3>
                            <p>
                                <?php if (!empty($a['excerpt'])): ?>
                                    <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 130, '...')) ?>
                                <?php else: ?>
                                    <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 130, '...')) ?>
                                <?php endif; ?>
                            </p>
                            <div class="card-footer">
                                <span class="card-link">Read More <i class="fas fa-arrow-right" style="font-size:0.65rem"></i></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                    <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-outline"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    <span class="pagination-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-outline">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="section-header">
                    <h2 class="section-title">No articles yet</h2>
                    <p class="section-desc">Check back soon for new stories.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Categories Sidebar -->
            <?php if (!empty($categories)): ?>
            <aside>
                <div class="sidebar-widget">
                    <h4>Categories</h4>
                    <?php foreach ($categories as $cat): ?>
                    <a href="/articles?category=<?= esc($cat['slug']) ?>" class="sidebar-cat-link">
                        <span><?= esc($cat['name']) ?></span>
                        <span class="sidebar-cat-count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
