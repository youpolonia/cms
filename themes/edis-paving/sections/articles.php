<?php
$articlesLabel = theme_get('articles.label', 'Latest News');
$articlesTitle = theme_get('articles.title', 'Tips & Project Updates');
$articlesDesc = theme_get('articles.description', 'Stay informed with paving tips, maintenance advice, and the latest news from our projects across Essex.');
$articlesBtnText = theme_get('articles.btn_text', 'Read All Articles');
$articlesBtnLink = theme_get('articles.btn_link', '/articles');
?>
<section class="section articles-section" id="articles">
    <div class="container">
        <div class="articles-header" data-animate>
            <div class="section-header-left">
                <span class="section-label" data-ts="articles.label">
                    <i class="fas fa-newspaper"></i>
                    <?= esc($articlesLabel) ?>
                </span>
                <h2 class="section-title" data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
                <p class="section-desc" data-ts="articles.description"><?= esc($articlesDesc) ?></p>
            </div>
            <a href="<?= esc($articlesBtnLink) ?>" 
               class="btn btn-outline articles-btn-desktop"
               data-ts="articles.btn_text"
               data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="articles-grid">
            <?php if (!empty($articles)): ?>
                <?php foreach (array_slice($articles, 0, 3) as $index => $a): ?>
                    <article class="article-card <?= $index === 0 ? 'article-featured' : '' ?>" data-animate>
                        <a href="/article/<?= esc($a['slug']) ?>" class="article-link">
                            <div class="article-image">
                                <?php if (!empty($a['featured_image'])): ?>
                                    <img src="<?= esc($a['featured_image']) ?>" alt="<?= esc($a['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="article-placeholder">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="article-content">
                                <div class="article-meta">
                                    <?php if (!empty($a['category_name'])): ?>
                                        <span class="article-category"><?= esc($a['category_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="article-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?>
                                    </span>
                                </div>
                                <h3 class="article-title"><?= esc($a['title']) ?></h3>
                                <p class="article-excerpt"><?= esc(mb_strimwidth(strip_tags(!empty($a['excerpt']) ? $a['excerpt'] : $a['content']), 0, 120, '...')) ?></p>
                                <span class="article-read-more">
                                    Read Article
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="article-card article-featured" data-animate>
                    <div class="article-link">
                        <div class="article-image">
                            <div class="article-placeholder">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                        </div>
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-category">Tips & Advice</span>
                                <span class="article-date"><i class="far fa-calendar-alt"></i> Coming Soon</span>
                            </div>
                            <h3 class="article-title">How to Choose the Right Paving for Your Home</h3>
                            <p class="article-excerpt">A comprehensive guide to selecting materials, patterns, and styles that complement your property...</p>
                            <span class="article-read-more">Read Article <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </article>
                <article class="article-card" data-animate>
                    <div class="article-link">
                        <div class="article-image">
                            <div class="article-placeholder">
                                <i class="fas fa-tools"></i>
                            </div>
                        </div>
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-category">Maintenance</span>
                                <span class="article-date"><i class="far fa-calendar-alt"></i> Coming Soon</span>
                            </div>
                            <h3 class="article-title">Seasonal Driveway Care Guide</h3>
                            <p class="article-excerpt">Keep your driveway looking pristine year-round with our expert maintenance tips...</p>
                            <span class="article-read-more">Read Article <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </article>
                <article class="article-card" data-animate>
                    <div class="article-link">
                        <div class="article-image">
                            <div class="article-placeholder">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-category">Projects</span>
                                <span class="article-date"><i class="far fa-calendar-alt"></i> Coming Soon</span>
                            </div>
                            <h3 class="article-title">Project Spotlight: Chigwell Renovation</h3>
                            <p class="article-excerpt">Take a behind-the-scenes look at our latest complete front garden transformation...</p>
                            <span class="article-read-more">Read Article <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </article>
            <?php endif; ?>
        </div>
        
        <div class="articles-cta-mobile" data-animate>
            <a href="<?= esc($articlesBtnLink) ?>" 
               class="btn btn-outline btn-lg"
               data-ts="articles.btn_text"
               data-ts-href="articles.btn_link">
                <?= esc($articlesBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>