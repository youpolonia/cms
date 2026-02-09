<section class="page-hero">
    <div class="page-hero-overlay"></div>
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
        <div style="display:grid;grid-template-columns:1fr 280px;gap:48px;align-items:start">
            <div>
                <?php if (!empty($articles)): ?>
                <div class="menu-grid">
                    <?php foreach ($articles as $a): ?>
                    <a href="/article/<?= esc($a['slug']) ?>" class="menu-card" style="text-decoration:none" data-animate>
                        <div class="menu-card-img">
                            <?php if (!empty($a['featured_image'])): ?>
                            <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" style="width:100%;height:200px;object-fit:cover">
                            <?php else: ?>
                            <div class="img-placeholder menu-ph"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="menu-card-tag"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="menu-card-body">
                            <div class="menu-card-header">
                                <h3><?= esc($a['title']) ?></h3>
                                <span class="menu-price" style="font-size:0.8rem"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                            </div>
                            <p>
                                <?php if (!empty($a['excerpt'])): ?>
                                    <?= esc(mb_strimwidth(strip_tags($a['excerpt']), 0, 120, '...')) ?>
                                <?php else: ?>
                                    <?= esc(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?>
                                <?php endif; ?>
                            </p>
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
                    <span style="color:var(--text-muted, #c4a882);font-size:0.9rem">Page <?= $currentPage ?> of <?= $totalPages ?></span>
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
                <div style="background:var(--surface, #241c14);border:1px solid rgba(212,165,116,.1);border-radius:8px;padding:28px">
                    <h4 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:20px;color:var(--text, #faf5ef)">Categories</h4>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        <?php foreach ($categories as $cat): ?>
                        <a href="/articles?category=<?= esc($cat['slug']) ?>" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:4px;background:var(--background, #1a1410);border:1px solid rgba(212,165,116,.1);color:var(--text-muted, #c4a882);font-size:0.9rem;text-decoration:none;transition:all 0.3s ease">
                            <span><?= esc($cat['name']) ?></span>
                            <span style="background:rgba(212,165,116,.1);color:var(--primary, #d4a574);padding:2px 10px;border-radius:4px;font-size:0.75rem;font-weight:600"><?= (int)$cat['article_count'] ?? 0 ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
