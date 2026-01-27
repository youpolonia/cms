<?php
// Ordered list of migration file basenames. Earlier = runs earlier.
// Framework-free: only execute(PDO $db): bool is allowed in migration classes.
return [
    '0000_example_noop.php',
    '0001_create_security_tables.php',
    '0002_create_analytics_tables.php',
    '0003_create_seo_tables.php',
    '0004_upgrade_menus_tables.php',
    '0005_upgrade_content_blocks_table.php',
    '0006_create_theme_builder_tables.php',
];
