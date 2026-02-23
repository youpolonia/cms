<?php
$statsLabel = theme_get('stats.label', 'The Numbers');
$statsTitle = theme_get('stats.title', 'Trusted by Builders Worldwide');
$statsDesc = theme_get('stats.description', 'From solo founders to growing teams, thousands are shipping faster with CodeForge.');
?>
<section class="section stats-section" id="stats">
    <div class="stats-bg-glow"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        
        <div class="stats-grid" data-animate>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value" data-count="12500">12,500+</div>
                <div class="stat-label">Active Builders</div>
                <div class="stat-bar"><span style="width: 85%;"></span></div>
            </div>
            
            <div class="stat-card featured">
                <div class="stat-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="stat-value" data-count="47000">47,000+</div>
                <div class="stat-label">Apps Generated</div>
                <div class="stat-bar"><span style="width: 92%;"></span></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-code"></i>
                </div>
                <div class="stat-value">2.4M</div>
                <div class="stat-label">Lines of Code</div>
                <div class="stat-bar"><span style="width: 78%;"></span></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">94%</div>
                <div class="stat-label">Time Saved</div>
                <div class="stat-bar"><span style="width: 94%;"></span></div>
            </div>
        </div>
        
        <div class="stats-logos" data-animate>
            <p class="logos-label">Powering products at</p>
            <div class="logos-row">
                <div class="logo-item">Y Combinator</div>
                <div class="logo-item">Techstars</div>
                <div class="logo-item">500 Startups</div>
                <div class="logo-item">Indie Hackers</div>
                <div class="logo-item">Product Hunt</div>
            </div>
        </div>
    </div>
</section>
