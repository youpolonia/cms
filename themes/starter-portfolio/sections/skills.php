<?php
/**
 * Starter Portfolio — Skills Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$skillsLabel = theme_get('skills.label', 'What We Do');
$skillsTitle = theme_get('skills.title', 'Our Skills');
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
            <div class="skill-icon"><i class="fas fa-code"></i></div>
            <h3 class="skill-title">Development</h3>
            <p class="skill-desc">Building modern, performant web applications with clean code.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-palette"></i></div>
            <h3 class="skill-title">Design</h3>
            <p class="skill-desc">Crafting beautiful, intuitive interfaces that users love.</p>
        </div>
        <div class="skill-card">
            <div class="skill-icon"><i class="fas fa-rocket"></i></div>
            <h3 class="skill-title">Strategy</h3>
            <p class="skill-desc">Planning and executing digital strategies that deliver results.</p>
        </div>
    </div>
</section>
