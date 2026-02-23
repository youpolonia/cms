<?php
$statsLabel = theme_get('stats.label', 'Our Track Record');
$statsTitle = theme_get('stats.title', 'Numbers That Speak for Themselves');
$statsDesc = theme_get('stats.description', 'Years of emergency response experience have built our reputation as the region\'s most trusted storm damage specialists.');

$stats = [
    [
        'number' => theme_get('stats.stat1_number', '5,000+'),
        'label' => theme_get('stats.stat1_label', 'Emergency Repairs'),
        'icon' => 'fa-tools',
        'ts_number' => 'stats.stat1_number',
        'ts_label' => 'stats.stat1_label'
    ],
    [
        'number' => theme_get('stats.stat2_number', '30 min'),
        'label' => theme_get('stats.stat2_label', 'Average Response'),
        'icon' => 'fa-stopwatch',
        'ts_number' => 'stats.stat2_number',
        'ts_label' => 'stats.stat2_label'
    ],
    [
        'number' => theme_get('stats.stat3_number', '98%'),
        'label' => theme_get('stats.stat3_label', 'Customer Satisfaction'),
        'icon' => 'fa-smile',
        'ts_number' => 'stats.stat3_number',
        'ts_label' => 'stats.stat3_label'
    ],
    [
        'number' => theme_get('stats.stat4_number', '15+'),
        'label' => theme_get('stats.stat4_label', 'Years Experience'),
        'icon' => 'fa-award',
        'ts_number' => 'stats.stat4_number',
        'ts_label' => 'stats.stat4_label'
    ]
];
?>
<section class="ssp-stats" id="stats">
    <div class="ssp-stats-bg"></div>
    <div class="ssp-stats-container">
        <div class="ssp-stats-content" data-animate>
            <span class="ssp-section-label ssp-section-label--light" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <h2 class="ssp-stats-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="ssp-stats-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        
        <div class="ssp-stats-grid" data-animate>
            <?php foreach ($stats as $index => $stat): ?>
            <div class="ssp-stat-card" style="--delay: <?= $index * 0.15 ?>s;">
                <div class="ssp-stat-icon">
                    <i class="fas <?= $stat['icon'] ?>"></i>
                </div>
                <div class="ssp-stat-number" data-ts="<?= $stat['ts_number'] ?>"><?= esc($stat['number']) ?></div>
                <div class="ssp-stat-label" data-ts="<?= $stat['ts_label'] ?>"><?= esc($stat['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
