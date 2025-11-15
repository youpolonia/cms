<?php
require_once __DIR__.'/../../services/VersionService.php';

class VersionCompareView {
    public static function render(array $version1, array $version2): string {
        $html = '
<div class="version-compare-container">';
        $html .= '
<h2>Version Comparison</h2>';
        $html .= '
<div class="version-diff-grid">';
        
        // Side-by-side comparison
        $html .= '
<div class="version-column">';
        $html .= '
<h3>Version '.htmlspecialchars(
$version1['version_number']).'</h3>';
        $html .= '
<div class="version-content">'.htmlspecialchars($version1['content']).'</div>';
        $html .= '
</div>';
        
        $html .= '
<div class="version-column">';
        $html .= '
<h3>Version '.htmlspecialchars(
$version2['version_number']).'</h3>';
        $html .= '
<div class="version-content">'.htmlspecialchars($version2['content']).'</div>';
        $html .= '
</div>';
        
        $html .= '</div>'; // Close grid
        
        // Diff highlights
        $diffs = VersionService::calculateDiffs($version1['content'], $version2['content']);
        if (!empty($diffs)) {
            $html .= '
<div class="diff-highlights">';
            $html .= '
<h3>Changes</h3>';
            foreach (
$diffs as $diff) {
                $html .= '
<div class="diff-item '.htmlspecialchars(
$diff['type']).'">';
                $html .= htmlspecialchars($diff['content']);
                $html .= '
</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '
</div>'; // Close container
        
        return $html;
    }
}
