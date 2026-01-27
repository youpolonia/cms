<?php
declare(strict_types=1);

require_once __DIR__ . '/../../services/personalization/privacymanager.php';

/**
 * Admin Privacy Settings Panel
 */
function renderPrivacySettingsPanel(): string {
    $html = '
<div class="privacy-settings">';
    $html .= '
<h2>Privacy Management</h2>';
    
    // Data retention control
    $currentRetention = PrivacyManager::getRetentionPeriod();
    $html .= '
<div class="setting-group">';
    $html .= '<label>Data Retention Period (days):</label>';
    $html .= '
<input type="number" name="retention_days" value="' .
 $currentRetention . '" min="1" max="365">';
    $html .= '</div>';

    // Bulk operations
    $html .= '
<div class="actions">';
    $html .= '
<button class="purge-btn">Purge Expired Data</button>';
    $html .= '
</div>';

    $html .= '</div>';
    return $html;
}
