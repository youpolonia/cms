<?php
/**
 * CLI script to seed library templates
 */

define('CMS_ROOT', '/var/www/cms');
require_once CMS_ROOT . '/core/bootstrap.php';
require_once __DIR__ . '/includes/class-jtb-library.php';
require_once __DIR__ . '/includes/class-jtb-library-seeder.php';

\JessieThemeBuilder\JTB_Library::createTables();
$count = \JessieThemeBuilder\JTB_Library_Seeder::seed();
echo "Seeded $count templates\n";
