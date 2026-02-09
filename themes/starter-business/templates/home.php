<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-bolt"></i>
                <?= esc(get_setting('hero_badge') ?: 'Welcome to ' . get_site_name()) ?>
            </div>
            <h1 class="hero-title"><?= esc(get_site_name()) ?> — <span class="text-gradient"><?= esc(get_setting('hero_tagline') ?: 'Your Trusted Partner') ?></span></h1>
            <p class="hero-desc"><?= esc(get_setting('hero_subtitle') ?: 'Delivering excellence through innovation, expertise, and dedication to our clients.') ?></p>
            <div class="hero-actions">
                <a href="#services" class="btn btn-primary btn-lg">Our Services <i class="fas fa-arrow-right"></i></a>
                <a href="/articles" class="btn btn-outline btn-lg">Read Insights</a>
            </div>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number"><?= count($pages) ?>+</div>
                    <div class="stat-label">Services</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= count($articles) ?>+</div>
                    <div class="stat-label">Articles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= array_sum(array_column($articles, 'views')) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pages as "Services" -->
<?php if (!empty($pages)): ?>
<section class="section services-section" id="services">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">What We Offer</span>
            <h2 class="section-title">Our <span class="text-gradient">Services</span></h2>
            <p class="section-desc">Explore what we have to offer across all our pages.</p>
        </div>
        <div class="services-grid">
            <?php $icons = ['fas fa-cogs', 'fas fa-chart-line', 'fas fa-shield-alt', 'fas fa-lightbulb', 'fas fa-users', 'fas fa-globe']; ?>
            <?php foreach ($pages as $i => $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="service-card fade-in-up" style="text-decoration:none">
                <?php if (!empty($p['featured_image'])): ?>
                <div style="margin:-40px -32px 24px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="service-icon">
                    <i class="<?= $icons[$i % count($icons)] ?>"></i>
                </div>
                <?php endif; ?>
                <h3 class="service-title"><?= esc($p['title']) ?></h3>
                <p class="service-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...')) ?></p>
                <span class="service-link">Learn more <i class="fas fa-arrow-right"></i></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Articles as "Insights" -->
<?php if (!empty($articles)): ?>
<section class="section testimonials-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Insights</span>
            <h2 class="section-title">Latest <span class="text-gradient">Articles</span></h2>
            <p class="section-desc">Stay informed with our latest business insights and thought leadership.</p>
        </div>
        <div class="services-grid">
            <?php foreach (array_slice($articles, 0, 6) as $a): ?>
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
        <div style="text-align:center;margin-top:48px">
            <a href="/articles" class="btn btn-primary">View All Articles <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Insights</span>
            <h2 class="section-title">No articles yet</h2>
            <p class="section-desc">Check back soon for business insights and articles.</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter CTA -->
<section class="section newsletter-section">
    <div class="container">
        <div class="newsletter-card">
            <div class="newsletter-content">
                <h2 class="newsletter-title">Stay Ahead of the Curve</h2>
                <p class="newsletter-desc">Get the latest insights, articles, and updates delivered to your inbox.</p>
            </div>
            <div class="newsletter-form">
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                    <a href="/articles" class="btn btn-primary btn-lg">Browse Articles <i class="fas fa-arrow-right"></i></a>
                    <a href="/" class="btn btn-outline btn-lg" style="color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2)">Home</a>
                </div>
            </div>
        </div>
    </div>
</section>
