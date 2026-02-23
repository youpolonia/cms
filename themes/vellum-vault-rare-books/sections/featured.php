<?php
$featuredLabel = theme_get('featured.label', 'Curated Selections');
$featuredTitle = theme_get('featured.title', 'Featured Acquisitions');
$featuredDesc = theme_get('featured.description', 'A rotating exhibition of exceptional first editions, signed copies, and historically significant manuscripts currently available for private viewing.');
$featuredBtnText = theme_get('featured.btn_text', 'View Full Catalogue');
$featuredBtnLink = theme_get('featured.btn_link', '/gallery');

$featuredImages = [
    'https://images.pexels.com/photos/33703392/pexels-photo-33703392.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/13012320/pexels-photo-13012320.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/34744054/pexels-photo-34744054.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/2393793/pexels-photo-2393793.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/33305547/pexels-photo-33305547.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/36095545/pexels-photo-36095545.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="section vvr-featured-section" id="featured" style="background-color: var(--surface);">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="featured.label"><?= esc($featuredLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="featured.title"><?= esc($featuredTitle) ?></h2>
            <p class="section-desc" data-ts="featured.description"><?= esc($featuredDesc) ?></p>
        </div>

        <div class="vvr-featured-grid">
            <!-- Item 1: Highlight -->
            <div class="vvr-featured-card vvr-featured-card--highlight" data-animate>
                <div class="vvr-featured-card-image">
                    <img src="<?= esc($featuredImages[0]) ?>" alt="Vibrant collection of books on display in a Delhi bookstore. Perfect for book lovers and readers." loading="lazy">
                    <div class="vvr-featured-card-badge">First Edition</div>
                </div>
                <div class="vvr-featured-card-content">
                    <h3 class="vvr-featured-card-title">"Ulysses" by James Joyce</h3>
                    <p class="vvr-featured-card-meta">Paris, 1922 | Signed by the author</p>
                    <p class="vvr-featured-card-desc">One of only 100 numbered copies on handmade paper, with original wrappers. Exceptional condition.</p>
                    <a href="/item/ulysses-1922" class="vvr-featured-card-link">Inquire for details <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="vvr-featured-card" data-animate>
                <div class="vvr-featured-card-image">
                    <img src="<?= esc($featuredImages[1]) ?>" alt="Captivating display of diverse book covers at a Bath, UK bookstore." loading="lazy">
                </div>
                <div class="vvr-featured-card-content">
                    <h3 class="vvr-featured-card-title">Shakespeare Folio</h3>
                    <p class="vvr-featured-card-meta">London, 1623 | Fourth Folio</p>
                    <p class="vvr-featured-card-desc">A remarkably clean copy of the Fourth Folio, with contemporary calf binding.</p>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="vvr-featured-card" data-animate>
                <div class="vvr-featured-card-image">
                    <img src="<?= esc($featuredImages[2]) ?>" alt="Warm, inviting bookstore with customers browsing books in a quaint indoor setting." loading="lazy">
                </div>
                <div class="vvr-featured-card-content">
                    <h3 class="vvr-featured-card-title">Newton's Principia</h3>
                    <p class="vvr-featured-card-meta">London, 1687 | First Edition</p>
                    <p class="vvr-featured-card-desc">The foundation of classical mechanics, with annotations by a contemporary mathematician.</p>
                </div>
            </div>

            <!-- Item 4 -->
            <div class="vvr-featured-card" data-animate>
                <div class="vvr-featured-card-image">
                    <img src="<?= esc($featuredImages[3]) ?>" alt="A young woman with long hair reading a book in a cozy bookstore aisle." loading="lazy">
                </div>
                <div class="vvr-featured-card-content">
                    <h3 class="vvr-featured-card-title">Austen Manuscript</h3>
                    <p class="vvr-featured-card-meta">Circa 1813 | Holograph pages</p>
                    <p class="vvr-featured-card-desc">Three autograph pages from an early draft of "Pride and Prejudice," with corrections.</p>
                </div>
            </div>
        </div>

        <div class="section-actions" data-animate>
            <a href="<?= esc($featuredBtnLink) ?>" class="vvr-btn vvr-btn-outline" data-ts="featured.btn_text" data-ts-href="featured.btn_link">
                <?= esc($featuredBtnText) ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
