<?php
$heroHeadline = theme_get('hero.headline', 'Precision Construction, Lasting Legacy');
$heroSubtitle = theme_get('hero.subtitle', 'We craft exceptional spaces that stand the test of time, blending innovative engineering with timeless design.');
$heroBtnText = theme_get('hero.btn_text', 'Start Your Project');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroBgImage = theme_get('hero.bg_image', '');

$aboutLabel = theme_get('about.label', 'OUR PHILOSOPHY');
$aboutTitle = theme_get('about.title', 'Built on Integrity, Driven by Innovation');
$aboutDesc = theme_get('about.description', 'For over two decades, Ironwood Construct has redefined the construction landscape. Our approach merges meticulous craftsmanship with cutting-edge technology, ensuring every project exceeds expectations.');
$aboutImage = theme_get('about.image', '');

$servicesLabel = theme_get('services.label', 'WHAT WE OFFER');
$servicesTitle = theme_get('services.title', 'Comprehensive Construction Solutions');
$servicesDesc = theme_get('services.description', 'From groundbreaking to ribbon-cutting, we provide end-to-end services tailored to your vision.');

$projectsLabel = theme_get('projects.label', 'OUR PORTFOLIO');
$projectsTitle = theme_get('projects.title', 'Landmark Projects That Define Skylines');
$projectsDesc = theme_get('projects.description', 'Explore our curated selection of completed works, showcasing our commitment to quality and innovation.');

$articlesLabel = theme_get('articles.label', 'INSIGHTS');
$articlesTitle = theme_get('articles.title', 'Latest Industry Perspectives');
$articlesDesc = theme_get('articles.description', 'Stay informed with expert analysis, project highlights, and construction trends.');
$articlesBtnText = theme_get('articles.btn_text', 'View All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');

$ctaTitle = theme_get('cta.title', 'Ready to Build Your Vision?');
$ctaDesc = theme_get('cta.description', 'Contact us for a detailed consultation. Let’s discuss how we can bring your architectural dreams to life.');
$ctaBtnText = theme_get('cta.btn_text', 'Schedule Consultation');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', '');
?>
<section class="section hero-section" id="hero">
    <div class="hero-bg" <?php if ($heroBgImage): ?>style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"<?php endif; ?>></div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content" data-animate>
            <div class="hero-badge" data-ts="hero.badge"><?= esc(theme_get('hero.badge', 'EST. 2002')) ?></div>
            <h1 class="hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#about" class="btn btn-outline">
                    Learn More <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
        <div class="hero-stats" data-animate>
            <div class="stat">
                <span class="stat-number">250+</span>
                <span class="stat-label">Projects Completed</span>
            </div>
            <div class="stat">
                <span class="stat-number">98%</span>
                <span class="stat-label">Client Satisfaction</span>
            </div>
            <div class="stat">
                <span class="stat-number">15</span>
                <span class="stat-label">Industry Awards</span>
            </div>
            <div class="stat">
                <span class="stat-number">24/7</span>
                <span class="stat-label">Support</span>
            </div>
        </div>
    </div>
    <div class="hero-divider">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V120H1200V0C800,80 400,100 0,0Z"></path>
        </svg>
    </div>
</section>

<section class="section about-section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-content" data-animate>
                <div class="section-header">
                    <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                    <div class="section-divider"></div>
                    <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                    <p class="section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                </div>
                <div class="about-features">
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-award"></i></div>
                        <div class="feature-text">
                            <h4>Certified Excellence</h4>
                            <p>LEED Accredited & OSHA Certified teams.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <div class="feature-text">
                            <h4>Collaborative Process</h4>
                            <p>Transparent communication at every stage.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                        <div class="feature-text">
                            <h4>Sustainable Focus</h4>
                            <p>Eco-friendly materials and methods.</p>
                        </div>
                    </div>
                </div>
                <a href="/about" class="btn btn-secondary">
                    Our Story <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="about-image" data-animate>
                <?php if ($aboutImage): ?>
                    <img src="<?= esc($aboutImage) ?>" alt="<?= esc($aboutTitle) ?>" data-ts-bg="about.image">
                <?php else: ?>
                    <div class="image-placeholder">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                <?php endif; ?>
                <div class="about-experience">
                    <span class="experience-years">22</span>
                    <span class="experience-text">Years of Excellence</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section services-section" id="services">
    <div class="container">
        <div class="section-header center" data-animate>
            <span class="section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="services-grid">
            <div class="service-card" data-animate>
                <div class="service-icon"><i class="fas fa-drafting-compass"></i></div>
                <h3>Architectural Design</h3>
                <p>Custom architectural solutions that balance aesthetics, functionality, and sustainability.</p>
                <ul class="service-list">
                    <li>Concept Development</li>
                    <li>3D Visualization</li>
                    <li>Permit Acquisition</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon"><i class="fas fa-tools"></i></div>
                <h3>Construction Management</h3>
                <p>End-to-end project oversight ensuring timelines, budgets, and quality standards are met.</p>
                <ul class="service-list">
                    <li>Budget Planning</li>
                    <li>Subcontractor Coordination</li>
                    <li>Quality Control</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon"><i class="fas fa-home"></i></div>
                <h3>Renovation & Remodeling</h3>
                <p>Transforming existing spaces with precision craftsmanship and modern upgrades.</p>
                <ul class="service-list">
                    <li>Structural Upgrades</li>
                    <li>Interior Remodeling</li>
                    <li>Historic Preservation</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon"><i class="fas fa-solar-panel"></i></div>
                <h3>Sustainable Building</h3>
                <p>Green construction practices that reduce environmental impact and operational costs.</p>
                <ul class="service-list">
                    <li>Energy-Efficient Systems</li>
                    <li>Green Material Sourcing</li>
                    <li>LEED Certification</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon"><i class="fas fa-paint-roller"></i></div>
                <h3>Interior Finishing</h3>
                <p>Premium finishes and custom millwork that elevate the final aesthetic of your space.</p>
                <ul class="service-list">
                    <li>Custom Cabinetry</li>
                    <li>Flooring & Tiling</li>
                    <li>Fixture Installation</li>
                </ul>
            </div>
            <div class="service-card" data-animate>
                <div class="service-icon"><i class="fas fa-clipboard-check"></i></div>
                <h3>Post-Construction</h3>
                <p>Comprehensive warranty support and maintenance services for long-term satisfaction.</p>
                <ul class="service-list">
                    <li>Warranty Management</li>
                    <li>Preventive Maintenance</li>
                    <li>System Training</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section projects-section" id="projects">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="projects.label"><?= esc($projectsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="projects.title"><?= esc($projectsTitle) ?></h2>
            <p class="section-desc" data-ts="projects.description"><?= esc($projectsDesc) ?></p>
        </div>
        <?php if (!empty($pages)): ?>
        <div class="projects-masonry">
            <?php foreach (array_slice($pages, 0, 6) as $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="project-card" data-animate>
                <?php if (!empty($p['featured_image'])): ?>
                <div class="project-image">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                    <div class="project-overlay">
                        <span class="view-btn">View Project <i class="fas fa-external-link-alt"></i></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="project-info">
                    <span class="project-category"><?= esc($p['template'] ?? 'Commercial') ?></span>
                    <h3><?= esc($p['title']) ?></h3>
                    <p><?= esc(mb_strimwidth(strip_tags(!empty($p['excerpt']) ? $p['excerpt'] : $p['content']), 0, 100, '...')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="projects-placeholder">
            <p>No project pages yet. Add pages in the CMS to display them here.</p>
        </div>
        <?php endif; ?>
        <div class="section-actions" data-animate>
            <a href="/portfolio" class="btn btn-outline">View Full Portfolio</a>
        </div>
    </div>
</section>

<section class="section articles-section" id="articles">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach (array_slice($articles, 0, 3) as $a): ?>
            <article class="article-card" data-animate>
                <?php if (!empty($a['featured_image'])): ?>
                <a href="/article/<?= esc($a['slug']) ?>" class="article-image">
                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                </a>
                <?php endif; ?>
                <div class="article-content">
                    <div class="article-meta">
                        <?php if (!empty($a['category_name'])): ?>
                        <span class="article-category"><?= esc($a['category_name']) ?></span>
                        <?php endif; ?>
                        <span class="article-date"><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                        <span class="article-views"><i class="fas fa-eye"></i> <?= esc($a['views'] ?? 0) ?></span>
                    </div>
                    <h3><a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a></h3>
                    <p><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 150, '...')) ?></p>
                    <a href="/article/<?= esc($a['slug']) ?>" class="article-link">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="articles-placeholder">
            <p>No articles yet. Add articles in the CMS to display them here.</p>
        </div>
        <?php endif; ?>
        <div class="section-actions center" data-animate>
            <a href="<?= esc($articlesBtnLink) ?>" class="btn btn-secondary" data-ts="articles.btn_text" data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?> <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="section cta-section" id="cta">
    <div class="cta-bg" <?php if ($ctaBgImage): ?>style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"<?php endif; ?>></div>
    <div class="cta-overlay"></div>
    <div class="container">
        <div class="cta-content" data-animate>
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" class="btn btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?> <i class="fas fa-calendar-check"></i>
                </a>
                <a href="tel:<?= esc(theme_get('contact.phone', '+15551234567')) ?>" class="btn btn-outline-light">
                    <i class="fas fa-phone"></i> Call Now
                </a>
            </div>
        </div>
    </div>
    <div class="cta-divider">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M1200,0V120H0V0C400,80 800,100 1200,0Z"></path>
        </svg>
    </div>
</section>