<section class="blog-hero" style="padding-bottom:40px">
    <h1>All Articles</h1>
    <p class="hero-subtitle"><?= $total ?> article<?= $total !== 1 ? 's' : '' ?> published</p>
</section>

<section class="posts-section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 280px;gap:48px;align-items:start">
            <div>
                <?php if (!empty($articles)): ?>
                <div class="posts-grid" style="grid-template-columns:repeat(2, 1fr)">
                    <?php foreach ($articles as $a): ?>
                    <div class="post-card">
                        <a href="/article/<?= esc($a['slug']) ?>" style="text-decoration:none">
                            <div class="post-card-image">
                                <?php if (!empty($a['featured_image'])): ?>
                                <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>">
                                <?php else: ?>
                                <div style="width:100%;height:100%;background:var(--blog-surface-light);display:flex;align-items:center;justify-content:center">
                                    <i class="fas fa-pen-fancy" style="font-size:1.5rem;color:var(--blog-border-light)"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="post-card-body">
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="category-badge"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                            <h3><a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a></h3>
                            <p class="excerpt">
                                <?php if (!empty($a['excerpt'])): ?>
                                    <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                                <?php else: ?>
                                    <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                                <?php endif; ?>
                            </p>
                            <div class="post-meta">
                                <span><i class="far fa-calendar"></i> <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                                <?php if (!empty($a['views'])): ?>
                                <span><i class="far fa-eye"></i> <?= number_format($a['views']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div style="display:flex;justify-content:center;align-items:center;gap:16px;margin-top:40px">
                    <?php if ($currentPage > 1): ?>
                    <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-outline btn-sm"><i class="fas fa-chevron-left"></i> Previous</a>
                    <?php endif; ?>
                    <span style="color:var(--blog-text-muted);font-size:0.9rem">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-outline btn-sm">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div style="text-align:center;padding:60px 0">
                    <h3>No articles yet</h3>
                    <p style="color:var(--blog-text-muted);margin-top:8px">Check back soon for new posts.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Categories Sidebar -->
            <?php if (!empty($categories)): ?>
            <aside>
                <div style="background:var(--blog-surface);border:1px solid var(--blog-border);border-radius:var(--radius);padding:28px">
                    <h3 style="font-family:var(--font-body);font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;color:var(--blog-text)">Categories</h3>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <?php foreach ($categories as $cat): ?>
                        <a href="/articles?category=<?= esc($cat['slug']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:var(--radius-sm);background:var(--blog-bg);border:1px solid var(--blog-border);color:var(--blog-text-muted);font-size:0.9rem;text-decoration:none;transition:all var(--transition)">
                            <span><?= esc($cat['name']) ?></span>
                            <span style="background:rgba(244,63,94,0.1);color:var(--blog-primary);padding:2px 10px;border-radius:50px;font-size:0.75rem;font-weight:600"><?= (int)$cat['article_count'] ?? 0 ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
