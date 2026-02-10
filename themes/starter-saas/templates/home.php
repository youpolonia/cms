<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                <?= esc(get_setting('hero_badge') ?: 'Welcome to ' . get_site_name()) ?>
            </div>
            <h1><?= esc(get_site_name()) ?> <span class="gradient-text"><?= esc(get_setting('hero_tagline') ?: 'Build Something Amazing') ?></span></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc(get_setting('hero_subtitle') ?: 'Discover our latest content, explore our pages, and stay up to date with everything new.') ?></p>
            <div class="hero-actions">
                <a href="/articles" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">Browse Articles <i class="fas fa-arrow-right"></i></a>
                <a href="#pages" class="btn btn-glass">Our Pages <i class="fas fa-chevron-down"></i></a>
            </div>

            <?php if (!empty($articles)): ?>
            <div class="hero-stats">
                <div class="stat">
                    <span class="stat-number"><?= count($articles) ?>+</span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= count($pages) ?>+</span>
                    <span class="stat-label">Pages</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= array_sum(array_column($articles, 'views')) ?></span>
                    <span class="stat-label">Total Views</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Latest Articles -->
<?php if (!empty($articles)): ?>
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Latest Articles</span>
            <h2>Fresh from the <span class="gradient-text">Blog</span></h2>
            <p>Stay informed with our latest articles and insights.</p>
        </div>
        <div class="features-grid">
            <?php foreach (array_slice($articles, 0, 6) as $a): ?>
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
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto">
                    <span style="font-size:0.8rem;color:#94a3b8">
                        <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?>
                    </span>
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="section-badge" style="margin:0;font-size:0.65rem"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:48px">
            <a href="/articles" class="btn btn-outline">View All Articles <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Articles</span>
            <h2>No articles yet</h2>
            <p>Check back soon for fresh content.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Pages Section -->
<?php if (!empty($pages)): ?>
<section class="showcase-section" id="pages">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Explore</span>
            <h2>Our <span class="gradient-text">Pages</span></h2>
            <p>Discover what we have to offer.</p>
        </div>
        <div class="features-grid" style="grid-template-columns:repeat(2, 1fr)">
            <?php foreach ($pages as $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                <?php if (!empty($p['featured_image'])): ?>
                <div style="margin:-32px -28px 20px;border-radius:16px 16px 0 0;overflow:hidden;height:200px">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="feature-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <?php endif; ?>
                <h3 class="feature-title" style="color:#f8fafc"><?= esc($p['title']) ?></h3>
                <p class="feature-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 150, '...')) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter CTA -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <h2>Stay in the <span class="gradient-text">Loop</span></h2>
            <p>Subscribe to get the latest updates, articles, and news delivered to your inbox.</p>
            <div class="cta-actions">
                <a href="/articles" class="btn btn-primary btn-lg">Browse Articles <i class="fas fa-arrow-right"></i></a>
                <a href="/" class="btn btn-glass">Back to Top <i class="fas fa-chevron-up"></i></a>
            </div>
        </div>
    </div>
</section>
