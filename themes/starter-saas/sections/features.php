<?php
/**
 * Starter SaaS — Features Section
 * Shows product features as glass cards with icons
 * Editable via Theme Studio. data-ts for live preview.
 */
$articlesLabel = theme_get('articles.label', 'Features');
$articlesTitle = theme_get('articles.title', 'Everything You Need');
$articlesDesc  = theme_get('articles.description', 'Powerful tools designed to streamline your workflow and boost productivity.');

// Product features (not dynamic articles — these define the product)
$features = [
    ['icon' => 'fas fa-bolt',         'title' => 'Workflow Automation', 'desc' => 'Build complex automations with a visual drag-and-drop editor. No code required.'],
    ['icon' => 'fas fa-chart-line',   'title' => 'Real-Time Analytics', 'desc' => 'Track KPIs, monitor team performance, and generate reports in seconds.'],
    ['icon' => 'fas fa-shield-alt',   'title' => 'Enterprise Security', 'desc' => 'SOC 2 Type II certified. End-to-end encryption, SSO, and role-based access.'],
    ['icon' => 'fas fa-plug',         'title' => '200+ Integrations',   'desc' => 'Connect with Slack, Jira, GitHub, Salesforce, and your entire stack.'],
    ['icon' => 'fas fa-users',        'title' => 'Team Collaboration',  'desc' => 'Shared workspaces, real-time editing, comments, and @mentions.'],
    ['icon' => 'fas fa-robot',        'title' => 'AI Assistant',        'desc' => 'Let AI suggest optimizations, auto-assign tasks, and predict bottlenecks.'],
];
?>
<!-- Features -->
<section class="features-section" id="features">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="articles.label"><?= esc($articlesLabel) ?></span>
            <h2 data-ts="articles.title"><?= esc($articlesTitle) ?></h2>
            <p data-ts="articles.description"><?= esc($articlesDesc) ?></p>
        </div>
        <div class="features-grid">
            <?php foreach ($features as $f): ?>
            <div class="feature-card glass-card">
                <div class="feature-icon"><i class="<?= $f['icon'] ?>"></i></div>
                <h3 class="feature-title"><?= esc($f['title']) ?></h3>
                <p class="feature-desc"><?= esc($f['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
