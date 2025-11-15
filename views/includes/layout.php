<?php
/**
 * Layout wrapper for CMS views
 * @param string $title Page title
 * @param string $content Main view content
 */
function render_layout($title, $content, $is_admin = false) {
    // Set page title if not already set
    if (!isset($GLOBALS['pageTitle'])) {
        $GLOBALS['pageTitle'] = $title;
    }
    
    if ($is_admin) {
        // Include admin header/footer
        require_once __DIR__.'/../admin/includes/header.php';
        echo $content;
        require_once __DIR__.'/../admin/includes/footer.php';
    } else {
        // Include public header/footer
        require_once __DIR__.'/header.php';
        echo $content;
        require_once __DIR__.'/footer.php';
    }
}
