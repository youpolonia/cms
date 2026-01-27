<?php
$hasData = !empty($metrics['summary']);

?><div class="metric-grid">
    <div class="metric-card">
        <div class="metric-value">
            <?= $hasData ? number_format($metrics['summary']['total_views']) : '--' ?>
        </div>
        <div class="metric-label">Total Views</div>
        <?php if ($hasData && $metrics['summary']['views_change'] != 0): ?>
            <div class="metric-change <?= $metrics['summary']['views_change'] > 0 ? 'positive' : 'negative' ?>">
                <?= abs($metrics['summary']['views_change']) ?>%
                <?= $metrics['summary']['views_change'] > 0 ? '↑' : '↓' ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="metric-card">
        <div class="metric-value">
            <?= $hasData ? number_format($metrics['summary']['avg_time'], 1) : '--' ?> min
        </div>
        <div class="metric-label">Avg. Time</div>
        <?php if ($hasData && $metrics['summary']['time_change'] != 0): ?>
            <div class="metric-change <?= $metrics['summary']['time_change'] > 0 ? 'positive' : 'negative' ?>">
                <?= abs($metrics['summary']['time_change']) ?>%
                <?= $metrics['summary']['time_change'] > 0 ? '↑' : '↓' ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="metric-card">
        <div class="metric-value">
            <?= $hasData ? number_format($metrics['summary']['bounce_rate'], 1) : '--' ?>%
        </div>
        <div class="metric-label">Bounce Rate</div>
        <?php if ($hasData && $metrics['summary']['bounce_change'] != 0): ?>
            <div class="metric-change <?= $metrics['summary']['bounce_change'] > 0 ? 'negative' : 'positive' ?>">
                <?= abs($metrics['summary']['bounce_change']) ?>%
                <?= $metrics['summary']['bounce_change'] > 0 ? '↑' : '↓' ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="metric-card">
        <div class="metric-value">
            <?= $hasData ? number_format($metrics['summary']['return_visitors']) : '--' ?>
        </div>
        <div class="metric-label">Return Visitors</div>
        <?php if ($hasData && $metrics['summary']['return_change'] != 0): ?>
            <div class="metric-change <?= $metrics['summary']['return_change'] > 0 ? 'positive' : 'negative' ?>">
                <?= abs($metrics['summary']['return_change']) ?>%
                <?= $metrics['summary']['return_change'] > 0 ? '↑' : '↓' ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.metric-card {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.metric-value {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.metric-label {
    color: #666;
    font-size: 0.9rem;
}

.metric-change {
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

.metric-change.positive {
    color: #28a745;
}

.metric-change.negative {
    color: #dc3545;
}
</style>
