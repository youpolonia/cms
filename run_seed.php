<?php
// Run library seed via CLI
define('CMS_ROOT', '/var/www/cms');

// Load CMS core
require_once CMS_ROOT . '/core/bootstrap.php';

// Load JTB classes
$jtbPath = CMS_ROOT . '/plugins/jessie-theme-builder';
require_once $jtbPath . '/includes/class-jtb-layout-library.php';
require_once $jtbPath . '/includes/class-jtb-library.php';
require_once $jtbPath . '/includes/class-jtb-library-seeder.php';

// Ensure tables exist
if (!JessieThemeBuilder\JTB_Library::tablesExist()) {
    JessieThemeBuilder\JTB_Library::createTables();
    echo "Tables created\n";
}

// Force reseed (delete old premade and seed again)
$count = JessieThemeBuilder\JTB_Library_Seeder::reseed();
echo "Reseeded: $count templates\n";

// Verify
$templates = JessieThemeBuilder\JTB_Library::getAll();
echo "\nTemplates in library:\n";
foreach ($templates as $t) {
    echo "- {$t['name']} ({$t['template_type']}) - {$t['category_slug']}\n";
}
