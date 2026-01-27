<?php
define("CMS_ROOT", "/var/www/cms");
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-element.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-registry.php";
\ = glob(CMS_ROOT . "/plugins/jessie-theme-builder/modules/content/*.php");
foreach (\ as \) {
    require_once \;
    echo "OK: " . basename(\) . "\n";
}
