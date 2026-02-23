<?php
$statsLabel = theme_get('stats.label', 'By The Numbers');
$statsTitle = theme_get('stats.title', 'Delivering Results at Scale');
$statsDesc = theme_get('stats.description', 'Our autonomous fleet operates around the clock, processing thousands of deliveries with unmatched efficiency.');
?>
<section class="section stats-section" id="stats">
    <div class="stats-bg-pattern"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card stat-card-featured" data-animate>
                <div class="stat-icon-wrap">
                    <div class="stat-icon"><i class="fas fa-box"></i></div>
                </div>
                <div class="stat-content">
                    <span class="stat-number" data-ts="stats.deliveries">2.5M+</span>
                    <span class="stat-label">Deliveries Completed</span>
                </div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 95%"></div>
                </div>
            </div>
            
            <div class="stat-card" data-animate>
                <div class="stat-icon"><i class="fas fa-robot"></i></div>
                <span class="stat-number" data-ts="stats.robots">850+</span>
                <span class="stat-label">Active Robots</span>
            </div>
            
            <div class="stat-card" data-animate>
                <div class="stat-icon"><i class="fas fa-city"></i></div>
                <span class="stat-number" data-ts="stats.cities">45</span>
                <span class="stat-label">Cities Covered</span>
            </div>
            
            <div class="stat-card" data-animate>
                <div class="stat-icon"><i class="fas fa-route"></i></div>
                <span class="stat-number" data-ts="stats.miles">12M</span>
                <span class="stat-label">Miles Traveled</span>
            </div>
            
            <div class="stat-card" data-animate>
                <div class="stat-icon"><i class="fas fa-leaf"></i></div>
                <span class="stat-number" data-ts="stats.carbon">8,500</span>
                <span class="stat-label">Tons CO₂ Saved</span>
            </div>
            
            <div class="stat-card" data-animate>
                <div class="stat-icon"><i class="fas fa-store"></i></div>
                <span class="stat-number" data-ts="stats.partners">1,200+</span>
                <span class="stat-label">Retail Partners</span>
            </div>
        </div>
    </div>
</section>
