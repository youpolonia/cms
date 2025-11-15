<?php
declare(strict_types=1);

require_once __DIR__ . '/../services/personalization/RecommendationEngine.php';

/**
 * Recommendation Widget
 */
function renderRecommendationWidget(int $userId): string {
    $recommendations = RecommendationEngine::getRecommendations($userId);
    
    $html = '
<div class="recommendation-widget">';
    $html .= '
<h3>Recommended for You</h3>';
    
    if (empty($recommendations)) {
        $html .= '
<div class="no-results">No recommendations available</div>';
    }
 else {
        $html .= '
<ul class="recommendation-list">';
        foreach ($recommendations as $itemId) {
            $html .= "
<li>
$itemId</li>";
        }
        $html .= '</ul>';
    }
    
    $html .= '
</div>';
    return $html;
}
