<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/sites.php';

if (!function_exists('sites_bootstrap_current_site')) {
    function sites_bootstrap_current_site()
    {
        global $CMS_CURRENT_SITE;

        if (isset($CMS_CURRENT_SITE)) {
            return $CMS_CURRENT_SITE;
        }

        $site = sites_resolve_current();
        $CMS_CURRENT_SITE = $site;

        if ($site !== null && is_array($site)) {
            if (!defined('CMS_SITE_ID') && !empty($site['id']) && is_string($site['id'])) {
                define('CMS_SITE_ID', (string)$site['id']);
            }
            if (!defined('CMS_SITE_LOCALE') && !empty($site['locale']) && is_string($site['locale'])) {
                define('CMS_SITE_LOCALE', (string)$site['locale']);
            }
            if (!defined('CMS_SITE_DOMAIN') && !empty($site['domain']) && is_string($site['domain'])) {
                define('CMS_SITE_DOMAIN', (string)$site['domain']);
            }
        }

        return $CMS_CURRENT_SITE;
    }
}
