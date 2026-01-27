<?php
/**
 * One-time migration: Add header/footer to Layout Library presets
 * Run via browser: /includes/migrations/migrate_layout_headers_footers.php
 * DEV_MODE required
 */

define('CMS_ROOT', dirname(dirname(__DIR__)));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    die('DEV_MODE required');
}

header('Content-Type: text/plain');

$db = \core\Database::connection();

// Mapping: layout_library_id => [header_template_id, footer_template_id]
$mappings = [
    9 => ['header' => 3, 'footer' => 4],   // Golden Plate
    12 => ['header' => 6, 'footer' => 5],  // Edi's Paving
];

$results = [];

foreach ($mappings as $layoutId => $templateIds) {
    echo "Processing layout ID {$layoutId}...\n";

    // Get current layout content
    $stmt = $db->prepare("SELECT name, content_json FROM tb_layout_library WHERE id = ?");
    $stmt->execute([$layoutId]);
    $layout = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$layout) {
        echo "  ERROR: Layout {$layoutId} not found\n";
        continue;
    }

    $content = json_decode($layout['content_json'], true);
    if (!$content) {
        echo "  ERROR: Invalid JSON in layout {$layoutId}\n";
        continue;
    }

    $updated = false;

    // Get and add header
    if (!empty($templateIds['header'])) {
        $stmt = $db->prepare("SELECT content_json FROM tb_site_templates WHERE id = ?");
        $stmt->execute([$templateIds['header']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($template) {
            $headerContent = json_decode($template['content_json'], true);
            if ($headerContent) {
                $content['header'] = $headerContent;
                $updated = true;
                echo "  Added header from template ID {$templateIds['header']}\n";
            }
        }
    }

    // Get and add footer
    if (!empty($templateIds['footer'])) {
        $stmt = $db->prepare("SELECT content_json FROM tb_site_templates WHERE id = ?");
        $stmt->execute([$templateIds['footer']]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($template) {
            $footerContent = json_decode($template['content_json'], true);
            if ($footerContent) {
                $content['footer'] = $footerContent;
                $updated = true;
                echo "  Added footer from template ID {$templateIds['footer']}\n";
            }
        }
    }

    // Save updated content
    if ($updated) {
        $newJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt = $db->prepare("UPDATE tb_layout_library SET content_json = ? WHERE id = ?");
        $stmt->execute([$newJson, $layoutId]);
        echo "  SAVED: {$layout['name']}\n";
        $results[] = $layout['name'];
    } else {
        echo "  SKIPPED: No changes for {$layout['name']}\n";
    }
}

echo "\n=== MIGRATION COMPLETE ===\n";
echo "Updated layouts: " . count($results) . "\n";
foreach ($results as $name) {
    echo "  - {$name}\n";
}
