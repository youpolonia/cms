<?php
$projectsLabel = theme_get('projects.label', 'Our Portfolio');
$projectsTitle = theme_get('projects.title', 'Recent Projects We\'re Proud Of');
$projectsDesc = theme_get('projects.description', 'Browse our gallery of completed work. Every project showcases our commitment to quality and attention to detail.');
$projectsBtnText = theme_get('projects.btn_text', 'View Full Gallery');
$projectsBtnLink = theme_get('projects.btn_link', '/gallery');
?>
<section class="section projects-section" id="projects">
    <div class="projects-bg"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="projects.label"><?= esc($projectsLabel) ?></span>
            <h2 class="section-title" data-ts="projects.title"><?= esc($projectsTitle) ?></h2>
            <p class="section-desc" data-ts="projects.description"><?= esc($projectsDesc) ?></p>
        </div>
        <div class="projects-showcase">
            <?php if (!empty($pages)): ?>
                <?php foreach (array_slice($pages, 0, 6) as $index => $p): ?>
                <a href="/page/<?= esc($p['slug']) ?>" class="project-item project-item--<?= ($index % 3 === 0) ? 'large' : 'small' ?>" data-animate>
                    <?php if (!empty($p['featured_image'])): ?>
                    <div class="project-image" style="background-image: url('<?= esc($p['featured_image']) ?>')"></div>
                    <?php else: ?>
                    <div class="project-image project-image--placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                    <?php endif; ?>
                    <div class="project-overlay">
                        <div class="project-info">
                            <h3 class="project-title"><?= esc($p['title']) ?></h3>
                            <span class="project-link">View Project <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <?php 
                $placeholders = [
                    ['title' => 'Block Paving Driveway', 'type' => 'Residential'],
                    ['title' => 'Commercial Car Park', 'type' => 'Commercial'],
                    ['title' => 'Garden Patio Design', 'type' => 'Residential'],
                    ['title' => 'Tarmac Installation', 'type' => 'Commercial'],
                    ['title' => 'Natural Stone Patio', 'type' => 'Residential'],
                    ['title' => 'Complete Landscaping', 'type' => 'Residential']
                ];
                foreach ($placeholders as $index => $project): ?>
                <div class="project-item project-item--<?= ($index % 3 === 0) ? 'large' : 'small' ?>" data-animate>
                    <div class="project-image project-image--placeholder">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <div class="project-overlay">
                        <div class="project-info">
                            <span class="project-type"><?= $project['type'] ?></span>
                            <h3 class="project-title"><?= $project['title'] ?></h3>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="projects-footer" data-animate>
            <a href="<?= esc($projectsBtnLink) ?>" class="btn btn-primary btn-lg" data-ts="projects.btn_text" data-ts-href="projects.btn_link">
                <span><?= esc($projectsBtnText) ?></span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>