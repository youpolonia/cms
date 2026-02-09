<?php
/**
 * CMS Admin Toolbar
 * 
 * Displays a WordPress-style admin bar at the top of front-end pages
 * when an admin user is logged in. Injected automatically by render_with_theme().
 */

/**
 * Check if current user is logged in admin
 */
function cms_is_admin_logged_in(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        return false;
    }
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role']);
}

/**
 * Generate admin toolbar HTML + CSS
 * 
 * @param array $context ['page_id' => int|null, 'page_title' => string, 'type' => 'page'|'article'|'home']
 * @return string HTML to inject after <body>
 */
function cms_admin_toolbar(array $context = []): string {
    if (!cms_is_admin_logged_in()) {
        return '';
    }
    
    $pageId = $context['page_id'] ?? null;
    $pageTitle = $context['page_title'] ?? '';
    $type = $context['type'] ?? 'page';
    $adminName = $_SESSION['admin_username'] ?? $_SESSION['admin_email'] ?? 'Admin';
    $siteName = '';
    
    // Try to get site name
    if (function_exists('get_site_name')) {
        $siteName = get_site_name();
    } elseif (function_exists('get_setting')) {
        $siteName = get_setting('site_name') ?? 'CMS';
    }
    
    // Build edit URL based on type
    $editUrl = '';
    $editLabel = '';
    if ($pageId && $type === 'page') {
        $editUrl = "/admin/pages/{$pageId}/edit";
        $editLabel = 'Edit Page';
    } elseif ($pageId && $type === 'article') {
        $editUrl = "/admin/articles/{$pageId}/edit";
        $editLabel = 'Edit Article';
    }
    
    // Escape helper
    $e = function($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    
    $html = <<<HTML
<!-- CMS Admin Toolbar -->
<style>
#cms-admin-toolbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 36px;
    background: #1e1e2e;
    color: #cdd6f4;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 13px;
    display: flex;
    align-items: center;
    padding: 0 16px;
    z-index: 99999;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    gap: 0;
}
#cms-admin-toolbar * {
    box-sizing: border-box;
}
#cms-admin-toolbar a {
    color: #cdd6f4;
    text-decoration: none;
    padding: 0 12px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
#cms-admin-toolbar a:hover {
    background: #313244;
    color: #f5f5f5;
}
#cms-admin-toolbar .tb-logo {
    font-weight: 700;
    font-size: 13px;
    padding: 0 14px 0 4px;
    color: #89b4fa;
    gap: 8px;
}
#cms-admin-toolbar .tb-logo:hover {
    color: #b4d0fb;
}
#cms-admin-toolbar .tb-logo svg {
    width: 18px;
    height: 18px;
}
#cms-admin-toolbar .tb-sep {
    width: 1px;
    height: 18px;
    background: #45475a;
    flex-shrink: 0;
}
#cms-admin-toolbar .tb-edit {
    color: #a6e3a1;
    font-weight: 600;
}
#cms-admin-toolbar .tb-edit:hover {
    background: rgba(166,227,161,0.12);
    color: #b8f0b3;
}
#cms-admin-toolbar .tb-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0;
}
#cms-admin-toolbar .tb-new-menu {
    position: relative;
}
#cms-admin-toolbar .tb-new-menu:hover .tb-dropdown {
    display: block;
}
#cms-admin-toolbar .tb-dropdown {
    display: none;
    position: absolute;
    top: 36px;
    left: 0;
    background: #1e1e2e;
    border: 1px solid #45475a;
    border-radius: 0 0 6px 6px;
    min-width: 160px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
#cms-admin-toolbar .tb-dropdown a {
    display: flex;
    padding: 8px 14px;
    height: auto;
    font-size: 12.5px;
}
#cms-admin-toolbar .tb-dropdown a:hover {
    background: #313244;
}
#cms-admin-toolbar .tb-user {
    color: #a6adc8;
    font-size: 12px;
    padding: 0 8px;
}
#cms-admin-toolbar .tb-logout {
    color: #f38ba8;
    font-size: 12px;
}
#cms-admin-toolbar .tb-logout:hover {
    background: rgba(243,139,168,0.1);
}
body.has-admin-toolbar {
    padding-top: 36px !important;
}
/* Fix for themes with fixed headers */
body.has-admin-toolbar .site-header,
body.has-admin-toolbar header,
body.has-admin-toolbar [class*="header"],
body.has-admin-toolbar nav:first-of-type {
    top: 36px !important;
}
@media (max-width: 768px) {
    #cms-admin-toolbar .tb-hide-mobile {
        display: none;
    }
}
</style>
<div id="cms-admin-toolbar">
    <a href="/admin/dashboard" class="tb-logo" title="Dashboard">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        {$e($siteName ?: 'Jessie CMS')}
    </a>
    <span class="tb-sep"></span>
HTML;
    
    // Edit button (if we have a page/article)
    if ($editUrl) {
        $html .= <<<HTML
    <a href="{$e($editUrl)}" class="tb-edit" title="Edit this {$type}">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        {$e($editLabel)}
    </a>
    <span class="tb-sep"></span>
HTML;
    }
    
    // New content dropdown
    $html .= <<<HTML
    <div class="tb-new-menu">
        <a href="#" onclick="return false">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New
        </a>
        <div class="tb-dropdown">
            <a href="/admin/pages/create">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Page
            </a>
            <a href="/admin/articles/create">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Article
            </a>
            <a href="/admin/media/upload">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Media
            </a>
        </div>
    </div>
    <span class="tb-sep tb-hide-mobile"></span>
    <a href="/admin/pages" class="tb-hide-mobile">Pages</a>
    <a href="/admin/articles" class="tb-hide-mobile">Articles</a>
    <a href="/admin/themes" class="tb-hide-mobile">Themes</a>

    <div class="tb-right">
        <span class="tb-user tb-hide-mobile">👤 {$e($adminName)}</span>
        <a href="/admin/logout" class="tb-logout">Logout</a>
    </div>
</div>
<script>document.body.classList.add('has-admin-toolbar');</script>
<!-- /CMS Admin Toolbar -->
HTML;

    return $html;
}
