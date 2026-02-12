<?php
// Hero Section Variables
$heroBadge = theme_get('hero.badge', 'EST. 2024');
$heroHeadline = theme_get('hero.headline', 'FIRE KISSED FLAVORS');
$heroSubtitle = theme_get('hero.subtitle', 'Experience bold, wood-fired cuisine where every dish tells a story of smoke, heat, and artistry.');
$heroBtnText = theme_get('hero.btn_text', 'RESERVE YOUR TABLE');
$heroBtnLink = theme_get('hero.btn_link', '#reservations');
$heroBg = theme_get('hero.bg_image', $themePath . '/assets/images/hero-bg.jpg');

// About Section Variables
$aboutLabel = theme_get('about.label', 'Our Story');
$aboutTitle = theme_get('about.title', 'BORN FROM EMBER & CRAFTED WITH SOUL');
$aboutDesc = theme_get('about.description', 'What began as a passion for open-flame cooking has grown into a culinary destination. Our chefs honor tradition while pushing boundaries, using locally-sourced ingredients and time-honored techniques to create dishes that ignite the senses.');
$aboutImage = theme_get('about.image', $themePath . '/assets/images/about.jpg');

// Menu Section Variables
$menuLabel = theme_get('menu.label', 'The Menu');
$menuTitle = theme_get('menu.title', 'SIGNATURE CREATIONS');
$menuDesc = theme_get('menu.description', 'Each plate is a testament to our craft—bold flavors, impeccable presentation, unforgettable taste.');

// Gallery Section Variables
$galleryLabel = theme_get('gallery.label', 'Gallery');
$galleryTitle = theme_get('gallery.title', 'A FEAST FOR THE EYES');
$galleryDesc = theme_get('gallery.description', 'Step inside our world of culinary artistry and warm ambiance.');

// Articles Section Variables
$articlesLabel = theme_get('articles.label', 'From Our Kitchen');
$articlesTitle = theme_get('articles.title', 'STORIES & RECIPES');
$articlesDesc = theme_get('articles.description', 'Behind-the-scenes tales, seasonal inspirations, and recipes to try at home.');
$articlesBtnText = theme_get('articles.btn_text', 'VIEW ALL ARTICLES');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');

// CTA Section Variables
$ctaTitle = theme_get('cta.title', 'READY TO EXPERIENCE THE FIRE?');
$ctaDesc = theme_get('cta.description', 'Join us for an evening of exceptional cuisine, warm hospitality, and memories that linger long after the last bite.');
$ctaBtnText = theme_get('cta.btn_text', 'MAKE A RESERVATION');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBg = theme_get('cta.bg_image', $themePath . '/assets/images/cta-bg.jpg');
?>

<!-- Hero Section -->
<section class="hero" id="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBg) ?>');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-embers">
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember"></span>
        <span class="ember"></span>
    </div>
    <div class="hero-content" data-animate>
        <div class="hero-badge" data-ts="hero.badge">
            <i class="fas fa-fire"></i>
            <span><?= esc($heroBadge) ?></span>
            <i class="fas fa-fire"></i>
        </div>
        <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="hero-actions">
            <a href="<?= esc($heroBtnLink) ?>" 
               class="btn btn-primary btn-flame"
               data-ts="hero.btn_text"
               data-ts-href="hero.btn_link">
                <?= esc($heroBtnText) ?>
            </a>
            <a href="#menu" class="btn btn-outline">
                <span>VIEW MENU</span>
                <i class="fas fa-utensils"></i>
            </a>
        </div>
    </div>
    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>

<!-- About Section -->
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrap" data-animate>
                <div class="about-image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>');"></div>
                <div class="about-image-accent"></div>
                <div class="about-badge">
                    <i class="fas fa-award"></i>
                    <span>Award Winning</span>
                </div>
            </div>
            <div class="about-content" data-animate>
                <span class="section-label" data-ts="about.label">
                    <i class="fas fa-fire-alt"></i>
                    <?= esc($aboutLabel) ?>
                </span>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <div class="section-divider">
                    <span></span>
                    <i class="fas fa-diamond"></i>
                    <span></span>
                </div>
                <p class="section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-fire"></i>
                        <span>Wood-Fired Kitchen</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-leaf"></i>
                        <span>Locally Sourced</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-wine-glass-alt"></i>
                        <span>Curated Wines</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section class="section menu-section" id="menu">
    <div class="menu-bg-pattern"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="menu.label">
                <i class="fas fa-utensils"></i>
                <?= esc($menuLabel) ?>
            </span>
            <h2 class="section-title" data-ts="menu.title"><?= esc($menuTitle) ?></h2>
            <div class="section-divider">
                <span></span>
                <i class="fas fa-diamond"></i>
                <span></span>
            </div>
            <p class="section-desc" data-ts="menu.description"><?= esc($menuDesc) ?></p>
        </div>
        
        <?php if (!empty($pages)): ?>
        <div class="menu-grid">
            <?php foreach ($pages as $index => $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="menu-card <?= $index === 0 ? 'menu-card-featured' : '' ?>" data-animate>
                <div class="menu-card-inner">
                    <?php if (!empty($p['featured_image'])): ?>
                    <div class="menu-card-image">
                        <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                        <div class="menu-card-overlay"></div>
                    </div>
                    <?php endif; ?>
                    <div class="menu-card-content">
                        <div class="menu-card-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h3><?= esc($p['title']) ?></h3>
                        <span class="menu-card-cta">
                            Explore <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="menu-showcase">
            <div class="menu-item" data-animate>
                <div class="menu-item-header">
                    <h3>Ember-Seared Ribeye</h3>
                    <span class="menu-price">$58</span>
                </div>
                <p>28-day aged prime beef, oak-smoked bone marrow butter, charred shallots</p>
                <span class="menu-tag"><i class="fas fa-fire"></i> Chef's Signature</span>
            </div>
            <div class="menu-item" data-animate>
                <div class="menu-item-header">
                    <h3>Oak-Roasted Salmon</h3>
                    <span class="menu-price">$42</span>
                </div>
                <p>Wild-caught Pacific salmon, maple glaze, roasted root vegetables</p>
                <span class="menu-tag"><i class="fas fa-leaf"></i> Sustainable</span>
            </div>
            <div class="menu-item" data-animate>
                <div class="menu-item-header">
                    <h3>Truffle Mushroom Risotto</h3>
                    <span class="menu-price">$34</span>
                </div>
                <p>Arborio rice, wild mushroom medley, black truffle, aged parmesan</p>
                <span class="menu-tag"><i class="fas fa-seedling"></i> Vegetarian</span>
            </div>
            <div class="menu-item" data-animate>
                <div class="menu-item-header">
                    <h3>Fire-Kissed Duck Breast</h3>
                    <span class="menu-price">$48</span>
                </div>
                <p>Crispy skin duck, cherry gastrique, sweet potato purée, braised greens</p>
                <span class="menu-tag"><i class="fas fa-star"></i> Popular</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Gallery Section -->
<section class="section gallery-section" id="gallery">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="gallery.label">
                <i class="fas fa-camera"></i>
                <?= esc($galleryLabel) ?>
            </span>
            <h2 class="section-title" data-ts="gallery.title"><?= esc($galleryTitle) ?></h2>
            <div class="section-divider">
                <span></span>
                <i class="fas fa-diamond"></i>
                <span></span>
            </div>
            <p class="section-desc" data-ts="gallery.description"><?= esc($galleryDesc) ?></p>
        </div>
        
        <?php
        $gallery = null;
        try {
            $stmt = $db->prepare("SELECT * FROM galleries WHERE is_public = 1 AND (theme = ? OR theme IS NULL) LIMIT 1");
            $stmt->execute([get_active_theme()]);
            $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($gallery) {
                $imgStmt = $db->prepare("SELECT * FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order ASC LIMIT 8");
                $imgStmt->execute([$gallery['id']]);
                $gallery['images'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {}
        ?>
        
        <?php if ($gallery && !empty($gallery['images'])): ?>
        <div class="gallery-mosaic" data-animate>
            <?php foreach ($gallery['images'] as $i => $img): ?>
            <div class="gallery-item gallery-item-<?= ($i % 5) + 1 ?>">
                <img src="/uploads/media/<?= esc($img['filename']) ?>" 
                     alt="<?= esc($img['title'] ?? 'Gallery image') ?>" 
                     loading="lazy">
                <div class="gallery-item-overlay">
                    <i class="fas fa-expand"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="gallery-mosaic gallery-placeholder" data-animate>
            <div class="gallery-item gallery-item-1">
                <div class="gallery-placeholder-inner">
                    <i class="fas fa-utensils"></i>
                </div>
            </div>
            <div class="gallery-item gallery-item-2">
                <div class="gallery-placeholder-inner">
                    <i class="fas fa-fire"></i>
                </div>
            </div>
            <div class="gallery-item gallery-item-3">
                <div class="gallery-placeholder-inner">
                    <i class="fas fa-wine-glass-alt"></i>
                </div>
            </div>
            <div class="gallery-item gallery-item-4">
                <div class="gallery-placeholder-inner">
                    <i class="fas fa-chair"></i>
                </div>
            </div>
            <div class="gallery-item gallery-item-5">
                <div class="gallery-placeholder-inner">
                    <i class="fas fa-concierge-bell"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Articles Section -->
<section class="section articles-section" id="articles">
    <div class="container">
        <div class="articles-header" data-animate>
            <div class="articles-header-content">
                <span class="section-label" data-ts="articles.label">
                    <i class="fas fa-newspaper"></i>
                    <?= esc($articlesLabel) ?>
                </span>
                <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
                <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
            </div>
            <a href="<?= esc($articlesBtnLink) ?>" 
               class="btn btn-outline"
               data-ts="articles.btn_text"
               data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach (array_slice($articles, 0, 3) as $index => $a): ?>
            <article class="article-card <?= $index === 0 ? 'article-card-featured' : '' ?>" data-animate>
                <a href="/article/<?= esc($a['slug']) ?>" class="article-card-link">
                    <?php if (!empty($a['featured_image'])): ?>
                    <div class="article-card-image">
                        <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                        <div class="article-card-overlay"></div>
                    </div>
                    <?php endif; ?>
                    <div class="article-card-content">
                        <div class="article-meta">
                            <?php if (!empty($a['category_name'])): ?>
                            <span class="article-category"><?= esc($a['category_name']) ?></span>
                            <?php endif; ?>
                            <span class="article-date">
                                <i class="far fa-calendar"></i>
                                <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?>
                            </span>
                        </div>
                        <h3 class="article-title"><?= esc($a['title']) ?></h3>
                        <p class="article-excerpt"><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 130, '...')) ?></p>
                        <span class="article-read-more">
                            Read More <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="articles-empty" data-animate>
            <i class="fas fa-pen-fancy"></i>
            <p>Stories from our kitchen coming soon...</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section" id="reservations">
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBg) ?>');"></div>
    <div class="cta-overlay"></div>
    <div class="cta-flames">
        <div class="flame flame-1"></div>
        <div class="flame flame-2"></div>
        <div class="flame flame-3"></div>
    </div>
    <div class="container">
        <div class="cta-content" data-animate>
            <div class="cta-icon">
                <i class="fas fa-fire"></i>
            </div>
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="btn btn-primary btn-flame btn-large"
                   data-ts="cta.btn_text"
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="tel:+15551234567" class="btn btn-ghost">
                    <i class="fas fa-phone"></i>
                    Call (555) 123-4567
                </a>
            </div>
        </div>
    </div>
</section>