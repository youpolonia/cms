<section class="page-content" style="padding-top:100px">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Blog</span>
            <h2>All <span class="gradient-text">Articles</span></h2>
            <p><?= $total ?> article<?= $total !== 1 ? 's' : '' ?> published</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 280px;gap:48px;align-items:start">
            <!-- Articles Grid -->
            <div>
                <?php if (!empty($articles)): ?>
                <div class="features-grid" style="grid-template-columns:repeat(2, 1fr)">
                    <?php foreach ($articles as $a): ?>
                    <a href="/article/<?= esc($a['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                        <?php if (!empty($a['featured_image'])): ?>
                        <div style="margin:-32px -28px 20px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                            <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                        </div>
                        <?php else: ?>
                        <div class="feature-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <?php endif; ?>
                        <h3 class="feature-title" style="color:#f8fafc"><?= esc($a['title']) ?></h3>
                        <p class="feature-desc">
                            <?php if (!empty($a['excerpt'])): ?>
                                <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                            <?php else: ?>
                                <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                            <?php endif; ?>
                        </p>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px">
                            <span style="font-size:0.8rem;color:#94a3b8"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="section-badge" style="margin:0;font-size:0.65rem"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div style="display:flex;justify-content:center;align-items:center;gap:16px;margin-top:48px">
                    <?php if ($currentPage > 1): ?>
                    <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-outline btn-sm"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    <span style="color:#94a3b8;font-size:0.9rem">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-outline btn-sm">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="section-header">
                    <h3>No articles yet</h3>
                    <p>Check back soon for fresh content.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Categories Sidebar -->
            <?php if (!empty($categories)): ?>
            <aside>
                <div class="glass-card" style="padding:28px;border:1px solid rgba(255,255,255,0.06);border-radius:16px">
                    <h3 style="font-size:1rem;font-weight:600;color:#f8fafc;margin-bottom:20px">Categories</h3>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <?php foreach ($categories as $cat): ?>
                        <a href="/articles?category=<?= esc($cat['slug']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);color:#e2e8f0;font-size:0.9rem;text-decoration:none;transition:all 0.2s ease">
                            <span><?= esc($cat['name']) ?></span>
                            <span style="background:rgba(99,102,241,0.15);color:#818cf8;padding:2px 10px;border-radius:50px;font-size:0.75rem;font-weight:600"><?= (int)$cat['article_count'] ?? 0 ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
