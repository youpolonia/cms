<?php
// This file is a plain PHP version of dashboard.blade.php
// Assuming $redisStatus, $recentRedisFailures, and $metrics are passed to this script.

// For security, ensure variables are initialized if they might not be set.
$redisStatus = $redisStatus ?? ['healthy' => false, 'last_check' => 'N/A'];
$recentRedisFailures = $recentRedisFailures ?? [];
$metrics = $metrics ?? [
    'events_processed' => 0,
    'processing_errors' => 0,
    'queue_percentage' => 0,
    'queue_size' => 0
];

// Helper function for escaping output (alternative to Blade's {{ }})
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
?><div class="container-fluid">
    <h1>Analytics Processing Dashboard</h1>
    
    <div class="row">
        <!-- Redis Health Card -->
        <div class="col-md-6">
            <div class="card <?php if($redisStatus['healthy']): ?>card-success<?php else: ?>card-danger<?php endif; ?>">
                <div class="card-header">
                    <h3 class="card-title">Redis Status</h3>
                </div>
                <div class="card-body">
                    <p>Status: <strong><?= $redisStatus['healthy'] ? 'Healthy' : 'Unhealthy' ?></strong></p>
                    <p>Last Check: <?= e($redisStatus['last_check']) ?></p>
                    
                    <?php if(!$redisStatus['healthy']): ?>
                        <div class="alert alert-warning">
                            <h5>Recent Failures</h5>
                            <ul>
                                <?php foreach($recentRedisFailures as $failure): ?>
                                    <li>
                                        <?= e($failure->error_code) ?>: <?= e($failure->error_message) ?>
                                        <small class="text-muted">
                                            <?php
                                                // Assuming $failure->failed_at is a DateTime object or a string.
                                                // diffForHumans() is a Carbon method, not available in pure PHP without library.
                                                // Replace with a simple date format.
                                                if ($failure->failed_at instanceof DateTimeInterface) {
                                                    echo e($failure->failed_at->format('Y-m-d H:i:s'));
                                                } elseif (is_string($failure->failed_at)) {
                                                    echo e($failure->failed_at);
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Processing Metrics Card -->
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Processing Metrics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-tasks"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Events Processed</span>
                                    <span class="info-box-number"><?= e(number_format($metrics['events_processed'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Processing Errors</span>
                                    <span class="info-box-number"><?= e(number_format($metrics['processing_errors'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <div class="progress-text">Processing Queue</div>
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 style="width: <?= e($metrics['queue_percentage']) ?>%"></div>
                        </div>
                        <span class="progress-description">
                            <?= e($metrics['queue_size']) ?> items in queue
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
