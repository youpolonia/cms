<?php
$projectsLabel = theme_get('projects.label', 'OUR PORTFOLIO');
$projectsTitle = theme_get('projects.title', 'Recent Construction Projects');
$projectsDesc = theme_get('projects.description', 'A showcase of our completed groundwork and paving projects, demonstrating our commitment to quality, safety, and timely delivery.');
?>
<section class="section projects-section" id="projects">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="projects.label"><?= esc($projectsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="projects.title"><?= esc($projectsTitle) ?></h2>
            <p class="section-desc" data-ts="projects.description"><?= esc($projectsDesc) ?></p>
        </div>
        <div class="projects-grid">
            <?php if (!empty($pages)): ?>
                <?php foreach (array_slice($pages, 0, 6) as $p): ?>
                    <a href="/page/<?= esc($p['slug']) ?>" class="project-card" data-animate>
                        <?php if (!empty($p['featured_image'])): ?>
                            <div class="project-image">
                                <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                                <div class="project-overlay">
                                    <span class="btn btn-outline">View Details <i class="fas fa-external-link-alt"></i></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="project-content">
                            <span class="project-category"><?= esc($p['category_name'] ?? 'Commercial') ?></span>
                            <h3 class="project-title"><?= esc($p['title']) ?></h3>
                            <p class="project-excerpt"><?= esc(mb_strimwidth(strip_tags(!empty($p['excerpt']) ? $p['excerpt'] : $p['content']), 0, 100, '...')) ?></p>
                            <div class="project-meta">
                                <span><i class="far fa-calendar"></i> <?= date('M Y', strtotime($p['created_at'])) ?></span>
                                <span><i class="fas fa-ruler-combined"></i> <?= $p['meta']['area'] ?? '15,000 sq ft' ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback demo projects -->
                <?php
                $demoProjects = [
                    ['title' => 'Industrial Park Pavement Overlay', 'category' => 'Asphalt', 'area' => '85,000 sq ft'],
                    ['title' => 'Corporate Campus Foundation & Drainage', 'category' => 'Concrete', 'area' => '2.5 acres'],
                    ['title' => 'Shopping Center Parking Lot Expansion', 'category' => 'Commercial', 'area' => '200 spaces'],
                    ['title' => 'Municipal Road Reconstruction', 'category' => 'Public Works', 'area' => '1.2 miles'],
                    ['title' => 'Warehouse Site Preparation', 'category' => 'Excavation', 'area' => '10 acres'],
                    ['title' => 'Hospital Emergency Access Paving', 'category' => 'Healthcare', 'area' => '45,000 sq ft'],
                ];
                ?>
                <?php foreach ($demoProjects as $project): ?>
                    <div class="project-card" data-animate>
                        <div class="project-image">
                            <div class="project-image-placeholder">
                                <i class="fas fa-hard-hat"></i>
                            </div>
                            <div class="project-overlay">
                                <span class="btn btn-outline">View Case Study</span>
                            </div>
                        </div>
                        <div class="project-content">
                            <span class="project-category"><?= $project['category'] ?></span>
                            <h3 class="project-title"><?= $project['title'] ?></h3>
                            <p class="project-excerpt">Professional paving and groundwork services delivered on schedule and within budget.</p>
                            <div class="project-meta">
                                <span><i class="far fa-calendar"></i> Completed 2024</span>
                                <span><i class="fas fa-ruler-combined"></i> <?= $project['area'] ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="section-footer" data-animate>
            <a href="/gallery" class="btn btn-outline">
                View Full Project Gallery <i class="fas fa-images"></i>
            </a>
        </div>
    </div>
</section>
