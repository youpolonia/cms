<?php
/**
 * JTB Theme — Home Template
 * If homepage is built with JTB, layout.php renders it directly.
 * This is the fallback for non-JTB home pages.
 */
$pageContent = $page['content'] ?? $content ?? '';

if ($pageContent) {
    $isRich = strlen(strip_tags($pageContent)) !== strlen($pageContent);
    if ($isRich) {
        echo '<div class="jtb-home-content">' . $pageContent . '</div>';
    } else {
        echo '<div class="jtb-home-content"><p>' . nl2br(esc($pageContent)) . '</p></div>';
    }
} else {
    // Empty home — show a welcome message
    echo '<div class="jtb-home-welcome" style="text-align:center;padding:120px 20px;">';
    echo '<h1>Welcome to ' . esc(get_site_name()) . '</h1>';
    echo '<p style="opacity:0.7;margin-top:12px;">Build your homepage with Jessie Theme Builder.</p>';
    echo '<a href="/admin/jessie-theme-builder" style="display:inline-block;margin-top:24px;padding:12px 28px;background:var(--jtb-primary,#7c3aed);color:#fff;border-radius:8px;text-decoration:none;">Open Theme Builder</a>';
    echo '</div>';
}
