<?php
$heroBadge = theme_get('hero.badge', '🚀 Now in Public Beta');
$heroHeadline = theme_get('hero.headline', 'Describe Your App. Watch It Build.');
$heroSubtitle = theme_get('hero.subtitle', 'CodeForge turns plain English into production-ready code. No coding bootcamp required—just your ideas and a keyboard.');
$heroBtnText = theme_get('hero.btn_text', 'Try It Free');
$heroBtnLink = theme_get('hero.btn_link', '#contact');
$heroSecondaryText = theme_get('hero.secondary_text', 'See Examples');
$heroSecondaryLink = theme_get('hero.secondary_link', '#features');
$heroBgImage = theme_get('hero.bg_image', '');
?>
<section class="hero" id="hero">
    <?php if ($heroBgImage): ?>
    <div class="hero-bg" data-ts-bg="hero.bg_image" style="background-image: url('<?= esc($heroBgImage) ?>');"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-particles" id="heroParticles"></div>
    <div class="hero-grid-pattern"></div>
    
    <div class="hero-content">
        <div class="container">
            <div class="hero-inner" data-animate>
                <div class="hero-badge" data-ts="hero.badge">
                    <span class="badge-pulse"></span>
                    <?= esc($heroBadge) ?>
                </div>
                
                <h1 class="hero-headline" data-ts="hero.headline">
                    <?= esc($heroHeadline) ?>
                </h1>
                
                <p class="hero-subtitle" data-ts="hero.subtitle">
                    <?= esc($heroSubtitle) ?>
                </p>
                
                <div class="hero-actions">
                    <a href="<?= esc($heroBtnLink) ?>" 
                       class="btn btn-primary btn-lg hero-cta"
                       data-ts="hero.btn_text"
                       data-ts-href="hero.btn_link">
                        <span class="btn-glow"></span>
                        <?= esc($heroBtnText) ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="<?= esc($heroSecondaryLink) ?>" 
                       class="btn btn-ghost btn-lg"
                       data-ts="hero.secondary_text"
                       data-ts-href="hero.secondary_link">
                        <i class="fas fa-play-circle"></i>
                        <?= esc($heroSecondaryText) ?>
                    </a>
                </div>
                
                <div class="hero-terminal" data-animate>
                    <div class="terminal-header">
                        <span class="terminal-dot red"></span>
                        <span class="terminal-dot yellow"></span>
                        <span class="terminal-dot green"></span>
                        <span class="terminal-title">codeforge-cli</span>
                    </div>
                    <div class="terminal-body">
                        <div class="terminal-line">
                            <span class="prompt">$</span>
                            <span class="command typing">codeforge build "a task management app with Kanban boards"</span>
                        </div>
                        <div class="terminal-line output">
                            <span class="success">✓</span> Analyzing requirements...
                        </div>
                        <div class="terminal-line output">
                            <span class="success">✓</span> Generating React components...
                        </div>
                        <div class="terminal-line output">
                            <span class="success">✓</span> Setting up API routes...
                        </div>
                        <div class="terminal-line output highlight">
                            <span class="success">✓</span> Build complete! Preview at localhost:3000
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero-scroll">
        <span>Scroll to explore</span>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</section>
