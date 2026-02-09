<?php
/**
 * Admin Navigation — redirect to Menu Manager
 * 
 * Navigation/menu management is handled by MenusController (MVC).
 * Routes: /admin/menus, /admin/menus/{id}/items, etc.
 * This file redirects from the legacy /admin/navigation URL.
 */
header('Location: /admin/menus');
exit;
