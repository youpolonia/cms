<?php
$aboutLabel = theme_get('about.label', 'Our Heritage');
$aboutTitle = theme_get('about.title', 'Three Generations of Paving Excellence');
$aboutDesc = theme_get('about.description', 'Since 2001, Edi\'s Paving has been transforming outdoor spaces across Essex. What started as a father-son operation has grown into one of the region\'s most respected groundworks and landscaping companies—all while maintaining the personal touch and attention to detail that made our reputation.');
$aboutImage = theme_get('about.image', $themePath . '/assets/images/about-team.jpg');
?>
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrapper" data-animate>
                <div class="about-image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>');"></div>
                <div class="experience-badge">
                    <span class="exp-number">23</span>
                    <span class="exp-text">Years of<br>Excellence</span>
                </div>
                <div class="about-accent"></div>
            </div>
            
            <div class="about-content" data-animate>
                <span class="section-label" data-ts="about.label">
                    <i class="fas fa-gem"></i>
                    <?= esc($aboutLabel) ?>
                </span>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="about-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                
                <div class="about-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Family Values</h4>
                            <p>Three generations committed to honest work and fair pricing.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Quality Guaranteed</h4>
                            <p>Every project backed by our comprehensive workmanship warranty.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="feature-text">
                            <h4>Personal Service</h4>
                            <p>Direct communication with owners, not salesmen or middlemen.</p>
                        </div>
                    </div>
                </div>
                
                <a href="#contact" class="btn btn-primary">
                    Discuss Your Project
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>