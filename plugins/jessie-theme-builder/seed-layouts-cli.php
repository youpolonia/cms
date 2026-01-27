<?php
/**
 * CLI script to seed layout gallery
 */

define('CMS_ROOT', '/var/www/cms');
require_once CMS_ROOT . '/core/bootstrap.php';
require_once __DIR__ . '/includes/class-jtb-layout-gallery.php';

\JessieThemeBuilder\JTB_Layout_Gallery::createTables();
echo "Layout gallery tables created and seeded.\n";

// Count layouts
$db = \core\Database::connection();
$stmt = $db->query("SELECT COUNT(*) as cnt FROM jtb_layouts");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total layouts: " . $row['cnt'] . "\n";
