<?php
class VersionStats {
    public static function render($stats) {
        
?><div class="version-stats">
            <h3>Change Statistics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label">Total Changes:</span>
                    <span class="stat-value"><?= $stats['total_changes'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Lines Added:</span>
                    <span class="stat-value added">+<?= $stats['added'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Lines Removed:</span>
                    <span class="stat-value removed">-<?= $stats['removed'] ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Unchanged:</span>
                    <span class="stat-value"><?= $stats['unchanged'] ?></span>
                </div>
            </div>
        </div>
        <?php
    }
}
