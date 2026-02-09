<?php
/**
 * CLI script to reseed library templates
 */

define('CMS_ROOT', '/var/www/cms');
require_once CMS_ROOT . '/core/bootstrap.php';
require_once __DIR__ . '/includes/class-jtb-layout-library.php';
require_once __DIR__ . '/includes/class-jtb-library.php';
require_once __DIR__ . '/includes/class-jtb-library-seeder.php';

// Ensure tables exist
\JessieThemeBuilder\JTB_Library::createTables();

// Delete existing premade templates
$db = \core\Database::connection();
$db->exec("DELETE FROM jtb_library_templates WHERE is_premade = 1");

// Re-seed
$count = \JessieThemeBuilder\JTB_Library_Seeder::seed();
echo "Re-seeded $count templates\n";
