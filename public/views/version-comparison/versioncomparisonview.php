<?php
class VersionComparisonView {
    public static function render($diffData, $stats) {
?>        <div class="version-comparison">
            <div class="comparison-header">
                <h2>Version Comparison</h2>
                <?php VersionNav::render(); 
?>            </div>
            
            <div class="comparison-body">
                <div class="side-by-side">
                    <div class="version-old">
                        <h3>Old Version</h3>
                        <?php echo DiffRenderer::visualDiff($diffData['side_by_side']);  ?>
                    </div>
                    <div class="version-new">
                        <h3>New Version</h3>
                        <?php echo DiffRenderer::visualDiff($diffData['side_by_side']);  ?>
                    </div>
                </div>
                
                <div class="stats-container">
                    <?php VersionStats::render($stats); 
?>                </div>
            </div>
        </div>
        <?php
    }
}
