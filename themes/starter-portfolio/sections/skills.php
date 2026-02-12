<?php
/**
 * Starter Portfolio — Skills Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$skillsLabel = theme_get('skills.label', 'Expertise');
$skillsTitle = theme_get('skills.title', 'What I Do');
?>
<!-- Skills Section -->
<div class="section-divider"><hr></div>
<section class="section">
    <div class="section-header">
        <div class="section-label" data-ts="skills.label"><?= esc($skillsLabel) ?></div>
        <h2 class="section-title" data-ts="skills.title"><?= esc($skillsTitle) ?></h2>
    </div>
    <div class="skills-grid">
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-palette"></i></div>
            <h3 class="skill-title">Visual Design</h3>
            <p class="skill-desc">Brand identity, typography, color systems, and visual storytelling.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-mobile-alt"></i></div>
            <h3 class="skill-title">UI/UX Design</h3>
            <p class="skill-desc">User research, wireframing, prototyping, and interface design.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-camera"></i></div>
            <h3 class="skill-title">Photography</h3>
            <p class="skill-desc">Portrait, landscape, editorial, and fine art photography.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-code"></i></div>
            <h3 class="skill-title">Front-End Dev</h3>
            <p class="skill-desc">HTML, CSS, JavaScript — bringing designs to life in the browser.</p>
        </div>
    </div>
</section>
