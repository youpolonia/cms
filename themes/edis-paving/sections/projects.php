<?php
$projectsLabel = theme_get('projects.label', 'Our Portfolio');
$projectsTitle = theme_get('projects.title', 'Recent Transformations');
$projectsDesc = theme_get('projects.description', 'See the quality of our work through these recent projects. Every job receives the same dedication—whether it\'s a small patio or a large commercial installation.');
?>
<section class="section projects-section" id="projects">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="projects.label">
                <i class="fas fa-camera"></i>
                <?= esc($projectsLabel) ?>
            </span>
            <h2 class="section-title" data-ts="projects.title"><?= esc($projectsTitle) ?></h2>
            <p class="section-desc" data-ts="projects.description"><?= esc($projectsDesc) ?></p>
        </div>
        
        <div class="projects-showcase">
            <?php if (!empty($pages)): ?>
                <?php foreach (array_slice($pages, 0, 6) as $index => $p): ?>
                    <a href="/page/<?= esc($p['slug']) ?>" class="project-item project-item-<?= $index + 1 ?>" data-animate>
                        <div class="project-image">
                            <?php if (!empty($p['featured_image'])): ?>
                                <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="project-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="project-overlay">
                            <div class="project-content">
                                <span class="project-category">Featured Project</span>
                                <h3 class="project-title"><?= esc($p['title']) ?></h3>
                                <span class="project-link">
                                    View Details
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="project-item project-item-1" data-animate>
                    <div class="project-image">
                        <div class="project-placeholder">
                            <i class="fas fa-th-large"></i>
                        </div>
                    </div>
                    <div class="project-overlay">
                        <div class="project-content">
                            <span class="project-category">Block Paving</span>
                            <h3 class="project-title">Victorian Driveway Restoration</h3>
                            <span class="project-link">View Details <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
                <div class="project-item project-item-2" data-animate>
                    <div class="project-image">
                        <div class="project-placeholder">
                            <i class="fas fa-border-all"></i>
                        </div>
                    </div>
                    <div class="project-overlay">
                        <div class="project-content">
                            <span class="project-category">Patio</span>
                            <h3 class="project-title">Modern Garden Terrace</h3>
                            <span class="project-link">View Details <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
                <div class="project-item project-item-3" data-animate>
                    <div class="project-image">
                        <div class="project-placeholder">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="project-overlay">
                        <div class="project-content">
                            <span class="project-category">Commercial</span>
                            <h3 class="project-title">Office Park Resurfacing</h3>
                            <span class="project-link">View Details <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
                <div class="project-item project-item-4" data-animate>
                    <div class="project-image">
                        <div class="project-placeholder">
                            <i class="fas fa-road"></i>
                        </div>
                    </div>
                    <div class="project-overlay">
                        <div class="project-content">
                            <span class="project-category">Asphalt</span>
                            <h3 class="project-title">Country Estate Driveway</h3>
                            <span class="project-link">View Details <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="projects-cta" data-animate>
            <a href="/gallery" class="btn btn-outline btn-lg">
                View Full Gallery
                <i class="fas fa-images"></i>
            </a>
        </div>
    </div>
</section>