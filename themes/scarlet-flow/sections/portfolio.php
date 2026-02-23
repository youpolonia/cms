<?php
$portfolioLabel = theme_get('portfolio.label', 'Our Work');
$portfolioTitle = theme_get('portfolio.title', 'DTC Brands We’ve Scaled');
$portfolioDesc = theme_get('portfolio.description', 'Real results for direct-to-consumer companies across fashion, wellness, tech, and home goods.');
?>
<section class="sf-section sf-portfolio">
    <div class="container">
        <div class="sf-section__header" data-animate>
            <span class="sf-section__label" data-ts="portfolio.label"><?= esc($portfolioLabel) ?></span>
            <div class="sf-section__divider"></div>
            <h2 class="sf-section__title" data-ts="portfolio.title"><?= esc($portfolioTitle) ?></h2>
            <p class="sf-section__desc" data-ts="portfolio.description"><?= esc($portfolioDesc) ?></p>
        </div>
        <div class="sf-portfolio__grid">
            <?php if (!empty($pages)): ?>
                <?php foreach (array_slice($pages, 0, 4) as $p): ?>
                    <a href="/page/<?= esc($p['slug']) ?>" class="sf-portfolio__item" data-animate>
                        <div class="sf-portfolio__image">
                            <?php if (!empty($p['featured_image'])): ?>
                                <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="sf-portfolio__placeholder">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                            <?php endif; ?>
                            <div class="sf-portfolio__overlay">
                                <span class="sf-portfolio__view">View Case Study</span>
                            </div>
                        </div>
                        <div class="sf-portfolio__content">
                            <span class="sf-portfolio__category"><?= esc($p['category_name'] ?? 'Marketing') ?></span>
                            <h3 class="sf-portfolio__title"><?= esc($p['title']) ?></h3>
                            <div class="sf-portfolio__results">
                                <div class="sf-portfolio__result">
                                    <strong>+185%</strong>
                                    <span>ROAS</span>
                                </div>
                                <div class="sf-portfolio__result">
                                    <strong>-32%</strong>
                                    <span>CPA</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback portfolio items -->
                <div class="sf-portfolio__item" data-animate>
                    <div class="sf-portfolio__image">
                        <div class="sf-portfolio__placeholder">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div class="sf-portfolio__overlay">
                            <span class="sf-portfolio__view">View Case Study</span>
                        </div>
                    </div>
                    <div class="sf-portfolio__content">
                        <span class="sf-portfolio__category">Fashion DTC</span>
                        <h3 class="sf-portfolio__title">Luxe Apparel Co.</h3>
                        <div class="sf-portfolio__results">
                            <div class="sf-portfolio__result">
                                <strong>+220%</strong>
                                <span>ROAS</span>
                            </div>
                            <div class="sf-portfolio__result">
                                <strong>-45%</strong>
                                <span>CPA</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sf-portfolio__item" data-animate>
                    <div class="sf-portfolio__image">
                        <div class="sf-portfolio__placeholder">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <div class="sf-portfolio__overlay">
                            <span class="sf-portfolio__view">View Case Study</span>
                        </div>
                    </div>
                    <div class="sf-portfolio__content">
                        <span class="sf-portfolio__category">Wellness</span>
                        <h3 class="sf-portfolio__title">VitaBoost Supplements</h3>
                        <div class="sf-portfolio__results">
                            <div class="sf-portfolio__result">
                                <strong>+150%</strong>
                                <span>ROAS</span>
                            </div>
                            <div class="sf-portfolio__result">
                                <strong>-28%</strong>
                                <span>CPA</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sf-portfolio__item" data-animate>
                    <div class="sf-portfolio__image">
                        <div class="sf-portfolio__placeholder">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="sf-portfolio__overlay">
                            <span class="sf-portfolio__view">View Case Study</span>
                        </div>
                    </div>
                    <div class="sf-portfolio__content">
                        <span class="sf-portfolio__category">Tech</span>
                        <h3 class="sf-portfolio__title">GadgetFlow</h3>
                        <div class="sf-portfolio__results">
                            <div class="sf-portfolio__result">
                                <strong>+310%</strong>
                                <span>ROAS</span>
                            </div>
                            <div class="sf-portfolio__result">
                                <strong>-52%</strong>
                                <span>CPA</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sf-portfolio__item" data-animate>
                    <div class="sf-portfolio__image">
                        <div class="sf-portfolio__placeholder">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="sf-portfolio__overlay">
                            <span class="sf-portfolio__view">View Case Study</span>
                        </div>
                    </div>
                    <div class="sf-portfolio__content">
                        <span class="sf-portfolio__category">Home Goods</span>
                        <h3 class="sf-portfolio__title">Artisan Living</h3>
                        <div class="sf-portfolio__results">
                            <div class="sf-portfolio__result">
                                <strong>+190%</strong>
                                <span>ROAS</span>
                            </div>
                            <div class="sf-portfolio__result">
                                <strong>-35%</strong>
                                <span>CPA</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="sf-portfolio__footer" data-animate>
            <a href="/portfolio" class="sf-btn sf-btn--secondary">
                <span>View All Case Studies</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
