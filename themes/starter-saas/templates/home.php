<?php
/**
 * Starter SaaS — Home Template
 * 
 * Sections are loaded dynamically based on Section Manager order/enabled state.
 */

// Section ordering and visibility
$themeConfig = get_theme_config(get_active_theme());
$defaultOrder = array_column($themeConfig['homepage_sections'] ?? [], 'id');
$sectionOrder = theme_get_section_order();
if (empty($sectionOrder)) {
    $sectionOrder = $defaultOrder;
}

foreach ($sectionOrder as $sectionId) {
    if (!theme_section_enabled($sectionId)) continue;
    $sectionFile = __DIR__ . '/../sections/' . $sectionId . '.php';
    if (file_exists($sectionFile)) {
        require $sectionFile;
    }
}
?>
