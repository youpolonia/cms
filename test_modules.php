<?php
define("CMS_ROOT", "/var/www/cms");
$pluginPath = CMS_ROOT . "/plugins/jessie-theme-builder";
require_once $pluginPath . "/includes/class-jtb-element.php";
require_once $pluginPath . "/includes/class-jtb-registry.php";
require_once $pluginPath . "/includes/class-jtb-fields.php";
JessieThemeBuilder\JTB_Registry::init();
$modulesPath = $pluginPath . "/modules";
$moduleCategories = array("structure", "content", "theme");
foreach ($moduleCategories as $category) {
    $categoryPath = $modulesPath . "/" . $category;
    if (is_dir($categoryPath)) {
        foreach (glob($categoryPath . "/*.php") as $moduleFile) {
            require_once $moduleFile;
        }
    }
}
$all = JessieThemeBuilder\JTB_Registry::getInstances();
echo "Total: " . count($all) . "\n";
foreach ($all as $s => $i) {
    echo $s . " (" . $i->category . ")\n";
}
