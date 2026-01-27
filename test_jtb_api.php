<?php
define("CMS_ROOT", "/var/www/cms");
require_once CMS_ROOT . "/config.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-element.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-registry.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-fields.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-renderer.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-settings.php";
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/class-jtb-builder.php";

JessieThemeBuilder\JTB_Registry::init();
JessieThemeBuilder\JTB_Fields::init();

$files = glob(CMS_ROOT . "/plugins/jessie-theme-builder/modules/*/*.php");
foreach ($files as $f) {
    require_once $f;
}

$modules = [];
$instances = JessieThemeBuilder\JTB_Registry::getInstances();
foreach ($instances as $slug => $module) {
    $modules[$slug] = [
        "slug" => $slug,
        "name" => $module->getName(),
        "icon" => $module->icon,
        "category" => $module->category,
        "fields_count" => count($module->getContentFields())
    ];
}

echo "Modules count: " . count($modules) . "\n";
echo "First 5 modules:\n";
$i = 0;
foreach ($modules as $slug => $m) {
    if ($i >= 5) break;
    $i++;
    echo "  - " . $slug . ": " . $m["name"] . " (" . $m["category"] . ") - " . $m["fields_count"] . " fields\n";
}

// Test blurb module fields
if (isset($instances['blurb'])) {
    echo "\nBlurb module content fields:\n";
    $fields = $instances['blurb']->getContentFields();
    foreach (array_slice(array_keys($fields), 0, 5) as $key) {
        $f = $fields[$key];
        echo "  - $key: type=" . ($f['type'] ?? 'unknown') . "\n";
    }
}
