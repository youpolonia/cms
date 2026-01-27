<?php
chdir("/var/www/cms");
require_once "/var/www/cms/core/bootstrap.php";
if (!defined("CMS_ROOT")) define("CMS_ROOT", "/var/www/cms");

$pluginPath = "/var/www/cms/plugins/jessie-theme-builder";
require_once $pluginPath . "/includes/class-jtb-element.php";
require_once $pluginPath . "/includes/class-jtb-registry.php";
require_once $pluginPath . "/includes/class-jtb-fonts.php";
require_once $pluginPath . "/includes/class-jtb-css-generator.php";
require_once $pluginPath . "/includes/class-jtb-renderer.php";
require_once $pluginPath . "/includes/class-jtb-library.php";

$dirs = ["structure", "content", "interactive", "media", "forms", "blog", "fullwidth", "theme"];
foreach ($dirs as $d) {
    $p = $pluginPath . "/modules/" . $d;
    if (is_dir($p)) {
        foreach (glob($p . "/*.php") as $f) {
            require_once $f;
        }
    }
}

$template = JessieThemeBuilder\JTB_Library::get(21);
if ($template) {
    $content = is_string($template["content"]) ? json_decode($template["content"], true) : $template["content"];

    echo "=== Template Structure ===\n";
    echo "Top level keys: " . implode(", ", array_keys($content)) . "\n\n";

    if (isset($content["content"])) {
        echo "Sections count: " . count($content["content"]) . "\n\n";

        foreach ($content["content"] as $i => $section) {
            echo "SECTION $i: type=" . $section["type"] . "\n";
            $rows = $section["children"] ?? [];
            echo "  Rows: " . count($rows) . "\n";

            foreach ($rows as $ri => $row) {
                $cols = $row["attrs"]["columns"] ?? "?";
                $colCount = count($row["children"] ?? []);
                echo "  ROW $ri: columns='$cols', actual children=$colCount\n";

                foreach ($row["children"] ?? [] as $ci => $col) {
                    $modCount = count($col["children"] ?? []);
                    echo "    COL $ci: type=" . $col["type"] . ", modules=$modCount\n";
                }
            }
            echo "\n";
        }
    }

    // Now render and check HTML structure
    echo "\n=== Rendered HTML Structure ===\n";
    $html = JessieThemeBuilder\JTB_Renderer::render($content);

    // Find first row with 2 columns
    preg_match('/<div[^>]*class="jtb-row jtb-row-cols-1-2-1-2"[^>]*>(.*?)<\/section>/s', $html, $m);
    if ($m) {
        $rowHtml = $m[0];
        preg_match_all('/class="jtb-column"/', $rowHtml, $cols);
        echo "First 2-column row has " . count($cols[0]) . " columns\n";
    }

    // Save HTML for inspection
    file_put_contents("/var/www/cms/debug_output.html", $html);
    echo "\nFull HTML saved to /var/www/cms/debug_output.html\n";
}
