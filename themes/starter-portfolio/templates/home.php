<!-- Hero Section -->
<section class="hero">
    <div class="hero-label">
        <span><?= esc(get_setting('hero_label') ?: 'Welcome') ?></span>
    </div>
    <h1 class="hero-title" data-ts="hero.headline">
        <span class="text-stroke"><?= esc(get_site_name()) ?></span><br>
        <span class="text-gradient"><?= esc(get_setting('hero_tagline') ?: 'Creative Portfolio') ?></span>
    </h1>
    <p class="hero-description" data-ts="hero.subtitle"><?= esc(get_setting('hero_subtitle') ?: 'Explore our projects, read our blog, and discover what we do best.') ?></p>
    <div class="hero-cta-group">
        <a href="#projects" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">View Projects <i class="fas fa-arrow-right"></i></a>
        <a href="/articles" class="btn btn-outline">Read Blog</a>
    </div>
    <div class="hero-scroll-indicator">
        <div class="scroll-line"></div>
        <span>Scroll</span>
    </div>
</section>

<!-- Pages as "Projects" -->
<?php if (!empty($pages)): ?>
<div class="section-divider"><hr></div>
<section class="section" id="projects">
    <div class="section-header">
        <div class="section-label" data-ts="pages.label">Projects</div>
        <h2 class="section-title" data-ts="pages.title">Featured <span class="text-gradient">Work</span></h2>
        <p class="section-subtitle" data-ts="pages.description">Explore our pages and projects.</p>
    </div>
    <div class="work-grid">
        <?php foreach ($pages as $p): ?>
        <a href="/page/<?= esc($p['slug']) ?>" class="work-card" style="text-decoration:none">
            <?php if (!empty($p['featured_image'])): ?>
            <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" class="work-card-image">
            <?php else: ?>
            <div style="width:100%;aspect-ratio:16/10;background:var(--color-surface);display:flex;align-items:center;justify-content:center">
                <i class="fas fa-file-alt" style="font-size:2rem;color:var(--color-border)"></i>
            </div>
            <?php endif; ?>
            <div class="work-card-content">
                <div class="work-card-title"><?= esc($p['title']) ?></div>
                <div class="work-card-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 100, '...')) ?></div>
            </div>
            <div class="work-card-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Articles as "Blog" -->
<?php if (!empty($articles)): ?>
<div class="section-divider"><hr></div>
<section class="section">
    <div class="section-header">
        <div class="section-label" data-ts="articles.label">Blog</div>
        <h2 class="section-title" data-ts="articles.title">Latest <span class="text-gradient">Posts</span></h2>
        <p class="section-subtitle" data-ts="articles.description">Thoughts, stories, and insights.</p>
    </div>
    <div class="work-grid">
        <?php foreach (array_slice($articles, 0, 4) as $a): ?>
        <a href="/article/<?= esc($a['slug']) ?>" class="work-card" style="text-decoration:none">
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
            </div>
            <div class="work-card-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:48px">
        <a href="/articles" class="btn btn-outline">View All Posts <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
<?php else: ?>
<div class="section-divider"><hr></div>
<section class="section">
    <div class="section-header">
        <div class="section-label">Blog</div>
        <h2 class="section-title">No posts yet</h2>
        <p class="section-subtitle">Check back soon for new content.</p>
    </div>
</section>
<?php endif; ?>

<!-- Skills Section -->
<div class="section-divider"><hr></div>
<section class="section">
    <div class="section-header">
        <div class="section-label">What We Do</div>
        <h2 class="section-title">Our <span class="text-gradient">Skills</span></h2>
    </div>
    <div class="skills-grid">
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-code"></i></div>
            <h3 class="skill-title">Development</h3>
            <p class="skill-desc">Building modern, performant web applications with clean code.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-palette"></i></div>
            <h3 class="skill-title">Design</h3>
            <p class="skill-desc">Crafting beautiful, intuitive interfaces that users love.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-rocket"></i></div>
            <h3 class="skill-title">Strategy</h3>
            <p class="skill-desc">Planning and executing digital strategies that deliver results.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <h2 class="cta-title" data-ts="cta.title">Let's <span class="text-gradient">Connect</span></h2>
    <p class="cta-description" data-ts="cta.description">Interested in working together? Let's make something great.</p>
    <div class="cta-button-group">
        <a href="/articles" class="btn btn-primary">Read Our Blog <i class="fas fa-arrow-right"></i></a>
    </div>
</section>
