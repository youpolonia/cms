<?php
/**
 * Starter Business — Services Section (Pages)
 * Editable via Theme Studio. data-ts for live preview.
 */
$servicesLabel = theme_get('services.label', 'What We Do');
$servicesTitle = theme_get('services.title', 'Our Services');
$servicesDesc  = theme_get('services.description', 'We help businesses grow through strategy, innovation, and results-driven solutions.');

$serviceIcons = [
    'our-services' => 'fas fa-cogs',
    'services' => 'fas fa-cogs',
    'case-studies' => 'fas fa-chart-line',
    'careers' => 'fas fa-briefcase',
    'our-projects' => 'fas fa-project-diagram',
    'projects' => 'fas fa-project-diagram',
];
$serviceDescriptions = [
    'our-services' => 'Consulting, strategy, and implementation tailored to your business needs',
    'services' => 'Consulting, strategy, and implementation tailored to your business needs',
    'case-studies' => 'Real results from real clients — see how we drive measurable growth',
    'careers' => 'Join a team of innovators building the future of business consulting',
    'our-projects' => 'A portfolio of successful projects across industries and scales',
    'projects' => 'A portfolio of successful projects across industries and scales',
];
?>
<!-- Services (Pages) -->
<?php if (!empty($pages)): ?>
<section class="section services-section" id="services">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="services-grid">
            <?php foreach ($pages as $p): 
                $icon = $serviceIcons[$p['slug']] ?? 'fas fa-cube';
                $desc = $serviceDescriptions[$p['slug']] ?? esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...'));
            ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="service-card" style="text-decoration:none" data-animate>
                <?php if (!empty($p['featured_image'])): ?>
                <div style="margin:-24px -24px 16px;border-radius:12px 12px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="service-icon"><i class="<?= $icon ?>"></i></div>
                <?php endif; ?>
                <h3><?= esc($p['title']) ?></h3>
                <p><?= $desc ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
