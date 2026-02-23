<?php
$projectsLabel = theme_get('projects.label', 'Our Portfolio');
$projectsTitle = theme_get('projects.title', 'Recent Projects');
$projectsDesc = theme_get('projects.description', 'Browse our latest completed work across Essex');
?>
<section class="section projects-section" id="projects">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="projects.label">
                <i class="fas fa-images"></i>
                <?= esc($projectsLabel) ?>
            </span>
            <h2 class="section-title" data-ts="projects.title"><?= esc($projectsTitle) ?></h2>
            <p class="section-desc" data-ts="projects.description"><?= esc($projectsDesc) ?></p>
        </div>
        <?php if (!empty($pages)): ?>
        <div class="projects-mosaic">
            <?php foreach (array_slice($pages, 0, 6) as $index => $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" 
               class="project-tile project-tile-<?= ($index % 3) + 1 ?>" 
               data-animate 
               style="--delay: <?= $index * 0.1 ?>s">
                <?php if (!empty($p['featured_image'])): ?>
                <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                <?php else: ?>
                <div class="project-placeholder">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <?php endif; ?>
                <div class="project-overlay">
                    <h3 class="project-title"><?= esc($p['title']) ?></h3>
                    <span class="project-view">
                        View Project <i class="fas fa-arrow-right"></i>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="projects-empty" data-animate>
            <i class="fas fa-images"></i>
            <p>Project gallery coming soon</p>
        </div>
        <?php endif; ?>
        <div class="projects-cta" data-animate>
            <a href="/pages" class="btn btn-outline">
                View All Projects <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
