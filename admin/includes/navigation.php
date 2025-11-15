<?php
if (!function_exists('renderAdminNavigation')) {
    function renderAdminNavigation(): void {
        echo '<nav class="container" style="margin:12px 0;display:flex;gap:12px;flex-wrap:wrap">';
        $links = [
            ['Dashboard','/admin/index.php'],
            ['AI Content','/admin/ai-content-creator.php'],
            ['SEO','/admin/seo.php'],
            ['Scheduler','/admin/scheduler.php'],
            ['Builder','/admin/theme-builder.php'],
            ['Builder (AI)','/admin/ai-theme-builder.php'],
            ['Articles','/admin/articles.php'],
            ['Pages','/admin/pages.php'],
            ['Categories','/admin/categories.php'],
            ['Comments','/admin/comments.php'],
            ['Galleries','/admin/galleries.php'],
            ['Search','/admin/search.php'],
            ['Users','/admin/users.php'],
            ['Modules','/admin/modules.php'],
            ['Menus','/admin/menus.php'],
            ['Widgets','/admin/widgets.php'],
            ['URLs','/admin/urls.php'],
            ['Logs','/admin/logs.php'],
            ['Maintenance','/admin/maintenance.php'],
            ['Backup','/admin/backup.php']
        ];
        $current = $_SERVER['SCRIPT_NAME'] ?? '';
        foreach ($links as $l) {
            $href = htmlspecialchars($l[1]);
            $text = htmlspecialchars($l[0]);
            $active = (strpos($current, basename($href)) !== false) ? ' class="active"' : '';
            echo "<a$active href=\"$href\">$text</a>";
        }
        echo '</nav>';
    }
}
renderAdminNavigation();
