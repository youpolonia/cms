<section class="page-header">
    <div class="page-header-content">
        <div class="container">
            <h1 class="page-title">Business Insights</h1>
            <div class="breadcrumbs">
                <a href="/">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                <span class="breadcrumb-current">Articles</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 300px;gap:48px;align-items:start">
            <div>
                <?php if (!empty($articles)): ?>
                <div class="services-grid" style="grid-template-columns:repeat(2, 1fr)">
                    <?php foreach ($articles as $a): ?>
                    <a href="/article/<?= esc($a['slug']) ?>" class="service-card" style="text-decoration:none">
                        <?php if (!empty($a['featured_image'])): ?>
                        <div style="margin:-40px -32px 24px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                            <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                        </div>
                        <?php else: ?>
                        <div class="service-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <?php endif; ?>
                        <h3 class="service-title"><?= esc($a['title']) ?></h3>
                        <p class="service-desc">
                            <?php if (!empty($a['excerpt'])): ?>
                                <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                            <?php else: ?>
                                <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                            <?php endif; ?>
                        </p>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                            <span style="font-size:0.8rem;color:var(--color-text_light, #64748b)"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="section-badge" style="margin:0;font-size:0.7rem"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div style="display:flex;justify-content:center;align-items:center;gap:16px;margin-top:48px">
                    <?php if ($currentPage > 1): ?>
                    <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-outline"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    <span style="color:var(--color-text_light, #64748b);font-size:0.9rem">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-outline">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div style="text-align:center;padding:60px 0">
                    <h3>No articles yet</h3>
                    <p style="color:var(--color-text_light, #64748b);margin-top:8px">Check back soon for business insights.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Categories Sidebar -->
            <?php if (!empty($categories)): ?>
            <aside>
                <div style="background:var(--color-surface, #f8fafc);border:1px solid var(--color-border, #e2e8f0);border-radius:16px;padding:28px">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--color-heading, #0f172a);margin-bottom:20px">Categories</h3>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <?php foreach ($categories as $cat): ?>
                        <a href="/articles?category=<?= esc($cat['slug']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:10px;background:var(--color-background, #ffffff);border:1px solid var(--color-border, #e2e8f0);color:var(--color-text, #1e293b);font-size:0.9rem;text-decoration:none;transition:all 0.25s ease">
                            <span><?= esc($cat['name']) ?></span>
                            <span style="background:rgba(37,99,235,0.08);color:var(--color-primary, #2563eb);padding:2px 12px;border-radius:100px;font-size:0.75rem;font-weight:600"><?= (int)$cat['article_count'] ?? 0 ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
