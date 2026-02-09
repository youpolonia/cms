<section class="page-hero">
    <h1 class="page-hero-title">Blog</h1>
    <div class="page-hero-meta"><?= $total ?> article<?= $total !== 1 ? 's' : '' ?> published</div>
</section>

<section class="section">
    <div style="display:grid;grid-template-columns:1fr 280px;gap:48px;align-items:start">
        <div>
            <?php if (!empty($articles)): ?>
            <div class="work-grid" style="grid-template-columns:repeat(2, 1fr)">
                <?php foreach ($articles as $a): ?>
                <a href="/article/<?= esc($a['slug']) ?>" class="work-card" style="text-decoration:none;grid-column:span 1">
                    <?php if (!empty($a['featured_image'])): ?>
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" class="work-card-image">
                    <?php else: ?>
                    <div style="width:100%;aspect-ratio:16/10;background:var(--color-surface);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-pen-fancy" style="font-size:2rem;color:var(--color-border)"></i>
                    </div>
                    <?php endif; ?>
                    <div class="work-card-content">
                        <?php if (!empty($a['category_name'])): ?>
                        <div class="work-card-tag"><?= esc($a['category_name']) ?></div>
                        <?php endif; ?>
                        <div class="work-card-title"><?= esc($a['title']) ?></div>
                        <div class="work-card-desc">
                            <?php if (!empty($a['excerpt'])): ?>
                                <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 100, '...')) ?>
                            <?php else: ?>
                                <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 100, '...')) ?>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:8px;font-size:0.75rem;color:var(--color-text-muted)"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></div>
                    </div>
                    <div class="work-card-arrow"><i class="fas fa-arrow-right"></i></div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div style="display:flex;justify-content:center;align-items:center;gap:16px;margin-top:48px">
                <?php if ($currentPage > 1): ?>
                <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Previous</a>
                <?php endif; ?>
                <span style="color:var(--color-text-muted);font-size:0.9rem">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                <?php if ($currentPage < $totalPages): ?>
                <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-outline">Next <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div style="text-align:center;padding:60px 0">
                <h3 style="margin-bottom:12px">No articles yet</h3>
                <p style="color:var(--color-text-muted)">Check back soon for new posts.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Categories Sidebar -->
        <?php if (!empty($categories)): ?>
        <aside>
            <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:12px;padding:28px">
                <h3 style="font-size:1rem;font-weight:600;margin-bottom:20px">Categories</h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <?php foreach ($categories as $cat): ?>
                    <a href="/articles?category=<?= esc($cat['slug']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:8px;background:var(--color-background);border:1px solid var(--color-border);color:var(--color-text-muted);font-size:0.9rem;text-decoration:none;transition:all 0.3s ease">
                        <span><?= esc($cat['name']) ?></span>
                        <span style="background:rgba(16,185,129,0.1);color:var(--color-primary);padding:2px 10px;border-radius:50px;font-size:0.75rem;font-weight:600"><?= (int)$cat['article_count'] ?? 0 ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
        <?php endif; ?>
    </div>
</section>
