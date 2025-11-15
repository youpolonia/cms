<?php
declare(strict_types=1);

require_once __DIR__ . '/../../services/personalization/PrivacyManager.php';
require_once __DIR__ . '/../../services/personalization/UserTracker.php';

/**
 * User Preference Center
 */
function renderPreferenceCenter(int $userId): string {
    $html = '<div class="preference-center">';
    $html .= '<h2>Personalization Preferences</h2>';
    
    // Tracking toggle
    $trackingStatus = UserTracker::hasConsent($userId, 'tracking');
    $html .= '<div class="preference-item">';
    $html .= '<label>';
    $html .= '<input type="checkbox" name="tracking" ' . ($trackingStatus ? 'checked' : '') . '>';
    $html .= ' Allow activity tracking';
    $html .= '</label>';
    $html .= '</div>';

    // Data retention info
    $retentionDays = PrivacyManager::getRetentionPeriod();
    $html .= '<div class="preference-info">';
    $html .= "Your data will be automatically deleted after $retentionDays days";
    $html .= '</div>';

    $html .= '<button class="save-btn">Save Preferences</button>';
    $html .= '</div>';

    return $html;
}
